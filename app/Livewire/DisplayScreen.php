<?php

namespace App\Livewire;

use App\Models\Service;
use App\Models\Queue;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Lazy;

#[Lazy]
class DisplayScreen extends Component
{
    public $services = [];
    public $waitingCounts = [];
    public $calledQueues = [];

    protected $listeners = ['queueUpdated' => '$refresh'];

    public function mount()
    {
        try {
            $this->refreshData();
        } catch (\Exception $e) {
            \Log::error('DisplayScreen: Error in mount - ' . $e->getMessage());
            $this->services = [];
            $this->waitingCounts = [];
            $this->calledQueues = [];
        }
    }

    public function render()
    {
        return view('livewire.display-screen');
    }

    public function refreshData()
    {
        try {
            $today = now()->toDateString();

            // Get all services with their queues for today (no cache for real-time)
            $servicesCollection = Service::with(['queues' => function($query) use ($today) {
                $query->whereDate('created_at', $today)
                      ->whereIn('status', ['waiting', 'called', 'done'])
                      ->orderBy('number', 'asc');
            }])->get();

            // Filter services to only include those with active queues (waiting or called)
            $activeServices = $servicesCollection->filter(function ($service) {
                // Include service if it has waiting queues or currently called queue
                $hasWaiting = $service->queues->where('status', 'waiting')->count() > 0;
                $hasCalled = $service->queues->where('status', 'called')->count() > 0;
                $hasRecentlyProcessed = $service->queues->whereIn('status', ['done', 'called'])->count() > 0;

                return $hasWaiting || $hasCalled || $hasRecentlyProcessed;
            });

            // Convert to array to ensure proper serialization in Livewire
            $this->services = $activeServices->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'prefix' => $service->prefix,
                ];
            })->toArray();

            // Reset counters
            $this->waitingCounts = [];
            $this->calledQueues = [];

            // Calculate waiting counts and called queues for each active service
            foreach ($activeServices as $service) {
                // Use the already loaded queues from the relationship
                $serviceQueues = $service->queues;

                // Count only queues with 'waiting' status
                $this->waitingCounts[$service->id] = $serviceQueues->where('status', 'waiting')->count();

                // Find the currently called queue for this service (the one with 'called' status)
                // We prioritize the most recently called queue if there are multiple
                $calledQueues = $serviceQueues->filter(function ($queue) {
                    return $queue->status === 'called';
                });

                if ($calledQueues->count() > 0) {
                    // Get the most recently called queue (latest created_at or highest number)
                    $mostRecentCalled = $calledQueues->sortByDesc(function($queue) {
                        return $queue->created_at;
                    })->first();

                    $this->calledQueues[$service->id] = $mostRecentCalled ? $mostRecentCalled->number : null;
                } else {
                    // If no queue is currently called, show the last called or done queue
                    $recentlyProcessed = $serviceQueues->filter(function ($queue) {
                        return $queue->status === 'done' || $queue->status === 'called';
                    })->sortByDesc(function($queue) {
                        return $queue->updated_at;
                    })->first();

                    if ($recentlyProcessed) {
                        $this->calledQueues[$service->id] = $recentlyProcessed->number;
                    } else {
                        $this->calledQueues[$service->id] = null;
                    }
                }
            }

        } catch (\Exception $e) {
            \Log::error('DisplayScreen: Error in refreshData - ' . $e->getMessage());
            $this->services = [];
            $this->waitingCounts = [];
            $this->calledQueues = [];
        }
    }

    #[On('queueUpdated')]
    public function handleQueueUpdate()
    {
        $this->refreshData();
    }

    public function getListeners()
    {
        return [
            'echo:queue-channel.QueueUpdated' => 'handleQueueUpdate',
        ];
    }
}