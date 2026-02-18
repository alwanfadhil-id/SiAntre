<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleBasedSessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (auth()->check()) {
            $user = auth()->user();

            // Determine the timeout based on user role
            $role = $user->role ?? 'patient'; // Default to patient if no role
            $timeout = $this->getTimeoutForRole($role);

            // Update session lifetime
            config(['session.lifetime' => $timeout]);

            // Also update the expire_on_close setting if needed
            config(['session.expire_on_close' => false]);
        }

        return $next($request);
    }

    /**
     * Get session timeout for a specific role (in minutes)
     */
    private function getTimeoutForRole(string $role): int
    {
        $timeouts = [
            'admin' => 60,      // 1 hour for admin
            'operator' => 120,  // 2 hours for operator
            'patient' => 30,    // 30 minutes for patient
        ];

        return $timeouts[$role] ?? $timeouts['patient']; // Default to patient timeout
    }
}
