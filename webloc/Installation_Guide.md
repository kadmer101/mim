# Dynamic Web Components API with WebBloc Standard - Complete Installation Guide

## Step 1: Laravel Project Setup with WebBloc Architecture

### 1.1 Create New Laravel Project
```bash
composer create-project laravel/laravel dynamic-webbloc-api
cd dynamic-webbloc-api
```

### 1.2 Install Required Packages
```bash
composer require laravel/breeze laravel/sanctum intervention/image spatie/laravel-permission maatwebsite/excel
npm install alpinejs sweetalert2
```

### 1.3 Install Laravel Breeze
```bash
php artisan breeze:install blade
npm install && npm run build
```

### 1.4 Install Laravel Sanctum
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 1.5 Install Spatie Permissions
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### 1.6 Configure Database and Run Migrations
```bash
php artisan migrate
php artisan db:seed
```

### 1.7 Generate Application Key
```bash
php artisan key:generate
```

### 1.8 Create Storage Link
```bash
php artisan storage:link
```

### 1.9 Create Custom Artisan Commands for WebBloc
```bash
php artisan make:command CreateWebsiteDatabase
php artisan make:command InstallWebBloc
php artisan make:command GenerateApiKeys
php artisan make:command BuildWebBlocCdn
```

### 1.10 Create Models for WebBloc Architecture
```bash
php artisan make:model Website -m
php artisan make:model WebsiteStatistic -m
php artisan make:model ApiKey -m
php artisan make:model WebBloc -m
php artisan make:model WebBlocInstance -m
```

### 1.11 Create Controllers for WebBloc System
```bash
php artisan make:controller Api/WebBlocController
php artisan make:controller Dashboard/WebsiteController
php artisan make:controller Dashboard/ApiKeyController
php artisan make:controller Dashboard/WebBlocController
php artisan make:controller Dashboard/StatisticsController
php artisan make:controller Dashboard/AdminController
```

### 1.12 Create Middleware for WebBloc Security
```bash
php artisan make:middleware ValidateApiKey
php artisan make:middleware DynamicSqliteConnection
php artisan make:middleware CorsMiddleware
php artisan make:middleware WebBlocRateLimiter
```

### 1.13 Create Services for WebBloc Management
```bash
php artisan make:provider DatabaseConnectionServiceProvider
```

### 1.14 Create Requests for WebBloc Validation
```bash
php artisan make:request Api/WebBlocRequest
php artisan make:request Dashboard/WebsiteRequest
php artisan make:request Dashboard/WebBlocRequest
```

### 1.15 Create Resources for WebBloc API
```bash
php artisan make:resource WebBlocResource
php artisan make:resource WebsiteResource
```

### 1.16 Create Jobs for WebBloc Processing
```bash
php artisan make:job ProcessWebsiteStatistics
php artisan make:job CleanupExpiredTokens
php artisan make:job BuildWebBlocCdnFiles
```

### 1.17 Create Notifications for WebBloc System
```bash
php artisan make:notification ApiKeyGenerated
php artisan make:notification WebsiteRegistered
php artisan make:notification WebBlocInstalled
```

## Step 2: Enhanced Project Files Structure (75+ Files) with WebBloc Standard

