## Production Deployment & Remaining Components

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


### 6. `public/deploy/install-webbloc.php` - No-SSH WebBloc Installation

```php
<?php
/**
 * WebBloc Auto-Installation Script
 * No SSH access required - executes via HTTP
 * 
 * Usage: https://yourdomain.com/deploy/install-webbloc.php
 * Security: Auto-deletes after successful installation
 */

// Security: Only run if not already installed
if (file_exists(__DIR__ . '/../../vendor/laravel/framework/src/Illuminate/Foundation/Application.php') && 
    file_exists(__DIR__ . '/../../.env')) {
    die('WebBloc already installed. For security, this installer is disabled.');
}

ini_set('max_execution_time', 300); // 5 minutes
ini_set('memory_limit', '512M');

$output = [];
$errors = [];

function logOutput($message, $isError = false) {
    global $output, $errors;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message";
    
    if ($isError) {
        $errors[] = $logMessage;
    } else {
        $output[] = $logMessage;
    }
    
    echo $logMessage . "\n";
    flush();
}

function executeCommand($command, $description = null) {
    if ($description) {
        logOutput("Starting: $description");
    }
    
    logOutput("Executing: $command");
    
    $output = [];
    $return_var = 0;
    exec($command . ' 2>&1', $output, $return_var);
    
    foreach ($output as $line) {
        logOutput("  $line");
    }
    
    if ($return_var !== 0) {
        logOutput("ERROR: Command failed with return code $return_var", true);
        return false;
    }
    
    logOutput("SUCCESS: Command completed");
    return true;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebBloc Installation</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2563eb; text-align: center; margin-bottom: 30px; }
        .status { padding: 15px; border-radius: 5px; margin: 10px 0; }
        .success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
        .error { background: #fef2f2; color: #b91c1c; border-left: 4px solid #ef4444; }
        .info { background: #eff6ff; color: #1e40af; border-left: 4px solid #3b82f6; }
        .log { background: #f8fafc; padding: 20px; border-radius: 5px; font-family: monospace; font-size: 14px; max-height: 400px; overflow-y: auto; white-space: pre-wrap; }
        .progress { width: 100%; height: 6px; background: #e5e7eb; border-radius: 3px; margin: 10px 0; }
        .progress-bar { height: 100%; background: #3b82f6; border-radius: 3px; transition: width 0.3s; }
        button { background: #2563eb; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #1d4ed8; }
        button:disabled { background: #9ca3af; cursor: not-allowed; }
    </style>
</head>
<body>

<div class="container">
    <h1>🚀 WebBloc Installation</h1>
    
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install'])): ?>
        
        <div class="info">
            <strong>Installation Started:</strong> This process may take 3-5 minutes. Please do not close this page.
        </div>
        
        <div class="progress">
            <div class="progress-bar" id="progressBar" style="width: 0%"></div>
        </div>
        
        <div class="log" id="logOutput">
        <?php
        
        // Step 1: Environment Setup
        logOutput("=== WebBloc Installation Process Started ===");
        logOutput("Checking system requirements...");
        
        $phpVersion = phpversion();
        if (version_compare($phpVersion, '8.1', '<')) {
            logOutput("ERROR: PHP 8.1+ required. Current version: $phpVersion", true);
        } else {
            logOutput("✓ PHP version check passed: $phpVersion");
        }
        
        // Check if Composer is available
        $composerPath = trim(shell_exec('which composer') ?: shell_exec('where composer'));
        if (empty($composerPath)) {
            $composerPath = 'php composer.phar';
        } else {
            $composerPath = 'composer';
        }
        
        logOutput("✓ Composer detected: $composerPath");
        
        echo "<script>document.getElementById('progressBar').style.width = '10%';</script>";
        flush();
        
        // Step 2: Install Dependencies
        logOutput("\n=== Installing Dependencies ===");
        if (!executeCommand("$composerPath install --no-dev --optimize-autoloader", "Installing Composer dependencies")) {
            logOutput("Installation failed during dependency installation", true);
            goto installation_complete;
        }
        
        echo "<script>document.getElementById('progressBar').style.width = '30%';</script>";
        flush();
        
        // Step 3: Environment Configuration
        logOutput("\n=== Environment Configuration ===");
        if (!file_exists('.env') && file_exists('.env.example')) {
            if (copy('.env.example', '.env')) {
                logOutput("✓ Environment file created from example");
            } else {
                logOutput("ERROR: Could not create .env file", true);
            }
        }
        
        // Generate application key
        if (!executeCommand('php artisan key:generate --force', "Generating application key")) {
            logOutput("Warning: Could not generate application key", true);
        }
        
        echo "<script>document.getElementById('progressBar').style.width = '40%';</script>";
        flush();
        
        // Step 4: Database Setup
        logOutput("\n=== Database Setup ===");
        if (!executeCommand('php artisan migrate:fresh --force', "Running database migrations")) {
            logOutput("ERROR: Database migration failed", true);
        } else {
            logOutput("✓ Database migrations completed");
        }
        
        echo "<script>document.getElementById('progressBar').style.width = '60%';</script>";
        flush();
        
        // Step 5: Seed Default Data
        logOutput("\n=== Seeding Default Data ===");
        if (!executeCommand('php artisan db:seed --force', "Seeding database with default data")) {
            logOutput("Warning: Database seeding had issues", true);
        } else {
            logOutput("✓ Default data seeded successfully");
        }
        
        echo "<script>document.getElementById('progressBar').style.width = '70%';</script>";
        flush();
        
        // Step 6: Storage & Permissions
        logOutput("\n=== Setting up Storage and Permissions ===");
        
        // Create storage link
        if (!executeCommand('php artisan storage:link', "Creating storage symbolic link")) {
            logOutput("Warning: Could not create storage link", true);
        }
        
        // Create necessary directories
        $directories = [
            'storage/databases',
            'public/cdn',
            'storage/app/public/uploads',
            'storage/app/backups',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views'
        ];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                if (mkdir($dir, 0755, true)) {
                    logOutput("✓ Created directory: $dir");
                } else {
                    logOutput("Warning: Could not create directory: $dir", true);
                }
            }
        }
        
        // Set permissions (if on Unix-like system)
        if (DIRECTORY_SEPARATOR === '/') {
            executeCommand('chmod -R 755 storage bootstrap/cache public/cdn', "Setting directory permissions");
            executeCommand('chmod -R 775 storage/databases', "Setting database directory permissions");
        }
        
        echo "<script>document.getElementById('progressBar').style.width = '80%';</script>";
        flush();
        
        // Step 7: Install Default WebBlocs
        logOutput("\n=== Installing Default WebBlocs ===");
        
        $webblocs = ['auth', 'comments', 'reviews', 'notifications'];
        foreach ($webblocs as $webbloc) {
            if (!executeCommand("php artisan webbloc:install $webbloc --force", "Installing $webbloc WebBloc")) {
                logOutput("Warning: Could not install $webbloc WebBloc", true);
            }
        }
        
        echo "<script>document.getElementById('progressBar').style.width = '90%';</script>";
        flush();
        
        // Step 8: Build CDN Assets
        logOutput("\n=== Building CDN Assets ===");
        if (!executeCommand('php artisan webbloc:build-cdn', "Building WebBloc CDN files")) {
            logOutput("Warning: CDN build had issues", true);
        }
        
        // Step 9: Cache Optimization
        logOutput("\n=== Optimizing Application ===");
        executeCommand('php artisan config:cache', "Caching configuration");
        executeCommand('php artisan route:cache', "Caching routes");
        executeCommand('php artisan view:cache', "Caching views");
        
        echo "<script>document.getElementById('progressBar').style.width = '100%';</script>";
        flush();
        
        logOutput("\n=== Installation Complete ===");
        logOutput("✓ WebBloc has been successfully installed!");
        logOutput("✓ You can now access your WebBloc dashboard");
        logOutput("⚠️  Don't forget to:");
        logOutput("   1. Update your .env file with proper database credentials");
        logOutput("   2. Configure your web server to serve the application");
        logOutput("   3. Set up SSL certificate for production");
        logOutput("   4. Create your first admin user via: php artisan make:admin");
        logOutput("   5. Delete this installer file for security");
        
        installation_complete:
        
        ?>
        </div>
        
        <?php if (empty($errors)): ?>
            <div class="success">
                <strong>🎉 Installation Successful!</strong><br>
                WebBloc has been installed successfully. You can now access your dashboard.<br>
                <strong>Important:</strong> Delete this installer file for security.
            </div>
            
            <script>
                setTimeout(() => {
                    if (confirm('Installation complete! Delete this installer file for security?')) {
                        fetch('<?= $_SERVER['PHP_SELF'] ?>?delete=1')
                            .then(() => alert('Installer deleted successfully!'))
                            .catch(() => alert('Please manually delete this file: <?= __FILE__ ?>'));
                    }
                }, 2000);
            </script>
        <?php else: ?>
            <div class="error">
                <strong>❌ Installation had errors:</strong><br>
                Please check the log above and resolve any issues before proceeding.
            </div>
        <?php endif; ?>
        
    <?php elseif (isset($_GET['delete'])): ?>
        <?php
        if (unlink(__FILE__)) {
            echo json_encode(['status' => 'success', 'message' => 'Installer deleted']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Could not delete installer']);
        }
        exit;
        ?>
        
    <?php else: ?>
        
        <div class="info">
            <strong>Welcome to WebBloc Installation!</strong><br>
            This will install and configure WebBloc on your server. The process includes:
        </div>
        
        <ul>
            <li>✅ Install PHP dependencies via Composer</li>
            <li>✅ Configure environment and generate security keys</li>
            <li>✅ Set up database and run migrations</li>
            <li>✅ Seed default data and WebBloc components</li>
            <li>✅ Configure storage and set permissions</li>
            <li>✅ Install default WebBlocs (auth, comments, reviews, notifications)</li>
            <li>✅ Build and optimize CDN assets</li>
            <li>✅ Cache configuration for production</li>
        </ul>
        
        <div class="info">
            <strong>Requirements:</strong><br>
            • PHP 8.1+ with required extensions<br>
            • Composer installed<br>
            • Write permissions on storage and cache directories<br>
            • Database connection configured in .env file
        </div>
        
        <form method="post">
            <button type="submit" name="install">🚀 Start Installation</button>
        </form>
        
    <?php endif; ?>
    
</div>

<script>
// Auto-scroll log output
const logOutput = document.getElementById('logOutput');
if (logOutput) {
    const observer = new MutationObserver(() => {
        logOutput.scrollTop = logOutput.scrollHeight;
    });
    observer.observe(logOutput, { childList: true, subtree: true });
}
</script>

</body>
</html>
```

