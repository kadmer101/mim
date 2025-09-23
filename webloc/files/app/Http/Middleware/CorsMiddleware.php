<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get website from request attributes (set by ValidateApiKey middleware)
        $website = $request->attributes->get('website');
        
        // Get allowed origins for this website
        $allowedOrigins = $this->getAllowedOrigins($website);
        
        // Handle preflight requests
        if ($request->isMethod('OPTIONS')) {
            return $this->handlePreflightRequest($request, $allowedOrigins);
        }

        $response = $next($request);

        return $this->addCorsHeaders($request, $response, $allowedOrigins);
    }

    /**
     * Get allowed origins for the website
     */
    private function getAllowedOrigins($website): array
    {
        if (!$website) {
            return config('webbloc.cors.allowed_origins', []);
        }

        // Get origins from website configuration
        $websiteOrigins = $website->allowed_origins ?? [];
        
        // Merge with global allowed origins
        $globalOrigins = config('webbloc.cors.allowed_origins', []);
        
        $allOrigins = array_merge($globalOrigins, $websiteOrigins);

        // Add website domain if available
        if ($website->domain) {
            $allOrigins[] = 'https://' . $website->domain;
            $allOrigins[] = 'http://' . $website->domain;
            
            // Add www variant
            if (!str_starts_with($website->domain, 'www.')) {
                $allOrigins[] = 'https://www.' . $website->domain;
                $allOrigins[] = 'http://www.' . $website->domain;
            }
        }

        return array_unique($allOrigins);
    }

    /**
     * Handle preflight requests
     */
    private function handlePreflightRequest(Request $request, array $allowedOrigins): Response
    {
        $origin = $request->header('Origin');
        
        // Check if origin is allowed
        if (!$this->isOriginAllowed($origin, $allowedOrigins)) {
            return response()->json(['message' => 'CORS policy violation'], 403);
        }

        $headers = [
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => implode(', ', [
                'Content-Type',
                'Authorization',
                'X-Requested-With',
                'X-API-Key',
                'Accept',
                'Origin',
                'Cache-Control',
                'Pragma'
            ]),
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age' => '86400', // 24 hours
        ];

        return response('', 200, $headers);
    }

    /**
     * Add CORS headers to response
     */
    private function addCorsHeaders(Request $request, Response $response, array $allowedOrigins): Response
    {
        $origin = $request->header('Origin');

        // Only add CORS headers if origin is provided and allowed
        if ($origin && $this->isOriginAllowed($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Expose-Headers', implode(', ', [
                'Content-Type',
                'Content-Length',
                'X-Total-Count',
                'X-Rate-Limit-Remaining',
                'X-Rate-Limit-Reset'
            ]));
        } elseif (in_array('*', $allowedOrigins)) {
            // Allow all origins if wildcard is set
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-API-Key');
        }

        // Add security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Add cache control for API responses
        if ($request->is('api/*')) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }

    /**
     * Check if origin is allowed
     */
    private function isOriginAllowed(?string $origin, array $allowedOrigins): bool
    {
        if (!$origin) {
            return false;
        }

        // Check for wildcard
        if (in_array('*', $allowedOrigins)) {
            return true;
        }

        // Exact match
        if (in_array($origin, $allowedOrigins)) {
            return true;
        }

        // Pattern matching for subdomain wildcards
        foreach ($allowedOrigins as $allowedOrigin) {
            if (str_contains($allowedOrigin, '*')) {
                $pattern = str_replace(['*', '.'], ['.+', '\.'], $allowedOrigin);
                if (preg_match("/^{$pattern}$/", $origin)) {
                    return true;
                }
            }
        }

        // Development environment checks
        if (app()->environment('local', 'development')) {
            $developmentOrigins = [
                'http://localhost',
                'http://127.0.0.1',
                'https://localhost',
                'https://127.0.0.1'
            ];

            foreach ($developmentOrigins as $devOrigin) {
                if (str_starts_with($origin, $devOrigin)) {
                    return true;
                }
            }
        }

        return false;
    }
}