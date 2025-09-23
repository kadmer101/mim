<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ApiKey extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'website_id',
        'user_id',
        'name',
        'public_key',
        'secret_key',
        'key_type',
        'permissions',
        'allowed_webbloc_types',
        'allowed_domains',
        'allowed_ips',
        'rate_limit_per_minute',
        'rate_limit_per_hour',
        'rate_limit_per_day',
        'status',
        'expires_at',
        'last_used_at',
        'activated_at',
        'total_requests',
        'successful_requests',
        'failed_requests',
        'current_month_requests',
        'current_day_requests',
        'requires_signature',
        'signature_algorithm',
        'max_request_size',
        'security_settings',
        'environment',
        'user_agent_pattern',
        'referer_pattern',
        'webhook_url',
        'webhook_events',
        'webhook_secret',
        'monitoring_enabled',
        'alert_thresholds',
        'alert_email',
        'description',
        'tags',
        'metadata',
        'created_by_ip',
        'last_used_ip',
        'usage_history',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'permissions' => 'array',
        'allowed_webbloc_types' => 'array',
        'allowed_domains' => 'array',
        'allowed_ips' => 'array',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'activated_at' => 'datetime',
        'total_requests' => 'integer',
        'successful_requests' => 'integer',
        'failed_requests' => 'integer',
        'current_month_requests' => 'integer',
        'current_day_requests' => 'integer',
        'requires_signature' => 'boolean',
        'max_request_size' => 'integer',
        'security_settings' => 'array',
        'webhook_events' => 'array',
        'monitoring_enabled' => 'boolean',
        'alert_thresholds' => 'array',
        'tags' => 'array',
        'metadata' => 'array',
        'usage_history' => 'array',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'secret_key',
        'webhook_secret',
    ];

    /**
     * The supported key types.
     *
     * @var array<string, string>
     */
    public const KEY_TYPES = [
        'standard' => 'Standard API Key',
        'webhook' => 'Webhook API Key',
        'admin' => 'Administrative API Key',
        'readonly' => 'Read-Only API Key',
        'development' => 'Development API Key',
    ];

    /**
     * The supported environments.
     *
     * @var array<string>
     */
    public const ENVIRONMENTS = ['development', 'staging', 'production'];

    /**
     * The signature algorithms.
     *
     * @var array<string>
     */
    public const SIGNATURE_ALGORITHMS = ['hmac-sha256', 'hmac-sha512'];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($apiKey) {
            if (!$apiKey->public_key) {
                $apiKey->public_key = $apiKey->generatePublicKey();
            }
            
            if (!$apiKey->secret_key && $apiKey->key_type !== 'readonly') {
                $apiKey->secret_key = $apiKey->generateSecretKey();
            }
            
            if (!$apiKey->activated_at && $apiKey->status === 'active') {
                $apiKey->activated_at = now();
            }
        });

        static::updating(function ($apiKey) {
            if ($apiKey->isDirty('status') && $apiKey->status === 'active' && !$apiKey->activated_at) {
                $apiKey->activated_at = now();
            }
        });
    }

    /**
     * Get the website that owns this API key.
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * Get the user that owns this API key.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a new public key.
     */
    protected function generatePublicKey(): string
    {
        do {
            $key = 'pk_' . Str::random(61);
        } while (static::where('public_key', $key)->exists());
        
        return $key;
    }

    /**
     * Generate a new secret key.
     */
    protected function generateSecretKey(): string
    {
        do {
            $key = 'sk_' . Str::random(125);
        } while (static::where('secret_key', $key)->exists());
        
        return $key;
    }

    /**
     * Check if the API key is active and not expired.
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if the API key has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];
        
        // Check for wildcard permission
        if (in_array('*', $permissions)) {
            return true;
        }
        
        // Check for specific permission
        if (in_array($permission, $permissions)) {
            return true;
        }
        
        // Check for parent permission (e.g., 'webbloc.*' covers 'webbloc.create')
        foreach ($permissions as $perm) {
            if (Str::endsWith($perm, '*') && Str::startsWith($permission, Str::before($perm, '*'))) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if the API key can access a specific WebBloc type.
     */
    public function canAccessWebBlocType(string $type): bool
    {
        $allowedTypes = $this->allowed_webbloc_types ?? [];
        
        // If no restrictions, allow all
        if (empty($allowedTypes)) {
            return true;
        }
        
        return in_array($type, $allowedTypes);
    }

    /**
     * Check if the request is from an allowed domain.
     */
    public function isDomainAllowed(string $domain): bool
    {
        $allowedDomains = $this->allowed_domains ?? [];
        
        // If no restrictions, allow all
        if (empty($allowedDomains)) {
            return true;
        }
        
        // Check for exact match or wildcard
        foreach ($allowedDomains as $allowedDomain) {
            if ($allowedDomain === '*' || $allowedDomain === $domain) {
                return true;
            }
            
            // Check for subdomain wildcard (e.g., *.example.com)
            if (Str::startsWith($allowedDomain, '*.')) {
                $baseDomain = Str::after($allowedDomain, '*.');
                if (Str::endsWith($domain, $baseDomain)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Check if the request is from an allowed IP address.
     */
    public function isIpAllowed(string $ip): bool
    {
        $allowedIps = $this->allowed_ips ?? [];
        
        // If no restrictions, allow all
        if (empty($allowedIps)) {
            return true;
        }
        
        foreach ($allowedIps as $allowedIp) {
            // Check for exact match
            if ($allowedIp === $ip) {
                return true;
            }
            
            // Check for CIDR notation
            if (Str::contains($allowedIp, '/')) {
                if ($this->ipInRange($ip, $allowedIp)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Check if an IP is in a CIDR range.
     */
    protected function ipInRange(string $ip, string $range): bool
    {
        [$subnet, $bits] = explode('/', $range);
        
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        
        return ($ip & $mask) === ($subnet & $mask);
    }

    /**
     * Check if the rate limit has been exceeded.
     */
    public function hasExceededRateLimit(string $period = 'minute'): bool
    {
        $now = Carbon::now();
        
        switch ($period) {
            case 'minute':
                $limit = $this->rate_limit_per_minute;
                $requests = $this->getRequestsInPeriod($now->copy()->subMinute(), $now);
                break;
                
            case 'hour':
                $limit = $this->rate_limit_per_hour;
                $requests = $this->getRequestsInPeriod($now->copy()->subHour(), $now);
                break;
                
            case 'day':
                $limit = $this->rate_limit_per_day;
                $requests = $this->current_day_requests;
                break;
                
            default:
                return false;
        }
        
        return $requests >= $limit;
    }

    /**
     * Get the number of requests in a specific time period.
     */
    protected function getRequestsInPeriod(Carbon $start, Carbon $end): int
    {
        // This is a simplified implementation
        // In a real application, you might want to use a more sophisticated
        // rate limiting system like Redis with sliding window
        
        $usageHistory = $this->usage_history ?? [];
        $count = 0;
        
        foreach ($usageHistory as $entry) {
            $timestamp = Carbon::parse($entry['timestamp']);
            if ($timestamp->between($start, $end)) {
                $count += $entry['requests'] ?? 1;
            }
        }
        
        return $count;
    }

    /**
     * Record an API request.
     */
    public function recordRequest(bool $successful = true, string $ip = null): void
    {
        $this->increment('total_requests');
        
        if ($successful) {
            $this->increment('successful_requests');
        } else {
            $this->increment('failed_requests');
        }
        
        // Update day and month counters
        $this->increment('current_day_requests');
        $this->increment('current_month_requests');
        
        // Update last used information
        $this->update([
            'last_used_at' => now(),
            'last_used_ip' => $ip,
        ]);
        
        // Update usage history
        $this->updateUsageHistory();
        
        // Check alert thresholds
        $this->checkAlertThresholds();
    }

    /**
     * Update the usage history.
     */
    protected function updateUsageHistory(): void
    {
        $history = $this->usage_history ?? [];
        $today = now()->format('Y-m-d');
        
        // Find or create today's entry
        $todayIndex = null;
        foreach ($history as $index => $entry) {
            if ($entry['date'] === $today) {
                $todayIndex = $index;
                break;
            }
        }
        
        if ($todayIndex !== null) {
            $history[$todayIndex]['requests']++;
        } else {
            $history[] = [
                'date' => $today,
                'requests' => 1,
                'timestamp' => now()->toISOString(),
            ];
        }
        
        // Keep only last 30 days
        $history = array_slice($history, -30);
        
        $this->update(['usage_history' => $history]);
    }

    /**
     * Check if alert thresholds have been exceeded.
     */
    protected function checkAlertThresholds(): void
    {
        if (!$this->monitoring_enabled || !$this->alert_thresholds) {
            return;
        }
        
        $thresholds = $this->alert_thresholds;
        
        // Check daily request threshold
        if (isset($thresholds['daily_requests'])) {
            $threshold = $thresholds['daily_requests'];
            if ($this->current_day_requests >= $threshold) {
                // Trigger alert (implementation depends on your notification system)
                $this->triggerAlert('daily_requests_exceeded', [
                    'requests' => $this->current_day_requests,
                    'threshold' => $threshold,
                ]);
            }
        }
        
        // Check error rate threshold
        if (isset($thresholds['error_rate'])) {
            $threshold = $thresholds['error_rate'];
            $errorRate = $this->total_requests > 0 ? 
                ($this->failed_requests / $this->total_requests) * 100 : 0;
                
            if ($errorRate >= $threshold) {
                $this->triggerAlert('error_rate_exceeded', [
                    'error_rate' => $errorRate,
                    'threshold' => $threshold,
                ]);
            }
        }
    }

    /**
     * Trigger an alert.
     */
    protected function triggerAlert(string $type, array $data): void
    {
        // This is where you would implement your alert system
        // For example, send an email, create a notification, etc.
        
        Log::warning("API Key Alert: {$type}", [
            'api_key_id' => $this->id,
            'website_id' => $this->website_id,
            'data' => $data,
        ]);
    }

    /**
     * Reset the daily request counter.
     */
    public function resetDailyRequests(): void
    {
        $this->update(['current_day_requests' => 0]);
    }

    /**
     * Reset the monthly request counter.
     */
    public function resetMonthlyRequests(): void
    {
        $this->update(['current_month_requests' => 0]);
    }

    /**
     * Generate a signature for request validation.
     */
    public function generateSignature(string $payload, string $algorithm = null): string
    {
        $algorithm = $algorithm ?? $this->signature_algorithm;
        
        switch ($algorithm) {
            case 'hmac-sha512':
                return hash_hmac('sha512', $payload, $this->secret_key);
                
            case 'hmac-sha256':
            default:
                return hash_hmac('sha256', $payload, $this->secret_key);
        }
    }

    /**
     * Verify a request signature.
     */
    public function verifySignature(string $payload, string $signature, string $algorithm = null): bool
    {
        if (!$this->requires_signature) {
            return true;
        }
        
        $expectedSignature = $this->generateSignature($payload, $algorithm);
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by key type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('key_type', $type);
    }

    /**
     * Scope to filter by environment.
     */
    public function scopeEnvironment($query, string $environment)
    {
        return $query->where('environment', $environment);
    }

    /**
     * Scope to filter non-expired keys.
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'green',
            'inactive' => 'gray',
            'suspended' => 'yellow',
            'revoked' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get the formatted expiry date.
     */
    public function getFormattedExpiryAttribute(): ?string
    {
        return $this->expires_at?->format('M d, Y H:i');
    }

    /**
     * Get the usage percentage for the day.
     */
    public function getDailyUsagePercentageAttribute(): float
    {
        if ($this->rate_limit_per_day <= 0) {
            return 0;
        }
        
        return ($this->current_day_requests / $this->rate_limit_per_day) * 100;
    }
}