| # | File Path | Artisan Command | Purpose | Dependencies |
|---|-----------|----------------|---------|--------------|
| 1 | `config/database.php` | - | Database config with dynamic SQLite for WebBloc | - |
| 2 | `config/sanctum.php` | Published via sanctum | API authentication config | Laravel Sanctum |
| 3 | `config/permission.php` | Published via spatie | Roles and permissions config | Spatie Permission |
| 4 | `config/cors.php` | - | CORS configuration for WebBloc API | - |
| 5 | `config/webbloc.php` | - | WebBloc standard configuration | - |
| 6 | `database/migrations/2024_01_01_000001_create_websites_table.php` | make:model Website -m | Websites table migration | - |
| 7 | `database/migrations/2024_01_01_000002_create_website_statistics_table.php` | make:model WebsiteStatistic -m | Statistics table migration | websites table |
| 8 | `database/migrations/2024_01_01_000003_create_api_keys_table.php` | make:model ApiKey -m | API keys table migration | websites table |
| 9 | `database/migrations/2024_01_01_000004_create_web_blocs_table.php` | make:model WebBloc -m | WebBloc definitions table | - |
| 10 | `database/migrations/2024_01_01_000005_create_web_bloc_instances_table.php` | make:model WebBlocInstance -m | WebBloc instances migration | websites, web_blocs |
| 11 | `database/migrations/sqlite/2024_01_01_000001_create_users_table.php` | - | SQLite users table template | - |
| 12 | `database/migrations/sqlite/2024_01_01_000002_create_web_blocs_table.php` | - | SQLite WebBlocs table template | - |
| 13 | `database/seeders/DatabaseSeeder.php` | - | Main database seeder | - |
| 14 | `database/seeders/WebBlocSeeder.php` | make:seeder WebBlocSeeder | WebBloc definitions seeder | - |
| 15 | `database/seeders/RolePermissionSeeder.php` | make:seeder RolePermissionSeeder | Roles and permissions seeder | Spatie Permission |
| 16 | `app/Models/Website.php` | make:model Website | Website model with WebBloc support | - |
| 17 | `app/Models/WebsiteStatistic.php` | make:model WebsiteStatistic | Statistics model | Website model |
| 18 | `app/Models/ApiKey.php` | make:model ApiKey | API key model with WebBloc security | Website model |
| 19 | `app/Models/WebBloc.php` | make:model WebBloc | WebBloc definition model | - |
| 20 | `app/Models/WebBlocInstance.php` | make:model WebBlocInstance | WebBloc instance model | Website, WebBloc |
| 21 | `app/Http/Controllers/Api/WebBlocController.php` | make:controller Api/WebBlocController | WebBloc standard API controller | All models |
| 22 | `app/Http/Controllers/Dashboard/WebsiteController.php` | make:controller Dashboard/WebsiteController | Website management dashboard | Website model |
| 23 | `app/Http/Controllers/Dashboard/ApiKeyController.php` | make:controller Dashboard/ApiKeyController | API key management | ApiKey model |
| 24 | `app/Http/Controllers/Dashboard/WebBlocController.php` | make:controller Dashboard/WebBlocController | WebBloc management dashboard | WebBloc model |
| 25 | `app/Http/Controllers/Dashboard/StatisticsController.php` | make:controller Dashboard/StatisticsController | Statistics dashboard | WebsiteStatistic model |
| 26 | `app/Http/Controllers/Dashboard/AdminController.php` | make:controller Dashboard/AdminController | Admin dashboard | All models |
| 27 | `app/Http/Middleware/ValidateApiKey.php` | make:middleware ValidateApiKey | API key validation for WebBloc | ApiKey model |
| 28 | `app/Http/Middleware/DynamicSqliteConnection.php` | make:middleware DynamicSqliteConnection | Dynamic SQLite for WebBloc | Website model |
| 29 | `app/Http/Middleware/CorsMiddleware.php` | make:middleware CorsMiddleware | CORS handling for WebBloc API | - |
| 30 | `app/Http/Middleware/WebBlocRateLimiter.php` | make:middleware WebBlocRateLimiter | Rate limiting for WebBloc API | - |
| 31 | `app/Http/Requests/Api/WebBlocRequest.php` | make:request Api/WebBlocRequest | WebBloc standard validation | - |
| 32 | `app/Http/Requests/Dashboard/WebsiteRequest.php` | make:request Dashboard/WebsiteRequest | Website form validation | - |
| 33 | `app/Http/Requests/Dashboard/WebBlocRequest.php` | make:request Dashboard/WebBlocRequest | WebBloc form validation | - |
| 34 | `app/Http/Resources/WebBlocResource.php` | make:resource WebBlocResource | WebBloc API resource | - |
| 35 | `app/Http/Resources/WebsiteResource.php` | make:resource WebsiteResource | Website API resource | - |
| 36 | `app/Services/DatabaseConnectionService.php` | - | Dynamic database connection | - |
| 37 | `app/Services/WebBlocService.php` | - | WebBloc standard logic | - |
| 38 | `app/Services/ApiKeyService.php` | - | API key management | - |
| 39 | `app/Services/StatisticsService.php` | - | Statistics calculation | - |
| 40 | `app/Services/CdnService.php` | - | CDN file management | - |
| 41 | `app/Console/Commands/CreateWebsiteDatabase.php` | make:command CreateWebsiteDatabase | Create SQLite databases | DatabaseConnectionService |
| 42 | `app/Console/Commands/InstallWebBloc.php` | make:command InstallWebBloc | Install WebBloc components | WebBlocService |
| 43 | `app/Console/Commands/GenerateApiKeys.php` | make:command GenerateApiKeys | Generate API keys | ApiKeyService |
| 44 | `app/Console/Commands/BuildWebBlocCdn.php` | make:command BuildWebBlocCdn | Build CDN files | CdnService |
| 45 | `app/Jobs/ProcessWebsiteStatistics.php` | make:job ProcessWebsiteStatistics | Statistics processing | StatisticsService |
| 46 | `app/Jobs/CleanupExpiredTokens.php` | make:job CleanupExpiredTokens | Token cleanup | Laravel Sanctum |
| 47 | `app/Jobs/BuildWebBlocCdnFiles.php` | make:job BuildWebBlocCdnFiles | CDN file building | CdnService |
| 48 | `app/Notifications/ApiKeyGenerated.php` | make:notification ApiKeyGenerated | API key notification | - |
| 49 | `app/Notifications/WebsiteRegistered.php` | make:notification WebsiteRegistered | Website registration notification | - |
| 50 | `app/Notifications/WebBlocInstalled.php` | make:notification WebBlocInstalled | WebBloc installation notification | - |
| 51 | `routes/api.php` | - | WebBloc API routes | All controllers |
| 52 | `routes/web.php` | - | Dashboard web routes | Dashboard controllers |
| 53 | `resources/views/dashboard/layouts/app.blade.php` | - | Dashboard layout with WebBloc | Laravel Breeze |
| 54 | `resources/views/dashboard/admin/index.blade.php` | - | Admin dashboard | Dashboard layout |
| 55 | `resources/views/dashboard/websites/index.blade.php` | - | Websites listing | Dashboard layout |
| 56 | `resources/views/dashboard/websites/create.blade.php` | - | Website creation form | Dashboard layout |
| 57 | `resources/views/dashboard/websites/edit.blade.php` | - | Website edit form | Dashboard layout |
| 58 | `resources/views/dashboard/api-keys/index.blade.php` | - | API keys management | Dashboard layout |
| 59 | `resources/views/dashboard/webblocs/index.blade.php` | - | WebBloc management | Dashboard layout |
| 60 | `resources/views/dashboard/webblocs/create.blade.php` | - | WebBloc creation form | Dashboard layout |
| 61 | `resources/views/dashboard/statistics/index.blade.php` | - | Statistics dashboard | Dashboard layout |
| 62 | `resources/views/components/webbloc/auth.blade.php` | - | Authentication WebBloc | Alpine.js, SweetAlert |
| 63 | `resources/views/components/webbloc/comments.blade.php` | - | Comments WebBloc | Alpine.js, SweetAlert |
| 64 | `resources/views/components/webbloc/reviews.blade.php` | - | Reviews WebBloc | Alpine.js, SweetAlert |
| 65 | `resources/views/components/webbloc/notifications.blade.php` | - | Notifications WebBloc | Alpine.js, SweetAlert |
| 66 | `resources/js/webbloc-core.js` | - | WebBloc core JavaScript | Alpine.js |
| 67 | `resources/js/webbloc-components.js` | - | WebBloc components JavaScript | Alpine.js, SweetAlert |
| 68 | `resources/css/webbloc-core.css` | - | WebBloc core CSS | - |
| 69 | `resources/css/webbloc-components.css` | - | WebBloc components CSS | - |
| 70 | `public/cdn/webbloc.min.js` | - | Minified CDN JavaScript | webbloc-core.js, webbloc-components.js |
| 71 | `public/cdn/webbloc.min.css` | - | Minified CDN CSS | webbloc-core.css, webbloc-components.css |
| 72 | `public/deploy/install-webbloc.php` | - | WebBloc installation script | - |
| 73 | `public/deploy/migrate.php` | - | Migration script for production | - |
| 74 | `public/deploy/build-assets.php` | - | Asset building script | - |
| 75 | `.env.example` | - | Environment example with WebBloc | - |

