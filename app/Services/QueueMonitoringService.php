<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\Queue;

class QueueMonitoringService
{
    /**
     * Log queue creation event
     */
    public function logQueueCreation($queue)
    {
        try {
            Log::channel('daily')->info('Queue Created', [
                'queue_id' => $queue->id,
                'number' => $queue->number,
                'service_id' => $queue->service_id,
                'service_name' => $queue->service->name ?? 'Unknown',
                'timestamp' => now()->toISOString(),
                'ip_address' => request()?->ip() ?? 'unknown',
                'user_agent' => request()?->userAgent() ?? 'unknown',
            ]);
        } catch (\Exception $e) {
            // Fail silently to avoid breaking the main flow
            \Log::error('Failed to log queue creation: ' . $e->getMessage());
        }
    }

    /**
     * Log queue status change event
     */
    public function logQueueStatusChange($queue, $oldStatus, $newStatus)
    {
        try {
            Log::channel('daily')->info('Queue Status Changed', [
                'queue_id' => $queue->id,
                'number' => $queue->number,
                'service_id' => $queue->service_id,
                'service_name' => $queue->service->name ?? 'Unknown',
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'timestamp' => now()->toISOString(),
                'ip_address' => request()?->ip() ?? 'unknown',
                'user_agent' => request()?->userAgent() ?? 'unknown',
            ]);
        } catch (\Exception $e) {
            // Fail silently to avoid breaking the main flow
            \Log::error('Failed to log queue status change: ' . $e->getMessage());
        }
    }

    /**
     * Log queue reset event
     */
    public function logQueueReset($count, $date)
    {
        try {
            Log::channel('daily')->info('Queue Reset Executed', [
                'count' => $count,
                'date' => $date,
                'timestamp' => now()->toISOString(),
                'ip_address' => request()?->ip() ?? 'unknown',
                'user_agent' => request()?->userAgent() ?? 'unknown',
            ]);
        } catch (\Exception $e) {
            // Fail silently to avoid breaking the main flow
            \Log::error('Failed to log queue reset: ' . $e->getMessage());
        }
    }

    /**
     * Log system statistics
     */
    public function logSystemStats()
    {
        try {
            $today = now()->toDateString();

            $stats = [
                'total_services' => \App\Models\Service::count(),
                'total_users' => \App\Models\User::count(),
                'today_total_queues' => Queue::whereDate('created_at', $today)->count(),
                'today_waiting_queues' => Queue::whereDate('created_at', $today)->where('status', 'waiting')->count(),
                'today_called_queues' => Queue::whereDate('created_at', $today)->where('status', 'called')->count(),
                'today_done_queues' => Queue::whereDate('created_at', $today)->where('status', 'done')->count(),
                'today_canceled_queues' => Queue::whereDate('created_at', $today)->where('status', 'canceled')->count(),
                'timestamp' => now()->toISOString(),
                'ip_address' => request()?->ip() ?? 'unknown',
            ];

            Log::channel('daily')->info('System Statistics', $stats);
        } catch (\Exception $e) {
            // Fail silently to avoid breaking the main flow
            \Log::error('Failed to log system stats: ' . $e->getMessage());
        }
    }

    /**
     * Log error event
     */
    public function logError($message, $context = [])
    {
        try {
            Log::channel('daily')->error($message, array_merge($context, [
                'timestamp' => now()->toISOString(),
                'ip_address' => request()?->ip() ?? 'unknown',
            ]));
        } catch (\Exception $e) {
            // Fail silently to avoid breaking the main flow
            \Log::error('Failed to log error: ' . $e->getMessage());
        }
    }

    /**
     * Log warning event
     */
    public function logWarning($message, $context = [])
    {
        try {
            Log::channel('daily')->warning($message, array_merge($context, [
                'timestamp' => now()->toISOString(),
                'ip_address' => request()?->ip() ?? 'unknown',
            ]));
        } catch (\Exception $e) {
            // Fail silently to avoid breaking the main flow
            \Log::error('Failed to log warning: ' . $e->getMessage());
        }
    }

    /**
     * Log performance metrics
     */
    public function logPerformance($operation, $duration, $context = [])
    {
        try {
            Log::channel('daily')->info('Performance Metric', [
                'operation' => $operation,
                'duration_ms' => $duration,
                'timestamp' => now()->toISOString(),
                'context' => $context,
            ]);
        } catch (\Exception $e) {
            // Fail silently to avoid breaking the main flow
            \Log::error('Failed to log performance metric: ' . $e->getMessage());
        }
    }

    /**
     * Log user activity
     */
    public function logUserActivity($userId, $action, $resource = null, $context = [])
    {
        try {
            Log::channel('daily')->info('User Activity', [
                'user_id' => $userId,
                'action' => $action,
                'resource' => $resource,
                'timestamp' => now()->toISOString(),
                'ip_address' => request()?->ip() ?? 'unknown',
                'user_agent' => request()?->userAgent() ?? 'unknown',
                'context' => $context,
            ]);
        } catch (\Exception $e) {
            // Fail silently to avoid breaking the main flow
            \Log::error('Failed to log user activity: ' . $e->getMessage());
        }
    }

    /**
     * Log service management event
     */
    public function logServiceManagement($userId, $action, $serviceId, $data)
    {
        Log::channel('daily')->info('Service Management Event', [
            'user_id' => $userId,
            'action' => $action, // create, update, delete
            'service_id' => $serviceId,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log user management event
     */
    public function logUserManagement($userId, $action, $targetUserId, $data)
    {
        Log::channel('daily')->info('User Management Event', [
            'user_id' => $userId,
            'action' => $action, // create, update, delete
            'target_user_id' => $targetUserId,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log validation error event
     */
    public function logValidationError($message, $context = [])
    {
        Log::channel('daily')->error('Validation Error', [
            'message' => $message,
            'context' => $context,
            'timestamp' => now()->toISOString(),
        ]);
    }
}