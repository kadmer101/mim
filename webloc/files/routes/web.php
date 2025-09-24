<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Dashboard\AdminController;
use App\Http\Controllers\Dashboard\WebsiteController;
use App\Http\Controllers\Dashboard\ApiKeyController;
use App\Http\Controllers\Dashboard\WebBlocController;
use App\Http\Controllers\Dashboard\StatisticsController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', function () {
    if (Auth::user()->hasRole('admin')) {
        return redirect()->route('dashboard.admin.index');
    }
    return redirect()->route('dashboard.websites.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Dashboard Routes
Route::middleware(['auth', 'verified'])->prefix('dashboard')->name('dashboard.')->group(function () {
    
    // Admin Dashboard Routes
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/stats', [AdminController::class, 'stats'])->name('stats');
        Route::get('/activities', [AdminController::class, 'activities'])->name('activities');
        Route::post('/clear-cache', [AdminController::class, 'clearCache'])->name('clear-cache');
        Route::post('/optimize-db', [AdminController::class, 'optimizeDatabase'])->name('optimize-db');
        Route::post('/backup', [AdminController::class, 'backupSystem'])->name('backup');
        Route::get('/system-info', [AdminController::class, 'systemInfo'])->name('system-info');
    });

    // Website Management Routes
    Route::resource('websites', WebsiteController::class);
    Route::prefix('websites')->name('websites.')->group(function () {
        Route::get('refresh', [WebsiteController::class, 'refresh'])->name('refresh');
        Route::post('{website}/verify', [WebsiteController::class, 'verify'])->name('verify');
        Route::post('{website}/regenerate-token', [WebsiteController::class, 'regenerateToken'])->name('regenerate-token');
        Route::get('{website}/statistics', [WebsiteController::class, 'statistics'])->name('statistics');
        Route::get('{website}/webblocs', [WebsiteController::class, 'webblocs'])->name('webblocs');
        Route::post('{website}/webblocs/{webbloc}/install', [WebsiteController::class, 'installWebBloc'])->name('install-webbloc');
        Route::delete('{website}/webblocs/{webbloc}/uninstall', [WebsiteController::class, 'uninstallWebBloc'])->name('uninstall-webbloc');
        Route::get('{website}/integration-code', [WebsiteController::class, 'integrationCode'])->name('integration-code');
    });

    // API Key Management Routes
    Route::resource('api-keys', ApiKeyController::class);
    Route::prefix('api-keys')->name('api-keys.')->group(function () {
        Route::get('refresh', [ApiKeyController::class, 'refresh'])->name('refresh');
        Route::post('{apiKey}/regenerate', [ApiKeyController::class, 'regenerate'])->name('regenerate');
        Route::post('{apiKey}/suspend', [ApiKeyController::class, 'suspend'])->name('suspend');
        Route::post('{apiKey}/activate', [ApiKeyController::class, 'activate'])->name('activate');
        Route::get('{apiKey}/usage', [ApiKeyController::class, 'usage'])->name('usage');
        Route::get('{apiKey}/logs', [ApiKeyController::class, 'logs'])->name('logs');
        Route::post('{apiKey}/test', [ApiKeyController::class, 'test'])->name('test');
    });

    // WebBloc Management Routes (Admin Only)
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('webblocs', WebBlocController::class);
        Route::prefix('webblocs')->name('webblocs.')->group(function () {
            Route::get('refresh', [WebBlocController::class, 'refresh'])->name('refresh');
            Route::post('{webbloc}/duplicate', [WebBlocController::class, 'duplicate'])->name('duplicate');
            Route::get('{webbloc}/export', [WebBlocController::class, 'export'])->name('export');
            Route::post('import', [WebBlocController::class, 'import'])->name('import');
            Route::post('{webbloc}/install-all', [WebBlocController::class, 'installToAllWebsites'])->name('install-all');
            Route::post('{webbloc}/build', [WebBlocController::class, 'buildComponent'])->name('build');
            Route::get('{webbloc}/preview', [WebBlocController::class, 'preview'])->name('preview');
            Route::post('{webbloc}/test', [WebBlocController::class, 'test'])->name('test');
        });
    });

    // Statistics and Analytics Routes
    Route::prefix('statistics')->name('statistics.')->group(function () {
        Route::get('/', [StatisticsController::class, 'index'])->name('index');
        Route::get('/website/{website?}', [StatisticsController::class, 'website'])->name('website');
        Route::get('/realtime', [StatisticsController::class, 'realtime'])->name('realtime');
        Route::get('/export', [StatisticsController::class, 'export'])->name('export');
        Route::get('/api-usage', [StatisticsController::class, 'apiUsage'])->name('api-usage');
        Route::get('/webbloc-performance', [StatisticsController::class, 'webBlocPerformance'])->name('webbloc-performance');
        Route::get('/error-analysis', [StatisticsController::class, 'errorAnalysis'])->name('error-analysis');
        Route::get('/geographic', [StatisticsController::class, 'geographic'])->name('geographic');
    });

    // CDN and Assets Routes
    Route::prefix('cdn')->name('cdn.')->group(function () {
        Route::post('/build', [WebBlocController::class, 'buildCdn'])->name('build');
        Route::get('/status', [WebBlocController::class, 'cdnStatus'])->name('status');
        Route::post('/purge', [WebBlocController::class, 'purgeCdn'])->name('purge');
    });

    // System Management Routes (Admin Only)
    Route::middleware(['role:admin'])->prefix('system')->name('system.')->group(function () {
        Route::get('/health', [AdminController::class, 'systemHealth'])->name('health');
        Route::get('/logs', [AdminController::class, 'viewLogs'])->name('logs');
        Route::post('/maintenance-mode', [AdminController::class, 'toggleMaintenanceMode'])->name('maintenance-mode');
        Route::get('/database-status', [AdminController::class, 'databaseStatus'])->name('database-status');
        Route::post('/run-migrations', [AdminController::class, 'runMigrations'])->name('run-migrations');
        Route::get('/queue-status', [AdminController::class, 'queueStatus'])->name('queue-status');
        Route::post('/restart-workers', [AdminController::class, 'restartWorkers'])->name('restart-workers');
    });

    // Documentation and Help Routes
    Route::prefix('help')->name('help.')->group(function () {
        Route::get('/', function () {
            return view('dashboard.help.index');
        })->name('index');
        Route::get('/api-documentation', function () {
            return view('dashboard.help.api-documentation');
        })->name('api-documentation');
        Route::get('/integration-guide', function () {
            return view('dashboard.help.integration-guide');
        })->name('integration-guide');
        Route::get('/webbloc-reference', function () {
            return view('dashboard.help.webbloc-reference');
        })->name('webbloc-reference');
        Route::get('/troubleshooting', function () {
            return view('dashboard.help.troubleshooting');
        })->name('troubleshooting');
        Route::get('/faq', function () {
            return view('dashboard.help.faq');
        })->name('faq');
    });
});

