<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            // Log unauthorized access attempt
            Log::warning('Unauthorized access attempt', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'expected_role' => $role,
                'timestamp' => now()->toISOString(),
            ]);

            return redirect('login');
        }

        $user = Auth::user();

        // Check if user has the required role
        if ($user->role !== $role) {
            // Log unauthorized access attempt
            Log::warning('Role-based access denied', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'actual_role' => $user->role,
                'expected_role' => $role,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString(),
            ]);

            // Log the user activity
            $monitoringService = new \App\Services\QueueMonitoringService();
            $monitoringService->logUserActivity($user->id, 'access_denied', 'role_check', [
                'expected_role' => $role,
                'actual_role' => $user->role,
            ]);

            abort(403, 'Unauthorized access.');
        }

        // Log successful access
        $monitoringService = new \App\Services\QueueMonitoringService();
        $monitoringService->logUserActivity($user->id, 'access_granted', 'role_check', [
            'role' => $role,
        ]);

        return $next($request);
    }
}
