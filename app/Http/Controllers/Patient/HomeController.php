<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        try {
            return view('patient.home');
        } catch (\Exception $e) {
            \Log::error('Error loading patient home page: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat halaman utama.');
        }
    }

    public function services()
    {
        try {
            $today = now()->toDateString();

            // Cache services for 1 hour (simple approach to avoid cache tag issues)
            try {
                $services = Cache::remember('services_list', 3600, function () {
                    return Service::select('id', 'name', 'prefix')->get(); // Only select needed columns
                });
            } catch (\Exception $e) {
                // If there's any issue with cache, fallback to direct query
                $services = Service::select('id', 'name', 'prefix')->get();
            }

            // Add queue counts to each service with caching
            foreach ($services as $service) {
                try {
                    $service->waiting_count = Cache::remember(
                        "service_{$service->id}_waiting_count_{$today}",
                        300, // 5 minutes cache
                        function () use ($service) {
                            return \App\Models\Queue::where('service_id', $service->id)
                                ->where('status', 'waiting')
                                ->whereDate('created_at', now()->toDateString())
                                ->count();
                        }
                    );
                } catch (\Exception $e) {
                    // If there's any issue with cache, fallback to direct query
                    $service->waiting_count = \App\Models\Queue::where('service_id', $service->id)
                        ->where('status', 'waiting')
                        ->whereDate('created_at', now()->toDateString())
                        ->count();
                }
            }

            return view('patient.services', compact('services'));
        } catch (\Exception $e) {
            \Log::error('Error loading patient services: ' . $e->getMessage());

            return view('patient.services', [
                'services' => collect([])
            ])->withErrors(['error' => 'Terjadi kesalahan saat memuat daftar layanan.']);
        }
    }

    public function generateQueue(Request $request)
    {
        \DB::beginTransaction();

        try {
            $validatedData = $request->validate([
                'service_id' => 'required|exists:services,id',
            ]);

            $serviceId = $validatedData['service_id'];
            $ipAddress = $request->ip(); // Get the user's IP address

            // Get the service to check if it has reached daily limit
            $service = \App\Models\Service::findOrFail($serviceId);

            // Check if user can take a queue for this service today based on business rules
            if (!\App\Models\Queue::userCanTakeQueueForService($ipAddress, $serviceId)) {
                \DB::rollback();

                // Get the user's latest queue to show details to the user
                $existingQueue = \App\Models\Queue::getUserLatestQueueForService($ipAddress, $serviceId);

                // Provide different messages based on the status of the existing queue
                $statusMessages = [
                    'waiting' => 'masih menunggu dipanggil',
                    'called' => 'sedang dipanggil',
                    'done' => 'sudah selesai dilayani (kontak admin jika ingin ambil antrian baru)',
                    'canceled' => 'dibatalkan'
                ];

                $statusMessage = $statusMessages[$existingQueue->status] ?? 'dalam status tidak diketahui';

                return redirect()->back()
                                 ->with('error', "Anda sudah mengambil antrian untuk layanan {$service->name} dengan nomor {$existingQueue->number} hari ini (status: {$statusMessage}). Anda hanya bisa mengambil antrian baru setelah antrian sebelumnya selesai atau dibatalkan.")
                                 ->withInput();
            }

            // Check if service has reached daily queue limit
            if ($service->hasReachedDailyLimit()) {
                \DB::rollback();
                return redirect()->back()
                                 ->with('error', 'Layanan ini telah mencapai batas maksimum antrian harian. Silakan kembali besok.')
                                 ->withInput();
            }

            // Rate limiting: Prevent spam queue generation
            $recentQueuesCount = \App\Models\Queue::where('service_id', $serviceId)
                ->where('status', 'waiting')
                ->where('created_at', '>=', now()->subMinutes(5))
                ->count();

            if ($recentQueuesCount > 10) { // More than 10 queues in 5 minutes
                \DB::rollback();
                return redirect()->back()
                                 ->with('error', 'Terlalu banyak permintaan antrian dalam waktu singkat. Silakan coba beberapa saat lagi.')
                                 ->withInput();
            }

            // Generate new queue number (this will use lockForUpdate to prevent race conditions)
            $queueNumber = \App\Models\Queue::generateNextNumber($serviceId);

            // Create new queue record with IP address
            $queue = \App\Models\Queue::create([
                'number' => $queueNumber,
                'service_id' => $serviceId,
                'status' => 'waiting',
                'ip_address' => $ipAddress, // Store the IP address
            ]);

            \DB::commit();

            // Clear the services list cache and service-specific cache to ensure accurate queue counts
            Cache::forget('services_list');
            Cache::forget("service_{$serviceId}_waiting_count_" . now()->toDateString());

            // Log the queue creation
            $monitoringService = new \App\Services\QueueMonitoringService();
            $monitoringService->logQueueCreation($queue);

            return redirect()->route('queue.status', ['number' => $queue->number])
                             ->with('success', 'Nomor antrian berhasil dibuat!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \DB::rollback();

            // Log validation errors
            $monitoringService = new \App\Services\QueueMonitoringService();
            $monitoringService->logError('Validation error generating queue: ' . $e->getMessage(), [
                'service_id' => $request->input('service_id'),
                'ip_address' => $request->ip(),
                'timestamp' => now()->toISOString(),
            ]);

            return redirect()->back()
                             ->with('error', 'Data yang dimasukkan tidak valid.')
                             ->withInput();
        } catch (\InvalidArgumentException $e) {
            \DB::rollback();

            // Log the specific error with more context
            $monitoringService = new \App\Services\QueueMonitoringService();
            $monitoringService->logError('Invalid argument error generating queue: ' . $e->getMessage(), [
                'service_id' => $request->input('service_id'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'timestamp' => now()->toISOString(),
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                             ->with('error', 'Format nomor antrian tidak valid.')
                             ->withInput();
        } catch (\OverflowException $e) {
            \DB::rollback();

            // Log the overflow error with more context
            $monitoringService = new \App\Services\QueueMonitoringService();
            $monitoringService->logError('Queue number overflow error: ' . $e->getMessage(), [
                'service_id' => $request->input('service_id'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'timestamp' => now()->toISOString(),
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                             ->with('error', 'Nomor antrian telah mencapai batas maksimum. Silakan hubungi administrator.')
                             ->withInput();
        } catch (\Exception $e) {
            \DB::rollback();

            // Log the general error with more context
            $monitoringService = new \App\Services\QueueMonitoringService();
            $monitoringService->logError('General error generating queue: ' . $e->getMessage(), [
                'service_id' => $request->input('service_id'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'timestamp' => now()->toISOString(),
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            // Provide user-friendly error message
            return redirect()->back()
                             ->with('error', 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.')
                             ->withInput();
        }
    }

    public function queueStatus($number)
    {
        try {
            $today = now()->toDateString();

            // Validate the queue number format
            if (!preg_match('/^[A-Z0-9]+\.\d+$/', $number)) {
                return redirect()->route('patient.home')
                                 ->with('error', 'Nomor antrian tidak valid.');
            }

            // Cache the queue lookup for 2 minutes
            $cacheKey = "queue_status_{$number}_{$today}";
            $queue = cache()->remember($cacheKey, 120, function () use ($number) {
                return \App\Models\Queue::where('number', $number)
                                      ->with('service:id,name') // Only load needed fields from service
                                      ->first();
            });

            if (!$queue) {
                return redirect()->route('patient.home')
                                 ->with('error', 'Nomor antrian tidak ditemukan.');
            }

            // Calculate people ahead in queue with caching
            $peopleAheadCacheKey = "people_ahead_{$queue->id}_{$today}";
            $peopleAhead = cache()->remember($peopleAheadCacheKey, 60, function () use ($queue) {
                return \App\Models\Queue::where('service_id', $queue->service_id)
                    ->where('status', 'waiting')
                    ->where('number', '<', $queue->number)
                    ->whereDate('created_at', now()->toDateString())
                    ->count();
            });

            // Add the calculated value to the queue object
            $queue->people_ahead = $peopleAhead;

            return view('patient.status', compact('queue'));
        } catch (\Exception $e) {
            \Log::error('Error loading queue status: ' . $e->getMessage());

            return redirect()->route('patient.home')
                             ->with('error', 'Terjadi kesalahan saat memuat status antrian.');
        }
    }
}
