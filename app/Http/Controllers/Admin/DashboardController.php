<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $today = now()->toDateString();

            // Cache static counts
            $totalServices = cache()->remember('total_services_count', 3600, function () {
                return Service::count();
            });

            $totalUsers = cache()->remember('total_users_count', 3600, function () {
                return User::count();
            });

            // Cache daily queue stats
            $queueStats = cache()->remember("daily_queue_stats_{$today}", 1800, function () use ($today) { // 30 minutes cache
                return Queue::selectRaw(
                    'COUNT(*) as total, ' .
                    'SUM(CASE WHEN status = "waiting" THEN 1 ELSE 0 END) as waiting, ' .
                    'SUM(CASE WHEN status = "done" THEN 1 ELSE 0 END) as done, ' .
                    'SUM(CASE WHEN status = "called" THEN 1 ELSE 0 END) as called, ' .
                    'SUM(CASE WHEN status = "canceled" THEN 1 ELSE 0 END) as canceled'
                )
                ->whereDate('created_at', $today)
                ->first();
            });

            $todayQueues = $queueStats->total ?? 0;
            $waitingQueues = $queueStats->waiting ?? 0;
            $todayCompleted = $queueStats->done ?? 0;
            $calledQueues = $queueStats->called ?? 0;
            $canceledQueues = $queueStats->canceled ?? 0;

            // Cache services with their queue counts
            $servicesWithQueues = cache()->remember("services_with_queues_{$today}", 1800, function () use ($today) {
                return Service::withCount(['queues' => function($query) use ($today) {
                    $query->whereDate('created_at', $today);
                }, 'queues as waiting_count' => function($query) use ($today) {
                    $query->where('status', 'waiting')->whereDate('created_at', $today);
                }, 'queues as called_count' => function($query) use ($today) {
                    $query->where('status', 'called')->whereDate('created_at', $today);
                }, 'queues as done_count' => function($query) use ($today) {
                    $query->where('status', 'done')->whereDate('created_at', $today);
                }, 'queues as canceled_count' => function($query) use ($today) {
                    $query->where('status', 'canceled')->whereDate('created_at', $today);
                }])->get();
            });

            // Cache additional statistics
            $stats = cache()->remember("daily_stats_{$today}", 1800, function () use ($today) {
                return Queue::selectRaw(
                    'AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg_wait_time, ' .
                    'COUNT(CASE WHEN status = "canceled" THEN 1 END) as canceled_count'
                )
                ->where('status', 'done')
                ->whereDate('created_at', $today)
                ->first();
            });

            $avgWaitTime = $stats ? ($stats->avg_wait_time ? round($stats->avg_wait_time, 1) : 0) : 0;
            $cancellationRate = $queueStats->total > 0 ? round(($stats->canceled_count / $queueStats->total) * 100, 2) : 0;

            // Cache peak hour data
            $peakHour = cache()->remember("peak_hour_{$today}", 1800, function () use ($today) {
                return Queue::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
                    ->whereDate('created_at', $today)
                    ->groupBy('hour')
                    ->orderByDesc('count')
                    ->first();
            });

            // Log system stats periodically (once per day per admin visit)
            $lastLogDate = cache()->get('last_system_stats_log', '');

            if ($lastLogDate !== $today) {
                $monitoringService = new \App\Services\QueueMonitoringService();
                $monitoringService->logSystemStats();
                cache()->put('last_system_stats_log', $today, now()->addDay());
            }

            return view('admin.dashboard', compact(
                'totalServices',
                'totalUsers',
                'todayQueues',
                'waitingQueues',
                'todayCompleted',
                'calledQueues',
                'canceledQueues',
                'servicesWithQueues',
                'avgWaitTime',
                'peakHour',
                'cancellationRate'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading admin dashboard: ' . $e->getMessage());

            // Return with default values in case of error
            return view('admin.dashboard', [
                'totalServices' => 0,
                'totalUsers' => 0,
                'todayQueues' => 0,
                'waitingQueues' => 0,
                'todayCompleted' => 0,
                'calledQueues' => 0,
                'canceledQueues' => 0,
                'servicesWithQueues' => collect([]),
                'avgWaitTime' => 0,
                'peakHour' => null,
                'cancellationRate' => 0
            ])->withErrors(['error' => 'Terjadi kesalahan saat memuat data dashboard.']);
        }
    }
}
