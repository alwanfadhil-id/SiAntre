<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'prefix',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the queues for the service.
     */
    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }

    /**
     * Scope a query to only include active services (not soft deleted).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Get today's waiting queues count
     */
    public function getTodaysWaitingQueueCount(): int
    {
        return $this->queues()
            ->whereDate('created_at', now()->toDateString())
            ->where('status', 'waiting')
            ->count();
    }

    /**
     * Get today's queues count for all statuses
     */
    public function getTodaysQueueCounts(): array
    {
        return $this->queues()
            ->whereDate('created_at', now()->toDateString())
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * Check if service has reached daily queue limit
     */
    public function hasReachedDailyLimit(int $limit = 100): bool
    {
        return $this->getTodaysWaitingQueueCount() >= $limit;
    }

    /**
     * Get the validation rules for creating a service
     */
    public static function getValidationRules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:services,name',
            'prefix' => 'required|string|max:10|regex:/^[A-Z0-9]+$/|unique:services,prefix',
        ];
    }

    /**
     * Get the validation rules for updating a service
     */
    public static function getUpdateValidationRules($id): array
    {
        return [
            'name' => 'required|string|max:255|unique:services,name,' . $id,
            'prefix' => 'required|string|max:10|regex:/^[A-Z0-9]+$/|unique:services,prefix,' . $id,
        ];
    }
}
