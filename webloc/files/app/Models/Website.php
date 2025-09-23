<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan; // Import the Artisan facades

class Website extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'url',
        'description',
        'domain',
        'sqlite_database_path',
        'sqlite_database_name',
        'sqlite_database_exists',
        'owner_id',
        'owner_email',
        'status',
        'verified',
        'verified_at',
        'subscription_type',
        'subscription_limits',
        'subscription_expires_at',
        'enabled_webblocs',
        'webbloc_settings',
        'public_api_key',
        'secret_api_key',
        'api_permissions',
        'api_rate_limit',
        'total_api_calls',
        'monthly_api_calls',
        'last_api_call_at',
        'cdn_url',
        'custom_css',
        'custom_js',
        'allowed_domains',
        'cors_settings',
        'ssl_required',
        'metadata',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sqlite_database_exists' => 'boolean',
        'verified' => 'boolean',
        'verified_at' => 'datetime',
        'subscription_limits' => 'array',
        'subscription_expires_at' => 'datetime',
        'enabled_webblocs' => 'array',
        'webbloc_settings' => 'array',
        'api_permissions' => 'array',
        'total_api_calls' => 'integer',
        'monthly_api_calls' => 'integer',
        'last_api_call_at' => 'datetime',
        'custom_css' => 'array',
        'custom_js' => 'array',
        'allowed_domains' => 'array',
        'cors_settings' => 'array',
        'ssl_required' => 'boolean',
        'metadata' => 'array',
        'settings' => 'array',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'secret_api_key',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($website) {
            $website->public_api_key = $website->public_api_key ?: Str::random(64);
            $website->secret_api_key = $website->secret_api_key ?: Str::random(128);
            $website->sqlite_database_name = 'website_' . time() . '_' . Str::random(8);
            $website->sqlite_database_path = storage_path('databases/' . $website->sqlite_database_name . '.sqlite');
        });

        static::created(function ($website) {
            $website->createSqliteDatabase();
        });

        static::deleting(function ($website) {
            if ($website->sqlite_database_exists) {
                $website->deleteSqliteDatabase();
            }
        });
    }

    /**
     * Get the owner of the website.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the website statistics.
     */
    public function statistics(): HasMany
    {
        return $this->hasMany(WebsiteStatistic::class);
    }

    /**
     * Get the API keys for the website.
     */
    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    /**
     * Get the WebBloc instances for the website.
     */
    public function webBlocInstances(): HasMany
    {
        return $this->hasMany(WebBlocInstance::class);
    }

    /**
     * Create the SQLite database for this website.
     */
    public function createSqliteDatabase(): bool
    {
        try {
            $databasePath = $this->sqlite_database_path;
            
            // Ensure the databases directory exists
            $directory = dirname($databasePath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Create the SQLite database file
            if (!file_exists($databasePath)) {
                touch($databasePath);
            }

            // Configure the dynamic connection
            $this->configureDynamicConnection();

            // Run SQLite migrations
            $this->runSqliteMigrations();

            // Update the database exists flag
            $this->update(['sqlite_database_exists' => true]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to create SQLite database for website ' . $this->id . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Configure dynamic SQLite connection for this website.
     */
    public function configureDynamicConnection(): void
    {
        $connectionName = $this->getConnectionName();
        
        Config::set("database.connections.{$connectionName}", [
            'driver' => 'sqlite',
            'database' => $this->sqlite_database_path,
            'prefix' => '',
            'foreign_key_constraints' => true,
            'options' => [
                \PDO::ATTR_TIMEOUT => 30,
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ],
        ]);
    }

    /**
     * Get the connection name for this website's SQLite database.
     */
    public function getConnectionName(): string
    {
        return 'webbloc_website_' . $this->id;
    }

    /**
     * Run SQLite migrations for this website.
     */
    public function runSqliteMigrations(): void
    {
        $connectionName = $this->getConnectionName();
        
        // Set the connection for migrations
        Config::set('database.default', $connectionName);
        
        // Run the SQLite-specific migrations
        Artisan::call('migrate', [
            '--database' => $connectionName,
            '--path' => 'database/migrations/sqlite',
            '--force' => true,
        ]);
        
        // Reset to default connection
        Config::set('database.default', env('DB_CONNECTION', 'mysql'));
    }

    /**
     * Delete the SQLite database for this website.
     */
    public function deleteSqliteDatabase(): bool
    {
        try {
            if (file_exists($this->sqlite_database_path)) {
                unlink($this->sqlite_database_path);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete SQLite database for website ' . $this->id . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get a connection to this website's SQLite database.
     */
    public function getSqliteConnection()
    {
        $this->configureDynamicConnection();
        return DB::connection($this->getConnectionName());
    }

    /**
     * Check if a WebBloc type is enabled for this website.
     */
    public function hasWebBlocType(string $type): bool
    {
        $enabled = $this->enabled_webblocs ?? [];
        return in_array($type, $enabled);
    }

    /**
     * Enable a WebBloc type for this website.
     */
    public function enableWebBlocType(string $type): void
    {
        $enabled = $this->enabled_webblocs ?? [];
        if (!in_array($type, $enabled)) {
            $enabled[] = $type;
            $this->update(['enabled_webblocs' => $enabled]);
        }
    }

    /**
     * Disable a WebBloc type for this website.
     */
    public function disableWebBlocType(string $type): void
    {
        $enabled = $this->enabled_webblocs ?? [];
        $enabled = array_filter($enabled, fn($t) => $t !== $type);
        $this->update(['enabled_webblocs' => array_values($enabled)]);
    }

    /**
     * Get WebBloc settings for a specific type.
     */
    public function getWebBlocSettings(string $type): array
    {
        $settings = $this->webbloc_settings ?? [];
        return $settings[$type] ?? [];
    }

    /**
     * Update WebBloc settings for a specific type.
     */
    public function updateWebBlocSettings(string $type, array $settings): void
    {
        $allSettings = $this->webbloc_settings ?? [];
        $allSettings[$type] = $settings;
        $this->update(['webbloc_settings' => $allSettings]);
    }

    /**
     * Check if the website's subscription is active.
     */
    public function hasActiveSubscription(): bool
    {
        if (!$this->subscription_expires_at) {
            return $this->subscription_type === 'free';
        }
        
        return $this->subscription_expires_at->isFuture();
    }

    /**
     * Check if the API rate limit has been exceeded.
     */
    public function hasExceededRateLimit(): bool
    {
        $limits = $this->subscription_limits ?? [];
        $dailyLimit = $limits['daily_api_calls'] ?? 1000;
        
        return $this->monthly_api_calls >= $dailyLimit;
    }

    /**
     * Increment the API call counter.
     */
    public function incrementApiCalls(): void
    {
        $this->increment('total_api_calls');
        $this->increment('monthly_api_calls');
        $this->update(['last_api_call_at' => now()]);
    }

    /**
     * Reset monthly API call counter.
     */
    public function resetMonthlyApiCalls(): void
    {
        $this->update(['monthly_api_calls' => 0]);
    }

    /**
     * Get the CDN URL for this website.
     */
    public function getCdnUrl(): string
    {
        return $this->cdn_url ?? config('webbloc.cdn.base_url');
    }

    /**
     * Generate integration code for this website.
     */
    public function generateIntegrationCode(): array
    {
        $cdnUrl = $this->getCdnUrl();
        
        return [
            'js' => "<script src=\"{$cdnUrl}/webbloc.min.js\" data-api-key=\"{$this->public_api_key}\"></script>",
            'css' => "<link rel=\"stylesheet\" href=\"{$cdnUrl}/webbloc.min.css\">",
            'config' => [
                'apiKey' => $this->public_api_key,
                'websiteId' => $this->id,
                'cdnUrl' => $cdnUrl,
            ],
        ];
    }

    /**
     * Scope to filter by status.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by verified websites.
     */
    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    /**
     * Scope to filter by subscription type.
     */
    public function scopeSubscription($query, string $type)
    {
        return $query->where('subscription_type', $type);
    }

    /**
     * Get the website's status badge color.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'green',
            'pending' => 'yellow',
            'suspended' => 'red',
            'inactive' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get formatted subscription expiry date.
     */
    public function getFormattedSubscriptionExpiryAttribute(): ?string
    {
        return $this->subscription_expires_at?->format('M d, Y');
    }
}