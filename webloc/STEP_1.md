I'll revise the installation guide and project structure to include multi-language features, use the correct Laravel 11+ commands, implement both ID and UUID, and add soft deletes.

# WebBloc Project: Complete Installation & Development Guide

## Installation Guide

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & NPM
- MySQL 8.0+
- SQLite support enabled in PHP

### Step 1: Create Laravel Project
```bash
composer create-project laravel/laravel webbloc
cd webbloc
```

### Step 2: Install Required Packages
```bash
# Install API support (Laravel 11+)
php artisan install:api

# Install Breeze for Blade views
composer require laravel/breeze
php artisan breeze:install blade

# Install other required packages
composer require intervention/image spatie/laravel-permission maatwebsite/excel spatie/laravel-translatable

# Install frontend dependencies
npm install alpinejs sweetalert2
```

### Step 3: Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file:
```env
APP_NAME=WebBloc
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=webbloc_central
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Multi-language settings
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
SUPPORTED_LOCALES=en,ar

FILESYSTEM_DISK=public
```

### Step 4: Publish and Configure Multi-language Support
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan lang:publish
```

### Step 5: Database Setup
```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
```

### Step 6: Final Setup
```bash
npm run build
php artisan config:clear
php artisan cache:clear
php artisan serve
```

---

## 12 Core Project Files with Artisan Commands

### 1. Website Model & Migration (with UUID and Soft Deletes)
**Command:** `php artisan make:model Website -m`
**Description:** Manages static website registrations with multi-language support

**Migration (database/migrations/xxxx_create_websites_table.php):**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('name'); // Multi-language support
            $table->json('description')->nullable(); // Multi-language support
            $table->string('domain')->unique();
            $table->string('public_key', 64)->unique();
            $table->string('secret_key', 64)->unique();
            $table->string('sqlite_database_path');
            $table->json('allowed_components')->default('[]');
            $table->string('default_locale', 5)->default('en');
            $table->json('supported_locales')->default('["en"]');
            $table->boolean('is_active')->default(true);
            $table->timestamp('verified_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->index(['public_key', 'secret_key']);
            $table->index('domain');
            $table->index('uuid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('websites');
    }
};
```

**Model (app/Models/Website.php):**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class Website extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    protected $fillable = [
        'uuid',
        'user_id',
        'name',
        'description',
        'domain',
        'public_key',
        'secret_key',
        'sqlite_database_path',
        'allowed_components',
        'default_locale',
        'supported_locales',
        'is_active',
        'verified_at'
    ];

    protected $casts = [
        'allowed_components' => 'array',
        'supported_locales' => 'array',
        'is_active' => 'boolean',
        'verified_at' => 'timestamp'
    ];

    public $translatable = ['name', 'description'];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($website) {
            $website->uuid = Str::uuid();
            $website->public_key = Str::random(32);
            $website->secret_key = Str::random(32);
        });

        static::created(function ($website) {
            $website->sqlite_database_path = database_path("sqlite/website_{$website->id}.sqlite");
            $website->save();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statistics(): HasMany
    {
        return $this->hasMany(WebsiteStatistic::class);
    }

    public function getSqliteConnection()
    {
        $config = [
            'driver' => 'sqlite',
            'database' => $this->sqlite_database_path,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ];

        return app('db')->connection($config);
    }

    public function supportsLocale(string $locale): bool
    {
        return in_array($locale, $this->supported_locales);
    }
}
```

### 2. WebBloc Model & Migration (with Multi-language)
**Command:** `php artisan make:model WebBloc -m`
**Description:** Defines standardized web component templates with multi-language support

**Migration (database/migrations/xxxx_create_web_blocs_table.php):**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_blocs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('type')->unique();
            $table->json('name'); // Multi-language support
            $table->json('description'); // Multi-language support
            $table->json('attributes');
            $table->json('crud_permissions');
            $table->json('metadata');
            $table->text('blade_template');
            $table->text('alpine_js_code');
            $table->text('css_styles');
            $table->boolean('requires_auth')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('version', 10)->default('1.0.0');
            $table->softDeletes();
            $table->timestamps();
            
            $table->index('type');
            $table->index('is_active');
            $table->index('uuid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_blocs');
    }
};
```

**Model (app/Models/WebBloc.php):**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class WebBloc extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    protected $fillable = [
        'uuid',
        'type',
        'name',
        'description',
        'attributes',
        'crud_permissions',
        'metadata',
        'blade_template',
        'alpine_js_code',
        'css_styles',
        'requires_auth',
        'is_active',
        'version'
    ];

    protected $casts = [
        'attributes' => 'array',
        'crud_permissions' => 'array',
        'metadata' => 'array',
        'requires_auth' => 'boolean',
        'is_active' => 'boolean'
    ];

    public $translatable = ['name', 'description'];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($webBloc) {
            $webBloc->uuid = Str::uuid();
        });
    }

    public function canPerform(string $operation): bool
    {
        return $this->crud_permissions[$operation] ?? false;
    }

    public function getDefaultAttributes(): array
    {
        return $this->attributes;
    }

    public function getLocalizedName(string $locale = null): string
    {
        return $this->getTranslation('name', $locale ?? app()->getLocale());
    }

    public function getLocalizedDescription(string $locale = null): string
    {
        return $this->getTranslation('description', $locale ?? app()->getLocale());
    }
}
```

### 3. Website Statistics Model & Migration
**Command:** `php artisan make:model WebsiteStatistic -m`
**Description:** Tracks API usage and component performance statistics

**Migration (database/migrations/xxxx_create_website_statistics_table.php):**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_statistics', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('website_id')->constrained()->onDelete('cascade');
            $table->string('component_type');
            $table->string('action');
            $table->integer('count')->default(0);
            $table->date('date');
            $table->string('locale', 5)->default('en');
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->unique(['website_id', 'component_type', 'action', 'date', 'locale'], 'website_stats_unique');
            $table->index(['website_id', 'date']);
            $table->index('component_type');
            $table->index('uuid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_statistics');
    }
};
```