### 7. `public/deploy/migrate.php` - No-SSH Migration Script

```php
<?php
/**
 * WebBloc Migration Script
 * Handles database migrations without SSH access
 * 
 * Usage: https://yourdomain.com/deploy/migrate.php
 */

// Security check - require basic auth or IP whitelist
$allowedIps = ['127.0.0.1', '::1']; // Add your IPs here
$requireAuth = true; // Set to false to disable auth
$username = 'webbloc_admin'; // Change this
$password = 'secure_migration_password_2024'; // Change this

// IP Check
$clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'];
$isAllowedIp = in_array($clientIp, $allowedIps) || $clientIp === '127.0.0.1';

// Basic Auth Check
$isAuthenticated = false;
if ($requireAuth && !$isAllowedIp) {
    if (!isset($_SERVER['PHP_AUTH_USER']) || 
        $_SERVER['PHP_AUTH_USER'] !== $username || 
        $_SERVER['PHP_AUTH_PW'] !== $password) {
        header('WWW-Authenticate: Basic realm="WebBloc Migration"');
        header('HTTP/1.0 401 Unauthorized');
        die('Authentication required for migration access.');
    }
    $isAuthenticated = true;
}

// Bootstrap Laravel
require_once __DIR__ . '/../../vendor/autoload.php';

if (!file_exists(__DIR__ . '/../../bootstrap/app.php')) {
    die('Laravel application not found. Please ensure WebBloc is properly installed.');
}

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

ini_set('max_execution_time', 300);
ini_set('memory_limit', '512M');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebBloc Database Migration</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 20px; background: #f8fafc; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        h1 { color: #1e40af; text-align: center; margin-bottom: 30px; }
        .status { padding: 15px; border-radius: 6px; margin: 15px 0; }
        .success { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .error { background: #fef2f2; color: #b91c1c; border: 1px solid #ef4444; }
        .warning { background: #fef3c7; color: #92400e; border: 1px solid #f59e0b; }
        .info { background: #eff6ff; color: #1e40af; border: 1px solid #3b82f6; }
        .log { background: #1f2937; color: #f9fafb; padding: 20px; border-radius: 6px; font-family: 'Courier New', monospace; font-size: 14px; max-height: 500px; overflow-y: auto; white-space: pre-wrap; }
        .button-group { text-align: center; margin: 20px 0; }
        button { background: #2563eb; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; margin: 0 10px; }
        button:hover { background: #1d4ed8; }
        button.danger { background: #dc2626; }
        button.danger:hover { background: #b91c1c; }
        button.success { background: #16a34a; }
        button.success:hover { background: #15803d; }
        button:disabled { background: #9ca3af; cursor: not-allowed; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
        .card { padding: 20px; border: 1px solid #e5e7eb; border-radius: 6px; background: #f9fafb; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .badge.up-to-date { background: #d1fae5; color: #065f46; }
        .badge.pending { background: #fef3c7; color: #92400e; }
        .badge.error { background: #fef2f2; color: #b91c1c; }
    </style>
</head>
<body>

<div class="container">
    <h1>🛠️ WebBloc Database Migration</h1>
    
    <?php
    
    function executeArtisan($command, $description = '') {
        try {
            ob_start();
            \Illuminate\Support\Facades\Artisan::call($command);
            $output = \Illuminate\Support\Facades\Artisan::output();
            ob_end_clean();
            
            return [
                'success' => true,
                'output' => $output,
                'command' => $command,
                'description' => $description
            ];
        } catch (Exception $e) {
            ob_end_clean();
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'command' => $command,
                'description' => $description
            ];
        }
    }
    
    function getMigrationStatus() {
        try {
            // Get pending migrations
            ob_start();
            \Illuminate\Support\Facades\Artisan::call('migrate:status');
            $output = \Illuminate\Support\Facades\Artisan::output();
            ob_end_clean();
            
            $lines = explode("\n", trim($output));
            $migrations = [];
            $inTable = false;
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (strpos($line, '|') === 0 && strpos($line, 'Migration name') !== false) {
                    $inTable = true;
                    continue;
                }
                
                if ($inTable && strpos($line, '|') === 0 && !empty(trim($line, '|+-'))) {
                    $parts = array_map('trim', explode('|', trim($line, '|')));
                    if (count($parts) >= 2) {
                        $migrations[] = [
                            'name' => $parts[1] ?? 'Unknown',
                            'status' => isset($parts[0]) && $parts[0] === 'Y' ? 'ran' : 'pending'
                        ];
                    }
                }
            }
            
            return $migrations;
        } catch (Exception $e) {
            return [];
        }
    }
    
    function getDatabaseInfo() {
        try {
            $default = config('database.default');
            $config = config("database.connections.{$default}");
            
            return [
                'driver' => $config['driver'] ?? 'unknown',
                'database' => $config['database'] ?? 'unknown',
                'host' => $config['host'] ?? 'unknown',
                'connection' => $default
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    function getWebsiteDatabases() {
        try {
            $websites = \App\Models\Website::all();
            $databases = [];
            
            foreach ($websites as $website) {
                $dbPath = storage_path("databases/website_{$website->id}.sqlite");
                $databases[] = [
                    'website_id' => $website->id,
                    'name' => $website->name,
                    'domain' => $website->domain,
                    'database_path' => $dbPath,
                    'exists' => file_exists($dbPath),
                    'size' => file_exists($dbPath) ? filesize($dbPath) : 0
                ];
            }
            
            return $databases;
        } catch (Exception $e) {
            return [];
        }
    }
    
    // Handle Actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        echo '<div class="log">';
        
        switch ($action) {
            case 'migrate':
                echo "Starting database migration...\n\n";
                $result = executeArtisan('migrate --force', 'Running pending migrations');
                echo $result['output'] ?? $result['error'] ?? 'No output';
                
                if ($result['success']) {
                    echo "\n\n✅ Migration completed successfully!";
                } else {
                    echo "\n\n❌ Migration failed: " . ($result['error'] ?? 'Unknown error');
                }
                break;
                
            case 'rollback':
                $steps = intval($_POST['steps'] ?? 1);
                echo "Rolling back last {$steps} migration batch(es)...\n\n";
                $result = executeArtisan("migrate:rollback --step={$steps} --force", "Rolling back migrations");
                echo $result['output'] ?? $result['error'] ?? 'No output';
                break;
                
            case 'fresh':
                if (($_POST['confirm_fresh'] ?? '') === 'yes') {
                    echo "🚨 DROPPING ALL TABLES AND RE-MIGRATING...\n\n";
                    $result = executeArtisan('migrate:fresh --force', 'Fresh migration (drops all tables)');
                    echo $result['output'] ?? $result['error'] ?? 'No output';
                    
                    if ($result['success'] && isset($_POST['seed_fresh'])) {
                        echo "\n\nSeeding database...\n";
                        $seedResult = executeArtisan('db:seed --force', 'Seeding database');
                        echo $seedResult['output'] ?? $seedResult['error'] ?? 'No seeding output';
                    }
                } else {
                    echo "❌ Fresh migration cancelled - confirmation required.";
                }
                break;
                
            case 'create_website_dbs':
                echo "Creating SQLite databases for all websites...\n\n";
                $result = executeArtisan('website:create-database --all --migrate', 'Creating website databases');
                echo $result['output'] ?? $result['error'] ?? 'No output';
                break;
                
            case 'seed':
                echo "Seeding database with default data...\n\n";
                $result = executeArtisan('db:seed --force', 'Seeding database');
                echo $result['output'] ?? $result['error'] ?? 'No output';
                break;
                
            case 'optimize':
                echo "Optimizing database and clearing caches...\n\n";
                $commands = [
                    'config:clear' => 'Clearing config cache',
                    'config:cache' => 'Caching configuration',
                    'route:clear' => 'Clearing route cache', 
                    'route:cache' => 'Caching routes',
                    'view:clear' => 'Clearing view cache',
                    'view:cache' => 'Caching views'
                ];
                
                foreach ($commands as $cmd => $desc) {
                    echo "Running: {$desc}\n";
                    $result = executeArtisan($cmd, $desc);
                    echo ($result['success'] ? '✅' : '❌') . " {$desc}\n";
                }
                echo "\n✅ Optimization complete!";
                break;
        }
        
        echo '</div>';
        echo '<div class="button-group"><button onclick="location.reload()">🔄 Refresh Page</button></div>';
    } else {
        
        // Display current status
        $dbInfo = getDatabaseInfo();
        $migrations = getMigrationStatus();
        $websiteDbs = getWebsiteDatabases();
        $pendingCount = array_reduce($migrations, function($count, $m) { return $count + ($m['status'] === 'pending' ? 1 : 0); }, 0);
        
        ?>
        
        <!-- Database Status -->
        <div class="card">
            <h3>📊 Database Status</h3>
            <?php if (isset($dbInfo['error'])): ?>
                <div class="status error">❌ Database connection error: <?= htmlspecialchars($dbInfo['error']) ?></div>
            <?php else: ?>
                <p><strong>Driver:</strong> <?= htmlspecialchars($dbInfo['driver']) ?></p>
                <p><strong>Database:</strong> <?= htmlspecialchars($dbInfo['database']) ?></p>
                <p><strong>Host:</strong> <?= htmlspecialchars($dbInfo['host']) ?></p>
                <p><strong>Connection:</strong> <?= htmlspecialchars($dbInfo['connection']) ?></p>
            <?php endif; ?>
        </div>
        
        <!-- Migration Status -->
        <div class="card">
            <h3>🔄 Migration Status</h3>
            <?php if ($pendingCount > 0): ?>
                <div class="status warning">
                    ⚠️ <strong><?= $pendingCount ?> pending migrations</strong> found. Database update needed.
                </div>
            <?php else: ?>
                <div class="status success">✅ All migrations are up to date</div>
            <?php endif; ?>
            
            <?php if (!empty($migrations)): ?>
                <details style="margin-top: 15px;">
                    <summary>View Migration Details (<?= count($migrations) ?> total)</summary>
                    <div style="max-height: 200px; overflow-y: auto; margin-top: 10px;">
                        <?php foreach (array_slice($migrations, -10) as $migration): ?>
                            <div style="padding: 5px 0; border-bottom: 1px solid #eee;">
                                <span class="badge <?= $migration['status'] === 'ran' ? 'up-to-date' : 'pending' ?>">
                                    <?= strtoupper($migration['status']) ?>
                                </span>
                                <?= htmlspecialchars($migration['name']) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </details>
            <?php endif; ?>
        </div>
        
        <!-- Website Databases Status -->
        <div class="card">
            <h3>🗄️ Website Databases</h3>
            <?php if (empty($websiteDbs)): ?>
                <p>No websites found or error accessing website data.</p>
            <?php else: ?>
                <p><strong><?= count($websiteDbs) ?></strong> website(s) registered</p>
                <?php
                $existingDbs = array_filter($websiteDbs, fn($db) => $db['exists']);
                $totalSize = array_sum(array_column($existingDbs, 'size'));
                ?>
                <p><strong><?= count($existingDbs) ?></strong> SQLite databases exist (<?= number_format($totalSize / 1024) ?> KB total)</p>
                
                <details>
                    <summary>View Website Database Details</summary>
                    <div style="max-height: 200px; overflow-y: auto; margin-top: 10px;">
                        <?php foreach ($websiteDbs as $db): ?>
                            <div style="padding: 5px 0; border-bottom: 1px solid #eee; font-size: 14px;">
                                <span class="badge <?= $db['exists'] ? 'up-to-date' : 'error' ?>">
                                    <?= $db['exists'] ? 'EXISTS' : 'MISSING' ?>
                                </span>
                                <strong><?= htmlspecialchars($db['domain']) ?></strong>
                                <?php if ($db['exists']): ?>
                                    (<?= number_format($db['size'] / 1024, 1) ?> KB)
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </details>
            <?php endif; ?>
        </div>
        
        <!-- Migration Actions -->
        <div class="card">
            <h3>⚡ Migration Actions</h3>
            
            <form method="post" style="margin: 10px 0;">
                <input type="hidden" name="action" value="migrate">
                <button type="submit" class="success" <?= $pendingCount === 0 ? 'disabled title="No pending migrations"' : '' ?>>
                    🚀 Run Migrations <?= $pendingCount > 0 ? "($pendingCount pending)" : '' ?>
                </button>
            </form>
            
            <form method="post" style="margin: 10px 0;">
                <input type="hidden" name="action" value="rollback">
                <label>Steps to rollback: <input type="number" name="steps" value="1" min="1" max="10" style="width: 60px;"></label>
                <button type="submit" class="danger">↩️ Rollback Migration</button>
            </form>
            
            <form method="post" style="margin: 10px 0;">
                <input type="hidden" name="action" value="create_website_dbs">
                <button type="submit">🗄️ Create Website Databases</button>
            </form>
            
            <form method="post" style="margin: 10px 0;">
                <input type="hidden" name="action" value="seed">
                <button type="submit">🌱 Seed Database</button>
            </form>
            
            <form method="post" style="margin: 10px 0;">
                <input type="hidden" name="action" value="optimize">
                <button type="submit">⚡ Optimize & Cache</button>
            </form>
        </div>
        
        <!-- Dangerous Actions -->
        <div class="card" style="border-color: #ef4444;">
            <h3 style="color: #dc2626;">🚨 Dangerous Actions</h3>
            <div class="status error">
                <strong>Warning:</strong> The action below will DELETE ALL DATA and recreate the database.
            </div>
            
            <form method="post" style="margin: 10px 0;" onsubmit="return confirm('⚠️ This will DELETE ALL DATA! Are you absolutely sure?');">
                <input type="hidden" name="action" value="fresh">
                <label>
                    <input type="checkbox" name="confirm_fresh" value="yes" required>
                    I understand this will delete all data
                </label><br><br>
                <label>
                    <input type="checkbox" name="seed_fresh" value="yes">
                    Also seed with default data after fresh migration
                </label><br><br>
                <button type="submit" class="danger">💥 Fresh Migration (Delete All)</button>
            </form>
        </div>
        
        <?php } ?>
</div>

<script>
// Auto-refresh page every 30 seconds if migrations are running
if (document.querySelector('.log')) {
    let countdown = 10;
    const refreshBtn = document.querySelector('button[onclick="location.reload()"]');
    if (refreshBtn) {
        const originalText = refreshBtn.textContent;
        const interval = setInterval(() => {
            refreshBtn.textContent = `${originalText} (${countdown})`;
            countdown--;
            if (countdown <= 0) {
                location.reload();
            }
        }, 1000);
    }
}
</script>

</body>
</html>
```

