<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueueController extends Controller
{
    public function reset(Request $request)
    {
        try {
            // Reset all yesterday's queues to canceled status (not today's)
            $yesterday = now()->subDay()->toDateString();

            // Use a database transaction to ensure data consistency
            $resetCount = DB::transaction(function () use ($yesterday) {
                $queues = Queue::whereDate('created_at', $yesterday)
                    ->whereIn('status', ['waiting', 'called'])
                    ->get();

                $count = 0;
                foreach ($queues as $queue) {
                    // Validate the status transition before updating
                    if ($queue->isValidStatusTransition('canceled')) {
                        $queue->update(['status' => 'canceled']);
                        $count++;
                    }
                }

                return $count;
            });

            // Log the reset event
            $monitoringService = new \App\Services\QueueMonitoringService();
            $monitoringService->logQueueReset($resetCount, $yesterday);

            return redirect()->back()->with('success', "Antrian kemarin telah direset. {$resetCount} antrian dibatalkan.");
        } catch (\Exception $e) {
            Log::error('Error resetting queues: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Terjadi kesalahan saat mereset antrian. Silakan coba lagi.');
        }
    }
}
