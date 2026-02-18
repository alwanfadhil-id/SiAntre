<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OperatorTest extends TestCase
{
    public function test_operator_can_access_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'operator']);
        $this->actingAs($user);

        $response = $this->get(route('operator.dashboard'));

        $response->assertStatus(200);
    }

    public function test_operator_can_view_service_queues(): void
    {
        $user = User::factory()->create(['role' => 'operator']);
        $this->actingAs($user);

        $service = Service::factory()->create(['name' => 'Poli Umum', 'prefix' => 'A']);

        $response = $this->get(route('operator.queues', $service));

        $response->assertStatus(200);
        $response->assertSee($service->name);
    }

    public function test_operator_can_call_next_queue(): void
    {
        $user = User::factory()->create(['role' => 'operator']);
        $this->actingAs($user);

        $service = Service::factory()->create(['prefix' => 'B']);
        $queue = Queue::create([
            'number' => 'B.001',
            'service_id' => $service->id,
            'status' => 'waiting'
        ]);

        // Visit the operator dashboard first to establish session and get CSRF token
        $this->get(route('operator.dashboard'));

        $response = $this->put(route('operator.queue.call', $queue));

        $response->assertRedirect();
        $this->assertDatabaseHas('queues', [
            'id' => $queue->id,
            'status' => 'called'
        ]);
    }

    public function test_operator_can_mark_queue_as_done(): void
    {
        $user = User::factory()->create(['role' => 'operator']);
        $this->actingAs($user);

        $service = Service::factory()->create();
        $queue = Queue::create([
            'number' => 'C.001',
            'service_id' => $service->id,
            'status' => 'called'
        ]);

        // Visit the operator dashboard first to establish session and get CSRF token
        $this->get(route('operator.dashboard'));

        $response = $this->put(route('operator.queue.done', $queue));

        $response->assertRedirect();
        $this->assertDatabaseHas('queues', [
            'id' => $queue->id,
            'status' => 'done'
        ]);
    }

    public function test_operator_can_cancel_queue(): void
    {
        $user = User::factory()->create(['role' => 'operator']);
        $this->actingAs($user);

        $service = Service::factory()->create();
        $queue = Queue::create([
            'number' => 'D.001',
            'service_id' => $service->id,
            'status' => 'called'
        ]);

        // Visit the operator dashboard first to establish session and get CSRF token
        $this->get(route('operator.dashboard'));

        $response = $this->put(route('operator.queue.cancel', $queue));

        $response->assertRedirect();
        $this->assertDatabaseHas('queues', [
            'id' => $queue->id,
            'status' => 'canceled'
        ]);
    }
}
