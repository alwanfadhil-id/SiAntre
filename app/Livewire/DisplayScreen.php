<?php

namespace App\Livewire;

use App\Models\Service;
use App\Models\Queue;
use Livewire\Component;
use Livewire\Attributes\On;

class DisplayScreen extends Component
{
    public $services = [];
    public $waitingCounts = [];
    public $calledQueues = [];

    protected $listeners = ['queueUpdated' => '$refresh'];

    public function mount()
    {
        \Log::info('DisplayScreen component mounting...');
        try {
            \Log::info('DisplayScreen component mounted, calling refreshData');
            $this->refreshData();
            \Log::info('DisplayScreen refreshData completed, services count: ' . count($this->services));
        } catch (\Exception $e) {
            \Log::error('DisplayScreen: Error in mount - ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());
            // Initialize empty arrays to prevent errors in the view
            $this->services = [];
            $this->waitingCounts = [];
            $this->calledQueues = [];
        }
    }

    public function render()
    {
        \Log::info('DisplayScreen component rendering, services count: ' . count($this->services));
        \Log::info('DisplayScreen component rendering, calledQueues: ' . json_encode($this->calledQueues));
        \Log::info('DisplayScreen component rendering, waitingCounts: ' . json_encode($this->waitingCounts));
        return view('livewire.display-screen');
    }

    public function refreshData()
    {
        try {
            $today = now()->toDateString();
            \Log::info('DisplayScreen: Refreshing data for date: ' . $today);

            // Get all services with their queues for today
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

            \Log::info('DisplayScreen: Number of active services retrieved: ' . $activeServices->count());

            // Reset counters
            $this->waitingCounts = [];
            $this->calledQueues = [];

            // Calculate waiting counts and called queues for each active service
            foreach ($activeServices as $service) {
                // Use the already loaded queues from the relationship
                $serviceQueues = $service->queues;

                // Debug: Log the number of queues for each service
                \Log::info('DisplayScreen: Service ' . $service->name . ' has ' . $serviceQueues->count() . ' total queues');

                // Count only queues with 'waiting' status
                $this->waitingCounts[$service->id] = $serviceQueues->where('status', 'waiting')->count();

                // Debug: Log the number of waiting queues
                \Log::info('DisplayScreen: Service ' . $service->name . ' has ' . $this->waitingCounts[$service->id] . ' waiting queues');

                // Find the currently called queue for this service (the one with 'called' status)
                // We prioritize the most recently called queue if there are multiple
                $calledQueues = $serviceQueues->filter(function ($queue) {
                    return $queue->status === 'called';
                });

                \Log::info('DisplayScreen: Service ' . $service->name . ' has ' . $calledQueues->count() . ' called queues');

                if ($calledQueues->count() > 0) {
                    // Get the most recently called queue (latest created_at or highest number)
                    $mostRecentCalled = $calledQueues->sortByDesc(function($queue) {
                        return $queue->created_at;
                    })->first();

                    $this->calledQueues[$service->id] = $mostRecentCalled ? $mostRecentCalled->number : null;

                    // Debug: Log the called queue number
                    \Log::info('DisplayScreen: Service ' . $service->name . ' called queue: ' . $this->calledQueues[$service->id]);
                } else {
                    // If no queue is currently called, show the last called or done queue
                    $recentlyProcessed = $serviceQueues->filter(function ($queue) {
                        return $queue->status === 'done' || $queue->status === 'called';
                    })->sortByDesc(function($queue) {
                        return $queue->updated_at;
                    })->first();

                    if ($recentlyProcessed) {
                        $this->calledQueues[$service->id] = $recentlyProcessed->number;
                        \Log::info('DisplayScreen: Service ' . $service->name . ' showing last processed queue: ' . $this->calledQueues[$service->id]);
                    } else {
                        $this->calledQueues[$service->id] = null;
                        \Log::info('DisplayScreen: Service ' . $service->name . ' has no called queue');
                    }
                }
            }

            // Final debug: Log all collected data
            \Log::info('DisplayScreen: Final calledQueues data: ' . json_encode($this->calledQueues));
            \Log::info('DisplayScreen: Final waitingCounts data: ' . json_encode($this->waitingCounts));

        } catch (\Exception $e) {
            \Log::error('DisplayScreen: Error in refreshData - ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());
            // Initialize empty arrays to prevent errors in the view
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
        // Since we're using log as broadcast driver, the echo listener won't work
        // We rely on manual refresh and the @On attribute
        return [
            // 'echo:queue-channel,QueueUpdated' => 'refreshData', // Disabled because broadcast is set to log
        ];
    }
}