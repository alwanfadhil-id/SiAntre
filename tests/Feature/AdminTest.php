<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminTest extends TestCase
{
    public function test_admin_can_access_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        $response = $this->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }

    public function test_admin_can_manage_services(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        // Visit the admin dashboard first to establish session and get CSRF token
        $this->get(route('admin.dashboard'));

        // Test viewing services
        $response = $this->get(route('admin.services.index'));
        $response->assertStatus(200);

        // Test creating a service
        $response = $this->post(route('admin.services.store'), [
            'name' => 'New Service',
            'prefix' => 'NS'
        ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('services', [
            'name' => 'New Service',
            'prefix' => 'NS'
        ]);

        // Test editing a service
        $service = Service::where('name', 'New Service')->first();
        $response = $this->put(route('admin.services.update', $service), [
            'name' => 'Updated Service',
            'prefix' => 'US'
        ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('services', [
            'name' => 'Updated Service',
            'prefix' => 'US'
        ]);

        // Test deleting a service (only if no queues are associated)
        // First, make sure no queues exist for this service
        $service->queues()->delete(); // Remove any queues associated with this service

        $response = $this->delete(route('admin.services.destroy', $service));
        $response->assertRedirect();

        // With soft deletes, the service should be marked as deleted but still exist
        $this->assertSoftDeleted('services', [
            'id' => $service->id
        ]);
    }

    public function test_admin_can_manage_users(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);

        // Visit the admin dashboard first to establish session and get CSRF token
        $this->get(route('admin.dashboard'));

        // Test viewing users
        $response = $this->get(route('admin.users.index'));
        $response->assertStatus(200);

        // Test creating a user
        $response = $this->post(route('admin.users.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'operator'
        ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'operator'
        ]);

        // Test editing a user - explicitly refresh to get the latest data
        $userToEdit = User::where('email', 'test@example.com')->firstOrFail();
        $this->assertEquals('operator', $userToEdit->role); // Verify initial role

        $newEmail = 'updated_test_' . uniqid() . '@example.com';

        $response = $this->put(route('admin.users.update', $userToEdit), [
            'name' => 'Updated User',
            'email' => $newEmail, // Unique email to avoid conflicts
            'role' => 'admin'
        ]);

        // Check for validation errors
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // Refresh the user and check the role
        $userToEdit->refresh();
        $this->assertEquals('admin', $userToEdit->role, 'Role was not updated properly');
        $this->assertEquals($newEmail, $userToEdit->email, 'Email was not updated properly');
        $this->assertEquals('Updated User', $userToEdit->name, 'Name was not updated properly');

        $this->assertDatabaseHas('users', [
            'name' => 'Updated User',
            'email' => $newEmail,
            'role' => 'admin'
        ]);
    }

    public function test_admin_can_reset_daily_queue(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        // Visit the admin dashboard first to establish session and get CSRF token
        $this->get(route('admin.dashboard'));

        // Create some test queues
        $service = Service::factory()->create();
        $queue = \App\Models\Queue::create([
            'number' => 'TEST.001',
            'service_id' => $service->id,
            'status' => 'waiting'
        ]);

        // Test reset functionality
        $response = $this->post(route('admin.reset.queue'));
        $response->assertRedirect();
    }
}
