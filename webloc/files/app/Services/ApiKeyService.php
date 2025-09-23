<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\Website;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ApiKeyService
{
    public function generateApiKey(Website $website, array $data = []): ApiKey
    {
        $keyString = $this->generateKeyString();
        $hashedKey = $this->hashKey($keyString);

        $apiKey = ApiKey::create([
            'website_id' => $website->id,
            'key' => $keyString,
            'hashed_key' => $hashedKey,
            'name' => $data['name'] ?? "API Key for {$website->name}",
            'status' => 'active',
            'permissions' => $data['permissions'] ?? $this->getDefaultPermissions(),
            'rate_limit' => $data['rate_limit'] ?? config('webbloc.api.default_rate_limit', 1000),
            'allowed_domains' => $data['allowed_domains'] ?? [$website->domain],
            'allowed_ips' => $data['allowed_ips'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'metadata' => [
                'user_agent' => request()->userAgent(),
                'ip_address' => request()->ip(),
                'generated_by' => 'system',
            ],
        ]);

        // Clear cache for this key
        $this->clearKeyCache($keyString);

        return $apiKey;
    }

    public function validateApiKey(string $key): ?ApiKey
    {
        $cacheKey = "api_key_validation:{$key}";
        
        return Cache::remember($cacheKey, 300, function () use ($key) {
            $hashedKey = $this->hashKey($key);
            
            $apiKey = ApiKey::where('hashed_key', $hashedKey)
                           ->where('status', 'active')
                           ->with('website')
                           ->first();

            if (!$apiKey) {
                return null;
            }

            // Check expiration
            if ($apiKey->expires_at && $apiKey->expires_at->isPast()) {
                $apiKey->update(['status' => 'expired']);
                return null;
            }

            // Check website status
            if ($apiKey->website->status !== 'active') {
                return null;
            }

            return $apiKey;
        });
    }

    public function checkRateLimit(ApiKey $apiKey, string $endpoint = null): bool
    {
        $rateLimitKey = "rate_limit:{$apiKey->id}:" . now()->format('Y-m-d-H');
        $currentUsage = Cache::get($rateLimitKey, 0);

        // Check global rate limit
        if ($currentUsage >= $apiKey->rate_limit) {
            return false;
        }

        // Check endpoint-specific rate limits
        if ($endpoint && isset($apiKey->endpoint_limits[$endpoint])) {
            $endpointKey = "rate_limit:{$apiKey->id}:{$endpoint}:" . now()->format('Y-m-d-H');
            $endpointUsage = Cache::get($endpointKey, 0);
            
            if ($endpointUsage >= $apiKey->endpoint_limits[$endpoint]) {
                return false;
            }
        }

        return true;
    }

    public function incrementUsage(ApiKey $apiKey, string $endpoint = null): void
    {
        $rateLimitKey = "rate_limit:{$apiKey->id}:" . now()->format('Y-m-d-H');
        Cache::increment($rateLimitKey);
        Cache::put($rateLimitKey, Cache::get($rateLimitKey), 3600); // Expire after 1 hour

        // Increment endpoint-specific usage
        if ($endpoint) {
            $endpointKey = "rate_limit:{$apiKey->id}:{$endpoint}:" . now()->format('Y-m-d-H');
            Cache::increment($endpointKey);
            Cache::put($endpointKey, Cache::get($endpointKey), 3600);
        }

        // Update API key statistics
        $apiKey->increment('request_count');
        $apiKey->update(['last_used_at' => now()]);

        // Log usage for analytics
        $this->logUsage($apiKey, $endpoint);
    }

    public function checkPermission(ApiKey $apiKey, string $permission): bool
    {
        if (empty($apiKey->permissions)) {
            return true; // No restrictions
        }

        return in_array($permission, $apiKey->permissions) || 
               in_array('*', $apiKey->permissions);
    }

    public function checkDomainAccess(ApiKey $apiKey, string $domain): bool
    {
        if (empty($apiKey->allowed_domains)) {
            return true; // No domain restrictions
        }

        // Check exact domain match
        if (in_array($domain, $apiKey->allowed_domains)) {
            return true;
        }

        // Check wildcard domains
        foreach ($apiKey->allowed_domains as $allowedDomain) {
            if (Str::startsWith($allowedDomain, '*.')) {
                $pattern = str_replace('*.', '', $allowedDomain);
                if (Str::endsWith($domain, $pattern)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function checkIpAccess(ApiKey $apiKey, string $ip): bool
    {
        if (empty($apiKey->allowed_ips)) {
            return true; // No IP restrictions
        }

        return in_array($ip, $apiKey->allowed_ips);
    }

    public function regenerateApiKey(ApiKey $apiKey): ApiKey
    {
        $newKeyString = $this->generateKeyString();
        $newHashedKey = $this->hashKey($newKeyString);

        // Clear old key cache
        $this->clearKeyCache($apiKey->key);

        // Update the API key
        $apiKey->update([
            'key' => $newKeyString,
            'hashed_key' => $newHashedKey,
            'regenerated_at' => now(),
            'metadata' => array_merge($apiKey->metadata ?? [], [
                'regenerated_by' => Auth::user()?->id ?? 'system',
                'regenerated_at' => now()->toISOString(),
                'previous_key_hash' => $apiKey->hashed_key,
            ]),
        ]);

        return $apiKey->refresh();
    }

    public function revokeApiKey(ApiKey $apiKey, string $reason = null): bool
    {
        $apiKey->update([
            'status' => 'revoked',
            'revoked_at' => now(),
            'metadata' => array_merge($apiKey->metadata ?? [], [
                'revoked_by' => Auth::user()?->id ?? 'system',
                'revoked_reason' => $reason,
                'revoked_at' => now()->toISOString(),
            ]),
        ]);

        // Clear cache
        $this->clearKeyCache($apiKey->key);

        return true;
    }

    public function getApiKeyUsage(ApiKey $apiKey, Carbon $startDate = null, Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();

        $usage = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $rateLimitKey = "rate_limit:{$apiKey->id}:" . $current->format('Y-m-d-H');
            $usage[$current->format('Y-m-d H:00')] = Cache::get($rateLimitKey, 0);
            $current->addHour();
        }

        return $usage;
    }

    public function cleanupExpiredKeys(): int
    {
        $expiredKeys = ApiKey::where('status', 'active')
                            ->whereNotNull('expires_at')
                            ->where('expires_at', '<', now())
                            ->get();

        foreach ($expiredKeys as $key) {
            $key->update(['status' => 'expired']);
            $this->clearKeyCache($key->key);
        }

        return $expiredKeys->count();
    }

    protected function generateKeyString(): string
    {
        $prefix = config('webbloc.api.key_prefix', 'wb');
        $randomString = Str::random(32);
        
        return $prefix . '_' . $randomString;
    }

    protected function hashKey(string $key): string
    {
        return hash('sha256', $key . config('app.key'));
    }

    protected function clearKeyCache(string $key): void
    {
        Cache::forget("api_key_validation:{$key}");
    }

    protected function getDefaultPermissions(): array
    {
        return [
            'webbloc.read',
            'webbloc.create',
            'webbloc.update',
            'webbloc.delete',
            'auth.login',
            'auth.register',
            'auth.profile',
        ];
    }

    protected function logUsage(ApiKey $apiKey, string $endpoint = null): void
    {
        // This would typically log to a dedicated usage table or analytics service
        // For now, we'll just update the API key's usage statistics
        
        $metadata = $apiKey->metadata ?? [];
        $metadata['usage_logs'] = $metadata['usage_logs'] ?? [];
        
        // Keep only the last 100 usage logs to prevent metadata from growing too large
        if (count($metadata['usage_logs']) >= 100) {
            $metadata['usage_logs'] = array_slice($metadata['usage_logs'], -99);
        }

        $metadata['usage_logs'][] = [
            'timestamp' => now()->toISOString(),
            'endpoint' => $endpoint,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        $apiKey->update(['metadata' => $metadata]);
    }
}