### 8. `public/deploy/build-assets.php` - No-SSH Asset Building (PHP-only)

```php
<?php
/**
 * WebBloc Asset Builder - No SSH Required
 * Builds and minifies CSS/JS assets using PHP only
 * 
 * Usage: https://yourdomain.com/deploy/build-assets.php
 */

// Security: Basic authentication required
$auth_user = 'webbloc_deploy';
$auth_pass = 'build_assets_secure_2024';

if (!isset($_SERVER['PHP_AUTH_USER']) || 
    $_SERVER['PHP_AUTH_USER'] !== $auth_user || 
    $_SERVER['PHP_AUTH_PW'] !== $auth_pass) {
    header('WWW-Authenticate: Basic realm="WebBloc Asset Builder"');
    header('HTTP/1.0 401 Unauthorized');
    die('Authentication required');
}

ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');

// Bootstrap Laravel if available
$laravelBootstrapped = false;
if (file_exists(__DIR__ . '/../../vendor/autoload.php') && file_exists(__DIR__ . '/../../bootstrap/app.php')) {
    try {
        require_once __DIR__ . '/../../vendor/autoload.php';
        $app = require_once __DIR__ . '/../../bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        $laravelBootstrapped = true;
    } catch (Exception $e) {
        // Fall back to non-Laravel mode
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebBloc Asset Builder</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 20px; background: #f1f5f9; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        h1 { color: #0f172a; text-align: center; margin-bottom: 30px; }
        .status { padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid; }
        .success { background: #dcfce7; color: #166534; border-color: #22c55e; }
        .error { background: #fef2f2; color: #dc2626; border-color: #ef4444; }
        .warning { background: #fefce8; color: #ca8a04; border-color: #eab308; }
        .info { background: #eff6ff; color: #2563eb; border-color: #3b82f6; }
        .log { background: #0f172a; color: #e2e8f0; padding: 20px; border-radius: 8px; font-family: 'Courier New', monospace; font-size: 14px; max-height: 600px; overflow-y: auto; white-space: pre-wrap; line-height: 1.4; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }
        .card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; }
        .card h3 { margin-top: 0; color: #334155; }
        button { background: #2563eb; color: white; padding: 12px 20px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; margin: 5px; transition: background 0.2s; }
        button:hover { background: #1d4ed8; }
        button.danger { background: #dc2626; }
        button.danger:hover { background: #b91c1c; }
        button.success { background: #059669; }
        button.success:hover { background: #047857; }
        .file-info { font-size: 12px; color: #64748b; margin-top: 5px; }
        .progress { width: 100%; height: 8px; background: #e2e8f0; border-radius: 4px; margin: 10px 0; overflow: hidden; }
        .progress-bar { height: 100%; background: linear-gradient(90deg, #3b82f6, #1d4ed8); transition: width 0.3s ease; }
        .file-tree { font-family: monospace; font-size: 12px; background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f1f5f9; font-weight: 600; }
    </style>
</head>
<body>

<div class="container">
    <h1>🎨 WebBloc Asset Builder</h1>
    
    <?php
    
    class AssetBuilder {
        private $basePath;
        private $outputPath;
        private $log = [];
        
        public function __construct($basePath) {
            $this->basePath = rtrim($basePath, '/');
            $this->outputPath = $this->basePath . '/public/cdn';
        }
        
        public function log($message, $type = 'info') {
            $timestamp = date('H:i:s');
            $this->log[] = ['time' => $timestamp, 'message' => $message, 'type' => $type];
            echo "[$timestamp] $message\n";
            flush();
        }
        
        public function ensureDirectory($path) {
            if (!is_dir($path)) {
                if (mkdir($path, 0755, true)) {
                    $this->log("✓ Created directory: " . basename($path));
                } else {
                    $this->log("✗ Failed to create directory: $path", 'error');
                    return false;
                }
            }
            return true;
        }
        
        public function minifyCSS($css) {
            // Remove comments
            $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
            // Remove whitespace
            $css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);
            // Remove extra spaces
            $css = preg_replace('/\s+/', ' ', $css);
            // Remove spaces around specific characters
            $css = str_replace(['; ', ' {', '{ ', ' }', '} ', ': ', ' :'], [';', '{', '{', '}', '}', ':', ':'], $css);
            return trim($css);
        }
        
        public function minifyJS($js) {
            // Basic JS minification - remove comments and excess whitespace
            $js = preg_replace('/\/\*[\s\S]*?\*\//', '', $js); // Remove /* */ comments
            $js = preg_replace('/\/\/.*$/m', '', $js); // Remove // comments
            $js = preg_replace('/\s+/', ' ', $js); // Collapse whitespace
            $js = str_replace(['; ', ' {', '{ ', ' }', '} '], [';', '{', '{', '}', '}'], $js);
            return trim($js);
        }
        
        public function buildCoreCSS() {
            $this->log("Building core CSS...");
            
            $cssFiles = [
                $this->basePath . '/resources/css/webbloc-core.css',
                $this->basePath . '/resources/css/webbloc-components.css'
            ];
            
            $combinedCSS = "/* WebBloc Core CSS - Generated " . date('Y-m-d H:i:s') . " */\n";
            $totalSize = 0;
            
            foreach ($cssFiles as $file) {
                if (file_exists($file)) {
                    $content = file_get_contents($file);
                    $combinedCSS .= "\n/* " . basename($file) . " */\n" . $content . "\n";
                    $totalSize += strlen($content);
                    $this->log("  ✓ Added " . basename($file) . " (" . number_format(strlen($content)) . " bytes)");
                } else {
                    $this->log("  ⚠ File not found: " . basename($file), 'warning');
                }
            }
            
            // Minify
            $minifiedCSS = $this->minifyCSS($combinedCSS);
            $compressionRatio = round((1 - strlen($minifiedCSS) / strlen($combinedCSS)) * 100, 1);
            
            // Write files
            $this->ensureDirectory($this->outputPath);
            
            // Full version
            file_put_contents($this->outputPath . '/webbloc.css', $combinedCSS);
            // Minified version
            file_put_contents($this->outputPath . '/webbloc.min.css', $minifiedCSS);
            
            $this->log("✓ Core CSS built: " . number_format(strlen($minifiedCSS)) . " bytes (compressed {$compressionRatio}%)");
            
            return [
                'original_size' => strlen($combinedCSS),
                'minified_size' => strlen($minifiedCSS),
                'compression' => $compressionRatio,
                'files_processed' => count(array_filter($cssFiles, 'file_exists'))
            ];
        }
        
        public function buildCoreJS() {
            $this->log("Building core JavaScript...");
            
            $jsFiles = [
                $this->basePath . '/resources/js/webbloc-core.js',
                $this->basePath . '/resources/js/webbloc-components.js'
            ];
            
            $combinedJS = "/* WebBloc Core JS - Generated " . date('Y-m-d H:i:s') . " */\n";
            
            foreach ($jsFiles as $file) {
                if (file_exists($file)) {
                    $content = file_get_contents($file);
                    $combinedJS .= "\n/* " . basename($file) . " */\n" . $content . "\n";
                    $this->log("  ✓ Added " . basename($file) . " (" . number_format(strlen($content)) . " bytes)");
                } else {
                    $this->log("  ⚠ File not found: " . basename($file), 'warning');
                }
            }
            
            // Minify
            $minifiedJS = $this->minifyJS($combinedJS);
            $compressionRatio = round((1 - strlen($minifiedJS) / strlen($combinedJS)) * 100, 1);
            
            // Write files
            file_put_contents($this->outputPath . '/webbloc.js', $combinedJS);
            file_put_contents($this->outputPath . '/webbloc.min.js', $minifiedJS);
            
            $this->log("✓ Core JS built: " . number_format(strlen($minifiedJS)) . " bytes (compressed {$compressionRatio}%)");
            
            return [
                'original_size' => strlen($combinedJS),
                'minified_size' => strlen($minifiedJS),
                'compression' => $compressionRatio,
                'files_processed' => count(array_filter($jsFiles, 'file_exists'))
            ];
        }
        
        public function buildComponentAssets() {
            $this->log("Building individual component assets...");
            
            $components = ['auth', 'comments', 'reviews', 'notifications'];
            $results = [];
            
            foreach ($components as $component) {
                $this->log("  Building {$component} component...");
                
                // Component CSS
                $cssPath = $this->basePath . "/resources/css/components/{$component}.css";
                if (file_exists($cssPath)) {
                    $css = file_get_contents($cssPath);
                    $minifiedCSS = $this->minifyCSS($css);
                    file_put_contents($this->outputPath . "/{$component}.css", $css);
                    file_put_contents($this->outputPath . "/{$component}.min.css", $minifiedCSS);
                    $this->log("    ✓ CSS: " . number_format(strlen($minifiedCSS)) . " bytes");
                }
                
                // Component JS
                $jsPath = $this->basePath . "/resources/js/components/{$component}.js";
                if (file_exists($jsPath)) {
                    $js = file_get_contents($jsPath);
                    $minifiedJS = $this->minifyJS($js);
                    file_put_contents($this->outputPath . "/{$component}.js", $js);
                    file_put_contents($this->outputPath . "/{$component}.min.js", $minifiedJS);
                    $this->log("    ✓ JS: " . number_format(strlen($minifiedJS)) . " bytes");
                }
                
                $results[$component] = 'processed';
            }
            
            return $results;
        }
        
        public function buildManifest() {
            $this->log("Building asset manifest...");
            
            $manifest = [
                'built_at' => date('c'),
                'version' => '1.0.0',
                'assets' => []
            ];
            
            $files = glob($this->outputPath . '/*.{css,js}', GLOB_BRACE);
            foreach ($files as $file) {
                $filename = basename($file);
                $manifest['assets'][$filename] = [
                    'size' => filesize($file),
                    'hash' => md5_file($file),
                    'modified' => date('c', filemtime($file))
                ];
            }
            
            file_put_contents($this->outputPath . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
            $this->log("✓ Manifest created with " . count($manifest['assets']) . " assets");
            
            return $manifest;
        }
        
        public function getAssetInfo() {
            $info = [];
            $totalSize = 0;
            
            if (is_dir($this->outputPath)) {
                $files = glob($this->outputPath . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $size = filesize($file);
                        $info[basename($file)] = [
                            'size' => $size,
                            'size_formatted' => $this->formatBytes($size),
                            'modified' => date('Y-m-d H:i:s', filemtime($file))
                        ];
                        $totalSize += $size;
                    }
                }
            }
            
            return ['files' => $info, 'total_size' => $totalSize];
        }
        
        public function formatBytes($bytes) {
            $units = ['B', 'KB', 'MB'];
            $bytes = max($bytes, 0);
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);
            $bytes /= pow(1024, $pow);
            return round($bytes, 1) . ' ' . $units[$pow];
        }
        
        public function cleanup() {
            $this->log("Cleaning up old assets...");
            $cleaned = 0;
            
            if (is_dir($this->outputPath)) {
                $files = glob($this->outputPath . '/*.{css,js,map}', GLOB_BRACE);
                foreach ($files as $file) {
                    // Keep files modified in last hour
                    if (filemtime($file) < (time() - 3600)) {
                        if (unlink($file)) {
                            $cleaned++;
                        }
                    }
                }
            }
            
            $this->log("✓ Cleaned up {$cleaned} old files");
            return $cleaned;
        }
    }
    
    $builder = new AssetBuilder(__DIR__ . '/../..');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        echo '<div class="log">';
        
        switch ($action) {
            case 'build_all':
                echo "🚀 Starting full asset build...\n\n";
                
                $cssResult = $builder->buildCoreCSS();
                echo "\n";
                
                $jsResult = $builder->buildCoreJS();
                echo "\n";
                
                $componentResults = $builder->buildComponentAssets();
                echo "\n";
                
                $manifest = $builder->buildManifest();
                echo "\n";
                
                echo "🎉 Build complete!\n";
                echo "CSS: {$builder->formatBytes($cssResult['minified_size'])} (compressed {$cssResult['compression']}%)\n";
                echo "JS: {$builder->formatBytes($jsResult['minified_size'])} (compressed {$jsResult['compression']}%)\n";
                break;
                
            case 'build_css':
                $result = $builder->buildCoreCSS();
                echo "\n✅ CSS build complete: {$builder->formatBytes($result['minified_size'])}";
                break;
                
            case 'build_js':
                $result = $builder->buildCoreJS();
                echo "\n✅ JS build complete: {$builder->formatBytes($result['minified_size'])}";
                break;
                
            case 'build_components':
                $builder->buildComponentAssets();
                echo "\n✅ Component assets built";
                break;
                
            case 'cleanup':
                $cleaned = $builder->cleanup();
                echo "✅ Cleanup complete: {$cleaned} files removed";
                break;
                
            case 'laravel_build':
                if ($laravelBootstrapped) {
                    echo "Running Laravel WebBloc CDN build command...\n\n";
                    try {
                        \Illuminate\Support\Facades\Artisan::call('webbloc:build-cdn');
                        echo \Illuminate\Support\Facades\Artisan::output();
                        echo "\n✅ Laravel build complete";
                    } catch (Exception $e) {
                        echo "❌ Laravel build failed: " . $e->getMessage();
                    }
                } else {
                    echo "❌ Laravel not available for this operation";
                }
                break;
        }
        
        echo '</div>';
        
        echo '<div style="text-align: center; margin: 20px 0;">
                <button onclick="location.reload()">🔄 Refresh Status</button>
              </div>';
        
    } else {
        
        // Display current status and controls
        $assetInfo = $builder->getAssetInfo();
        
        ?>
        
        <!-- Asset Status -->
        <div class="card">
            <h3>📦 Current Assets</h3>
            <?php if (empty($assetInfo['files'])): ?>
                <div class="status warning">No assets found. Run a build to generate assets.</div>
            <?php else: ?>
                <div class="status success">
                    <?= count($assetInfo['files']) ?> assets found 
                    (<?= $builder->formatBytes($assetInfo['total_size']) ?> total)
                </div>
                
                <table>
                    <thead>
                        <tr><th>File</th><th>Size</th><th>Modified</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assetInfo['files'] as $filename => $info): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($filename) ?></code></td>
                                <td><?= $info['size_formatted'] ?></td>
                                <td><?= $info['modified'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Build Actions -->
        <div class="grid">
            <div class="card">
                <h3>🏗️ Build Actions</h3>
                
                <form method="post" style="margin: 10px 0;">
                    <input type="hidden" name="action" value="build_all">
                    <button type="submit" class="success">🚀 Full Build (CSS + JS + Components)</button>
                </form>
                
                <form method="post" style="margin: 10px 0;">
                    <input type="hidden" name="action" value="build_css">
                    <button type="submit">🎨 Build CSS Only</button>
                </form>
                
                <form method="post" style="margin: 10px 0;">
                    <input type="hidden" name="action" value="build_js">
                    <button type="submit">⚡ Build JavaScript Only</button>
                </form>
                
                <form method="post" style="margin: 10px 0;">
                    <input type="hidden" name="action" value="build_components">
                    <button type="submit">🧩 Build Components Only</button>
                </form>
            </div>
            
            <div class="card">
                <h3>🧹 Maintenance</h3>
                
                <form method="post" style="margin: 10px 0;">
                    <input type="hidden" name="action" value="cleanup">
                    <button type="submit">🗑️ Cleanup Old Assets</button>
                </form>
                
                <?php if ($laravelBootstrapped): ?>
                    <form method="post" style="margin: 10px 0;">
                        <input type="hidden" name="action" value="laravel_build">
                        <button type="submit">🔥 Laravel CDN Build</button>
                    </form>
                    <div class="status success">✅ Laravel integration available</div>
                <?php else: ?>
                    <div class="status warning">⚠️ Laravel integration unavailable</div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Source Files -->
        <div class="card">
            <h3>📁 Source Files</h3>
            <div class="file-tree">
                <?php
                $sourceFiles = [
                    'CSS Files' => [
                        'resources/css/webbloc-core.css',
                        'resources/css/webbloc-components.css'
                    ],
                    'JavaScript Files' => [
                        'resources/js/webbloc-core.js',
                        'resources/js/webbloc-components.js'
                    ],
                    'Component Files' => [
                        'resources/css/components/',
                        'resources/js/components/'
                    ]
                ];
                
                foreach ($sourceFiles as $category => $files):
                ?>
                    <strong><?= $category ?>:</strong><br>
                    <?php foreach ($files as $file): ?>
                        <?php 
                        $fullPath = $builder->basePath . '/' . $file;
                        $exists = file_exists($fullPath);
                        $size = $exists ? filesize($fullPath) : 0;
                        ?>
                        <?= $exists ? '✓' : '✗' ?> <?= $file ?>
                        <?php if ($exists): ?>
                            (<?= $builder->formatBytes($size) ?>)
                        <?php endif; ?>
                        <br>
                    <?php endforeach; ?>
                    <br>
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php } ?>
</div>

</body>
</html>
```

