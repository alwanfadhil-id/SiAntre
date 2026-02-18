<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\IpWhitelist;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IpWhitelistTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_ip_whitelist()
    {
        // Create an admin user
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Test that admin can access the IP whitelist index
        $response = $this->get(route('admin.ip-whitelist.index'));
        $response->assertStatus(200);

        // Test creating an IP whitelist entry
        $response = $this->post(route('admin.ip-whitelist.store'), [
            'ip_address' => '192.168.1.100',
            'description' => 'Test IP for office',
            'category' => 'admin',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ip_whitelists', [
            'ip_address' => '192.168.1.100',
            'description' => 'Test IP for office',
            'category' => 'admin',
            'is_active' => true,
        ]);

        // Test updating an IP whitelist entry
        $ipEntry = IpWhitelist::first();
        $response = $this->put(route('admin.ip-whitelist.update', $ipEntry->id), [
            'ip_address' => '192.168.1.101',
            'description' => 'Updated IP for office',
            'category' => 'operator',
            'is_active' => false,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ip_whitelists', [
            'ip_address' => '192.168.1.101',
            'description' => 'Updated IP for office',
            'category' => 'operator',
            'is_active' => false,
        ]);

        // Test deleting an IP whitelist entry
        $response = $this->delete(route('admin.ip-whitelist.destroy', $ipEntry->id));
        $response->assertRedirect();
        $this->assertDatabaseMissing('ip_whitelists', [
            'id' => $ipEntry->id,
        ]);
    }

    public function test_ip_model_scopes_work_correctly()
    {
        // Create some IP whitelist entries
        IpWhitelist::create([
            'ip_address' => '192.168.1.100',
            'description' => 'Active IP',
            'category' => 'admin',
            'is_active' => true,
            'expires_at' => null,
        ]);

        IpWhitelist::create([
            'ip_address' => '192.168.1.101',
            'description' => 'Inactive IP',
            'category' => 'admin',
            'is_active' => false,
            'expires_at' => null,
        ]);

        IpWhitelist::create([
            'ip_address' => '192.168.1.102',
            'description' => 'Expired IP',
            'category' => 'admin',
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        // Test the active scope
        $activeIps = IpWhitelist::active()->get();
        $this->assertCount(1, $activeIps);
        $this->assertEquals('192.168.1.100', $activeIps->first()->ip_address);

        // Test the byCategory scope
        $adminIps = IpWhitelist::byCategory('admin')->get();
        $this->assertCount(3, $adminIps);
    }
}