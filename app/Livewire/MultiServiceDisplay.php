<?php

namespace App\Livewire;

use App\Models\Service;
use App\Models\Queue;
use Livewire\Component;
use Livewire\Attributes\Lazy;

#[Lazy]
class MultiServiceDisplay extends Component
{
    public $services = [];
    public $serviceData = [];
    public $displayMode = 'grid'; // grid, list, single

    protected $listeners = ['queueUpdated' => 'refreshData'];

    public function mount($services = [], $displayMode = 'grid')
    {
        $this->displayMode = $displayMode;
        
        // Convert service IDs to array if needed
        if (!is_array($services)) {
            $services = [$services];
        }
        
        $this->services = $services;
        $this->refreshData();
    }

    public function refreshData()
    {
        try {
            $today = now()->toDateString();

            if (empty($this->services)) {
                $this->serviceData = [];
                return;
            }

            // Get selected services with their queues
            $servicesCollection = Service::with(['queues' => function($query) use ($today) {
                $query->whereDate('created_at', $today)
                      ->whereIn('status', ['waiting', 'called', 'done'])
                      ->orderBy('number', 'asc');
            }])
            ->whereIn('id', $this->services)
            ->get();

            $this->serviceData = $servicesCollection->map(function ($service) use ($today) {
                $serviceQueues = $service->queues;

                // Count waiting
                $waitingCount = $serviceQueues->where('status', 'waiting')->count();

                // Find currently called queue
                $calledQueues = $serviceQueues->filter(function ($queue) {
                    return $queue->status === 'called';
                });

                $calledNumber = null;
                if ($calledQueues->count() > 0) {
                    $mostRecentCalled = $calledQueues->sortByDesc(function($queue) {
                        return $queue->created_at;
                    })->first();
                    $calledNumber = $mostRecentCalled ? $mostRecentCalled->number : null;
                } else {
                    // Show last processed queue
                    $recentlyProcessed = $serviceQueues->filter(function ($queue) {
                        return in_array($queue->status, ['done', 'called']);
                    })->sortByDesc(function($queue) {
                        return $queue->updated_at;
                    })->first();

                    if ($recentlyProcessed) {
                        $calledNumber = $recentlyProcessed->number;
                    }
                }

                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'prefix' => $service->prefix,
                    'waiting_count' => $waitingCount,
                    'called_number' => $calledNumber,
                ];
            })->toArray();

        } catch (\Exception $e) {
            \Log::error('MultiServiceDisplay: Error in refreshData - ' . $e->getMessage());
            $this->serviceData = [];
        }
    }

    public function render()
    {
        return view('livewire.multi-service-display');
    }
}