### 9. `resources/views/dashboard/webblocs/index.blade.php`

```blade
@extends('layouts.dashboard')

@section('title', 'WebBlocs Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">WebBlocs Management</h1>
            <p class="text-muted">Manage available WebBloc components and their installations</p>
        </div>
        <div>
            @can('create', App\Models\WebBloc::class)
                <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#createWebBlocModal">
                    <i class="fas fa-plus"></i> Create WebBloc
                </button>
            @endcan
            <button type="button" class="btn btn-outline-secondary" onclick="refreshWebBlocs()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total WebBlocs</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cubes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active WebBlocs</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Installations</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['installations'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-download fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Active Websites</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['websites'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-globe fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Filter & Search</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.webblocs.index') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <label for="search">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Search name, type...">
                    </div>
                    <div class="col-md-2">
                        <label for="type">Type</label>
                        <select class="form-control" id="type" name="type">
                            <option value="">All Types</option>
                            <option value="auth" {{ request('type') === 'auth' ? 'selected' : '' }}>Authentication</option>
                            <option value="comments" {{ request('type') === 'comments' ? 'selected' : '' }}>Comments</option>
                            <option value="reviews" {{ request('type') === 'reviews' ? 'selected' : '' }}>Reviews</option>
                            <option value="notifications" {{ request('type') === 'notifications' ? 'selected' : '' }}>Notifications</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="development" {{ request('status') === 'development' ? 'selected' : '' }}>Development</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="sort">Sort By</label>
                        <select class="form-control" id="sort" name="sort">
                            <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Name</option>
                            <option value="type" {{ request('sort') === 'type' ? 'selected' : '' }}>Type</option>
                            <option value="installations" {{ request('sort') === 'installations' ? 'selected' : '' }}>Installations</option>
                            <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Date Created</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <div class="d-flex">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('dashboard.webblocs.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- WebBlocs Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">WebBlocs ({{ $webblocs->total() }})</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow">
                    @can('create', App\Models\WebBloc::class)
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#bulkInstallModal">
                            <i class="fas fa-download fa-sm fa-fw mr-2 text-gray-400"></i>
                            Bulk Install to Websites
                        </a>
                        <a class="dropdown-item" href="#" onclick="exportWebBlocs()">
                            <i class="fas fa-file-export fa-sm fa-fw mr-2 text-gray-400"></i>
                            Export WebBlocs
                        </a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @if($webblocs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="webBlocsTable">
                        <thead class="table-light">
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                </th>
                                <th>WebBloc</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Installations</th>
                                <th>Version</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($webblocs as $webbloc)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_webblocs[]" value="{{ $webbloc->id }}" class="webbloc-checkbox">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="webbloc-icon me-3">
                                                <i class="fas {{ $webbloc->getIconClass() }} fa-lg text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">{{ $webbloc->name }}</div>
                                                <div class="text-muted small">{{ Str::limit($webbloc->description, 60) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $webbloc->getTypeBadgeClass() }}">
                                            {{ ucfirst($webbloc->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $webbloc->status === 'active' ? 'success' : ($webbloc->status === 'inactive' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($webbloc->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="me-2">{{ $webbloc->installation_count }}</span>
                                            @if($webbloc->installation_count > 0)
                                                <a href="{{ route('dashboard.webblocs.installations', $webbloc) }}" 
                                                   class="btn btn-sm btn-outline-info" title="View installations">
                                                    <i class="fas fa-list"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <code>{{ $webbloc->version }}</code>
                                    </td>
                                    <td>
                                        <span title="{{ $webbloc->updated_at->format('Y-m-d H:i:s') }}">
                                            {{ $webbloc->updated_at->diffForHumans() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('dashboard.webblocs.show', $webbloc) }}" 
                                               class="btn btn-sm btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('update', $webbloc)
                                                <a href="{{ route('dashboard.webblocs.edit', $webbloc) }}" 
                                                   class="btn btn-sm btn-outline-secondary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @can('install', $webbloc)
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="showInstallModal({{ $webbloc->id }})" title="Install to Website">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            @endcan
                                            @can('delete', $webbloc)
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteWebBloc({{ $webbloc->id }})" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center p-3">
                    <div class="text-muted">
                        Showing {{ $webblocs->firstItem() }} to {{ $webblocs->lastItem() }} of {{ $webblocs->total() }} results
                    </div>
                    <div>
                        {{ $webblocs->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-cubes fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">No WebBlocs Found</h5>
                    <p class="text-muted">Start by creating your first WebBloc component.</p>
                    @can('create', App\Models\WebBloc::class)
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createWebBlocModal">
                            <i class="fas fa-plus"></i> Create WebBloc
                        </button>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Create WebBloc Modal -->
@can('create', App\Models\WebBloc::class)
<div class="modal fade" id="createWebBlocModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New WebBloc</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('dashboard.webblocs.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="type" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="auth">Authentication</option>
                                    <option value="comments">Comments</option>
                                    <option value="reviews">Reviews</option>
                                    <option value="notifications">Notifications</option>
                                    <option value="custom">Custom</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="version" class="form-label">Version</label>
                                <input type="text" class="form-control" id="version" name="version" value="1.0.0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="development">Development</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Supported Operations</label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="crud_create" name="crud[create]" value="1" checked>
                                    <label class="form-check-label" for="crud_create">Create</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="crud_read" name="crud[read]" value="1" checked>
                                    <label class="form-check-label" for="crud_read">Read</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="crud_update" name="crud[update]" value="1" checked>
                                    <label class="form-check-label" for="crud_update">Update</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="crud_delete" name="crud[delete]" value="1" checked>
                                    <label class="form-check-label" for="crud_delete">Delete</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create WebBloc</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

<!-- Install to Website Modal -->
<div class="modal fade" id="installWebBlocModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Install WebBloc to Website</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="installWebBlocForm">
                @csrf
                <input type="hidden" id="install_webbloc_id" name="webbloc_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="install_website_id" class="form-label">Select Website <span class="text-danger">*</span></label>
                        <select class="form-control" id="install_website_id" name="website_id" required>
                            <option value="">Choose Website...</option>
                            @foreach($websites as $website)
                                <option value="{{ $website->id }}">{{ $website->name }} ({{ $website->domain }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Installation Settings</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="auto_activate" name="auto_activate" value="1" checked>
                            <label class="form-check-label" for="auto_activate">Auto-activate after installation</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rebuild_cdn" name="rebuild_cdn" value="1">
                            <label class="form-check-label" for="rebuild_cdn">Rebuild CDN assets</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Install WebBloc</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Auto-refresh functionality
let autoRefresh = false;
function toggleAutoRefresh() {
    autoRefresh = !autoRefresh;
    if (autoRefresh) {
        setInterval(refreshWebBlocs, 30000); // Refresh every 30 seconds
    }
}

function refreshWebBlocs() {
    window.location.reload();
}

// Select all functionality
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.webbloc-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

// Show install modal
function showInstallModal(webBlocId) {
    document.getElementById('install_webbloc_id').value = webBlocId;
    new bootstrap.Modal(document.getElementById('installWebBlocModal')).show();
}

// Handle install form submission
document.getElementById('installWebBlocForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const webBlocId = formData.get('webbloc_id');
    const websiteId = formData.get('website_id');
    
    // Show loading state
    Swal.fire({
        title: 'Installing WebBloc...',
        text: 'Please wait while we install the WebBloc to your website.',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Make AJAX request
    fetch(`/dashboard/webblocs/${webBlocId}/install`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            website_id: websiteId,
            auto_activate: formData.get('auto_activate') === '1',
            rebuild_cdn: formData.get('rebuild_cdn') === '1'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'WebBloc Installed!',
                text: data.message,
                confirmButtonText: 'Great!'
            }).then(() => {
                bootstrap.Modal.getInstance(document.getElementById('installWebBlocModal')).hide();
                refreshWebBlocs();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Installation Failed',
                text: data.message || 'An error occurred during installation.',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An unexpected error occurred. Please try again.',
            confirmButtonText: 'OK'
        });
    });
});

// Delete WebBloc
function deleteWebBloc(webBlocId) {
    Swal.fire({
        title: 'Delete WebBloc?',
        text: 'This action cannot be undone. All installations of this WebBloc will also be removed.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/dashboard/webblocs/${webBlocId}`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            
            form.appendChild(csrfToken);
            form.appendChild(methodField);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Export WebBlocs
