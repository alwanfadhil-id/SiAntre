<?php

namespace App\Jobs;

use App\Models\Queue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessQueueOperation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $queueId;
    protected $operation;
    protected $data;

    /**
     * Create a new job instance.
     *
     * @param int $queueId
     * @param string $operation
     * @param array $data
     */
    public function __construct(int $queueId, string $operation, array $data = [])
    {
        $this->queueId = $queueId;
        $this->operation = $operation;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $queue = Queue::find($this->queueId);

            if (!$queue) {
                Log::warning("Queue not found for ID: {$this->queueId}");
                return;
            }

            switch ($this->operation) {
                case 'call':
                    $queue->update(['status' => 'called']);
                    Log::info("Queue {$queue->number} called in background job");
                    break;

                case 'done':
                    $queue->update(['status' => 'done']);
                    Log::info("Queue {$queue->number} marked as done in background job");
                    break;

                case 'cancel':
                    $queue->update(['status' => 'canceled']);
                    Log::info("Queue {$queue->number} canceled in background job");
                    break;

                case 'reset_daily':
                    // Reset all waiting queues to canceled at end of day
                    Queue::whereDate('created_at', today())
                         ->where('status', 'waiting')
                         ->update(['status' => 'canceled']);
                    Log::info("Daily queue reset completed in background job");
                    break;

                default:
                    Log::warning("Unknown operation: {$this->operation}");
                    break;
            }
        } catch (\Exception $e) {
            Log::error("Error processing queue operation: " . $e->getMessage());
            throw $e; // Re-throw to trigger retry mechanism
        }
    }
}
