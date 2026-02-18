<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueDuplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_generate_multiple_queues_for_same_service_per_day_when_previous_is_active(): void
    {
        $service = Service::factory()->create(['name' => 'Poli Umum', 'prefix' => 'A']);

        // Mock the IP address for testing
        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.100']);

        // First queue generation should succeed
        $response = $this->post('/queue/generate', [
            'service_id' => $service->id
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check that a queue was created
        $this->assertDatabaseHas('queues', [
            'service_id' => $service->id,
            'status' => 'waiting',
            'ip_address' => '192.168.1.100'
        ]);

        // Get the created queue to check its number
        $queue = Queue::where('ip_address', '192.168.1.100')->first();
        $expectedNumber = $queue->number;

        // Second queue generation for the same service should fail when previous is still active
        $response = $this->post('/queue/generate', [
            'service_id' => $service->id
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', "Anda sudah mengambil antrian untuk layanan {$service->name} dengan nomor {$expectedNumber} hari ini (status: masih menunggu dipanggil). Anda hanya bisa mengambil antrian baru setelah antrian sebelumnya selesai atau dibatalkan.");

        // Verify that no additional queue was created for the same service
        $this->assertDatabaseCount('queues', 1);
    }

    public function test_user_can_generate_queues_for_different_services(): void
    {
        $service1 = Service::factory()->create(['name' => 'Poli Umum', 'prefix' => 'A']);
        $service2 = Service::factory()->create(['name' => 'Laboratorium', 'prefix' => 'L']);

        // Mock the IP address for testing
        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.100']);

        // First queue generation for service 1 should succeed
        $response = $this->post('/queue/generate', [
            'service_id' => $service1->id
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Queue generation for service 2 should also succeed
        $response = $this->post('/queue/generate', [
            'service_id' => $service2->id
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify that two queues were created for different services
        $this->assertDatabaseCount('queues', 2);
        $this->assertDatabaseHas('queues', [
            'service_id' => $service1->id,
            'status' => 'waiting',
            'ip_address' => '192.168.1.100'
        ]);
        $this->assertDatabaseHas('queues', [
            'service_id' => $service2->id,
            'status' => 'waiting',
            'ip_address' => '192.168.1.100'
        ]);
    }

    public function test_user_can_generate_new_queue_if_previous_was_completed(): void
    {
        $service = Service::factory()->create(['name' => 'Poli Umum', 'prefix' => 'A']);

        // Mock the IP address for testing
        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.100']);

        // Create a queue with 'done' status manually
        $completedQueue = Queue::create([
            'number' => 'A.001',
            'service_id' => $service->id,
            'status' => 'done',
            'ip_address' => '192.168.1.100'
        ]);

        // Now user should be able to take a new queue for the same service
        $response = $this->post('/queue/generate', [
            'service_id' => $service->id
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify that a new queue was created
        $this->assertDatabaseCount('queues', 2);
        $this->assertDatabaseHas('queues', [
            'service_id' => $service->id,
            'status' => 'waiting',
            'ip_address' => '192.168.1.100'
        ]);
    }

    public function test_user_can_generate_new_queue_if_previous_was_canceled(): void
    {
        $service = Service::factory()->create(['name' => 'Poli Umum', 'prefix' => 'A']);

        // Mock the IP address for testing
        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.100']);

        // Create a queue with 'canceled' status manually
        $canceledQueue = Queue::create([
            'number' => 'A.001',
            'service_id' => $service->id,
            'status' => 'canceled',
            'ip_address' => '192.168.1.100'
        ]);

        // Now user should be able to take a new queue for the same service
        $response = $this->post('/queue/generate', [
            'service_id' => $service->id
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify that a new queue was created
        $this->assertDatabaseCount('queues', 2);
        $this->assertDatabaseHas('queues', [
            'service_id' => $service->id,
            'status' => 'waiting',
            'ip_address' => '192.168.1.100'
        ]);
    }

    public function test_user_cannot_generate_new_queue_if_previous_is_still_active(): void
    {
        $service = Service::factory()->create(['name' => 'Poli Umum', 'prefix' => 'A']);

        // Mock the IP address for testing
        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.100']);

        // Create a queue with 'called' status manually
        $activeQueue = Queue::create([
            'number' => 'A.001',
            'service_id' => $service->id,
            'status' => 'called',
            'ip_address' => '192.168.1.100'
        ]);

        // Now user should NOT be able to take a new queue for the same service
        $response = $this->post('/queue/generate', [
            'service_id' => $service->id
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Verify that no new queue was created
        $this->assertDatabaseCount('queues', 1);
    }
}