function exportWebBlocs() {
    const selectedCheckboxes = document.querySelectorAll('.webbloc-checkbox:checked');
    const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
    
    if (selectedIds.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No WebBlocs Selected',
            text: 'Please select at least one WebBloc to export.',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    // Create download link
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/dashboard/webblocs/export';
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.appendChild(csrfToken);
    
    selectedIds.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'webbloc_ids[]';
        input.value = id;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush

@push('styles')
<style>
.webbloc-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fc;
    border-radius: 8px;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,123,255,.075);
}

.badge {
    font-size: 0.75em;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.card {
    border: none;
    border-radius: 10px;
}

.card-header {
    border-bottom: 1px solid #e3e6f0;
    background-color: #f8f9fc;
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
</style>
@endpush
```

### 10. `resources/views/dashboard/statistics/index.blade.php`

```blade
@extends('layouts.dashboard')

@section('title', 'Statistics & Analytics')

@section('content')
<div class="container-fluid" x-data="statisticsData()">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Statistics & Analytics</h1>
            <p class="text-muted">Monitor your WebBloc platform performance and usage</p>
        </div>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-primary" @click="refreshData()">
                <i class="fas fa-sync-alt" :class="{'fa-spin': loading}"></i> Refresh
            </button>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-download"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" @click="exportData('csv')">CSV Format</a></li>
                    <li><a class="dropdown-item" href="#" @click="exportData('xlsx')">Excel Format</a></li>
                    <li><a class="dropdown-item" href="#" @click="exportData('json')">JSON Format</a></li>
                </ul>
            </div>
            <button type="button" class="btn btn-outline-success" @click="toggleAutoRefresh()">
                <i class="fas fa-play" x-show="!autoRefresh"></i>
                <i class="fas fa-pause" x-show="autoRefresh"></i>
                <span x-text="autoRefresh ? 'Stop Auto' : 'Auto Refresh'"></span>
            </button>
        </div>
    </div>

    <!-- Time Range Selector -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <label class="form-label">Time Range</label>
                    <select class="form-select" x-model="timeRange" @change="loadData()">
                        <option value="24h">Last 24 Hours</option>
                        <option value="7d">Last 7 Days</option>
                        <option value="30d">Last 30 Days</option>
                        <option value="90d">Last 90 Days</option>
                        <option value="1y">Last Year</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Custom Date Range</label>
                    <div class="input-group">
                        <input type="date" class="form-control" x-model="customDateFrom">
                        <span class="input-group-text">to</span>
                        <input type="date" class="form-control" x-model="customDateTo">
                        <button class="btn btn-outline-primary" @click="loadCustomRange()">Apply</button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Update Frequency</label>
                    <select class="form-select" x-model="refreshInterval">
                        <option value="0">Manual</option>
                        <option value="30">30 seconds</option>
                        <option value="60">1 minute</option>
                        <option value="300">5 minutes</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row mb-4">
        <!-- Total API Calls -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">API Calls (Total)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" x-text="formatNumber(stats.api_calls_total || 0)"></div>
                            <div class="text-xs text-success" x-show="stats.api_calls_change > 0">
                                <i class="fas fa-arrow-up"></i> <span x-text="stats.api_calls_change + '%'"></span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Websites -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Websites</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" x-text="stats.active_websites || 0"></div>
                            <div class="text-xs text-success" x-show="stats.websites_change > 0">
                                <i class="fas fa-arrow-up"></i> <span x-text="stats.websites_change + ' new'"></span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-globe fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Users -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" x-text="formatNumber(stats.total_users || 0)"></div>
                            <div class="progress progress-sm mr-2">
                                <div class="progress-bar bg-info" role="progressbar" 
                                     :style="`width: ${Math.min((stats.total_users / 10000) * 100, 100)}%`"></div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Response Formats -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Response Distribution</div>
                            <div class="small">
                                <div>HTML: <span x-text="(stats.format_distribution?.html || 75) + '%'"></span></div>
                                <div>JSON: <span x-text="(stats.format_distribution?.json || 15) + '%'"></span></div>
                                <div>Other: <span x-text="(stats.format_distribution?.other || 10) + '%'"></span></div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-pie fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- API Usage Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">API Usage Over Time</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow">
                            <a class="dropdown-item" href="#" @click="chartType = 'line'">Line Chart</a>
                            <a class="dropdown-item" href="#" @click="chartType = 'bar'">Bar Chart</a>
                            <a class="dropdown-item" href="#" @click="chartType = 'area'">Area Chart</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="apiUsageChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- WebBloc Usage Breakdown -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">WebBloc Usage</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="webBlocUsageChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <template x-for="(webbloc, index) in webBlocStats" :key="webbloc.type">
                            <span class="mr-2">
                                <i class="fas fa-circle" :class="`text-${getWebBlocColor(index)}`"></i>
                                <span x-text="webbloc.type + ' (' + webbloc.percentage + '%)'"></span>
                            </span>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance Metrics</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Avg Response Time</div>
                                <div class="h6 mb-0" x-text="(stats.avg_response_time || 0) + 'ms'"></div>
                            </div>
                            <div class="mb-3">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Success Rate</div>
                                <div class="h6 mb-0 text-success" x-text="(stats.success_rate || 0) + '%'"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Cache Hit Rate</div>
                                <div class="h6 mb-0 text-info" x-text="(stats.cache_hit_rate || 0) + '%'"></div>
                            </div>
                            <div class="mb-3">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Database Size</div>
                                <div class="h6 mb-0" x-text="formatBytes(stats.database_size || 0)"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Chart -->
                    <canvas id="performanceChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Error Analysis</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Error Type</th>
                                    <th>Count</th>
                                    <th>Rate</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="error in errorStats" :key="error.type">
                                    <tr>
                                        <td x-text="error.type"></td>
                                        <td x-text="error.count"></td>
                                        <td x-text="error.rate + '%'"></td>
                                        <td>
                                            <i class="fas" 
                                               :class="error.trend > 0 ? 'fa-arrow-up text-danger' : 'fa-arrow-down text-success'"></i>
                                            <span x-text="Math.abs(error.trend) + '%'"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Tables -->
    <div class="row">
        <!-- Top Websites -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Websites by Usage</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Website</th>
                                    <th>API Calls</th>
                                    <th>Users</th>
                                    <th>Growth</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="website in topWebsites" :key="website.id">
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <i class="fas fa-globe text-primary"></i>
                                                </div>
                                                <div>
                                                    <div class="font-weight-bold" x-text="website.name"></div>
                                                    <div class="text-muted small" x-text="website.domain"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td x-text="formatNumber(website.api_calls)"></td>
                                        <td x-text="website.users"></td>
                                        <td>
                                            <span class="badge" 
                                                  :class="website.growth > 0 ? 'badge-success' : 'badge-danger'"
                                                  x-text="(website.growth > 0 ? '+' : '') + website.growth + '%'"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <template x-for="activity in recentActivity" :key="activity.id">
                        <div class="d-flex align-items-center py-2 border-bottom">
                            <div class="me-3">
                                <div class="icon-circle" :class="`bg-${getActivityColor(activity.type)}`">
                                    <i class="fas" :class="getActivityIcon(activity.type)" style="color: white;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="small font-weight-bold" x-text="activity.title"></div>
                                <div class="text-muted small" x-text="activity.description"></div>
                                <div class="text-xs text-muted" x-text="activity.time"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-time Updates -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div class="toast" id="liveToast" role="alert" aria-live="assertive" aria-atomic="true" x-show="showToast">
            <div class="toast-header">
                <i class="fas fa-chart-line text-primary me-2"></i>
                <strong class="me-auto">Statistics Updated</strong>
                <small x-text="lastUpdate"></small>
                <button type="button" class="btn-close" @click="showToast = false"></button>
            </div>
            <div class="toast-body" x-text="toastMessage">
                Statistics have been refreshed with the latest data.
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function statisticsData() {
    return {
        // Data properties
        loading: false,
        autoRefresh: false,
        refreshInterval: 0,
        timeRange: '24h',
        customDateFrom: '',
        customDateTo: '',
        chartType: 'line',
        
        // Statistics data
        stats: {},
        webBlocStats: [],
        errorStats: [],
        topWebsites: [],
        recentActivity: [],
        
        // Charts
        apiUsageChart: null,
        webBlocUsageChart: null,
        performanceChart: null,
        
        // Toast
        showToast: false,
        toastMessage: '',
        lastUpdate: '',
        
        // Initialize
        init() {
            this.loadData();
            this.initializeCharts();
            this.setupAutoRefresh();
        },
        
        // Load data from API
        async loadData() {
            this.loading = true;
            
            try {
                const response = await fetch(`/dashboard/admin/stats?range=${this.timeRange}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                
                this.stats = data.stats || {};
                this.webBlocStats = data.webbloc_stats || [];
                this.errorStats = data.error_stats || [];
                this.topWebsites = data.top_websites || [];
                this.recentActivity = data.recent_activity || [];
                
                this.updateCharts();
                this.showUpdateToast();
                
            } catch (error) {
                console.error('Failed to load statistics:', error);
                this.showErrorToast('Failed to load statistics data');
            } finally {
                this.loading = false;
            }
        },
        
        // Load custom date range
        loadCustomRange() {
            if (this.customDateFrom && this.customDateTo) {
                this.timeRange = 'custom';
                this.loadData();
            }
        },
        
        // Refresh data
        refreshData() {
            this.loadData();
        },
        
        // Setup auto refresh
        setupAutoRefresh() {
            this.$watch('refreshInterval', (newValue) => {
                if (this.intervalId) {
                    clearInterval(this.intervalId);
                }
                
                if (newValue > 0) {
                    this.intervalId = setInterval(() => {
                        this.loadData();
                    }, newValue * 1000);
                    this.autoRefresh = true;
                } else {
                    this.autoRefresh = false;
                }
            });
        },
        
        // Toggle auto refresh
        toggleAutoRefresh() {
            if (this.autoRefresh) {
                this.refreshInterval = 0;
            } else {
                this.refreshInterval = 60; // 1 minute default
            }
        },
        
        // Initialize charts
        initializeCharts() {
            // API Usage Chart
            const ctx1 = document.getElementById('apiUsageChart').getContext('2d');
            this.apiUsageChart = new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'API Calls',
                        data: [],
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // WebBloc Usage Chart
            const ctx2 = document.getElementById('webBlocUsageChart').getContext('2d');
            this.webBlocUsageChart = new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
            
            // Performance Chart
            const ctx3 = document.getElementById('performanceChart').getContext('2d');
            this.performanceChart = new Chart(ctx3, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Response Time (ms)',
                        data: [],
                        borderColor: '#1cc88a',
                        backgroundColor: 'rgba(28, 200, 138, 0.1)',
                        yAxisID: 'y'
                    }, {
                        label: 'Success Rate (%)',
                        data: [],
                        borderColor: '#36b9cc',
                        backgroundColor: 'rgba(54, 185, 204, 0.1)',
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: {
                                drawOnChartArea: false,
                            },
                        }
                    }
                }
            });
        },
        
        // Update charts with new data
        updateCharts() {
            if (this.stats.api_usage_timeline) {
                this.apiUsageChart.data.labels = this.stats.api_usage_timeline.labels;
                this.apiUsageChart.data.datasets[0].data = this.stats.api_usage_timeline.data;
                this.apiUsageChart.update();
            }
            
            if (this.webBlocStats.length > 0) {
                this.webBlocUsageChart.data.labels = this.webBlocStats.map(w => w.type);
                this.webBlocUsageChart.data.datasets[0].data = this.webBlocStats.map(w => w.count);
                this.webBlocUsageChart.update();
            }
            
            if (this.stats.performance_timeline) {
                this.performanceChart.data.labels = this.stats.performance_timeline.labels;
                this.performanceChart.data.datasets[0].data = this.stats.performance_timeline.response_time;
                this.performanceChart.data.datasets[1].data = this.stats.performance_timeline.success_rate;
                this.performanceChart.update();
            }
        },
        
        // Export data
        async exportData(format) {
            try {
                const response = await fetch(`/dashboard/admin/stats/export?format=${format}&range=${this.timeRange}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `webbloc-statistics-${this.timeRange}.${format}`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                } else {
                    throw new Error('Export failed');
                }
            } catch (error) {
                this.showErrorToast('Failed to export data');
            }
        },
        
        // Utility methods
        formatNumber(num) {
            if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
            if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
            return num.toString();
        },
        
        formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },
        
        getWebBlocColor(index) {
            const colors = ['primary', 'success', 'info', 'warning', 'danger'];
            return colors[index % colors.length];
        },
        
        getActivityColor(type) {
            const colors = {
                'api_call': 'primary',
                'user_register': 'success',
                'error': 'danger',
                'webbloc_install': 'info',
                'website_add': 'warning'
            };
            return colors[type] || 'secondary';
        },
        
        getActivityIcon(type) {
            const icons = {
                'api_call': 'fa-chart-line',
                'user_register': 'fa-user-plus',
                'error': 'fa-exclamation-triangle',
                'webbloc_install': 'fa-download',
                'website_add': 'fa-globe'
            };
            return icons[type] || 'fa-info';
        },
        
        showUpdateToast() {
            this.lastUpdate = new Date().toLocaleTimeString();
            this.toastMessage = 'Statistics have been refreshed with the latest data.';
            this.showToast = true;
            
            setTimeout(() => {
                this.showToast = false;
            }, 3000);
        },
        
        showErrorToast(message) {
            this.toastMessage = message;
            this.showToast = true;
            
            setTimeout(() => {
                this.showToast = false;
            }, 5000);
        }
    }
}

// Initialize Alpine.js component when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Additional initialization if needed
});
</script>
@endpush