**Model (app/Models/WebsiteStatistic.php):**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class WebsiteStatistic extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'website_id',
        'component_type',
        'action',
        'count',
        'date',
        'locale',
        'metadata'
    ];

    protected $casts = [
        'date' => 'date',
        'metadata' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($statistic) {
            $statistic->uuid = Str::uuid();
        });
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public static function incrementStat(
        int $websiteId, 
        string $componentType, 
        string $action, 
        string $locale = 'en',
        array $metadata = []
    ): void {
        static::updateOrCreate([
            'website_id' => $websiteId,
            'component_type' => $componentType,
            'action' => $action,
            'date' => now()->toDateString(),
            'locale' => $locale
        ], [
            'count' => \DB::raw('count + 1'),
            'metadata' => $metadata
        ]);
    }
}
```

### 4. SQLite Database Service (Enhanced)
**Command:** `php artisan make:service SQLiteDatabaseService`
**Description:** Manages dynamic SQLite connections with multi-language support

**Service (app/Services/SQLiteDatabaseService.php):**
```php
<?php

namespace App\Services;

use App\Models\Website;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SQLiteDatabaseService
{
    public function createWebsiteDatabase(Website $website): bool
    {
        try {
            $databasePath = database_path("sqlite/website_{$website->id}.sqlite");
            
            // Ensure directory exists
            File::ensureDirectoryExists(dirname($databasePath));
            
            // Create empty SQLite file
            File::put($databasePath, '');
            
            // Connect and create tables
            $connection = $this->getConnection($website);
            $this->createWebsiteTables($connection);
            
            // Update website record
            $website->update(['sqlite_database_path' => $databasePath]);
            
            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to create SQLite database for website {$website->id}: " . $e->getMessage());
            return false;
        }
    }

    public function getConnection(Website $website): Connection
    {
        $connectionName = "website_{$website->id}";
        
        config(["database.connections.{$connectionName}" => [
            'driver' => 'sqlite',
            'database' => $website->sqlite_database_path,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]]);

        return DB::connection($connectionName);
    }

    private function createWebsiteTables(Connection $connection): void
    {
        // Users table for website-specific users
        $connection->statement('
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                uuid TEXT UNIQUE NOT NULL,
                name TEXT NOT NULL,
                email TEXT UNIQUE NOT NULL,
                email_verified_at TIMESTAMP NULL,
                password TEXT NOT NULL,
                locale TEXT DEFAULT "en",
                remember_token TEXT NULL,
                deleted_at TIMESTAMP NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )
        ');

        // WebBlocs table for component instances with multi-language support
        $connection->statement('
            CREATE TABLE IF NOT EXISTS webblocs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                uuid TEXT UNIQUE NOT NULL,
                user_id INTEGER NULL,
                type TEXT NOT NULL,
                title TEXT NULL,
                content TEXT NULL,
                locale TEXT DEFAULT "en",
                attributes TEXT NULL,
                metadata TEXT NULL,
                is_published BOOLEAN DEFAULT 1,
                deleted_at TIMESTAMP NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
            )
        ');

        // Create indexes
        $connection->statement('CREATE INDEX IF NOT EXISTS idx_users_uuid ON users(uuid)');
        $connection->statement('CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)');
        $connection->statement('CREATE INDEX IF NOT EXISTS idx_webblocs_uuid ON webblocs(uuid)');
        $connection->statement('CREATE INDEX IF NOT EXISTS idx_webblocs_type ON webblocs(type)');
        $connection->statement('CREATE INDEX IF NOT EXISTS idx_webblocs_locale ON webblocs(locale)');
        $connection->statement('CREATE INDEX IF NOT EXISTS idx_webblocs_user_id ON webblocs(user_id)');
        $connection->statement('CREATE INDEX IF NOT EXISTS idx_webblocs_created_at ON webblocs(created_at)');
    }

    public function deleteWebsiteDatabase(Website $website): bool
    {
        try {
            if (File::exists($website->sqlite_database_path)) {
                File::delete($website->sqlite_database_path);
            }
            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to delete SQLite database for website {$website->id}: " . $e->getMessage());
            return false;
        }
    }
}
```

### 5. WebBloc API Controller (Multi-language Enhanced)
**Command:** `php artisan make:controller Api/WebBlocController --api`
**Description:** Handles all CRUD operations with multi-language support

**Controller (app/Http/Controllers/Api/WebBlocController.php):**
```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Models\WebBloc;
use App\Services\SQLiteDatabaseService;
use App\Models\WebsiteStatistic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WebBlocController extends Controller
{
    protected SQLiteDatabaseService $sqliteService;

    public function __construct(SQLiteDatabaseService $sqliteService)
    {
        $this->sqliteService = $sqliteService;
    }

    public function index(Request $request, string $type): JsonResponse
    {
        try {
            $website = $this->authenticateWebsite($request);
            $locale = $this->getLocale($request, $website);
            
            $webBloc = WebBloc::where('type', $type)->where('is_active', true)->first();
            
            if (!$webBloc || !$webBloc->canPerform('read')) {
                return response()->json([
                    'error' => __('messages.component_not_found', [], $locale)
                ], 404);
            }

            $connection = $this->sqliteService->getConnection($website);
            
            $query = $connection->table('webblocs')
                ->where('type', $type)
                ->where('locale', $locale)
                ->whereNull('deleted_at');
            
            // Apply filters from request
            if ($request->has('limit')) {
                $query->limit($request->integer('limit', 10));
            }
            
            if ($request->has('sort')) {
                $sortOrder = $request->input('sort') === 'newest' ? 'desc' : 'asc';
                $query->orderBy('created_at', $sortOrder);
            }

            $items = $query->get();
            
            // Track statistics
            WebsiteStatistic::incrementStat($website->id, $type, 'read', $locale);
            
            return response()->json([
                'success' => true,
                'data' => $items,
                'meta' => [
                    'type' => $type,
                    'locale' => $locale,
                    'count' => $items->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => __('messages.fetch_failed', [], $this->getLocale($request))
            ], 500);
        }
    }

    public function store(Request $request, string $type): JsonResponse
    {
        try {
            $website = $this->authenticateWebsite($request);
            $locale = $this->getLocale($request, $website);
            
            $webBloc = WebBloc::where('type', $type)->where('is_active', true)->first();
            
            if (!$webBloc || !$webBloc->canPerform('create')) {
                return response()->json([
                    'error' => __('messages.component_not_creatable', [], $locale)
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'nullable|string|max:255',
                'content' => 'required|string|max:5000',
                'attributes' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $connection = $this->sqliteService->getConnection($website);
            
            $itemId = $connection->table('webblocs')->insertGetId([
                'uuid' => Str::uuid(),
                'type' => $type,
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'locale' => $locale,
                'attributes' => json_encode($request->input('attributes', [])),
                'metadata' => json_encode([
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $item = $connection->table('webblocs')->find($itemId);
            
            // Track statistics
            WebsiteStatistic::incrementStat($website->id, $type, 'create', $locale);
            
            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => __('messages.component_created', [], $locale)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => __('messages.create_failed', [], $this->getLocale($request))
            ], 500);
        }
    }

    public function show(Request $request, string $type, int $id): JsonResponse
    {
        try {
            $website = $this->authenticateWebsite($request);
            $locale = $this->getLocale($request, $website);
            
            $webBloc = WebBloc::where('type', $type)->where('is_active', true)->first();
            
            if (!$webBloc || !$webBloc->canPerform('read')) {
                return response()->json([
                    'error' => __('messages.component_not_readable', [], $locale)
                ], 404);
            }

            $connection = $this->sqliteService->getConnection($website);
            $item = $connection->table('webblocs')
                ->where('type', $type)
                ->where('id', $id)
                ->where('locale', $locale)
                ->whereNull('deleted_at')
                ->first();

            if (!$item) {
                return response()->json([
                    'error' => __('messages.item_not_found', [], $locale)
                ], 404);
            }

            // Track statistics
            WebsiteStatistic::incrementStat($website->id, $type, 'read', $locale);

            return response()->json([
                'success' => true,
                'data' => $item
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => __('messages.fetch_failed', [], $this->getLocale($request))
            ], 500);
        }
    }

    public function update(Request $request, string $type, int $id): JsonResponse
    {
        try {
            $website = $this->authenticateWebsite($request);
            $locale = $this->getLocale($request, $website);
            
            $webBloc = WebBloc::where('type', $type)->where('is_active', true)->first();
            
            if (!$webBloc || !$webBloc->canPerform('update')) {
                return response()->json([
                    'error' => __('messages.component_not_updatable', [], $locale)
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'nullable|string|max:255',
                'content' => 'nullable|string|max:5000',
                'attributes' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $connection = $this->sqliteService->getConnection($website);
            
            $updateData = array_filter([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'attributes' => $request->has('attributes') ? json_encode($request->input('attributes')) : null,
                'updated_at' => now()
            ]);

            $updated = $connection->table('webblocs')
                ->where('type', $type)
                ->where('id', $id)
                ->where('locale', $locale)
                ->whereNull('deleted_at')
                ->update($updateData);

            if (!$updated) {
                return response()->json([
                    'error' => __('messages.item_not_found', [], $locale)
                ], 404);
            }

            $item = $connection->table('webblocs')
                ->where('type', $type)
                ->where('id', $id)
                ->first();
            
            // Track statistics
            WebsiteStatistic::incrementStat($website->id, $type, 'update', $locale);

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => __('messages.component_updated', [], $locale)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => __('messages.update_failed', [], $this->getLocale($request))
            ], 500);
        }
    }

    public function destroy(Request $request, string $type, int $id): JsonResponse
    {
        try {
            $website = $this->authenticateWebsite($request);
            $locale = $this->getLocale($request, $website);
            
            $webBloc = WebBloc::where('type', $type)->where('is_active', true)->first();
            
            if (!$webBloc || !$webBloc->canPerform('delete')) {
                return response()->json([
                    'error' => __('messages.component_not_deletable', [], $locale)
                ], 404);
            }

            $connection = $this->sqliteService->getConnection($website);
            
            // Soft delete
            $deleted = $connection->table('webblocs')
                ->where('type', $type)
                ->where('id', $id)
                ->where('locale', $locale)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => now()]);

            if (!$deleted) {
                return response()->json([
                    'error' => __('messages.item_not_found', [], $locale)
                ], 404);
            }

            // Track statistics
            WebsiteStatistic::incrementStat($website->id, $type, 'delete', $locale);

            return response()->json([
                'success' => true,
                'message' => __('messages.component_deleted', [], $locale)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => __('messages.delete_failed', [], $this->getLocale($request))
            ], 500);
        }
    }

    private function authenticateWebsite(Request $request): Website
    {
        $publicKey = $request->header('X-Public-Key');
        $secretKey = $request->header('X-Secret-Key');

        if (!$publicKey || !$secretKey) {
            throw new \Exception('Authentication headers missing');
        }

        $website = Website::where('public_key', $publicKey)
            ->where('secret_key', $secretKey)
            ->where('is_active', true)
            ->first();

        if (!$website) {
            throw new \Exception('Invalid credentials');
        }

        return $website;
    }

    private function getLocale(Request $request, Website $website = null): string
    {
        $requestedLocale = $request->header('Accept-Language', 'en');
        
        if ($website && !$website->supportsLocale($requestedLocale)) {
            return $website->default_locale;
        }

        return $requestedLocale;
    }
}
```

### 6. Language Middleware
**Command:** `php artisan make:middleware SetLocale`
**Description:** Sets application locale based on request headers

**Middleware (app/Http/Middleware/SetLocale.php):**
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = config('app.supported_locales', ['en']);
        $locale = $request->header('Accept-Language', config('app.locale'));
        
        // Extract locale from Accept-Language header (e.g., 'en-US' -> 'en')
        $locale = substr($locale, 0, 2);
        
        if (in_array($locale, $supportedLocales)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
```

### 7. Dashboard Controller (Multi-language)
**Command:** `php artisan make:controller Dashboard/DashboardController`
**Description:** Manages admin dashboard with multi-language support

**Controller (app/Http/Controllers/Dashboard/DashboardController.php):**
```php
<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Models\WebBloc;
use App\Models\WebsiteStatistic;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_websites' => Website::count(),
            'active_websites' => Website::where('is_active', true)->count(),
            'total_components' => WebBloc::count(),
            'active_components' => WebBloc::where('is_active', true)->count(),
            'total_api_calls' => WebsiteStatistic::sum('count'),
            'today_api_calls' => WebsiteStatistic::whereDate('date', today())->sum('count')
        ];

        $recentWebsites = Website::with('user')
            ->latest()
            ->take(5)
            ->get();

        $topComponents = WebsiteStatistic::select('component_type')
            ->selectRaw('SUM(count) as total_usage')
            ->groupBy('component_type')
            ->orderByDesc('total_usage')
            ->take(5)
            ->get();

        return view('dashboard.index', compact('stats', 'recentWebsites', 'topComponents'));
    }

    public function websites(): View
    {
        $websites = Website::with(['user', 'statistics'])
            ->withCount('statistics')
            ->paginate(15);

        return view('dashboard.websites.index', compact('websites'));
    }

    public function components(): View
    {
        $components = WebBloc::paginate(15);

        return view('dashboard.components.index', compact('components'));
    }

    public function statistics(): View
    {
        $statistics = WebsiteStatistic::with('website')
            ->latest()
            ->paginate(20);

        $chartData = WebsiteStatistic::selectRaw('DATE(date) as date, SUM(count) as total')
            ->whereBetween('date', [now()->subDays(30), now()])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('dashboard.statistics.index', compact('statistics', 'chartData'));
    }
}
```

### 8. Website Management Controller
**Command:** `php artisan make:controller Dashboard/WebsiteController --resource`
**Description:** Manages website CRUD operations

**Controller (app/Http/Controllers/Dashboard/WebsiteController.php):**
```php
<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Models\User;
use App\Services\SQLiteDatabaseService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;

class WebsiteController extends Controller
{
    protected SQLiteDatabaseService $sqliteService;

    public function __construct(SQLiteDatabaseService $sqliteService)
    {
        $this->sqliteService = $sqliteService;
    }

    public function index(): View
    {
        $websites = Website::with('user')
            ->latest()
            ->paginate(15);

        return view('dashboard.websites.index', compact('websites'));
    }

    public function create(): View
    {
        $users = User::orderBy('name')->get();
        $supportedLocales = config('app.supported_locales', ['en']);
        
        return view('dashboard.websites.create', compact('users', 'supportedLocales'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'name.*' => 'required|string|max:255',
            'description.*' => 'nullable|string|max:1000',
            'domain' => 'required|url|unique:websites,domain',
            'default_locale' => 'required|string|in:' . implode(',', config('app.supported_locales', ['en'])),
            'supported_locales' => 'required|array|min:1',
            'supported_locales.*' => 'string|in:' . implode(',', config('app.supported_locales', ['en'])),
            'allowed_components' => 'nullable|array',
            'allowed_components.*' => 'string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $website = Website::create([
            'user_id' => $request->user_id,
            'name' => $request->name,
            'description' => $request->description,
            'domain' => $request->domain,
            'default_locale' => $request->default_locale,
            'supported_locales' => $request->supported_locales,
            'allowed_components' => $request->allowed_components ?? []
        ]);

        // Create SQLite database
        $this->sqliteService->createWebsiteDatabase($website);

        return redirect()->route('dashboard.websites.index')
            ->with('success', __('messages.website_created'));
    }

    public function show(Website $website): View
    {
        $website->load(['user', 'statistics']);
        
        $recentStats = $website->statistics()
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.websites.show', compact('website', 'recentStats'));
    }

    public function edit(Website $website): View
    {
        $users = User::orderBy('name')->get();
        $supportedLocales = config('app.supported_locales', ['en']);
        
        return view('dashboard.websites.edit', compact('website', 'users', 'supportedLocales'));
    }

    public function update(Request $request, Website $website): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'name.*' => 'required|string|max:255',
            'description.*' => 'nullable|string|max:1000',
            'domain' => 'required|url|unique:websites,domain,' . $website->id,
            'default_locale' => 'required|string|in:' . implode(',', config('app.supported_locales', ['en'])),
            'supported_locales' => 'required|array|min:1',
            'supported_locales.*' => 'string|in:' . implode(',', config('app.supported_locales', ['en'])),
            'allowed_components' => 'nullable|array',
            'allowed_components.*' => 'string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $website->update([
            'user_id' => $request->user_id,
            'name' => $request->name,
            'description' => $request->description,
            'domain' => $request->domain,
            'default_locale' => $request->default_locale,
            'supported_locales' => $request->supported_locales,
            'allowed_components' => $request->allowed_components ?? [],
            'is_active' => $request->boolean('is_active')
        ]);

        return redirect()->route('dashboard.websites.show', $website)
            ->with('success', __('messages.website_updated'));
    }

    public function destroy(Website $website): RedirectResponse
    {
        // Soft delete the website
        $website->delete();

        return redirect()->route('dashboard.websites.index')
            ->with('success', __('messages.website_deleted'));
    }

    public function regenerateKeys(Website $website): RedirectResponse
    {
        $website->update([
            'public_key' => \Str::random(32),
            'secret_key' => \Str::random(32)
        ]);

        return redirect()->back()
            ->with('success', __('messages.keys_regenerated'));
    }
}
```

### 9. Component Management Controller
**Command:** `php artisan make:controller Dashboard/ComponentController --resource`
**Description:** Manages WebBloc component templates

**Controller (app/Http/Controllers/Dashboard/ComponentController.php):**
```php
<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\WebBloc;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;

class ComponentController extends Controller
{
    public function index(): View
    {
        $components = WebBloc::latest()->paginate(15);
        
        return view('dashboard.components.index', compact('components'));
    }

    public function create(): View
    {
        $supportedLocales = config('app.supported_locales', ['en']);
        
        return view('dashboard.components.create', compact('supportedLocales'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|max:50|unique:web_blocs,type',
            'name.*' => 'required|string|max:255',
            'description.*' => 'required|string|max:1000',
            'attributes' => 'required|json',
            'crud_permissions' => 'required|array',
            'crud_permissions.create' => 'boolean',
            'crud_permissions.read' => 'boolean',
            'crud_permissions.update' => 'boolean',
            'crud_permissions.delete' => 'boolean',
            'blade_template' => 'required|string',
            'alpine_js_code' => 'required|string',
            'css_styles' => 'nullable|string',
            'requires_auth' => 'boolean',
            'version' => 'required|string|max:10'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        WebBloc::create([
            'type' => $request->type,
            'name' => $request->name,
            'description' => $request->description,
            'attributes' => json_decode($request->attributes, true),
            'crud_permissions' => $request->crud_permissions,
            'metadata' => [],
            'blade_template' => $request->blade_template,
            'alpine_js_code' => $request->alpine_js_code,
            'css_styles' => $request->css_styles ?? '',
            'requires_auth' => $request->boolean('requires_auth'),
            'version' => $request->version
        ]);

        return redirect()->route('dashboard.components.index')
            ->with('success', __('messages.component_created'));
    }

    public function show(WebBloc $component): View
    {
        return view('dashboard.components.show', compact('component'));
    }

    public function edit(WebBloc $component): View
    {
        $supportedLocales = config('app.supported_locales', ['en']);
        
        return view('dashboard.components.edit', compact('component', 'supportedLocales'));
    }

    public function update(Request $request, WebBloc $component): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|max:50|unique:web_blocs,type,' . $component->id,
            'name.*' => 'required|string|max:255',
            'description.*' => 'required|string|max:1000',
            'attributes' => 'required|json',
            'crud_permissions' => 'required|array',
            'crud_permissions.create' => 'boolean',
            'crud_permissions.read' => 'boolean',
            'crud_permissions.update' => 'boolean',
            'crud_permissions.delete' => 'boolean',
            'blade_template' => 'required|string',
            'alpine_js_code' => 'required|string',
            'css_styles' => 'nullable|string',
            'requires_auth' => 'boolean',
            'is_active' => 'boolean',
            'version' => 'required|string|max:10'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $component->update([
            'type' => $request->type,
            'name' => $request->name,
            'description' => $request->description,
            'attributes' => json_decode($request->attributes, true),
            'crud_permissions' => $request->crud_permissions,
            'blade_template' => $request->blade_template,
            'alpine_js_code' => $request->alpine_js_code,
            'css_styles' => $request->css_styles ?? '',
            'requires_auth' => $request->boolean('requires_auth'),
            'is_active' => $request->boolean('is_active'),
            'version' => $request->version
        ]);

        return redirect()->route('dashboard.components.show', $component)
            ->with('success', __('messages.component_updated'));
    }

    public function destroy(WebBloc $component): RedirectResponse
    {
        $component->delete();

        return redirect()->route('dashboard.components.index')
            ->with('success', __('messages.component_deleted'));
    }
}
```

### 10. User Model (Enhanced with UUID)
**Command:** `php artisan make:migration add_uuid_to_users_table`
**Description:** Add UUID support to existing User model

**Migration (database/migrations/xxxx_add_uuid_to_users_table.php):**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
```

**Enhanced User Model (app/Models/User.php):**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'locale'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'timestamp',
        'password' => 'hashed',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($user) {
            $user->uuid = Str::uuid();
            $user->locale = $user->locale ?? app()->getLocale();
        });
    }

    public function websites(): HasMany
    {
        return $this->hasMany(Website::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
```

### 11. Language Files
**Command:** Create language files manually
**Description:** Multi-language support files

**English (lang/en/messages.php):**
```php
<?php

return [
    'component_not_found' => 'Component not found or not accessible',
    'component_not_creatable' => 'Component not found or not creatable',
    'component_not_readable' => 'Component not found or not readable',
    'component_not_updatable' => 'Component not found or not updatable',
    'component_not_deletable' => 'Component not found or not deletable',
    'item_not_found' => 'Item not found',
    'fetch_failed' => 'Failed to fetch components',
    'create_failed' => 'Failed to create component',
    'update_failed' => 'Failed to update component',
    'delete_failed' => 'Failed to delete component',
    'component_created' => 'Component created successfully',
    'component_updated' => 'Component updated successfully',
    'component_deleted' => 'Component deleted successfully',
    'website_created' => 'Website created successfully',
    'website_updated' => 'Website updated successfully',
    'website_deleted' => 'Website deleted successfully',
    'keys_regenerated' => 'API keys regenerated successfully',
];
```

**Arabic (lang/ar/messages.php):**
```php
<?php

return [
    'component_not_found' => 'المكون غير موجود أو غير قابل للوصول',
    'component_not_creatable' => 'المكون غير موجود أو غير قابل للإنشاء',
    'component_not_readable' => 'المكون غير موجود أو غير قابل للقراءة',
    'component_not_updatable' => 'المكون غير موجود أو غير قابل للتحديث',
    'component_not_deletable' => 'المكون غير موجود أو غير قابل للحذف',
    'item_not_found' => 'العنصر غير موجود',
    'fetch_failed' => 'فشل في جلب المكونات',
    'create_failed' => 'فشل في إنشاء المكون',
    'update_failed' => 'فشل في تحديث المكون',
    'delete_failed' => 'فشل في حذف المكون',
    'component_created' => 'تم إنشاء المكون بنجاح',
    'component_updated' => 'تم تحديث المكون بنجاح',
    'component_deleted' => 'تم حذف المكون بنجاح',
    'website_created' => 'تم إنشاء الموقع بنجاح',
    'website_updated' => 'تم تحديث الموقع بنجاح',
    'website_deleted' => 'تم حذف الموقع بنجاح',
    'keys_regenerated' => 'تم إعادة إنشاء مفاتيح API بنجاح',
];
```

### 12. Configuration File
**Command:** `php artisan make:provider WebBlocServiceProvider`
**Description:** Service provider for WebBloc configurations

**Config File (config/webbloc.php):**
```php
<?php

return [
    'supported_locales' => ['en', 'ar'],
    'default_locale' => 'en',
    'api_rate_limit' => 1000, // requests per hour
    'sqlite_path' => database_path('sqlite'),
    'default_components' => [
        'comment' => [
            'name' => [
                'en' => 'Comments',
                'ar' => 'التعليقات'
            ],
            'description' => [
                'en' => 'User comments system',
                'ar' => 'نظام تعليقات المستخدمين'
            ]
        ],
        'review' => [
            'name' => [
                'en' => 'Reviews',
                'ar' => 'المراجعات'
            ],
            'description' => [
                'en' => 'Product/service reviews',
                'ar' => 'مراجعات المنتجات/الخدمات'
            ]
        ]
    ]
];
```

**Service Provider (app/Providers/WebBlocServiceProvider.php):**
```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SQLiteDatabaseService;

class WebBlocServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SQLiteDatabaseService::class);
        
        $this->mergeConfigFrom(
            __DIR__.'/../../config/webbloc.php', 'webbloc'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/webbloc.php' => config_path('webbloc.php'),
            ], 'webbloc-config');
        }

        // Set supported locales in app config
        config(['app.supported_locales' => config('webbloc.supported_locales')]);
    }
}
```

---

## All Project Blade Views to Create

### 1. Dashboard Layout Views
- `resources/views/layouts/dashboard.blade.php` - Main dashboard layout with multi-language support
- `resources/views/components/sidebar.blade.php` - Dashboard sidebar component
- `resources/views/components/navbar.blade.php` - Dashboard navbar with language switcher

### 2. Dashboard Main Views
- `resources/views/dashboard/index.blade.php` - Dashboard homepage with statistics
- `resources/views/dashboard/profile.blade.php` - User profile management

### 3. Website Management Views
- `resources/views/dashboard/websites/index.blade.php` - Websites listing page
- `resources/views/dashboard/websites/create.blade.php` - Create website form
- `resources/views/dashboard/websites/edit.blade.php` - Edit website form
- `resources/views/dashboard/websites/show.blade.php` - Website details view

### 4. Component Management Views
- `resources/views/dashboard/components/index.blade.php` - Components listing
- `resources/views/dashboard/components/create.blade.php` - Create component form
- `resources/views/dashboard/components/edit.blade.php` - Edit component form
- `resources/views/dashboard/components/show.blade.php` - Component details view

### 5. Statistics & Analytics Views
- `resources/views/dashboard/statistics/index.blade.php` - Statistics overview
- `resources/views/dashboard/statistics/website.blade.php` - Per-website statistics
- `resources/views/dashboard/statistics/component.blade.php` - Component usage stats

### 6. User Management Views
- `resources/views/dashboard/users/index.blade.php` - Users listing
- `resources/views/dashboard/users/create.blade.php` - Create user form
- `resources/views/dashboard/users/edit.blade.php` - Edit user form
- `resources/views/dashboard/users/show.blade.php` - User details view

### 7. Authentication Views (Enhanced)
- `resources/views/auth/login.blade.php` - Login form with multi-language
- `resources/views/auth/register.blade.php` - Registration form
- `resources/views/auth/forgot-password.blade.php` - Password reset request
- `resources/views/auth/reset-password.blade.php` - Password reset form

### 8. WebBloc Component Templates
- `resources/views/webblocs/comment.blade.php` - Comment component template
- `resources/views/webblocs/review.blade.php` - Review component template
- `resources/views/webblocs/auth.blade.php` - Authentication component template

### 9. API Documentation Views
- `resources/views/docs/index.blade.php` - API documentation homepage
- `resources/views/docs/authentication.blade.php` - Authentication docs
- `resources/views/docs/components.blade.php` - Components API docs
- `resources/views/docs/integration.blade.php` - Integration guide

### 10. Public Landing Views
- `resources/views/welcome.blade.php` - Public homepage
- `resources/views/pricing.blade.php` - Pricing page
- `resources/views/contact.blade.php` - Contact form

### 11. Email Templates
- `resources/views/emails/website-verified.blade.php` - Website verification email
- `resources/views/emails/api-key-generated.blade.php` - API key notification
- `resources/views/emails/usage-alert.blade.php` - Usage limit alerts

### 12. Error Pages
- `resources/views/errors/404.blade.php` - Custom 404 page
- `resources/views/errors/500.blade.php` - Custom 500 page
- `resources/views/errors/429.blade.php` - Rate limit exceeded page

Each blade view will include:
- Multi-language support using `__()` helper
- RTL support for Arabic language
- Responsive design with Tailwind CSS
- Alpine.js integration for dynamic behavior
- SweetAlert2 for notifications
- Proper CSRF protection
- UUID-based routing where applicable

The project structure provides a solid foundation for a multi-language, secure WebBloc system with comprehensive CRUD operations, soft deletes, and UUID support as requested.