// Public WebBloc demonstration routes
Route::prefix('demo')->name('demo.')->group(function () {
    Route::get('/', function () {
        return view('demo.index');
    })->name('index');
    
    Route::get('/auth', function () {
        return view('demo.auth');
    })->name('auth');
    
    Route::get('/comments', function () {
        return view('demo.comments');
    })->name('comments');
    
    Route::get('/reviews', function () {
        return view('demo.reviews');
    })->name('reviews');
    
    Route::get('/notifications', function () {
        return view('demo.notifications');
    })->name('notifications');
});

// CDN Routes (public access for WebBloc assets)
Route::prefix('cdn')->name('cdn.')->group(function () {
    Route::get('/webbloc.min.js', function () {
        return response()->file(public_path('cdn/webbloc.min.js'), [
            'Content-Type' => 'application/javascript',
            'Cache-Control' => 'public, max-age=31536000',
            'Access-Control-Allow-Origin' => '*',
        ]);
    })->name('js');
    
    Route::get('/webbloc.min.css', function () {
        return response()->file(public_path('cdn/webbloc.min.css'), [
            'Content-Type' => 'text/css',
            'Cache-Control' => 'public, max-age=31536000',
            'Access-Control-Allow-Origin' => '*']);
    })->name('css');
    
    Route::get('/webbloc-components/{component}.js', function ($component) {
        $path = public_path("cdn/components/{$component}.js");
        if (!file_exists($path)) {
            abort(404);
        }
        return response()->file($path, [
            'Content-Type' => 'application/javascript',
            'Cache-Control' => 'public, max-age=31536000',
            'Access-Control-Allow-Origin' => '*'
        ]);
    })->name('component-js');
    
    Route::get('/webbloc-components/{component}.css', function ($component) {
        $path = public_path("cdn/components/{$component}.css");
        if (!file_exists($path)) {
            abort(404);
        }
        return response()->file($path, [
            'Content-Type' => 'text/css',
            'Cache-Control' => 'public, max-age=31536000',
            'Access-Control-Allow-Origin' => '*'
        ]);
    })->name('component-css');
});

// Installation and Setup Routes (for automated deployment)
Route::prefix('setup')->middleware('guest')->name('setup.')->group(function () {
    Route::get('/', function () {
        if (Schema::hasTable('users')) {
            return redirect('/');
        }
        return view('setup.index');
    })->name('index');
    
    Route::post('/database', function () {
        // Database setup logic
        return response()->json(['success' => true]);
    })->name('database');
    
    Route::post('/admin', function () {
        // Admin user creation logic
        return response()->json(['success' => true]);
    })->name('admin');
    
    Route::post('/complete', function () {
        // Setup completion logic
        return response()->json(['success' => true]);
    })->name('complete');
});

// Webhook Routes (for external integrations)
Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::post('/github', function () {
        // GitHub webhook for automated deployments
        return response()->json(['received' => true]);
    })->name('github');
    
    Route::post('/payment/{provider}', function ($provider) {
        // Payment webhook handlers
        return response()->json(['received' => true]);
    })->name('payment');
});

// Health Check Route (for monitoring)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'version' => config('app.version', '1.0.0'),
        'environment' => app()->environment(),
        'services' => [
            'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
            'cache' => Cache::store()->getStore() ? 'connected' : 'disconnected',
            'queue' => 'unknown' // Could be enhanced with actual queue health check
        ]
    ]);
})->name('health');

// Fallback route for SPA behavior (if needed)
Route::fallback(function () {
    return view('errors.404');
});

require __DIR__.'/auth.php';