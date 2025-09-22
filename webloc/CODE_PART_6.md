## Production Deployment & Remaining Components (12 Files)

### 1. app/Models/WebsiteStatistic.php

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'date',
        'api_requests',
        'unique_users',
        'webbloc_interactions',
        'errors',
        'bandwidth_used',
        'response_time_avg',
        'metadata',
    ];

    protected $casts = [
        'date' => 'date',
        'metadata' => 'array',
        'response_time_avg' => 'float',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function getFormattedBandwidthAttribute(): string
    {
        return $this->formatBytes($this->bandwidth_used);
    }

    public function getErrorRateAttribute(): float
    {
        if ($this->api_requests === 0) {
            return 0;
        }

        return round(($this->errors / $this->api_requests) * 100, 2);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeForWebsite($query, $websiteId)
    {
        return $query->where('website_id', $websiteId);
    }

    protected function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
```

### 2. app/Models/WebBlocInstance.php

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebBlocInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'webbloc_id',
        'version',
        'settings',
        'status',
        'installation_data',
        'last_updated_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'installation_data' => 'array',
        'last_updated_at' => 'datetime',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function webBloc(): BelongsTo
    {
        return $this->belongsTo(WebBloc::class);
    }

    public function getWebBlocTypeAttribute(): string
    {
        return $this->webBloc->type ?? 'unknown';
    }

    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOutdated(): bool
    {
        return $this->version !== $this->webBloc->version;
    }

    public function getIntegrationCode(): string
    {
        $attributes = [];

        // Add common settings as attributes
        foreach ($this->settings as $key => $value) {
            if (in_array($key, ['theme', 'limit', 'sort', 'mode'])) {
                $attributes[$key] = $value;
            }
        }

        $attributeString = '';
        if (!empty($attributes)) {
            $attributeString = " w2030b_tags='" . json_encode($attributes) . "'";
        }

        return '<div w2030b="' . $this->webBloc->type . '"' . $attributeString . '>Loading...</div>';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForWebsite($query, $websiteId)
    {
        return $query->where('website_id', $websiteId);
    }

    public function scopeOfType($query, $type)
    {
        return $query->whereHas('webBloc', function ($q) use ($type) {
            $q->where('type', $type);
        });
    }
}
```

### 3. app/Providers/DatabaseConnectionServiceProvider.php

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use App\Services\DatabaseConnectionService;

class DatabaseConnectionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DatabaseConnectionService::class, function ($app) {
            return new DatabaseConnectionService();
        });

        $this->app->alias(DatabaseConnectionService::class, 'db.sqlite.connection.service');
    }

    public function boot(): void
    {
        // Configure dynamic database connections
        $this->configureDynamicConnections();
        
        // Register custom DB macros
        $this->registerDatabaseMacros();
    }

    protected function configureDynamicConnections(): void
    {
        // Extend the database manager to support dynamic SQLite connections
        DB::extend('sqlite_dynamic', function ($config, $name) {
            $config['database'] = $config['database'] ?? ':memory:';
            
            return new \Illuminate\Database\SQLiteConnection(
                new \PDO("sqlite:{$config['database']}", null, null, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]),
                $config['database'],
                $config['prefix'] ?? '',
                $config
            );
        });
    }

    protected function registerDatabaseMacros(): void
    {
        // Add a macro to get website-specific connection
        DB::macro('website', function ($websiteId) {
            return app(DatabaseConnectionService::class)->getConnection($websiteId);
        });

        // Add a macro to switch to website database
        DB::macro('useWebsiteDatabase', function ($websiteId) {
            $service = app(DatabaseConnectionService::class);
            $connectionName = "website_sqlite_{$websiteId}";
            
            if (!array_key_exists($connectionName, config('database.connections'))) {
                $databasePath = $service->getDatabasePath($websiteId);
                
                config([
                    "database.connections.{$connectionName}" => [
                        'driver' => 'sqlite',
                        'database' => $databasePath,
                        'prefix' => '',
                        'foreign_key_constraints' => true,
                    ]
                ]);
            }
            
            return DB::connection($connectionName);
        });
    }
}
```

### 4. database/seeders/DatabaseSeeder.php

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            WebBlocSeeder::class,
            // Add other seeders as needed
        ]);

        // Create sample data only in development environment
        if (app()->environment('local', 'development')) {
            $this->call([
                DevelopmentSeeder::class,
            ]);
        }
    }
}
```

### 5. database/seeders/RolePermissionSeeder.php

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Website management
            'websites.view',
            'websites.create',
            'websites.edit',
            'websites.delete',
            'websites.verify',
            
            // API Key management
            'api-keys.view',
            'api-keys.create',
            'api-keys.edit',
            'api-keys.delete',
            'api-keys.regenerate',
            
            // WebBloc management
            'webblocs.view',
            'webblocs.create',
            'webblocs.edit',
            'webblocs.delete',
            'webblocs.install',
            'webblocs.uninstall',
            
            // Statistics and analytics
            'statistics.view',
            'statistics.export',
            
            // Admin functions
            'admin.dashboard',
            'admin.system-info',
            'admin.logs',
            'admin.maintenance',
            'admin.cache-management',
            'admin.backup',
            
            // User management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.impersonate',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Super Admin - has all permissions
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        // Admin - has most permissions except user management
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo([
            'websites.view',
            'websites.create',
            'websites.edit',
            'websites.verify',
            'api-keys.view',
            'api-keys.create',
            'api-keys.edit',
            'api-keys.regenerate',
            'webblocs.view',
            'webblocs.create',
            'webblocs.edit',
            'webblocs.install',
            'webblocs.uninstall',
            'statistics.view',
            'statistics.export',
            'admin.dashboard',
            'admin.system-info',
            'admin.cache-management',
        ]);

        // Website Owner - can manage their own websites
        $websiteOwnerRole = Role::create(['name' => 'website-owner']);
        $websiteOwnerRole->givePermissionTo([
            'websites.view',
            'websites.edit',
            'api-keys.view',
            'api-keys.create',
            'api-keys.edit',
            'api-keys.regenerate',
            'webblocs.view',
            'webblocs.install',
            'webblocs.uninstall',
            'statistics.view',
        ]);

        // Developer - technical access
        $developerRole = Role::create(['name' => 'developer']);
        $developerRole->givePermissionTo([
            'websites.view',
            'websites.create',
            'websites.edit',
            'webblocs.view',
            'webblocs.create',
            'webblocs.edit',
            'webblocs.install',
            'statistics.view',
            'admin.system-info',
            'admin.logs',
        ]);

        // Support - limited access for customer support
        $supportRole = Role::create(['name' => 'support']);
        $supportRole->givePermissionTo([
            'websites.view',
            'api-keys.view',
            'webblocs.view',
            'statistics.view',
            'users.view',
        ]);

        // Create default super admin user if none exists
        if (User::count() === 0) {
            $superAdmin = User::create([
                'name' => 'Super Admin',
                'email' => 'admin@' . parse_url(config('app.url'), PHP_URL_HOST),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);

            $superAdmin->assignRole($superAdminRole);
            
            $this->command->info('Super admin user created: ' . $superAdmin->email);
            $this->command->warn('Default password: password (please change immediately!)');
        }

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
```