@push('styles')
<style>
.progress-sm {
    height: 0.5rem;
}

.icon-circle {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-primary { background-color: #4e73df !important; }
.bg-success { background-color: #1cc88a !important; }
.bg-info { background-color: #36b9cc !important; }
.bg-warning { background-color: #f6c23e !important; }
.bg-danger { background-color: #e74a3b !important; }
.bg-secondary { background-color: #858796 !important; }

.chart-area {
    position: relative;
    height: 300px;
}

.chart-pie {
    position: relative;
    height: 200px;
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.card {
    border: none;
    border-radius: 10px;
}

.toast {
    min-width: 300px;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,123,255,.075);
}
</style>
@endpush
```

### 11. `.env.example` - Complete Environment Configuration

```env
# Application Configuration
APP_NAME="WebBloc Platform"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://webbloc.example.com
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
APP_MAINTENANCE_DRIVER=file

# WebBloc Specific Configuration
WEBBLOC_VERSION=1.0.0
WEBBLOC_RESPONSE_FORMAT_HTML=75
WEBBLOC_RESPONSE_FORMAT_JSON=15
WEBBLOC_RESPONSE_FORMAT_OTHER=10
WEBBLOC_MAX_WEBSITES_PER_USER=10
WEBBLOC_DEFAULT_RATE_LIMIT_PER_MINUTE=100
WEBBLOC_DEFAULT_RATE_LIMIT_PER_HOUR=5000
WEBBLOC_DEFAULT_RATE_LIMIT_PER_DAY=50000
WEBBLOC_CDN_URL=https://cdn.webbloc.example.com
WEBBLOC_API_PREFIX=api/v1

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=webbloc_platform
DB_USERNAME=webbloc_user
DB_PASSWORD=secure_database_password_2024
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# SQLite Configuration (Per-Website Databases)
SQLITE_DATABASE_PATH=storage/databases
SQLITE_BACKUP_PATH=storage/app/backups/databases
SQLITE_MAX_SIZE_MB=100
SQLITE_AUTO_VACUUM=true
SQLITE_JOURNAL_MODE=WAL
SQLITE_SYNCHRONOUS=NORMAL

# Redis Configuration
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0

# Cache Configuration
CACHE_STORE=redis
CACHE_PREFIX=webbloc_cache
WEBBLOC_CACHE_TTL=3600
WEBBLOC_STATS_CACHE_TTL=300
WEBBLOC_API_CACHE_TTL=600

# Session Configuration
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=null

# Queue Configuration
QUEUE_CONNECTION=redis
QUEUE_PREFIX=webbloc_queue

# Broadcasting
BROADCAST_CONNECTION=log

# Logging Configuration
LOG_CHANNEL=stack
LOG_STACK=single,daily
LOG_LEVEL=info
LOG_DAILY_DAYS=14
LOG_MAX_FILES=10

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=webbloc@example.com
MAIL_PASSWORD=mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=webbloc@example.com
MAIL_FROM_NAME="${APP_NAME}"

# AWS Configuration (for file storage if needed)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

# Filesystem Configuration
FILESYSTEM...

```
