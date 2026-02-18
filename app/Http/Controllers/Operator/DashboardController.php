<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $today = now()->toDateString();

            // Cache services for 1 hour
            $services = cache()->remember('operator_services_list', 3600, function () {
                return Service::all();
            });

            $currentQueues = [];

            // Get current called queues for each service
            foreach ($services as $service) {
                $currentQueues[$service->id] = cache()->remember(
                    "current_called_queue_{$service->id}_{$today}",
                    300, // 5 minutes cache
                    function () use ($service, $today) {
                        return Queue::where('service_id', $service->id)
                            ->where('status', 'called')
                            ->whereDate('created_at', $today)
                            ->first();
                    }
                );
            }

            // Get today's completed queues for history (cached for 10 minutes)
            $todayHistory = cache()->remember(
                "today_completed_queues_{$today}",
                600, // 10 minutes cache
                function () use ($today) {
                    return Queue::where('status', 'done')
                        ->whereDate('created_at', $today)
                        ->with('service')
                        ->orderBy('updated_at', 'desc')
                        ->limit(10)
                        ->get();
                }
            );

            return view('operator.dashboard', compact('services', 'currentQueues', 'todayHistory'));
        } catch (\Exception $e) {
            Log::error('Error loading operator dashboard: ' . $e->getMessage());

            // Return with default values in case of error
            return view('operator.dashboard', [
                'services' => collect([]),
                'currentQueues' => [],
                'todayHistory' => collect([])
            ])->withErrors(['error' => 'Terjadi kesalahan saat memuat data dashboard.']);
        }
    }
}