## Step 3: Installation Steps After File Creation

### 3.1 Configure Environment for WebBloc
```bash
cp .env.example .env
# Edit .env with database credentials and WebBloc settings
```

### 3.2 Run Migrations and Seeders
```bash
php artisan migrate:fresh --seed
```

### 3.3 Create Initial Website Databases
```bash
php artisan website:create-database --all
```

### 3.4 Install Default WebBlocs
```bash
php artisan webbloc:install auth --websites=all
php artisan webbloc:install comments --websites=all
php artisan webbloc:install reviews --websites=all
php artisan webbloc:install notifications --websites=all
```

### 3.5 Generate Initial API Keys
```bash
php artisan apikey:generate --website-id=1
```

### 3.6 Build WebBloc CDN Files
```bash
php artisan webbloc:build-cdn
```

### 3.7 Build Assets
```bash
npm run build
```

### 3.8 Set Permissions (Linux/Mac)
```bash
chmod -R 775 storage bootstrap/cache public/cdn
```

## Step 4: WebBloc Installation and Management Guide

### 4.1 WebBloc Standard Structure

Each WebBloc follows this standardized structure:

#### 4.1.1 WebBloc Definition (MySQL `web_blocs` table)
```php
[
    'type' => 'comment', // WebBloc type identifier
    'name' => 'Comments System',
    'version' => '1.0.0',
    'attributes' => [
        'limit' => ['type' => 'integer', 'default' => 10],
        'sort' => ['type' => 'string', 'default' => 'newest'],
        'auth_required' => ['type' => 'boolean', 'default' => false],
        'moderation' => ['type' => 'boolean', 'default' => true]
    ],
    'crud' => [
        'create' => true,
        'read' => true,
        'update' => true,
        'delete' => true
    ],
    'metadata' => [
        'description' => 'Dynamic commenting system for static websites',
        'author' => 'WebBloc Team',
        'tags' => ['comments', 'social', 'interaction']
    ]
]
```

