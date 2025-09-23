<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiKey;
use App\Models\Website;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Extract API key from header or query parameter
        $apiKey = $request->header('X-API-Key') ?? $request->input('api_key');

        if (!$apiKey) {
            return $this->unauthorizedResponse('API key is required');
        }

        // Validate API key format
        if (!$this->isValidKeyFormat($apiKey)) {
            return $this->unauthorizedResponse('Invalid API key format');
        }

        // Check API key in cache first for performance
        $cacheKey = "api_key:{$apiKey}";
        $apiKeyData = Cache::remember($cacheKey, 300, function () use ($apiKey) {
            return ApiKey::with('website')
                ->where('key', $apiKey)
                ->where('is_active', true)
                ->where('expires_at', '>', now())
                ->first();
        });

        if (!$apiKeyData) {
            return $this->unauthorizedResponse('Invalid or expired API key');
        }

        // Check if website is active
        if (!$apiKeyData->website || !$apiKeyData->website->is_active) {
            return $this->unauthorizedResponse('Website is not active');
        }

        // Check API key permissions for the current route
        $route = $request->route()->getName();
        if (!$this->hasPermission($apiKeyData, $route)) {
            return $this->forbiddenResponse('Insufficient permissions');
        }

        // Rate limiting per API key
        $rateLimitKey = "rate_limit:api:{$apiKey}";
        $maxAttempts = $apiKeyData->rate_limit_per_minute ?? 60;
        
        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return $this->rateLimitResponse($seconds);
        }

        RateLimiter::hit($rateLimitKey, 60); // 60 seconds window

        // Log API usage
        $this->logApiUsage($apiKeyData, $request);

        // Add API key and website to request attributes
        $request->attributes->add([
            'api_key' => $apiKeyData,
            'website' => $apiKeyData->website
        ]);

        return $next($request);
    }

    /**
     * Validate API key format
     */
    private function isValidKeyFormat(string $key): bool
    {
        // API key should be 64 characters long and alphanumeric
        return preg_match('/^[a-zA-Z0-9]{64}$/', $key);
    }

    /**
     * Check if API key has permission for the route
     */
    private function hasPermission(ApiKey $apiKey, ?string $route): bool
    {
        if (!$route) {
            return false;
        }

        $permissions = $apiKey->permissions ?? [];

        // Default permissions mapping
        $routePermissions = [
            'api.auth.register' => 'auth.register',
            'api.auth.login' => 'auth.login',
            'api.auth.logout' => 'auth.logout',
            'api.auth.profile' => 'auth.profile',
            'api.webblocs.index' => 'webblocs.read',
            'api.webblocs.show' => 'webblocs.read',
            'api.webblocs.store' => 'webblocs.create',
            'api.webblocs.update' => 'webblocs.update',
            'api.webblocs.destroy' => 'webblocs.delete',
            'api.webblocs.render' => 'webblocs.render',
            'api.webblocs.metadata' => 'webblocs.read'
        ];

        $requiredPermission = $routePermissions[$route] ?? null;

        if (!$requiredPermission) {
            return true; // Allow if no specific permission required
        }

        return in_array($requiredPermission, $permissions) || in_array('*', $permissions);
    }

    /**
     * Log API usage for analytics
     */
    private function logApiUsage(ApiKey $apiKey, Request $request): void
    {
        try {
            // Update API key usage statistics
            $apiKey->increment('total_requests');
            $apiKey->update(['last_used_at' => now()]);

            // Update website statistics
            $apiKey->website->increment('total_requests');

            // Store detailed usage log (could be moved to a queue for performance)
            $this->storeUsageLog($apiKey, $request);

        } catch (\Exception $e) {
            // Log error but don't fail the request
            Log::error('Failed to log API usage', [
                'api_key_id' => $apiKey->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Store detailed usage log
     */
    private function storeUsageLog(ApiKey $apiKey, Request $request): void
    {
        // This could be implemented as a job for better performance
        $logData = [
            'api_key_id' => $apiKey->id,
            'website_id' => $apiKey->website_id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'timestamp' => now(),
            'response_time' => 0 // Will be updated after response
        ];

        // Store in cache or database for analytics
        Cache::put("api_log:{$apiKey->id}:" . time(), $logData, 3600);
    }

    /**
     * Response helpers
     */
    private function unauthorizedResponse(string $message): Response
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => 'UNAUTHORIZED'
        ], 401);
    }

    private function forbiddenResponse(string $message): Response
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => 'FORBIDDEN'
        ], 403);
    }

    private function rateLimitResponse(int $seconds): Response
    {
        return response()->json([
            'success' => false,
            'message' => 'Rate limit exceeded',
            'error_code' => 'RATE_LIMIT_EXCEEDED',
            'retry_after' => $seconds
        ], 429)->header('Retry-After', $seconds);
    }
}