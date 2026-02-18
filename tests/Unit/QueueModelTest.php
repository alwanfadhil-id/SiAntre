<?php

namespace Tests\Unit;

use App\Models\Queue;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_next_number_returns_correct_format()
    {
        // Create a service with prefix
        $service = Service::factory()->create(['prefix' => 'A']);

        // Create a queue to establish sequence
        Queue::create([
            'number' => 'A.001',
            'service_id' => $service->id,
            'status' => 'done'
        ]);

        $nextNumber = Queue::generateNextNumber($service->id);

        $this->assertEquals('A.002', $nextNumber);
    }

    public function test_generate_next_number_starts_from_one_if_no_previous_queue()
    {
        $service = Service::factory()->create(['prefix' => 'B']);

        $nextNumber = Queue::generateNextNumber($service->id);

        $this->assertEquals('B.001', $nextNumber);
    }

    public function test_generate_next_number_respects_service_boundaries()
    {
        $serviceA = Service::factory()->create(['prefix' => 'A']);
        $serviceB = Service::factory()->create(['prefix' => 'B']);

        // Create queue for service A
        Queue::create([
            'number' => 'A.001',
            'service_id' => $serviceA->id,
            'status' => 'done'
        ]);

        // Generate next number for service B (should start from 1)
        $nextNumberForB = Queue::generateNextNumber($serviceB->id);

        $this->assertEquals('B.001', $nextNumberForB);

        // Generate next number for service A (should be 2)
        $nextNumberForA = Queue::generateNextNumber($serviceA->id);

        $this->assertEquals('A.002', $nextNumberForA);
    }
}
