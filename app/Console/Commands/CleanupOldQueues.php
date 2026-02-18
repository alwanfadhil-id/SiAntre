<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupOldQueues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:cleanup
                            {--days=30 : Number of days to keep old queues}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old queues that are older than specified days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $dryRun = $this->option('dry-run');

        $cutoffDate = \Illuminate\Support\Carbon::now()->subDays($days)->toDateString();

        $this->info("Cleaning up queues older than {$cutoffDate} (more than {$days} days old)");

        // Find queues that are older than the cutoff date
        $oldQueues = \App\Models\Queue::whereDate('created_at', '<', $cutoffDate)
                          ->where('status', '!=', 'waiting') // Don't delete active queues
                          ->where('status', '!=', 'called')  // Don't delete called queues
                          ->get();

        if ($oldQueues->isEmpty()) {
            $this->info('No old queues found to clean up.');
            return 0;
        }

        $this->info("Found {$oldQueues->count()} old queues to clean up:");

        foreach ($oldQueues as $queue) {
            $this->line("- Queue #{$queue->id}: {$queue->number} (Status: {$queue->status}, Created: {$queue->created_at})");
        }

        // Also find expired queues (not just old ones)
        $expiredQueues = \App\Models\Queue::expired()->get();

        if (!$expiredQueues->isEmpty()) {
            $this->info("Additionally found {$expiredQueues->count()} expired queues:");

            foreach ($expiredQueues as $queue) {
                $this->line("- Queue #{$queue->id}: {$queue->number} (Status: {$queue->status}, Created: {$queue->created_at}, Expired)");
            }
        }

        if ($dryRun) {
            $this->info('Dry run completed. No queues were actually deleted.');
            return 0;
        }

        $confirmed = $this->confirm('Do you really wish to delete these old and expired queues?', true);

        if ($confirmed) {
            // Delete old queues
            $deletedOldCount = \App\Models\Queue::whereDate('created_at', '<', $cutoffDate)
                                ->where('status', '!=', 'waiting')
                                ->where('status', '!=', 'called')
                                ->delete();

            // Mark expired queues as canceled
            $expiredQueues = \App\Models\Queue::expired()->get();
            $canceledExpiredCount = 0;

            foreach ($expiredQueues as $queue) {
                $queue->update(['status' => 'canceled']);
                $canceledExpiredCount++;
            }

            $this->info("Successfully deleted {$deletedOldCount} old queues and marked {$canceledExpiredCount} expired queues as canceled.");
        } else {
            $this->info('Cleanup cancelled by user.');
        }

        return 0;
    }
}