### 4.2 Create New WebBloc Command
```bash
php artisan make:webbloc [webbloc-name] --type=[type] --crud=[create,read,update,delete]
```

### 4.3 WebBloc Installation Process

#### 4.3.1 Install WebBloc to Specific Website
```bash
php artisan webbloc:install [webbloc-type] --website-id=[id]
```

#### 4.3.2 Install WebBloc to All Websites
```bash
php artisan webbloc:install [webbloc-type] --websites=all
```

### 4.4 WebBloc Component Templates

#### 4.4.1 Controller Template (`app/Http/Controllers/Api/WebBlocController.php`)
```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\WebBlocRequest;
use App\Http\Resources\WebBlocResource;
use App\Services\WebBlocService;
use Illuminate\Http\Request;

class WebBlocController extends Controller
{
    protected $webBlocService;

    public function __construct(WebBlocService $webBlocService)
    {
        $this->webBlocService = $webBlocService;
    }

    public function index(Request $request, $type)
    {
        $webBlocs = $this->webBlocService->list($type, $request->all());
        return WebBlocResource::collection($webBlocs);
    }

    public function store(WebBlocRequest $request, $type)
    {
        $webBloc = $this->webBlocService->create($type, $request->validated());
        return new WebBlocResource($webBloc);
    }

    public function show($type, $id)
    {
        $webBloc = $this->webBlocService->find($type, $id);
        return new WebBlocResource($webBloc);
    }

    public function update(WebBlocRequest $request, $type, $id)
    {
        $webBloc = $this->webBlocService->update($type, $id, $request->validated());
        return new WebBlocResource($webBloc);
    }

    public function destroy($type, $id)
    {
        $this->webBlocService->delete($type, $id);
        return response()->json(['message' => 'WebBloc deleted successfully']);
    }
}
```

