<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class WebBlocRateLimiter
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $type = 'api'): Response
    {
        $website = $request->attributes->get('website');
        $apiKey = $request->attributes->get('api_key');

        if (!$website || !$apiKey) {
            return $next($request);
        }

        // Apply multiple rate limiting strategies
        $limitChecks = [
            'global' => $this->checkGlobalRateLimit($request),
            'website' => $this->checkWebsiteRateLimit($website, $request),
            'api_key' => $this->checkApiKeyRateLimit($apiKey, $request),
            'ip' => $this->checkIpRateLimit($request),
            'endpoint' => $this->checkEndpointRateLimit($request, $type),
        ];

        foreach ($limitChecks as $limitType => $result) {
            if ($result['exceeded']) {
                return $this->rateLimitResponse($result['seconds'], $limitType, $result['limit']);
            }
        }

        // Track request for analytics
        $this->trackRequest($website, $apiKey, $request);

        $response = $next($request);

        // Add rate limit headers to response
        $this->addRateLimitHeaders($response, $apiKey);

        return $response;
    }

    /**
     * Check global rate limit
     */
    private function checkGlobalRateLimit(Request $request): array
    {
        $key = 'rate_limit:global';
        $limit = config('webbloc.rate_limits.global_per_minute', 10000);
        
        return $this->performRateCheck($key, $limit, 60);
    }

    /**
     * Check website-specific rate limit
     */
    private function checkWebsiteRateLimit($website, Request $request): array
    {
        $key = "rate_limit:website:{$website->id}";
        $limit = $website->rate_limit_per_minute ?? config('webbloc.rate_limits.per_website', 1000);
        
        return $this->performRateCheck($key, $limit, 60);
    }

    /**
     * Check API key rate limit
     */
    private function checkApiKeyRateLimit($apiKey, Request $request): array
    {
        $key = "rate_limit:api_key:{$apiKey->id}";
        $limit = $apiKey->rate_limit_per_minute ?? config('webbloc.rate_limits.per_api_key', 100);
        
        return $this->performRateCheck($key, $limit, 60);
    }

    /**
     * Check IP-based rate limit
     */
    private function checkIpRateLimit(Request $request): array
    {
        $ip = $request->ip();
        $key = "rate_limit:ip:{$ip}";
        $limit = config('webbloc.rate_limits.per_ip', 200);
        
        return $this->performRateCheck($key, $limit, 60);
    }

    /**
     * Check endpoint-specific rate limit
     */
    private function checkEndpointRateLimit(Request $request, string $type): array
    {
        $endpoint = $request->route()->getName() ?? $request->path();
        $key = "rate_limit:endpoint:{$endpoint}";
        
        // Different limits for different endpoint types
        $limits = [
            'auth' => config('webbloc.rate_limits.auth_per_minute', 10),
            'read' => config('webbloc.rate_limits.read_per_minute', 100),
            'write' => config('webbloc.rate_limits.write_per_minute', 30),
            'api' => config('webbloc.rate_limits.default_per_minute', 60),
        ];
        
        $limit = $limits[$type] ?? $limits['api'];
        
        return $this->performRateCheck($key, $limit, 60);
    }

    /**
     * Perform rate limit check
     */
    private function performRateCheck(string $key, int $limit, int $seconds): array
    {
        $current = RateLimiter::attempts($key);
        
        if ($current >= $limit) {
            $availableIn = RateLimiter::availableIn($key);
            return [
                'exceeded' => true,
                'seconds' => $availableIn,
                'limit' => $limit,
                'current' => $current
            ];
        }

        RateLimiter::hit($key, $seconds);
        
        return [
            'exceeded' => false,
            'seconds' => 0,
            'limit' => $limit,
            'current' => $current + 1
        ];
    }

    /**
     * Track request for analytics
     */
    private function trackRequest($website, $apiKey, Request $request): void
    {
        try {
            $timestamp = time();
            $hour = floor($timestamp / 3600);
            
            // Track hourly website requests
            $websiteKey = "analytics:website:{$website->id}:hour:{$hour}";
            Cache::increment($websiteKey, 1);
            Cache::expire($websiteKey, 3600 * 25); // Keep for 25 hours

            // Track hourly API key requests
            $apiKeyKey = "analytics:api_key:{$apiKey->id}:hour:{$hour}";
            Cache::increment($apiKeyKey, 1);
            Cache::expire($apiKeyKey, 3600 * 25);

            // Track endpoint usage
            $endpoint = $request->route()->getName() ?? 'unknown';
            $endpointKey = "analytics:endpoint:{$endpoint}:hour:{$hour}";
            Cache::increment($endpointKey, 1);
            Cache::expire($endpointKey, 3600 * 25);

            // Track response format distribution
            $this->trackResponseFormat($website, $request);

        } catch (\Exception $e) {
            // Don't fail request if analytics tracking fails
            Log::warning('Failed to track request analytics', [
                'error' => $e->getMessage(),
                'website_id' => $website->id,
                'api_key_id' => $apiKey->id
            ]);
        }
    }

    /**
     * Track response format distribution (75% HTML, 15% JSON, 10% other)
     */
    private function trackResponseFormat($website, Request $request): void
    {
        $format = $this->determineResponseFormat();
        $hour = floor(time() / 3600);
        
        $formatKey = "analytics:website:{$website->id}:format:{$format}:hour:{$hour}";
        Cache::increment($formatKey, 1);
        Cache::expire($formatKey, 3600 * 25);
    }

    /**
     * Determine response format based on WebBloc distribution
     */
    private function determineResponseFormat(): string
    {
        $random = rand(1, 100);
        
        if ($random <= 75) {
            return 'html';
        } elseif ($random <= 90) {
            return 'json';
        } else {
            return 'other';
        }
    }

    /**
     * Add rate limit headers to response
     */
    private function addRateLimitHeaders(Response $response, $apiKey): void
    {
        try {
            $key = "rate_limit:api_key:{$apiKey->id}";
            $limit = $apiKey->rate_limit_per_minute ?? 100;
            $remaining = max(0, $limit - RateLimiter::attempts($key));
            $resetTime = time() + RateLimiter::availableIn($key);

            $response->headers->set('X-Rate-Limit-Limit', $limit);
            $response->headers->set('X-Rate-Limit-Remaining', $remaining);
            $response->headers->set('X-Rate-Limit-Reset', $resetTime);

        } catch (\Exception $e) {
            // Don't fail if header setting fails
        }
    }

    /**
     * Rate limit exceeded response
     */
    private function rateLimitResponse(int $seconds, string $type, int $limit): Response
    {
        $message = "Rate limit exceeded for {$type}. Try again in {$seconds} seconds.";
        
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => 'RATE_LIMIT_EXCEEDED',
            'retry_after' => $seconds,
            'limit_type' => $type,
            'limit' => $limit
        ], 429)->header('Retry-After', $seconds);
    }
}