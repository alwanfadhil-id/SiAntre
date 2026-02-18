<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class SecurityImplementationTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_factor_authentication_setup()
    {
        // Test that User model has 2FA methods
        $user = User::factory()->create();
        
        $this->assertTrue(method_exists($user, 'hasTwoFactorEnabled'));
        $this->assertTrue(method_exists($user, 'generateTwoFactorSecret'));
        $this->assertTrue(method_exists($user, 'getTwoFactorQrCodeUrl'));
        $this->assertTrue(method_exists($user, 'verifyTwoFactorCode'));
        $this->assertTrue(method_exists($user, 'enableTwoFactor'));
        $this->assertTrue(method_exists($user, 'disableTwoFactor'));
        
        // Test that 2FA columns exist in fillable
        $this->assertContains('google2fa_secret', $user->getFillable());
        $this->assertContains('google2fa_enabled', $user->getFillable());
    }

    public function test_ip_whitelist_middleware_registered()
    {
        // Test that the IP whitelist middleware is registered
        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
        $routeMiddleware = $kernel->getRouteMiddleware();
        
        $this->assertArrayHasKey('ip.whitelist', $routeMiddleware);
        $this->assertEquals(\Orkhanahmadov\LaravelIpMiddleware\WhitelistMiddleware::class, $routeMiddleware['ip.whitelist']);
    }

    public function test_permission_package_installed()
    {
        // Test that permission package files exist
        $this->assertFileExists(base_path('vendor/spatie/laravel-permission/src/PermissionServiceProvider.php'));
    }

    public function test_security_headers_middleware_exists()
    {
        // Test that security headers middleware exists
        $this->assertTrue(class_exists(\App\Http\Middleware\SecurityHeaders::class));
    }

    public function test_session_timeout_middleware_exists()
    {
        // Test that session timeout middleware exists
        $this->assertTrue(class_exists(\App\Http\Middleware\RoleBasedSessionTimeout::class));
    }

    public function test_user_model_has_google2fa_methods()
    {
        // Test that User model has Google2FA methods
        $user = new User();
        
        $this->assertTrue(method_exists($user, 'hasTwoFactorEnabled'));
        $this->assertTrue(method_exists($user, 'generateTwoFactorSecret'));
        $this->assertTrue(method_exists($user, 'getTwoFactorQrCodeUrl'));
        $this->assertTrue(method_exists($user, 'verifyTwoFactorCode'));
        $this->assertTrue(method_exists($user, 'enableTwoFactor'));
        $this->assertTrue(method_exists($user, 'disableTwoFactor'));
    }
}