#### 4.4.2 Migration Template (`database/migrations/2024_01_01_000004_create_web_blocs_table.php`)
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebBlocsTable extends Migration
{
    public function up()
    {
        Schema::create('web_blocs', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();
            $table->string('name');
            $table->string('version');
            $table->json('attributes')->nullable();
            $table->json('crud')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('web_blocs');
    }
}
```

#### 4.4.3 Blade Component Template (`resources/views/components/webbloc/comments.blade.php`)
```blade
<div x-data="webBlocComponent('comment')" class="w2030b-comment">
    <template x-if="loading">Loading...</template>
    <template x-if="!loading">
        <div x-for="comment in comments" :key="comment.id">
            <p x-text="comment.content"></p>
            <button @click="deleteComment(comment.id)">Delete</button>
        </div>
        <form @submit.prevent="addComment">
            <textarea x-model="newComment.content"></textarea>
            <button type="submit">Add Comment</button>
        </form>
    </template>
</div>

<script>
function webBlocComponent(type) {
    return {
        type: type,
        loading: true,
        comments: [],
        newComment: { content: '' },
        init() {
            this.fetchComments();
        },
        fetchComments() {
            fetch(`/api/webblocs/${this.type}`)
                .then(response => response.json())
                .then(data => {
                    this.comments = data;
                    this.loading = false;
                });
        },
        addComment() {
            fetch(`/api/webblocs/${this.type}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(this.newComment)
            })
            .then(response => response.json())
            .then(data => {
                this.comments.push(data);
                this.newComment.content = '';
            });
        },
        deleteComment(id) {
            fetch(`/api/webblocs/${this.type}/${id}`, { method: 'DELETE' })
                .then(() => {
                    this.comments = this.comments.filter(comment => comment.id !== id);
                });
        }
    }
}
</script>
```

#### 4.4.4 Request Template (`app/Http/Requests/Api/WebBlocRequest.php`)
```php
<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class WebBlocRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'content' => 'required|string|max:255',
            'author' => 'required|string|max:100',
            // Add more common validation rules here
        ];
    }
}
```

#### 4.4.5 Resource Template (`app/Http/Resources/WebBlocResource.php`)
```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WebBlocResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'content' => $this->content,
            'author' => $this->author,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // Add more common fields here
        ];
    }
}
```

### 4.5 Integration Syntax for Static Websites

Web components are defined in Blade components and can be inserted into static websites using the following syntax:

```html
<div w2030b="[component_name]" w2030b_tags='{"limit": 10, "sort": "newest"}'>Content Loading...</div>
```

**Example:**

```html
<div w2030b="comments" w2030b_tags='{"limit": 10, "sort": "newest"}'>Content Loading...</div>
```

### 4.6 Deployment Commands (No SSH Required)

```bash
php public/deploy/install-webbloc.php
php public/deploy/migrate.php
php public/deploy/build-assets.php
```

### 4.7 CDN Integration

CDN links for JavaScript and CSS files that can be included in static websites to load dynamic components:

```html
<script src="https://2030b.com/webloc/cdn/webbloc.min.js"></script>
<link rel="stylesheet" href="https://2030b.com/webloc/cdn/webbloc.min.css">
```

**Note:** CDN links for JavaScript and CSS files should not interfere with the static website's existing scripts and stylesheets. Only web components called via API with a public key will be included. There will also be a secret key for additional security.

### 4.8 Notifications

#### SweetAlert Integration

```javascript
Swal.fire({
    icon: 'success',
    title: 'Success!',
    text: 'Your comment has been added.',
});
```

#### Toast Notifications

```javascript
toast({
    message: 'This is a toast message.',
    type: 'success',
    duration: 5000
});
```

### 4.9 Error Handling and Validation

Robust error handling and input validation to ensure data integrity and security. For example, in the `WebBlocRequest` class:

```php
public function rules()
{
    return [
        'content' => 'required|string|max:255',
        'author' => 'required|string|max:100',
        // Add more common validation rules here
    ];
}
```

### 4.10 Performance Optimization

Caching strategies and database indexing to ensure fast response times and efficient data retrieval. For example, adding an index to the `user_id` column:

```php
$table->index('user_id');
```

### 4.11 Security Measures

API rate limiting, input sanitization, and secure storage of sensitive data to protect against common vulnerabilities. For example, using the `ValidateApiKey` middleware:

```php
public function handle($request, Closure $next)
{
    if (!$this->validateApiKey($request->bearerToken())) {
        return response()->json(['error' => 'Invalid API key'], 401);
    }

    return $next($request);
}
```

### 4.12 Example WebBloc Definitions

#### Comments WebBloc

```php
[
    'type' => 'comment',
    'name' => 'Comments System',
    'version' => '1.0.0',
    'attributes' => [
        'limit' => ['type' => 'integer', 'default' => 10],
        'sort' => ['type' => 'string', 'default' => 'newest'],
        'auth_required' => ['type' => 'boolean', 'default' => false],
        'moderation' => ['type' => 'boolean', 'default' => true]
    ],
    'crud' => [
        'create' => true,
        'read' => true,
        'update' => true,
        'delete' => true
    ],
    'metadata' => [
        'description' => 'Dynamic commenting system for static websites',
        'author' => 'WebBloc Team',
        'tags' => ['comments', 'social', 'interaction']
    ]
]
```

#### Reviews WebBloc

```php
[
    'type' => 'review',
    'name' => 'Reviews System',
    'version' => '1.0.0',
    'attributes' => [
        'limit' => ['type' => 'integer', 'default' => 5],
        'sort' => ['type' => 'string', 'default' => 'highest_rating'],
        'auth_required' => ['type' => 'boolean', 'default' => true],
        'moderation' => ['type' => 'boolean', 'default' => true]
    ],
    'crud' => [
        'create' => true,
        'read' => true,
        'update' => true,
        'delete' => true
    ],
    'metadata' => [
        'description' => 'Dynamic review system for static websites',
        'author' => 'WebBloc Team',
        'tags' => ['reviews', 'ratings', 'feedback']
    ]
]
```
