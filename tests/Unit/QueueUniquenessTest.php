<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Service;
use App\Models\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QueueUniquenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_queue_numbers_are_unique_per_day()
    {
        // Create a service
        $service = Service::factory()->create(['prefix' => 'TEST']);

        // Generate and save a queue number (simulating actual app behavior)
        $firstNumber = Queue::generateNextNumber($service->id);
        Queue::create([
            'number' => $firstNumber,
            'service_id' => $service->id,
            'status' => 'waiting'
        ]);

        // Generate another queue number for the same service
        $secondNumber = Queue::generateNextNumber($service->id);

        // They should be different
        $this->assertNotEquals($firstNumber, $secondNumber);

        // The second should be one number higher
        $prefix = 'TEST.';
        $firstNum = intval(substr($firstNumber, strlen($prefix)));
        $secondNum = intval(substr($secondNumber, strlen($prefix)));

        $this->assertEquals($firstNum + 1, $secondNum);
    }
    
    public function test_duplicate_queue_numbers_for_same_service_cannot_be_created()
    {
        // Create a service
        $service = Service::factory()->create(['prefix' => 'UNIQ']);

        // Create a queue with a specific number
        Queue::create([
            'number' => 'UNIQ.001',
            'service_id' => $service->id,
            'status' => 'waiting'
        ]);

        // Try to create another queue with the same number for the same service on the same day
        // This should trigger a unique constraint violation
        $this->expectException(\Illuminate\Database\QueryException::class);

        Queue::create([
            'number' => 'UNIQ.001', // Same number
            'service_id' => $service->id, // Same service
            'status' => 'waiting'
        ]);
    }

    public function test_different_services_can_have_same_queue_numbers()
    {
        // Create two different services with the same prefix
        $service1 = Service::factory()->create(['prefix' => 'SAME']);
        $service2 = Service::factory()->create(['prefix' => 'SAME']); // Same prefix on purpose

        // Create a queue with the same number for the first service
        $queue1 = Queue::create([
            'number' => 'SAME.001',
            'service_id' => $service1->id,
            'status' => 'waiting'
        ]);

        // Creating the same queue number for the second service should succeed
        $queue2 = Queue::create([
            'number' => 'SAME.001', // Same number
            'service_id' => $service2->id, // Different service
            'status' => 'waiting'
        ]);

        // Both queues should exist with the same number but different service IDs
        $this->assertEquals('SAME.001', $queue1->number);
        $this->assertEquals('SAME.001', $queue2->number);
        $this->assertNotEquals($queue1->service_id, $queue2->service_id);
    }
}