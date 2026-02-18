<?php

namespace App\Http\Middleware;

use App\Models\IpWhitelist;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DatabaseIpWhitelistMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip IP check in local/testing environments
        if (app()->environment('local', 'testing')) {
            return $next($request);
        }

        // Determine if this route should be IP-restricted
        $fullPath = $request->path();

        // Only apply IP whitelist to specific admin and operator routes
        // The path should start with 'admin/' or 'operator/' followed by additional path segments
        $isAdminRoute = str_starts_with($fullPath, 'admin/');
        $isOperatorRoute = str_starts_with($fullPath, 'operator/');

        // Special case: also check for exact 'admin' or 'operator' paths
        if ($fullPath === 'admin' || $fullPath === 'operator') {
            $isAdminRoute = ($fullPath === 'admin');
            $isOperatorRoute = ($fullPath === 'operator');
        }

        // Only apply IP whitelist to admin and operator routes
        if (!$isAdminRoute && !$isOperatorRoute) {
            return $next($request);
        }

        // Get the client IP address
        $clientIp = $this->getClientIp($request);

        // Get the category from route parameters or other sources
        $category = $this->getCategoryFromRequest($request);

        // Get all active IPs for this category from the database
        $allowedIps = IpWhitelist::getActiveIpsByCategory($category);

        // Check if the client IP is in the allowed list
        if (!in_array($clientIp, $allowedIps)) {
            abort(403, 'Access denied. Your IP address is not authorized to access this resource.');
        }

        return $next($request);
    }

    /**
     * Get the client IP address
     */
    private function getClientIp(Request $request): string
    {
        // Check for various headers that might contain the real IP
        $ip = $request->ip(); // Laravel's default method

        // Additional check for proxy headers
        if ($request->header('CF-Connecting-IP')) {
            // Cloudflare
            $ip = $request->header('CF-Connecting-IP');
        } elseif ($request->header('X-Forwarded-For')) {
            // X-Forwarded-For header
            $ips = explode(',', $request->header('X-Forwarded-For'));
            $ip = trim($ips[0]);
        } elseif ($request->header('X-Real-IP')) {
            // X-Real-IP header
            $ip = $request->header('X-Real-IP');
        }

        return $ip;
    }

    /**
     * Determine the category based on the request
     */
    private function getCategoryFromRequest(Request $request): string
    {
        // Determine category based on route or other criteria
        $path = $request->path();

        if (str_starts_with($path, 'admin')) {
            return 'admin';
        } elseif (str_starts_with($path, 'operator')) {
            return 'operator';
        }

        // Only check IP whitelist for admin and operator routes
        // Return a category that won't be restricted for other routes
        return 'admin'; // Default to admin category for safety, but this won't affect public routes
    }
}
