<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BasicFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

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

        // Visit the services page first to get the session with CSRF token
        $this->get(route('patient.services'));

        $response = $this->post(route('queue.generate'), [
            'service_id' => $service->id
        ]);

        $response->assertRedirect(); // Expecting redirect after successful POST

        $this->assertDatabaseHas('queues', [
            'service_id' => $service->id,
            'status' => 'waiting'
        ]);
    }
}