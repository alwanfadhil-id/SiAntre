<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessQueueOperation;
use App\Models\Service;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class QueueController extends Controller
{
    public function index(Service $service, Request $request)
    {
        try {
            // Authorize that the operator can access this service
            if (!$this->userCanAccessService($service->id)) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses ke layanan ini.');
            }

            $today = now()->toDateString();

            // Validate the queue number if provided
            $queueNumber = null;
            if ($request->has('queue_number') && !empty($request->queue_number)) {
                $queueNumber = trim($request->queue_number);
                // Additional validation could be added here if needed
            }

            // Determine cache key based on filters
            $cacheKey = "operator_queues_{$service->id}_{$today}";
            if ($queueNumber) {
                $cacheKey .= "_search_" . $queueNumber;
            }

            // Cache the queues data for 5 minutes
            $queues = cache()->remember($cacheKey, 300, function () use ($service, $queueNumber, $today) {
                $query = Queue::where('service_id', $service->id)
                    ->whereDate('created_at', $today)
                    ->orderBy('number');

                // Apply search filter if queue number is provided
                if ($queueNumber) {
                    $query = $query->where('number', $queueNumber);
                }

                return $query->get();
            });

            // Cache the next queue for 2 minutes (since it changes frequently)
            $nextQueue = cache()->remember("next_queue_{$service->id}_{$today}", 120, function () use ($service, $today) {
                return Queue::where('service_id', $service->id)
                    ->where('status', 'waiting')
                    ->whereDate('created_at', $today)
                    ->orderBy('number')
                    ->limit(1) // Only get the first one
                    ->first();
            });

            // Cache the currently called queue for 2 minutes
            $currentQueue = cache()->remember("current_queue_{$service->id}_{$today}", 120, function () use ($service, $today) {
                return Queue::where('service_id', $service->id)
                    ->where('status', 'called')
                    ->whereDate('created_at', $today)
                    ->limit(1) // Only get one record
                    ->first();
            });

            return view('operator.queues', compact('service', 'queues', 'nextQueue', 'currentQueue'));
        } catch (\Exception $e) {
            \Log::error('Error loading operator queues: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat antrian layanan.');
        }
    }

    public function call(Queue $queue)
    {
        // Authorize that the operator can manage this service
        if (!$this->userCanAccessService($queue->service_id)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke layanan ini.');
        }

        \DB::beginTransaction();

        try {
            // Validate that the queue is in 'waiting' status before calling
            if ($queue->status !== 'waiting') {
                \DB::rollback();
                return redirect()->back()->with('error', 'Hanya antrian dengan status "waiting" yang bisa dipanggil.');
            }

            // Check if there's already a queue in 'called' status for this service
            $currentlyCalled = Queue::where('service_id', $queue->service_id)
                ->where('status', 'called')
                ->whereDate('created_at', now()->toDateString())
                ->lockForUpdate() // Lock to prevent race conditions
                ->first();

            if ($currentlyCalled) {
                // There's already a called queue - warn the operator
                \DB::rollback();
                return redirect()->back()->with('error', 'Masih ada antrian yang sedang dipanggil. Selesaikan atau batalkan antrian tersebut terlebih dahulu.');
            }

            // Validate the status transition
            if (!$queue->isValidStatusTransition('called')) {
                \DB::rollback();
                return redirect()->back()->with('error', 'Status antrian tidak dapat diubah menjadi dipanggil dari status saat ini.');
            }

            // Update the specified queue to 'called'
            $oldStatus = $queue->status;
            $queue->update(['status' => 'called']);

            \DB::commit();

            // Dispatch the job to handle any background processing
            ProcessQueueOperation::dispatch($queue->id, 'call');

            // Clear the relevant caches to ensure fresh data is loaded on next request
            $this->clearOperatorCaches($queue->service_id);

            // Log the status change
            $monitoringService = new \App\Services\QueueMonitoringService();
            $monitoringService->logQueueStatusChange($queue, $oldStatus, 'called');

            return redirect()->back()->with('success', 'Antrian dipanggil!');
        } catch (\Exception $e) {
            \DB::rollback();

            // Log the error with more context
            $monitoringService = new \App\Services\QueueMonitoringService();
            $monitoringService->logError('Error calling queue: ' . $e->getMessage(), [
                'queue_id' => $queue->id,
                'user_id' => auth()->id(),
                'user_name' => auth()->user()?->name,
                'service_id' => $queue->service_id,
                'original_status' => $queue->status,
                'attempted_status' => 'called',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
                'timestamp' => now()->toISOString(),
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            // Provide user-friendly error message
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat memanggil antrian. Silakan coba lagi atau hubungi administrator.');
        }
    }

    public function done(Queue $queue)
    {
        // Authorize that the operator can manage this service
        if (!$this->userCanAccessService($queue->service_id)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke layanan ini.');
        }

        $serviceId = $queue->service_id;

        \DB::beginTransaction();

        try {
            // Validate status transition
            if (!$queue->isValidStatusTransition('done')) {
                \DB::rollback();
                return redirect()->back()->with('error', 'Status antrian tidak dapat diubah menjadi selesai dari status saat ini.');
            }

            $oldStatus = $queue->status;
            $queue->update(['status' => 'done']);

            \DB::commit();

            // Dispatch the job to handle any background processing
            ProcessQueueOperation::dispatch($queue->id, 'done');

            // Clear the relevant caches to ensure fresh data is loaded on next request
            $this->clearOperatorCaches($serviceId);

            // Log the status change
            $monitoringService = new \App\Services\QueueMonitoringService();
            $monitoringService->logQueueStatusChange($queue, $oldStatus, 'done');

            return redirect()->back()->with('success', 'Status antrian diubah menjadi selesai!');
        } catch (\Exception $e) {
            \DB::rollback();

            // Log the error with more context
            $monitoringService = new \App\Services\QueueMonitoringService();
            $monitoringService->logError('Error marking queue as done: ' . $e->getMessage(), [
                'queue_id' => $queue->id,
                'user_id' => auth()->id(),
                'user_name' => auth()->user()?->name,
                'service_id' => $queue->service_id,
                'original_status' => $queue->status,
                'attempted_status' => 'done',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
                'timestamp' => now()->toISOString(),
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            // Provide user-friendly error message
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat menandai antrian selesai. Silakan coba lagi atau hubungi administrator.');
        }
    }

    public function cancel(Queue $queue)
    {
        // Authorize that the operator can manage this service
        if (!$this->userCanAccessService($queue->service_id)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke layanan ini.');
        }

        $serviceId = $queue->service_id;

        \DB::beginTransaction();

        try {
            // Validate status transition
            if (!$queue->isValidStatusTransition('canceled')) {
                \DB::rollback();
                return redirect()->back()->with('error', 'Status antrian tidak dapat diubah menjadi dibatalkan dari status saat ini.');
            }

            $oldStatus = $queue->status;
            $queue->update(['status' => 'canceled']);

            \DB::commit();

            // Dispatch the job to handle any background processing
            ProcessQueueOperation::dispatch($queue->id, 'cancel');

            // Clear the relevant caches to ensure fresh data is loaded on next request
            $this->clearOperatorCaches($serviceId);

            // Log the status change
            $monitoringService = new \App\Services\QueueMonitoringService();
            $monitoringService->logQueueStatusChange($queue, $oldStatus, 'canceled');

            return redirect()->back()->with('success', 'Status antrian diubah menjadi dibatalkan!');
        } catch (\Exception $e) {
            \DB::rollback();

            // Log the error with more context
            $monitoringService = new \App\Services\QueueMonitoringService();
            $monitoringService->logError('Error canceling queue: ' . $e->getMessage(), [
                'queue_id' => $queue->id,
                'user_id' => auth()->id(),
                'user_name' => auth()->user()?->name,
                'service_id' => $queue->service_id,
                'original_status' => $queue->status,
                'attempted_status' => 'canceled',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
                'timestamp' => now()->toISOString(),
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            // Provide user-friendly error message
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat membatalkan antrian. Silakan coba lagi atau hubungi administrator.');
        }
    }

    /**
     * Check if the current user can access a specific service
     */
    private function userCanAccessService($serviceId)
    {
        // For now, we'll allow access if the service exists
        // In a more complex system, you might implement service assignment logic
        // For example, check if the user is assigned to manage this specific service
        try {
            $service = \App\Models\Service::findOrFail($serviceId);
            // In a more advanced system, you would check if the user has permission to access this service
            // For now, we'll allow access to all services for operators
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Clear operator interface caches for a specific service
     */
    private function clearOperatorCaches($serviceId)
    {
        $today = now()->toDateString();

        // Clear the cached data for this service
        cache()->forget("operator_queues_{$serviceId}_{$today}");
        cache()->forget("next_queue_{$serviceId}_{$today}");
        cache()->forget("current_queue_{$serviceId}_{$today}");
    }
}
