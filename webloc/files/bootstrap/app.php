<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\ValidateApiKey;
use App\Http\Middleware\DynamicSqliteConnection;
use App\Http\Middleware\CorsMiddleware;
use App\Http\Middleware\WebBlocRateLimiter;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global middleware
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Register WebBloc middleware aliases
        $middleware->alias([
            'api.key.validate' => ValidateApiKey::class,
            'api.sqlite.dynamic' => DynamicSqliteConnection::class,
            'api.cors' => CorsMiddleware::class,
            'api.rate.limit' => WebBlocRateLimiter::class,
        ]);

        // Configure throttle limits
        $middleware->throttleApi('api', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->ip());
        });

        $middleware->throttleApi('auth', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by($request->ip());
        });

        $middleware->throttleApi('write', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(30)->by($request->ip());
        });

        // CORS configuration
        // $middleware->append(\Fruitcake\Cors\HandleCors::class);

        // Custom middleware groups
        $middleware->group('webbloc.api', [
            ValidateApiKey::class,
            DynamicSqliteConnection::class,
            CorsMiddleware::class,
            WebBlocRateLimiter::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle API authentication exceptions
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required',
                    'error_code' => 'AUTHENTICATION_REQUIRED'
                ], 401);
            }
        });

        // Handle validation exceptions
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'error_code' => 'VALIDATION_FAILED',
                    'errors' => $e->errors()
                ], 422);
            }
        });

        // Handle model not found exceptions
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found',
                    'error_code' => 'RESOURCE_NOT_FOUND'
                ], 404);
            }
        });

        // Handle method not allowed exceptions
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Method not allowed',
                    'error_code' => 'METHOD_NOT_ALLOWED',
                    'allowed_methods' => $e->getHeaders()['Allow'] ?? []
                ], 405);
            }
        });

        // Handle rate limiting exceptions
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rate limit exceeded',
                    'error_code' => 'RATE_LIMIT_EXCEEDED',
                    'retry_after' => $e->getHeaders()['Retry-After'] ?? 60
                ], 429);
            }
        });

        // Handle database connection exceptions
        $exceptions->render(function (\Illuminate\Database\QueryException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                Log::error('Database error in API', [
                    'error' => $e->getMessage(),
                    'request' => $request->path(),
                    'method' => $request->method()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Database error occurred',
                    'error_code' => 'DATABASE_ERROR'
                ], 500);
            }
        });

        // Handle general exceptions for API routes
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') && !config('app.debug')) {
                Log::error('API Exception', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'request' => $request->path(),
                    'method' => $request->method()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'An unexpected error occurred',
                    'error_code' => 'INTERNAL_SERVER_ERROR'
                ], 500);
            }
        });

        // Log all exceptions for monitoring
        $exceptions->report(function (\Throwable $e) {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });
    })
    ->create();