<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class QueueTest extends TestCase
{
    public function test_patient_can_view_services_page(): void
    {
        $service = Service::factory()->create(['name' => 'Poli Umum', 'prefix' => 'A']);

        $response = $this->get(route('patient.services'));

        $response->assertStatus(200);
        $response->assertSee($service->name);
    }

    public function test_patient_can_generate_queue_number(): void
    {
        $service = Service::factory()->create(['name' => 'Poli Gigi', 'prefix' => 'B']);

        // Visit the services page first to establish session and get CSRF token
        $this->get(route('patient.services'));

        $response = $this->post(route('queue.generate'), [
            'service_id' => $service->id
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('queues', [
            'service_id' => $service->id,
            'status' => 'waiting'
        ]);
    }

    public function test_patient_can_view_queue_status(): void
    {
        $service = Service::factory()->create(['name' => 'Laboratorium', 'prefix' => 'LAB']);

        // Generate a queue number using the application logic
        $queueNumber = Queue::generateNextNumber($service->id);
        $queue = Queue::create([
            'number' => $queueNumber,
            'service_id' => $service->id,
            'status' => 'waiting'
        ]);

        $response = $this->get(route('queue.status', ['number' => $queue->number]));

        $response->assertStatus(200);
        $response->assertSee($queue->number);
        $response->assertSee($service->name);
    }

    public function test_patient_sees_estimated_wait_time(): void
    {
        $service = Service::factory()->create(['name' => 'Apotek', 'prefix' => 'APT']);

        // Create 3 people ahead in queue using the application logic
        $queueNumbers = [];
        for ($i = 1; $i <= 3; $i++) {
            $queueNumber = Queue::generateNextNumber($service->id);
            $queue = Queue::create([
                'number' => $queueNumber,
                'service_id' => $service->id,
                'status' => 'waiting'
            ]);
            $queueNumbers[] = $queue;
        }

        // Create the user's queue (should be 4th in line)
        $userQueueNumber = Queue::generateNextNumber($service->id);
        $userQueue = Queue::create([
            'number' => $userQueueNumber,
            'service_id' => $service->id,
            'status' => 'waiting'
        ]);

        $response = $this->get(route('queue.status', ['number' => $userQueue->number]));

        $response->assertStatus(200);
        $response->assertSee('Masih ada 3 orang di depan Anda');
    }
}
