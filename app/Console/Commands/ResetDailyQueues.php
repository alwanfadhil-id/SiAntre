<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Queue;

class ResetDailyQueues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:reset-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all unclosed queues from yesterday to canceled status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get yesterday's date
        $yesterday = now()->subDay()->toDateString();

        // Find all queues from yesterday that are still waiting or called (not done or canceled)
        $queues = Queue::whereDate('created_at', $yesterday)
                      ->whereIn('status', ['waiting', 'called'])
                      ->get();

        $count = 0;
        foreach ($queues as $queue) {
            $queue->update(['status' => 'canceled']);
            $count++;
        }

        // Log the reset event
        $monitoringService = new \App\Services\QueueMonitoringService();
        $monitoringService->logQueueReset($count, $yesterday);

        $this->info("Successfully reset {$count} queues from {$yesterday} to canceled status.");
    }
}
