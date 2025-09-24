<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WebBlocController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Apply global API middleware
Route::middleware([
    'api.key.validate',
    'api.sqlite.dynamic',
    'api.cors',
    'api.rate.limit'
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authentication Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->name('api.auth.')->group(function () {
        // User registration
        Route::post('/register', [AuthController::class, 'register'])
            ->name('register')
            ->middleware('throttle:auth');

        // User login
        Route::post('/login', [AuthController::class, 'login'])
            ->name('login')
            ->middleware('throttle:auth');

        // User logout
        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('logout');

        // User profile
        Route::get('/profile', [AuthController::class, 'profile'])
            ->name('profile');

        // Update user profile
        Route::put('/profile', [AuthController::class, 'updateProfile'])
            ->name('profile.update');

        // Password reset (placeholder for future implementation)
        Route::post('/password/email', function () {
            return response()->json([
                'success' => false,
                'message' => 'Password reset not implemented yet'
            ], 501);
        })->name('password.email');

        Route::post('/password/reset', function () {
            return response()->json([
                'success' => false,
                'message' => 'Password reset not implemented yet'
            ], 501);
        })->name('password.reset');
    });

    /*
    |--------------------------------------------------------------------------
    | WebBloc Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('webblocs')->name('api.webblocs.')->group(function () {
        
        // Get WebBloc type metadata
        Route::get('/{type}/metadata', [WebBlocController::class, 'metadata'])
            ->name('metadata')
            ->where('type', '[a-zA-Z_][a-zA-Z0-9_]*');

        // Render WebBloc as HTML
        Route::get('/{type}/render', [WebBlocController::class, 'render'])
            ->name('render')
            ->where('type', '[a-zA-Z_][a-zA-Z0-9_]*');

        // List WebBlocs of specific type
        Route::get('/{type}', [WebBlocController::class, 'index'])
            ->name('index')
            ->where('type', '[a-zA-Z_][a-zA-Z0-9_]*');

        // Get specific WebBloc
        Route::get('/{type}/{id}', [WebBlocController::class, 'show'])
            ->name('show')
            ->where(['type' => '[a-zA-Z_][a-zA-Z0-9_]*', 'id' => '[0-9]+']);

        // Create new WebBloc
        Route::post('/{type}', [WebBlocController::class, 'store'])
            ->name('store')
            ->middleware('throttle:write')
            ->where('type', '[a-zA-Z_][a-zA-Z0-9_]*');

        // Update WebBloc
        Route::put('/{type}/{id}', [WebBlocController::class, 'update'])
            ->name('update')
            ->middleware('throttle:write')
            ->where(['type' => '[a-zA-Z_][a-zA-Z0-9_]*', 'id' => '[0-9]+']);

        // Delete WebBloc
        Route::delete('/{type}/{id}', [WebBlocController::class, 'destroy'])
            ->name('destroy')
            ->middleware('throttle:write')
            ->where(['type' => '[a-zA-Z_][a-zA-Z0-9_]*', 'id' => '[0-9]+']);
    });

    /*
    |--------------------------------------------------------------------------
    | Utility Routes
    |--------------------------------------------------------------------------
    */
    
    // Health check endpoint
    Route::get('/health', function (Request $request) {
        $website = $request->attributes->get('website');
        
        return response()->json([
            'success' => true,
            'message' => 'API is healthy',
            'data' => [
                'website_id' => $website->id ?? null,
                'timestamp' => now()->toISOString(),
                'version' => config('app.version', '1.0.0')
            ]
        ]);
    })->name('api.health');

    // API information endpoint
    Route::get('/info', function (Request $request) {
        $website = $request->attributes->get('website');
        $apiKey = $request->attributes->get('api_key');
        
        return response()->json([
            'success' => true,
            'data' => [
                'website' => [
                    'id' => $website->id,
                    'name' => $website->name,
                    'domain' => $website->domain,
                ],
                'api_key' => [
                    'id' => $apiKey->id,
                    'name' => $apiKey->name,
                    'permissions' => $apiKey->permissions,
                    'rate_limit' => $apiKey->rate_limit_per_minute,
                ],
                'formats' => [
                    'html' => '75%',
                    'json' => '15%',
                    'other' => '10%'
                ],
                'endpoints' => [
                    'auth' => [
                        'register' => '/api/auth/register',
                        'login' => '/api/auth/login',
                        'logout' => '/api/auth/logout',
                        'profile' => '/api/auth/profile'
                    ],
                    'webblocs' => [
                        'list' => '/api/webblocs/{type}',
                        'show' => '/api/webblocs/{type}/{id}',
                        'create' => '/api/webblocs/{type}',
                        'update' => '/api/webblocs/{type}/{id}',
                        'delete' => '/api/webblocs/{type}/{id}',
                        'render' => '/api/webblocs/{type}/render',
                        'metadata' => '/api/webblocs/{type}/metadata'
                    ]
                ]
            ]
        ]);
    })->name('api.info');
});

/*
|--------------------------------------------------------------------------
| Fallback Routes
|--------------------------------------------------------------------------
*/

// Handle undefined API routes
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found',
        'error_code' => 'ENDPOINT_NOT_FOUND'
    ], 404);
});

// Handle method not allowed
Route::any('{any}', function () {
    return response()->json([
        'success' => false,
        'message' => 'Method not allowed',
        'error_code' => 'METHOD_NOT_ALLOWED'
    ], 405);
})->where('any', '.*');