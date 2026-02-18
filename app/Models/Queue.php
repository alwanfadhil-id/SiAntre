<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Queue extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'number',
        'service_id',
        'status',
        'queue_date',
        'ip_address',
    ];

    protected $casts = [
        'number' => 'string',
        'queue_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'queue_date',
    ];

    /**
     * Automatically set queue_date when creating a new record
     */
    protected static function booted()
    {
        static::creating(function ($queue) {
            if (empty($queue->queue_date)) {
                $queue->queue_date = now()->toDateString();
            }
        });
    }

    /**
     * Boot the model and attach event listeners
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($queue) {
            // Log queue creation
            $monitoringService = new \App\Services\QueueMonitoringService();
            $monitoringService->logQueueCreation($queue);
        });

        static::updated(function ($queue) {
            // Log status changes
            if ($queue->isDirty('status')) {
                $monitoringService = new \App\Services\QueueMonitoringService();
                $monitoringService->logQueueStatusChange($queue, $queue->getOriginal('status'), $queue->status);
            }
        });
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Get the service that owns the queue.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Check if a status transition is valid
     */
    public function isValidStatusTransition(string $newStatus): bool
    {
        $currentStatus = $this->status;

        // Define valid transitions
        $validTransitions = [
            'waiting' => ['called'],
            'called' => ['done', 'canceled'],
            'done' => [], // 'done' is final state
            'canceled' => [] // 'canceled' is final state
        ];

        $isValid = isset($validTransitions[$currentStatus]) &&
                   in_array($newStatus, $validTransitions[$currentStatus]);

        if (!$isValid) {
            \Log::warning("Invalid status transition attempted", [
                'queue_id' => $this->id,
                'from' => $currentStatus,
                'to' => $newStatus,
                'user_id' => auth()->id() ?? 'guest',
                'ip_address' => request()->ip() ?? 'unknown',
                'timestamp' => now()->toISOString()
            ]);
        }

        return $isValid;
    }

    /**
     * Get the validation rules for creating a queue
     */
    public static function getValidationRules(): array
    {
        return [
            'number' => 'required|string|max:20|unique:queues,number,NULL,id,queue_date,' . now()->toDateString(),
            'service_id' => 'required|exists:services,id',
            'status' => 'required|in:waiting,called,done,canceled',
        ];
    }

    /**
     * Generate the next queue number for a service.
     */
    public static function generateNextNumber($serviceId): string
    {
        $today = now()->toDateString();

        // Use a more robust locking mechanism to prevent race conditions
        // We'll lock on the service record itself to ensure exclusive access
        $service = \App\Models\Service::where('id', $serviceId)->lockForUpdate()->first();
        if (!$service) {
            throw new \InvalidArgumentException("Service not found: {$serviceId}");
        }
        $prefix = $service->prefix;

        // Also lock the queue records for this service today to prevent race conditions
        $lastQueue = self::where('service_id', $serviceId)
            ->whereDate('queue_date', $today)
            ->orderBy('number', 'desc')
            ->lockForUpdate() // Tambahkan locking di sini untuk mencegah race condition
            ->first();

        if ($lastQueue) {
            // Extract the numeric part from the last queue number
            $lastNumberStr = preg_replace('/^[A-Z]+\./', '', $lastQueue->number);

            // Validate that we got a valid number string
            if (!is_numeric($lastNumberStr)) {
                throw new \InvalidArgumentException("Invalid queue number format: {$lastQueue->number}");
            }

            $lastNumber = intval($lastNumberStr);

            // Check for potential integer overflow (though extremely unlikely with queue numbers)
            if ($lastNumber >= PHP_INT_MAX) {
                throw new \OverflowException("Queue number has reached maximum value for service: {$serviceId}");
            }

            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . '.' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get the estimated wait time in minutes based on average service time
     * This is a simplified calculation - in a real system you might use historical data
     */
    public function getEstimatedWaitTime(): int
    {
        // Get the number of people ahead in the queue
        $peopleAhead = self::where('service_id', $this->service_id)
            ->where('status', 'waiting')
            ->where('number', '<', $this->number)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        // Assuming average service time is 5 minutes per person
        return $peopleAhead * 5;
    }

    /**
     * Get the position in queue
     */
    public function getPositionInQueue(): int
    {
        if ($this->status !== 'waiting') {
            return 0; // Not in waiting queue
        }

        // Count how many people came before this queue number
        return self::where('service_id', $this->service_id)
            ->where('status', 'waiting')
            ->where('number', '<=', $this->number)
            ->whereDate('created_at', now()->toDateString())
            ->count();
    }

    /**
     * Check if a user (by IP address) already has an active queue for a specific service today
     * This method checks if user can take a new queue based on business rules:
     * - User can only take one queue per service per day, unless previous queue is done/canceled
     */
    public static function userCanTakeQueueForService(string $ipAddress, int $serviceId): bool
    {
        // Get the user's latest queue for this service today
        $existingQueue = self::where('ip_address', $ipAddress)
            ->where('service_id', $serviceId)
            ->whereDate('created_at', now()->toDateString())
            ->latest('created_at') // Get the most recent queue
            ->first();

        // If no previous queue exists, user can take a new one
        if (!$existingQueue) {
            return true;
        }

        // If previous queue is done or canceled, user can take a new one
        // This allows users who have completed their visit to come back if needed
        if (in_array($existingQueue->status, ['done', 'canceled'])) {
            return true;
        }

        // If previous queue is still in process (waiting or called), user cannot take a new one
        return false;
    }

    /**
     * Get the latest queue for a user (by IP address) for a specific service today
     */
    public static function getUserLatestQueueForService(string $ipAddress, int $serviceId)
    {
        return self::where('ip_address', $ipAddress)
            ->where('service_id', $serviceId)
            ->whereDate('created_at', now()->toDateString())
            ->latest('created_at')
            ->first();
    }

    /**
     * Check if the queue is expired (not processed within a certain time)
     */
    public function isExpired(): bool
    {
        // Antrian dianggap expired jika statusnya 'waiting' lebih dari 4 jam
        if ($this->status === 'waiting' && $this->created_at->diffInHours(now()) > 4) {
            return true;
        }

        // Antrian dianggap expired jika statusnya 'called' lebih dari 2 jam
        if ($this->status === 'called' && $this->updated_at->diffInHours(now()) > 2) {
            return true;
        }

        return false;
    }

    /*
    // Temporarily commented out to troubleshoot test failures
    public function scopeExpired($query)
    {
        $fourHoursAgo = now()->subHours(4);
        $twoHoursAgo = now()->subHours(2);

        return $query->where(function($q) use ($fourHoursAgo) {
            // Queues that have been 'waiting' for more than 4 hours
            $q->where('status', 'waiting')
              ->where('created_at', '<', $fourHoursAgo);
        })->orWhere(function($q) use ($twoHoursAgo) {
            // Queues that have been 'called' for more than 2 hours
            $q->where('status', 'called')
              ->where('updated_at', '<', $twoHoursAgo);
        });
    }
    */

    /**
     * Get validation rules that include IP address
     */
    public static function getValidationRulesWithIp(): array
    {
        $rules = self::getValidationRules();
        $rules['ip_address'] = 'required|ip';
        return $rules;
    }
}
