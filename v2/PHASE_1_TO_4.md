## File: bootstrap/app.php
### Purpose: Laravel 12+ application bootstrapper with service provider registration and middleware configuration
### Dependencies: Laravel 12.x framework, custom service providers
### Key Features: Application initialization, middleware groups, exception handling, console commands

```php
<?php

use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\EnsureUserRole;
use App\Http\Middleware\LocalizationMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global middleware stack
        $middleware->use([
            \Illuminate\Http\Middleware\TrustHosts::class,
            \Illuminate\Http\Middleware\TrustProxies::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ]);

        // Web middleware group
        $middleware->web(append: [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            LocalizationMiddleware::class,
        ]);

        // API middleware group
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ], append: [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Route middleware aliases
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            'signed' => \App\Http\Middleware\ValidateSignature::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            
            // Custom MIM platform middleware
            'subscription' => CheckSubscription::class,
            'role' => EnsureUserRole::class,
            'localize' => LocalizationMiddleware::class,
        ]);

        // Additional security headers for production
        if (app()->environment('production')) {
            $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        }

        // Rate limiting groups
        $middleware->throttle('challenges', function (Request $request) {
            return $request->user()
                ? Limit::perDay(3)->by($request->user()->id)
                : Limit::perDay(1)->by($request->ip());
        });

        $middleware->throttle('votes', function (Request $request) {
            return $request->user()
                ? Limit::perDay(50)->by($request->user()->id)
                : Limit::none();
        });

        $middleware->throttle('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(60)->by($request->user()->id)
                : Limit::perMinute(10)->by($request->ip());
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Payment-specific exception handling
        $exceptions->render(function (PaymentException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Payment processing failed',
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                ], 422);
            }

            return redirect()->back()
                ->withErrors(['payment' => $e->getMessage()])
                ->withInput();
        });

        // Binance API exception handling
        $exceptions->render(function (BinanceApiException $e, Request $request) {
            \Log::error('Binance API Error', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'user_id' => $request->user()?->id,
                'request_data' => $request->except(['password', 'password_confirmation']),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Payment service temporarily unavailable',
                    'message' => 'Please try again later or contact support',
                ], 503);
            }

            return redirect()->back()
                ->withErrors(['payment' => 'Payment service temporarily unavailable. Please try again later.'])
                ->withInput();
        });

        // Security logging for unauthorized access
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            \Log::warning('Unauthorized access attempt', [
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
                'route' => $request->route()?->getName(),
                'message' => $e->getMessage(),
            ]);

            return response()->view('errors.403', [], 403);
        });

        // Global error logging with context
        $exceptions->reportable(function (Throwable $e) {
            if ($e instanceof \Exception) {
                \Log::error('Application Error', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        });
    })
    ->create();
```

## File: bootstrap/providers.php
### Purpose: Service provider registration order and configuration
### Dependencies: Laravel service providers, custom application providers
### Key Features: Ordered provider loading, environment-specific providers

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Laravel Framework Service Providers
    |--------------------------------------------------------------------------
    |
    | Core Laravel framework service providers loaded in optimal order
    | for the MIM platform's requirements.
    |
    */
    
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
    
    /*
    |--------------------------------------------------------------------------
    | Custom MIM Platform Service Providers
    |--------------------------------------------------------------------------
    |
    | Platform-specific service providers for payments, gamification,
    | and other business logic components.
    |
    */
    
    App\Providers\PaymentServiceProvider::class,
    
    /*
    |--------------------------------------------------------------------------
    | Third-Party Service Providers
    |--------------------------------------------------------------------------
    |
    | External package service providers required for platform functionality.
    | These are loaded after core providers to ensure proper dependency resolution.
    |
    */
    
    // Spatie package providers (automatically discovered)
    // Laravel package providers (automatically discovered)
    // Binance connector providers (automatically discovered)
];
```

## File: .env.production
### Purpose: Production environment configuration template
### Dependencies: None (environment file)
### Key Features: Production-optimized settings, security configurations

```env
# MIM Platform - Production Environment Configuration
# Copy this file to .env and configure with your production values

APP_NAME="Mim Platform"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_TIMEZONE=UTC

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mim_platform
DB_USERNAME=
DB_PASSWORD=

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CLIENT=phpredis

# Cache Configuration
CACHE_DRIVER=redis
CACHE_PREFIX=mim_prod

# Session Configuration
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict

# Queue Configuration
QUEUE_CONNECTION=database
QUEUE_FAILED_DRIVER=database

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# Binance API Configuration
BINANCE_API_KEY=
BINANCE_SECRET_KEY=
BINANCE_TESTNET=false
BINANCE_BASE_URL=https://api.binance.com
BINANCE_WEBHOOK_SECRET=

# Platform Configuration
MIM_SUBSCRIPTION_PRICE=99
MIM_REWARD_AMOUNT=999
MIM_GRACE_PERIOD_DAYS=7
MIM_ADMIN_EMAIL=admin@your-domain.com
MIM_MAINTENANCE_MODE=false
MIM_CHALLENGE_LIMIT_DAILY=3
MIM_VOTE_LIMIT_DAILY=50

# Security Configuration
BCRYPT_ROUNDS=12
HASH_VERIFY=true

# Pusher Configuration (Real-time notifications)
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=

# Telescope Configuration (Disable in production)
TELESCOPE_ENABLED=false

# File Storage Configuration
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

# Logging Configuration
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# Trusted Proxies (if behind load balancer)
TRUSTED_PROXIES="*"
TRUSTED_HOSTS=

# 2FA Configuration
GOOGLE_2FA_ENABLED=true
GOOGLE_2FA_COMPANY="${APP_NAME}"

# Scout Search Configuration
SCOUT_DRIVER=algolia
ALGOLIA_APP_ID=
ALGOLIA_SECRET=

# Activity Log Configuration
ACTIVITY_LOGGER_ENABLED=true
ACTIVITY_LOGGER_DELETE_RECORDS_OLDER_THAN_DAYS=365

# Rate Limiting
RATE_LIMITER_STORE=redis

# Backup Configuration
BACKUP_ARCHIVE_PASSWORD=
BACKUP_DESTINATION_DISK=s3
```

## File: .env.staging
### Purpose: Staging environment configuration template
### Dependencies: None (environment file)
### Key Features: Staging-specific settings, debug enabled, testnet usage

```env
# MIM Platform - Staging Environment Configuration
# Copy this file to .env for staging deployment and testing

APP_NAME="Mim Platform (Staging)"
APP_ENV=staging
APP_KEY=
APP_DEBUG=true
APP_URL=https://staging.your-domain.com
APP_TIMEZONE=UTC

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mim_platform_staging
DB_USERNAME=
DB_PASSWORD=

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CLIENT=phpredis

# Cache Configuration
CACHE_DRIVER=redis
CACHE_PREFIX=mim_staging

# Session Configuration
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# Queue Configuration
QUEUE_CONNECTION=database
QUEUE_FAILED_DRIVER=database

# Mail Configuration (Use Mailtrap or similar for testing)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=staging@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# Binance API Configuration (TESTNET ENABLED)
BINANCE_API_KEY=
BINANCE_SECRET_KEY=
BINANCE_TESTNET=true
BINANCE_BASE_URL=https://testnet.binance.vision
BINANCE_WEBHOOK_SECRET=staging_webhook_secret

# Platform Configuration
MIM_SUBSCRIPTION_PRICE=1
MIM_REWARD_AMOUNT=10
MIM_GRACE_PERIOD_DAYS=1
MIM_ADMIN_EMAIL=admin@staging.your-domain.com
MIM_MAINTENANCE_MODE=false
MIM_CHALLENGE_LIMIT_DAILY=10
MIM_VOTE_LIMIT_DAILY=100

# Security Configuration
BCRYPT_ROUNDS=10
HASH_VERIFY=true

# Pusher Configuration
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

# Telescope Configuration (ENABLED for debugging)
TELESCOPE_ENABLED=true
TELESCOPE_DOMAIN=staging.your-domain.com
TELESCOPE_PATH=telescope

# File Storage Configuration
FILESYSTEM_DISK=local
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=mim-staging
AWS_USE_PATH_STYLE_ENDPOINT=false

# Logging Configuration
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# Debugbar Configuration (ENABLED for staging)
DEBUGBAR_ENABLED=true

# 2FA Configuration
GOOGLE_2FA_ENABLED=true
GOOGLE_2FA_COMPANY="${APP_NAME}"

# Scout Search Configuration
SCOUT_DRIVER=collection
ALGOLIA_APP_ID=
ALGOLIA_SECRET=

# Activity Log Configuration
ACTIVITY_LOGGER_ENABLED=true
ACTIVITY_LOGGER_DELETE_RECORDS_OLDER_THAN_DAYS=30

# Rate Limiting (More permissive for testing)
RATE_LIMITER_STORE=redis

# Backup Configuration
BACKUP_ARCHIVE_PASSWORD=staging_backup_password
BACKUP_DESTINATION_DISK=local
```

## File: config/mim.php
### Purpose: Custom MIM platform configuration settings
### Dependencies: Laravel config system
### Key Features: Subscription pricing, reward amounts, feature toggles, business rules

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MIM Platform Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration values specific to the MIM platform
    | including subscription pricing, reward amounts, feature flags, and
    | business logic parameters.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Subscription Configuration
    |--------------------------------------------------------------------------
    |
    | Monthly subscription pricing and billing configuration
    |
    */
    'subscription' => [
        'price' => env('MIM_SUBSCRIPTION_PRICE', 99),
        'currency' => 'USDT',
        'grace_period_days' => env('MIM_GRACE_PERIOD_DAYS', 7),
        'auto_renewal' => true,
        'trial_days' => 0, // No free trial
        'billing_cycle' => 'monthly',
        'proration' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Reward Configuration
    |--------------------------------------------------------------------------
    |
    | Challenge reward amounts and payout configuration
    |
    */
    'rewards' => [
        'valid_challenge_amount' => env('MIM_REWARD_AMOUNT', 999),
        'currency' => 'USDT',
        'escrow_period_hours' => 24,
        'max_pending_claims' => 3,
        'approval_required' => true,
        'minimum_payout' => 1,
        'payout_batch_size' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Platform Limits & Rules
    |--------------------------------------------------------------------------
    |
    | User interaction limits and platform business rules
    |
    */
    'limits' => [
        'challenges_per_day' => env('MIM_CHALLENGE_LIMIT_DAILY', 3),
        'responses_per_hour' => 20,
        'votes_per_day' => env('MIM_VOTE_LIMIT_DAILY', 50),
        'search_queries_per_minute' => 30,
        'max_evidence_files' => 5,
        'max_file_size_mb' => 5,
        'max_challenge_length' => 10000,
        'min_challenge_length' => 50,
        'max_response_length' => 5000,
        'min_response_length' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | Challenge Lifecycle Configuration
    |--------------------------------------------------------------------------
    |
    | Timing and workflow configuration for challenge processing
    |
    */
    'challenge_lifecycle' => [
        'open_debate_days' => 7,
        'expert_review_days' => 14,
        'final_resolution_hours' => 48,
        'auto_close_inactive_days' => 30,
        'expert_assignment_timeout_hours' => 72,
        'dispute_period_days' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Toggle various platform features on/off
    |
    */
    'features' => [
        'challenge_submission' => env('MIM_FEATURE_CHALLENGES', true),
        'voting_system' => env('MIM_FEATURE_VOTING', true),
        'reputation_system' => env('MIM_FEATURE_REPUTATION', true),
        'badge_system' => env('MIM_FEATURE_BADGES', true),
        'leaderboards' => env('MIM_FEATURE_LEADERBOARDS', true),
        'real_time_notifications' => env('MIM_FEATURE_REALTIME', true),
        'file_uploads' => env('MIM_FEATURE_UPLOADS', true),
        'search_functionality' => env('MIM_FEATURE_SEARCH', true),
        'social_sharing' => env('MIM_FEATURE_SHARING', true),
        'email_notifications' => env('MIM_FEATURE_EMAIL_NOTIFICATIONS', true),
        'two_factor_auth' => env('GOOGLE_2FA_ENABLED', true),
        'maintenance_mode' => env('MIM_MAINTENANCE_MODE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Moderation
    |--------------------------------------------------------------------------
    |
    | Content filtering and moderation settings
    |
    */
    'moderation' => [
        'auto_moderate' => true,
        'require_approval' => [
            'first_challenge' => true,
            'new_user_responses' => false,
            'flagged_content' => true,
        ],
        'spam_detection' => true,
        'duplicate_detection' => true,
        'profanity_filter' => true,
        'min_reputation_for_voting' => 10,
        'min_reputation_for_responses' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Administration
    |--------------------------------------------------------------------------
    |
    | Administrative settings and contact information
    |
    */
    'admin' => [
        'email' => env('MIM_ADMIN_EMAIL', 'admin@mim-platform.com'),
        'support_email' => env('MIM_SUPPORT_EMAIL', 'support@mim-platform.com'),
        'notification_emails' => [
            'critical_errors' => env('MIM_ADMIN_EMAIL', 'admin@mim-platform.com'),
            'payment_issues' => env('MIM_ADMIN_EMAIL', 'admin@mim-platform.com'),
            'reward_claims' => env('MIM_ADMIN_EMAIL', 'admin@mim-platform.com'),
        ],
        'auto_assign_experts' => true,
        'expert_workload_limit' => 10,
        'backup_frequency_hours' => 24,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Platform security configuration
    |
    */
    'security' => [
        'password_min_length' => 8,
        'password_require_mixed_case' => true,
        'password_require_numbers' => true,
        'password_require_symbols' => true,
        'session_timeout_minutes' => 120,
        'max_login_attempts' => 5,
        'lockout_duration_minutes' => 15,
        'require_email_verification' => true,
        'force_https' => env('APP_ENV') === 'production',
        'csrf_protection' => true,
        'audit_user_actions' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Internationalization
    |--------------------------------------------------------------------------
    |
    | Multi-language support configuration
    |
    */
    'localization' => [
        'default_locale' => 'en',
        'available_locales' => ['en', 'ar', 'fr'],
        'rtl_locales' => ['ar'],
        'fallback_locale' => 'en',
        'auto_detect_locale' => true,
        'locale_session_key' => 'mim_locale',
        'locale_cookie_name' => 'mim_locale',
        'locale_cookie_lifetime' => 365 * 24 * 60, // 1 year in minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | Caching and performance optimization settings
    |
    */
    'performance' => [
        'cache_challenges' => true,
        'cache_leaderboards' => true,
        'cache_statistics' => true,
        'cache_ttl_minutes' => 60,
        'enable_query_cache' => true,
        'compress_responses' => true,
        'lazy_load_images' => true,
        'cdn_enabled' => env('CDN_ENABLED', false),
        'cdn_url' => env('CDN_URL', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Public API settings and rate limiting
    |
    */
    'api' => [
        'enabled' => true,
        'version' => 'v1',
        'rate_limit_per_minute' => 60,
        'rate_limit_per_hour' => 1000,
        'require_authentication' => false, // For public endpoints
        'pagination_per_page' => 20,
        'max_pagination_per_page' => 100,
        'response_cache_minutes' => 5,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Analytics & Reporting
    |--------------------------------------------------------------------------
    |
    | Platform analytics and reporting configuration
    |
    */
    'analytics' => [
        'enabled' => true,
        'track_user_activity' => true,
        'track_page_views' => true,
        'track_api_usage' => true,
        'retention_days' => 365,
        'anonymize_ip' => true,
        'google_analytics_id' => env('GOOGLE_ANALYTICS_ID', ''),
        'daily_reports' => true,
        'weekly_reports' => true,
        'monthly_reports' => true,
    ],
];
```

## File: config/binance.php
### Purpose: Binance API configuration for cryptocurrency payments
### Dependencies: Binance connector package, Laravel config system
### Key Features: API credentials, testnet toggle, webhook configuration, rate limiting

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Binance API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Binance API integration including credentials,
    | endpoints, and webhook settings for cryptocurrency payments.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | API Credentials
    |--------------------------------------------------------------------------
    |
    | Binance API key and secret for authentication. These should be
    | set in your environment file and never committed to version control.
    |
    */
    'api_key' => env('BINANCE_API_KEY'),
    'secret_key' => env('BINANCE_SECRET_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Environment Configuration
    |--------------------------------------------------------------------------
    |
    | Toggle between testnet and production environments
    |
    */
    'testnet' => env('BINANCE_TESTNET', false),
    
    /*
    |--------------------------------------------------------------------------
    | API Endpoints
    |--------------------------------------------------------------------------
    |
    | Binance API base URLs for different environments
    |
    */
    'endpoints' => [
        'production' => [
            'api' => 'https://api.binance.com',
            'ws' => 'wss://stream.binance.com:9443',
            'futures' => 'https://fapi.binance.com',
        ],
        'testnet' => [
            'api' => 'https://testnet.binance.vision',
            'ws' => 'wss://teststream.binance.vision',
            'futures' => 'https://testnet.binancefuture.com',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | Primary API base URL (automatically selected based on testnet setting)
    |
    */
    'base_url' => env('BINANCE_BASE_URL', function () {
        return env('BINANCE_TESTNET', false) 
            ? 'https://testnet.binance.vision' 
            : 'https://api.binance.com';
    }),

    /*
    |--------------------------------------------------------------------------
    | Supported Currencies
    |--------------------------------------------------------------------------
    |
    | Cryptocurrencies supported by the MIM platform
    |
    */
    'supported_currencies' => [
        'USDT' => [
            'name' => 'Tether USD',
            'symbol' => 'USDT',
            'decimals' => 6,
            'min_amount' => 1,
            'max_amount' => 10000,
            'networks' => ['BSC', 'ETH', 'TRX'],
            'preferred_network' => 'BSC', // Lower fees
        ],
        'BUSD' => [
            'name' => 'Binance USD',
            'symbol' => 'BUSD',
            'decimals' => 8,
            'min_amount' => 1,
            'max_amount' => 10000,
            'networks' => ['BSC', 'ETH'],
            'preferred_network' => 'BSC',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | Primary currency for MIM platform transactions
    |
    */
    'default_currency' => 'USDT',
    'default_network' => 'BSC',

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Webhook settings for receiving payment notifications from Binance
    |
    */
    'webhook' => [
        'secret' => env('BINANCE_WEBHOOK_SECRET'),
        'url' => env('APP_URL') . '/api/webhooks/binance',
        'enabled' => env('BINANCE_WEBHOOK_ENABLED', true),
        'verify_signature' => true,
        'timeout_seconds' => 30,
        'max_retries' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | API rate limiting configuration to stay within Binance limits
    |
    */
    'rate_limits' => [
        'requests_per_minute' => 1200,
        'requests_per_second' => 20,
        'weight_per_minute' => 1200,
        'orders_per_second' => 10,
        'orders_per_day' => 200000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Configuration
    |--------------------------------------------------------------------------
    |
    | HTTP client configuration for API requests
    |
    */
    'http' => [
        'timeout' => 30,
        'connect_timeout' => 10,
        'retry_attempts' => 3,
        'retry_delay_ms' => 500,
        'verify_ssl' => true,
        'user_agent' => 'MIM-Platform/1.0',
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Security-related configuration for API interactions
    |
    */
    'security' => [
        'recv_window' => 5000, // 5 seconds
        'timestamp_offset' => 0,
        'validate_signatures' => true,
        'log_requests' => env('APP_DEBUG', false),
        'log_responses' => env('APP_DEBUG', false),
        'mask_sensitive_data' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Processing
    |--------------------------------------------------------------------------
    |
    | Configuration for processing payments and transactions
    |
    */
    'payments' => [
        'subscription_fee' => [
            'amount' => 99,
            'currency' => 'USDT',
            'description' => 'MIM Platform Monthly Subscription',
        ],
        'reward_payout' => [
            'amount' => 999,
            'currency' => 'USDT',
            'description' => 'Challenge Reward Payout',
        ],
        'minimum_balance' => 1000, // Platform wallet minimum balance
        'auto_withdrawal_threshold' => 50000, // Auto-withdraw to cold storage
        'confirmation_blocks' => [
            'BSC' => 15,
            'ETH' => 12,
            'TRX' => 19,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Wallet Configuration
    |--------------------------------------------------------------------------
    |
    | Platform wallet and address management
    |
    */
    'wallet' => [
        'hot_wallet_address' => env('BINANCE_HOT_WALLET_ADDRESS'),
        'cold_wallet_address' => env('BINANCE_COLD_WALLET_ADDRESS'),
        'fee_wallet_address' => env('BINANCE_FEE_WALLET_ADDRESS'),
        'auto_generate_addresses' => true,
        'address_pool_size' => 100,
        'reuse_addresses' => false,
        'derivation_path' => "m/44'/60'/0'/0", // Ethereum derivation path
    ],

    /*
    |--------------------------------------------------------------------------
    | Transaction Monitoring
    |--------------------------------------------------------------------------
    |
    | Settings for monitoring and reconciling transactions
    |
    */
    'monitoring' => [
        'poll_interval_seconds' => 30,
        'max_poll_attempts' => 720, // 6 hours
        'reconciliation_schedule' => '0 */6 * * *', // Every 6 hours
        'alert_thresholds' => [
            'failed_transactions_per_hour' => 10,
            'pending_transactions_hours' => 2,
            'wallet_balance_low' => 500,
            'api_error_rate_percent' => 5,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fees Configuration
    |--------------------------------------------------------------------------
    |
    | Fee structure for different types of transactions
    |
    */
    'fees' => [
        'deposit_fee_percent' => 0,
        'withdrawal_fee_percent' => 0.1,
        'network_fees' => [
            'BSC' => 0.5,  // USDT
            'ETH' => 2.0,  // USDT
            'TRX' => 1.0,  // USDT
        ],
        'platform_fee_percent' => 2.5, // Platform fee on rewards
        'gas_price_gwei' => [
            'slow' => 20,
            'standard' => 25,
            'fast' => 35,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Handling
    |--------------------------------------------------------------------------
    |
    | Configuration for handling API errors and failures
    |
    */
    'error_handling' => [
        'retry_on_errors' => [
            'network_timeout',
            'rate_limit_exceeded',
            'server_error',
            'service_unavailable',
        ],
        'fail_on_errors' => [
            'invalid_signature',
            'insufficient_balance',
            'invalid_address',
            'suspended_account',
        ],
        'log_all_errors' => true,
        'notify_admins_on_critical' => true,
        'max_consecutive_failures' => 5,
        'circuit_breaker_timeout_minutes' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Development & Testing
    |--------------------------------------------------------------------------
    |
    | Settings for development and testing environments
    |
    */
    'development' => [
        'mock_api_responses' => env('BINANCE_MOCK_RESPONSES', false),
        'simulate_delays' => env('BINANCE_SIMULATE_DELAYS', false),
        'test_private_key' => env('BINANCE_TEST_PRIVATE_KEY'),
        'test_wallet_address' => env('BINANCE_TEST_WALLET_ADDRESS'),
        'fake_transaction_hashes' => env('BINANCE_FAKE_TX_HASHES', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compliance & Reporting
    |--------------------------------------------------------------------------
    |
    | Settings for regulatory compliance and reporting
    |
    */
    'compliance' => [
        'kyc_required_above' => 1000, // USDT
        'daily_withdrawal_limit' => 10000, // USDT
        'monthly_withdrawal_limit' => 100000, // USDT
        'suspicious_activity_threshold' => 50000, // USDT
        'report_large_transactions' => true,
        'transaction_monitoring' => true,
        'aml_screening' => true,
    ],
];
```

## File: config/reputation.php
### Purpose: Gamification and reputation system configuration
### Dependencies: Laravel config system
### Key Features: Point values, badge requirements, level thresholds, achievement rules

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Reputation System Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the MIM platform's gamification and reputation system
    | including point values, badge requirements, and achievement rules.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Point Values
    |--------------------------------------------------------------------------
    |
    | Point awards for different user actions on the platform
    |
    */
    'points' => [
        // Challenge-related actions
        'challenge_submitted' => 100,
        'challenge_approved' => 50,
        'challenge_resolved_valid' => 500, // Bonus for valid mistakes
        'challenge_resolved_refuted' => 25, // Participation points
        'challenge_vote_received_up' => 10,
        'challenge_vote_received_down' => -5,
        
        // Response-related actions
        'response_posted' => 25,
        'response_helpful_voted' => 15,
        'response_expert_bonus' => 50, // Additional points for expert responses
        'response_vote_received_up' => 5,
        'response_vote_received_down' => -2,
        
        // Voting and engagement
        'vote_cast' => 2,
        'daily_login' => 5,
        'profile_completed' => 25,
        'email_verified' => 15,
        'first_challenge' => 75,
        'first_response' => 30,
        
        // Streak bonuses
        'daily_streak_bonus' => [
            7 => 50,   // 1 week
            30 => 200, // 1 month
            90 => 500, // 3 months
            365 => 2000, // 1 year
        ],
        
        // Penalties
        'challenge_rejected_spam' => -100,
        'response_flagged_inappropriate' => -50,
        'violation_warning' => -25,
        'violation_temporary_ban' => -200,
    ],

    /*
    |--------------------------------------------------------------------------
    | Reputation Levels
    |--------------------------------------------------------------------------
    |
    | User reputation level thresholds and associated benefits
    |
    */
    'levels' => [
        'beginner' => [
            'min_points' => 0,
            'max_points' => 99,
            'title' => 'Seeker',
            'color' => '#6B7280', // Gray
            'benefits' => [
                'can_vote' => true,
                'can_submit_challenges' => true,
                'challenges_per_day' => 1,
                'responses_per_hour' => 5,
            ],
        ],
        'intermediate' => [
            'min_points' => 100,
            'max_points' => 499,
            'title' => 'Questioner',
            'color' => '#10B981', // Green
            'benefits' => [
                'can_vote' => true,
                'can_submit_challenges' => true,
                'challenges_per_day' => 2,
                'responses_per_hour' => 10,
                'priority_support' => false,
            ],
        ],
        'advanced' => [
            'min_points' => 500,
            'max_points' => 1999,
            'title' => 'Scholar',
            'color' => '#3B82F6', // Blue
            'benefits' => [
                'can_vote' => true,
                'can_submit_challenges' => true,
                'challenges_per_day' => 3,
                'responses_per_hour' => 15,
                'priority_support' => true,
                'can_flag_content' => true,
            ],
        ],
        'expert' => [
            'min_points' => 2000,
            'max_points' => 4999,
            'title' => 'Expert',
            'color' => '#8B5CF6', // Purple
            'benefits' => [
                'can_vote' => true,
                'can_submit_challenges' => true,
                'challenges_per_day' => 5,
                'responses_per_hour' => 20,
                'priority_support' => true,
                'can_flag_content' => true,
                'can_moderate' => true,
                'expert_badge' => true,
            ],
        ],
        'legend' => [
            'min_points' => 5000,
            'max_points' => PHP_INT_MAX,
            'title' => 'Legend',
            'color' => '#F59E0B', // Yellow/Gold
            'benefits' => [
                'can_vote' => true,
                'can_submit_challenges' => true,
                'challenges_per_day' => 10,
                'responses_per_hour' => 30,
                'priority_support' => true,
                'can_flag_content' => true,
                'can_moderate' => true,
                'expert_badge' => true,
                'legend_badge' => true,
                'unlimited_access' => true,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Achievement Badges
    |--------------------------------------------------------------------------
    |
    | Configuration for achievement badges and their requirements
    |
    */
    'badges' => [
        // Milestone badges
        'first_challenge' => [
            'name' => 'First Challenge',
            'description' => 'Submit your first challenge',
            'icon' => 'trophy',
            'color' => '#10B981',
            'requirements' => [
                'challenges_submitted' => 1,
            ],
            'reward_points' => 50,
            'rarity' => 'common',
        ],
        
        'active_debater' => [
            'name' => 'Active Debater',
            'description' => 'Participate in 10 challenge discussions',
            'icon' => 'chat-bubble-left-right',
            'color' => '#3B82F6',
            'requirements' => [
                'responses_posted' => 10,
            ],
            'reward_points' => 100,
            'rarity' => 'common',
        ],
        
        'popular_contributor' => [
            'name' => 'Popular Contributor',
            'description' => 'Receive 100 total upvotes',
            'icon' => 'heart',
            'color' => '#EF4444',
            'requirements' => [
                'upvotes_received' => 100,
            ],
            'reward_points' => 200,
            'rarity' => 'uncommon',
        ],
        
        'top_challenger' => [
            'name' => 'Top Challenger',
            'description' => 'Rank in top 10 on the leaderboard',
            'icon' => 'star',
            'color' => '#F59E0B',
            'requirements' => [
                'leaderboard_rank' => 10,
            ],
            'reward_points' => 300,
            'rarity' => 'rare',
        ],
        
        'streak_master' => [
            'name' => 'Streak Master',
            'description' => 'Maintain a 30-day activity streak',
            'icon' => 'fire',
            'color' => '#F97316',
            'requirements' => [
                'daily_streak' => 30,
            ],
            'reward_points' => 500,
            'rarity' => 'rare',
        ],
        
        'reputation_legend' => [
            'name' => 'Reputation Legend',
            'description' => 'Achieve 5000+ reputation points',
            'icon' => 'academic-cap',
            'color' => '#8B5CF6',
            'requirements' => [
                'reputation_points' => 5000,
            ],
            'reward_points' => 1000,
            'rarity' => 'legendary',
        ],
        
        'mistake_finder' => [
            'name' => 'Mistake Finder',
            'description' => 'Successfully identify a valid Islamic mistake',
            'icon' => 'magnifying-glass',
            'color' => '#10B981',
            'requirements' => [
                'valid_challenges' => 1,
            ],
            'reward_points' => 2000,
            'rarity' => 'epic',
        ],
        
        'truth_seeker' => [
            'name' => 'Truth Seeker',
            'description' => 'Submit 100 challenges',
            'icon' => 'light-bulb',
            'color' => '#F59E0B',
            'requirements' => [
                'challenges_submitted' => 100,
            ],
            'reward_points' => 1500,
            'rarity' => 'epic',
        ],
        
        'community_pillar' => [
            'name' => 'Community Pillar',
            'description' => 'Help resolve 50 challenges with expert responses',
            'icon' => 'building-library',
            'color' => '#8B5CF6',
            'requirements' => [
                'expert_responses' => 50,
                'role' => 'expert',
            ],
            'reward_points' => 2500,
            'rarity' => 'legendary',
        ],
        
        'platform_champion' => [
            'name' => 'Platform Champion',
            'description' => 'Reach the highest level of platform engagement',
            'icon' => 'crown',
            'color' => '#DC2626',
            'requirements' => [
                'reputation_points' => 10000,
                'challenges_submitted' => 50,
                'responses_posted' => 500,
                'upvotes_received' => 1000,
            ],
            'reward_points' => 5000,
            'rarity' => 'mythical',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Badge Rarity Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for badge rarity levels and their visual styling
    |
    */
    'rarity' => [
        'common' => [
            'color' => '#6B7280',
            'border_color' => '#9CA3AF',
            'glow' => false,
        ],
        'uncommon' => [
            'color' => '#10B981',
            'border_color' => '#059669',
            'glow' => false,
        ],
        'rare' => [
            'color' => '#3B82F6',
            'border_color' => '#2563EB',
            'glow' => true,
        ],
        'epic' => [
            'color' => '#8B5CF6',
            'border_color' => '#7C3AED',
            'glow' => true,
        ],
        'legendary' => [
            'color' => '#F59E0B',
            'border_color' => '#D97706',
            'glow' => true,
        ],
        'mythical' => [
            'color' => '#DC2626',
            'border_color' => '#B91C1C',
            'glow' => true,
            'animated' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Leaderboard Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for various leaderboards and rankings
    |
    */
    'leaderboards' => [
        'types' => [
            'reputation' => [
                'name' => 'Top Contributors',
                'description' => 'Users with highest reputation points',
                'sort_by' => 'reputation_points',
                'sort_order' => 'desc',
                'cache_minutes' => 60,
            ],
            'challenges' => [
                'name' => 'Top Challengers',
                'description' => 'Most active challenge submitters',
                'sort_by' => 'challenges_submitted',
                'sort_order' => 'desc',
                'cache_minutes' => 30,
            ],
            'responses' => [
                'name' => 'Top Debaters',
                'description' => 'Most active in discussions',
                'sort_by' => 'responses_posted',
                'sort_order' => 'desc',
                'cache_minutes' => 30,
            ],
            'upvotes' => [
                'name' => 'Most Helpful',
                'description' => 'Users with most upvoted content',
                'sort_by' => 'upvotes_received',
                'sort_order' => 'desc',
                'cache_minutes' => 60,
            ],
            'streaks' => [
                'name' => 'Dedication Masters',
                'description' => 'Longest activity streaks',
                'sort_by' => 'current_streak',
                'sort_order' => 'desc',
                'cache_minutes' => 360, // 6 hours
            ],
        ],
        'display_count' => 50,
        'update_frequency_minutes' => 30,
        'show_rankings' => true,
        'show_progress' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Point Expiration
    |--------------------------------------------------------------------------
    |
    | Configuration for point expiration and decay
    |
    */
    'expiration' => [
        'enabled' => false, // Disabled for MIM platform
        'decay_after_days' => 365,
        'decay_rate_percent' => 10,
        'minimum_activity_days' => 30,
        'grace_period_days' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Reputation-related notification settings
    |
    */
    'notifications' => [
        'level_up' => true,
        'badge_earned' => true,
        'leaderboard_rank_change' => true,
        'streak_milestones' => true,
        'point_threshold_reached' => [1000, 2500, 5000, 10000],
        'weekly_summary' => true,
        'monthly_report' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Advanced Features
    |--------------------------------------------------------------------------
    |
    | Advanced gamification features and settings
    |
    */
    'advanced' => [
        'seasonal_events' => [
            'enabled' => true,
            'double_points_periods' => [
                'ramadan' => true,
                'hajj_season' => true,
                'islamic_new_year' => true,
            ],
            'special_challenges' => true,
            'limited_time_badges' => true,
        ],
        'team_challenges' => [
            'enabled' => false, // Future feature
            'max_team_size' => 5,
            'team_point_multiplier' => 1.5,
        ],
        'mentorship_program' => [
            'enabled' => false, // Future feature
            'mentor_requirements' => [
                'reputation_points' => 2000,
                'role' => 'expert',
            ],
            'mentee_benefits' => [
                'faster_approval' => true,
                'bonus_points' => 25,
            ],
        ],
    ],
];
```

## File: app/Exceptions/Handler.php
### Purpose: Global exception handler with custom error handling for MIM platform
### Dependencies: Laravel framework, custom exceptions
### Key Features: Payment error handling, security logging, user-friendly error responses

```php
<?php

namespace App\Exceptions;

use App\Exceptions\PaymentException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'binance_api_key',
        'binance_secret_key',
        'wallet_private_key',
        '2fa_code',
        'verification_code',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // Payment exception handling with detailed logging
        $this->renderable(function (PaymentException $e, Request $request) {
            Log::error('Payment Exception', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'user_id' => $request->user()?->id,
                'request_data' => $request->except($this->dontFlash),
                'stack_trace' => $e->getTraceAsString(),
                'binance_error_code' => $e->getBinanceErrorCode(),
                'transaction_id' => $e->getTransactionId(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'payment_failed',
                    'message' => $e->getUserMessage(),
                    'code' => $e->getCode(),
                    'transaction_id' => $e->getTransactionId(),
                ], 422);
            }

            return redirect()->back()
                ->withErrors(['payment' => $e->getUserMessage()])
                ->withInput($request->except($this->dontFlash));
        });

        // Binance API specific errors
        $this->renderable(function (\Exception $e, Request $request) {
            if (str_contains($e->getMessage(), 'Binance') || 
                str_contains(get_class($e), 'Binance')) {
                
                Log::error('Binance API Error', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'user_id' => $request->user()?->id,
                    'endpoint' => $request->fullUrl(),
                    'method' => $request->method(),
                ]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'payment_service_unavailable',
                        'message' => 'Payment service is temporarily unavailable. Please try again later.',
                    ], 503);
                }

                return redirect()->back()
                    ->withErrors(['payment' => 'Payment service is temporarily unavailable. Please try again later.'])
                    ->withInput($request->except($this->dontFlash));
            }
        });

        // Authentication exception with security logging
        $this->renderable(function (AuthenticationException $e, Request $request) {
            Log::info('Authentication Required', [
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'route' => $request->route()?->getName(),
                'url' => $request->fullUrl(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'unauthenticated',
                    'message' => 'Authentication required.',
                    'redirect_url' => route('login'),
                ], 401);
            }

            return redirect()->guest(route('login'))
                ->with('info', 'Please sign in to access this feature.');
        });

        // Authorization exception with security logging
        $this->renderable(function (AuthorizationException $e, Request $request) {
            Log::warning('Unauthorized Access Attempt', [
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'route' => $request->route()?->getName(),
                'url' => $request->fullUrl(),
                'message' => $e->getMessage(),
                'required_permission' => $e->ability ?? null,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'unauthorized',
                    'message' => 'You do not have permission to perform this action.',
                ], 403);
            }

            return response()->view('errors.403', [
                'message' => 'You do not have permission to access this resource.',
            ], 403);
        });

        // CSRF token mismatch
        $this->renderable(function (TokenMismatchException $e, Request $request) {
            Log::warning('CSRF Token Mismatch', [
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'route' => $request->route()?->getName(),
                'referer' => $request->headers->get('referer'),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'token_mismatch',
                    'message' => 'Session expired. Please refresh the page and try again.',
                ], 419);
            }

            return redirect()->back()
                ->withErrors(['csrf' => 'Session expired. Please try again.'])
                ->withInput($request->except($this->dontFlash));
        });

        // Model not found exception
        $this->renderable(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'resource_not_found',
                    'message' => 'The requested resource was not found.',
                ], 404);
            }

            return response()->view('errors.404', [
                'message' => 'The requested resource was not found.',
            ], 404);
        });

        // General HTTP exceptions
        $this->renderable(function (HttpException $e, Request $request) {
            $statusCode = $e->getStatusCode();
            
            Log::info('HTTP Exception', [
                'status_code' => $statusCode,
                'message' => $e->getMessage(),
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
                'route' => $request->route()?->getName(),
                'url' => $request->fullUrl(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'http_error',
                    'message' => $this->getHttpErrorMessage($statusCode),
                    'status_code' => $statusCode,
                ], $statusCode);
            }

            // Try to render custom error page
            if (view()->exists("errors.{$statusCode}")) {
                return response()->view("errors.{$statusCode}", [
                    'message' => $this->getHttpErrorMessage($statusCode),
                ], $statusCode);
            }

            return response()->view('errors.generic', [
                'code' => $statusCode,
                'message' => $this->getHttpErrorMessage($statusCode),
            ], $statusCode);
        });

        // Subscription-related errors
        $this->renderable(function (\Exception $e, Request $request) {
            if (str_contains($e->getMessage(), 'subscription') || 
                str_contains($e->getMessage(), 'payment required')) {
                
                Log::info('Subscription Required', [
                    'user_id' => $request->user()?->id,
                    'message' => $e->getMessage(),
                    'route' => $request->route()?->getName(),
                ]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'subscription_required',
                        'message' => 'Active subscription required to access this feature.',
                        'redirect_url' => route('subscription.plans'),
                    ], 402);
                }

                return redirect()->route('subscription.plans')
                    ->with('warning', 'Active subscription required to access this feature.');
            }
        });

        // Critical system errors
        $this->reportable(function (Throwable $e) {
            // Don't report certain exceptions to avoid log spam
            $dontReport = [
                ValidationException::class,
                AuthenticationException::class,
                AuthorizationException::class,
                ModelNotFoundException::class,
                NotFoundHttpException::class,
                TokenMismatchException::class,
            ];

            if (!in_array(get_class($e), $dontReport)) {
                Log::critical('System Error', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'request_id' => request()->headers->get('X-Request-ID'),
                    'user_id' => auth()->id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                // Notify administrators of critical errors
                if (app()->environment('production')) {
                    $this->notifyAdminsOfCriticalError($e);
                }
            }
        });
    }

    /**
     * Get user-friendly error message for HTTP status codes.
     */
    private function getHttpErrorMessage(int $statusCode): string
    {
        return match ($statusCode) {
            400 => 'Bad request. Please check your input and try again.',
            401 => 'Authentication required to access this resource.',
            403 => 'You do not have permission to access this resource.',
            404 => 'The requested page or resource was not found.',
            405 => 'Method not allowed for this resource.',
            408 => 'Request timeout. Please try again.',
            413 => 'File too large. Please upload a smaller file.',
            422 => 'Invalid data provided. Please check your input.',
            429 => 'Too many requests. Please slow down and try again later.',
            500 => 'Internal server error. Our team has been notified.',
            502 => 'Service temporarily unavailable. Please try again later.',
            503 => 'Service temporarily unavailable for maintenance.',
            504 => 'Request timeout. Please try again.',
            default => 'An unexpected error occurred. Please try again.',
        };
    }

    /**
     * Notify administrators of critical errors.
     */
    private function notifyAdminsOfCriticalError(Throwable $e): void
    {
        try {
            \Notification::route('mail', config('mim.admin.email'))
                ->notify(new \App\Notifications\CriticalErrorNotification($e));
        } catch (\Exception $notificationException) {
            Log::error('Failed to send critical error notification', [
                'original_error' => $e->getMessage(),
                'notification_error' => $notificationException->getMessage(),
            ]);
        }
    }

    /**
     * Convert a validation exception into a JSON response.
     */
    protected function invalidJson($request, ValidationException $exception): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => 'validation_failed',
            'message' => 'The given data was invalid.',
            'errors' => $exception->errors(),
        ], $exception->status);
    }

    /**
     * Convert an authentication exception into a response.
     */
    protected function unauthenticated($request, AuthenticationException $exception): Response|JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error' => 'unauthenticated',
                'message' => 'Authentication required.',
            ], 401);
        }

        return redirect()->guest($exception->redirectTo() ?? route('login'));
    }
}
```

## File: app/Exceptions/PaymentException.php
### Purpose: Custom exception for payment processing errors
### Dependencies: Laravel exception handling
### Key Features: Binance-specific error codes, transaction tracking, user-friendly messages

```php
<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentException extends Exception
{
    /**
     * Binance-specific error code
     */
    protected ?string $binanceErrorCode = null;

    /**
     * Transaction ID related to the error
     */
    protected ?string $transactionId = null;

    /**
     * User-friendly error message
     */
    protected ?string $userMessage = null;

    /**
     * Additional error context
     */
    protected array $context = [];

    /**
     * Create a new payment exception instance.
     */
    public function __construct(
        string $message = 'Payment processing failed',
        int $code = 422,
        ?Exception $previous = null,
        ?string $userMessage = null,
        ?string $binanceErrorCode = null,
        ?string $transactionId = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        
        $this->userMessage = $userMessage ?? $this->getDefaultUserMessage();
        $this->binanceErrorCode = $binanceErrorCode;
        $this->transactionId = $transactionId;
        $this->context = $context;
    }

    /**
     * Create a Binance API error exception.
     */
    public static function binanceApiError(
        string $errorMessage,
        string $binanceErrorCode,
        ?string $transactionId = null,
        array $context = []
    ): self {
        $userMessage = self::getBinanceUserMessage($binanceErrorCode);
        
        return new self(
            message: "Binance API Error: {$errorMessage}",
            code: 503,
            userMessage: $userMessage,
            binanceErrorCode: $binanceErrorCode,
            transactionId: $transactionId,
            context: $context
        );
    }

    /**
     * Create an insufficient balance exception.
     */
    public static function insufficientBalance(
        float $required,
        float $available,
        string $currency = 'USDT',
        ?string $transactionId = null
    ): self {
        return new self(
            message: "Insufficient balance: Required {$required} {$currency}, Available {$available} {$currency}",
            code: 402,
            userMessage: "Insufficient balance. Please add funds to your wallet and try again.",
            transactionId: $transactionId,
            context: [
                'required_amount' => $required,
                'available_amount' => $available,
                'currency' => $currency,
            ]
        );
    }

    /**
     * Create an invalid wallet address exception.
     */
    public static function invalidWalletAddress(
        string $address,
        string $currency = 'USDT',
        ?string $transactionId = null
    ): self {
        return new self(
            message: "Invalid wallet address: {$address} for currency {$currency}",
            code: 422,
            userMessage: "Invalid wallet address. Please check your wallet address and try again.",
            transactionId: $transactionId,
            context: [
                'wallet_address' => $address,
                'currency' => $currency,
            ]
        );
    }

    /**
     * Create a transaction timeout exception.
     */
    public static function transactionTimeout(
        string $transactionId,
        int $timeoutMinutes = 30
    ): self {
        return new self(
            message: "Transaction {$transactionId} timed out after {$timeoutMinutes} minutes",
            code: 408,
            userMessage: "Transaction is taking longer than expected. Please check your transaction status or contact support.",
            transactionId: $transactionId,
            context: [
                'timeout_minutes' => $timeoutMinutes,
            ]
        );
    }

    /**
     * Create a rate limit exceeded exception.
     */
    public static function rateLimitExceeded(
        string $limit,
        int $retryAfterSeconds = 60
    ): self {
        return new self(
            message: "Rate limit exceeded: {$limit}",
            code: 429,
            userMessage: "Too many payment requests. Please wait a moment and try again.",
            context: [
                'limit' => $limit,
                'retry_after_seconds' => $retryAfterSeconds,
            ]
        );
    }

    /**
     * Create a subscription payment failed exception.
     */
    public static function subscriptionPaymentFailed(
        string $reason,
        ?string $transactionId = null
    ): self {
        return new self(
            message: "Subscription payment failed: {$reason}",
            code: 402,
            userMessage: "Subscription payment failed. Please update your payment method or contact support.",
            transactionId: $transactionId,
            context: [
                'failure_reason' => $reason,
            ]
        );
    }

    /**
     * Create a reward payout failed exception.
     */
    public static function rewardPayoutFailed(
        string $reason,
        float $amount,
        string $currency = 'USDT',
        ?string $transactionId = null
    ): self {
        return new self(
            message: "Reward payout failed: {$reason}",
            code: 500,
            userMessage: "Reward payout failed. Our team has been notified and will resolve this issue.",
            transactionId: $transactionId,
            context: [
                'failure_reason' => $reason,
                'payout_amount' => $amount,
                'currency' => $currency,
            ]
        );
    }

    /**
     * Get user-friendly message based on Binance error code.
     */
    private static function getBinanceUserMessage(string $binanceErrorCode): string
    {
        return match ($binanceErrorCode) {
            '-1000' => 'Payment service is temporarily unavailable. Please try again later.',
            '-1001' => 'Payment service is disconnected. Please try again later.',
            '-1002' => 'You are not authorized to perform this payment operation.',
            '-1003' => 'Too many payment requests. Please slow down and try again.',
            '-1006' => 'Unexpected payment service response. Please try again.',
            '-1007' => 'Payment service timeout. Please try again.',
            '-1021' => 'Payment request expired. Please try again.',
            '-1022' => 'Invalid payment signature. Please refresh the page and try again.',
            '-2010' => 'Insufficient account balance for this transaction.',
            '-2011' => 'Order cancellation rejected. Please contact support.',
            '-2013' => 'Order does not exist or has already been processed.',
            '-2014' => 'Invalid API key format or permissions.',
            '-2015' => 'Invalid API key, IP, or permissions for action.',
            default => 'Payment processing failed. Please try again or contact support.',
        };
    }

    /**
     * Get default user-friendly message.
     */
    private function getDefaultUserMessage(): string
    {
        return match ($this->code) {
            400 => 'Invalid payment request. Please check your information and try again.',
            401 => 'Payment authentication failed. Please verify your credentials.',
            402 => 'Payment required. Please update your payment method.',
            403 => 'Payment operation not authorized. Please contact support.',
            408 => 'Payment request timed out. Please try again.',
            422 => 'Invalid payment data. Please check your information.',
            429 => 'Too many payment requests. Please wait and try again.',
            500 => 'Payment system error. Our team has been notified.',
            503 => 'Payment service temporarily unavailable. Please try again later.',
            default => 'Payment processing failed. Please try again or contact support.',
        };
    }

    /**
     * Get Binance error code.
     */
    public function getBinanceErrorCode(): ?string
    {
        return $this->binanceErrorCode;
    }

    /**
     * Get transaction ID.
     */
    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    /**
     * Get user-friendly message.
     */
    public function getUserMessage(): string
    {
        return $this->userMessage;
    }

    /**
     * Get error context.
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Report the exception for logging and monitoring.
     */
    public function report(): void
    {
        Log::error('Payment Exception', [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'binance_error_code' => $this->binanceErrorCode,
            'transaction_id' => $this->transactionId,
            'user_message' => $this->userMessage,
            'context' => $this->context,
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'trace' => $this->getTraceAsString(),
        ]);

        // Send critical payment errors to admin
        if ($this->code >= 500) {
            try {
                \Notification::route('mail', config('mim.admin.notification_emails.payment_issues'))
                    ->notify(new \App\Notifications\CriticalPaymentError($this));
            } catch (\Exception $e) {
                Log::error('Failed to send payment error notification', [
                    'notification_error' => $e->getMessage(),
                    'original_error' => $this->getMessage(),
                ]);
            }
        }
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error' => 'payment_failed',
                'message' => $this->getUserMessage(),
                'code' => $this->getCode(),
                'transaction_id' => $this->getTransactionId(),
                'binance_error_code' => $this->getBinanceErrorCode(),
            ], $this->getCode());
        }

        return redirect()->back()
            ->withErrors(['payment' => $this->getUserMessage()])
            ->withInput($request->except(['password', 'password_confirmation', 'binance_api_key', 'binance_secret_key']));
    }

    /**
     * Convert the exception to an array for API responses.
     */
    public function toArray(): array
    {
        return [
            'error' => 'payment_failed',
            'message' => $this->getUserMessage(),
            'code' => $this->getCode(),
            'transaction_id' => $this->getTransactionId(),
            'binance_error_code' => $this->getBinanceErrorCode(),
            'context' => $this->getContext(),
        ];
    }
}
```

## File: app/Console/Kernel.php
### Purpose: Console command scheduling and registration for MIM platform
### Dependencies: Laravel console kernel, custom commands
### Key Features: Automated tasks, payment processing, reputation calculations, system maintenance

```php
<?php

namespace App\Console;

use App\Console\Commands\CalculateReputationCommand;
use App\Console\Commands\GenerateStatisticsCommand;
use App\Console\Commands\ProcessSubscriptionRenewalsCommand;
use App\Console\Commands\SyncBinanceTransactionsCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // =================================================================
        // CRITICAL BUSINESS OPERATIONS - High Priority
        // =================================================================

        // Process subscription renewals every hour
        $schedule->command('subscriptions:process-renewals')
                 ->hourly()
                 ->withoutOverlapping(30) // Prevent concurrent runs
                 ->runInBackground()
                 ->onSuccess(function () {
                     Log::info('Subscription renewals processed successfully');
                 })
                 ->onFailure(function () {
                     Log::error('Subscription renewal processing failed');
                     $this->notifyAdmins('Subscription Renewal Failed', 'The hourly subscription renewal process has failed.');
                 });

        // Sync Binance transactions every 15 minutes
        $schedule->command('binance:sync-transactions')
                 ->everyFifteenMinutes()
                 ->withoutOverlapping(10)
                 ->runInBackground()
                 ->onFailure(function () {
                     Log::error('Binance transaction sync failed');
                 });

        // Process pending reward payouts every 30 minutes
        $schedule->command('rewards:process-payouts')
                 ->everyThirtyMinutes()
                 ->withoutOverlapping(20)
                 ->runInBackground()
                 ->onSuccess(function () {
                     Log::info('Reward payouts processed successfully');
                 })
                 ->onFailure(function () {
                     Log::error('Reward payout processing failed');
                     $this->notifyAdmins('Reward Payout Failed', 'The reward payout process has failed.');
                 });

        // =================================================================
        // REPUTATION & GAMIFICATION - Medium Priority
        // =================================================================

        // Calculate user reputation daily at 2:00 AM
        $schedule->command('reputation:calculate')
                 ->dailyAt('02:00')
                 ->withoutOverlapping(120) // 2 hours max
                 ->runInBackground()
                 ->onSuccess(function () {
                     Log::info('Daily reputation calculation completed');
                 })
                 ->onFailure(function () {
                     Log::error('Daily reputation calculation failed');
                 });

        // Update leaderboards every 4 hours
        $schedule->command('reputation:update-leaderboards')
                 ->cron('0 */4 * * *')
                 ->withoutOverlapping(60)
                 ->runInBackground();

        // Process badge awards daily at 3:00 AM
        $schedule->command('reputation:process-badges')
                 ->dailyAt('03:00')
                 ->withoutOverlapping(60)
                 ->runInBackground();

        // Check and award streak bonuses daily at 1:00 AM
        $schedule->command('reputation:process-streaks')
                 ->dailyAt('01:00')
                 ->withoutOverlapping(30)
                 ->runInBackground();

        // =================================================================
        // PLATFORM MAINTENANCE - Medium Priority
        // =================================================================

        // Generate daily statistics at 4:00 AM
        $schedule->command('statistics:generate daily')
                 ->dailyAt('04:00')
                 ->withoutOverlapping(90)
                 ->runInBackground()
                 ->onSuccess(function () {
                     Log::info('Daily statistics generated successfully');
                 });

        // Generate weekly statistics on Sundays at 5:00 AM
        $schedule->command('statistics:generate weekly')
                 ->weeklyOn(0, '05:00') // Sunday
                 ->withoutOverlapping(120)
                 ->runInBackground();

        // Generate monthly statistics on the 1st at 6:00 AM
        $schedule->command('statistics:generate monthly')
                 ->monthlyOn(1, '06:00')
                 ->withoutOverlapping(180)
                 ->runInBackground();

        // =================================================================
        // CONTENT MANAGEMENT - Medium Priority
        // =================================================================

        // Auto-close inactive challenges daily at 12:00 AM
        $schedule->command('challenges:auto-close-inactive')
                 ->dailyAt('00:00')
                 ->withoutOverlapping(60)
                 ->runInBackground();

        // Send expert assignment reminders every 6 hours
        $schedule->command('challenges:remind-experts')
                 ->cron('0 */6 * * *')
                 ->withoutOverlapping(30)
                 ->runInBackground();

        // Process challenge deadline notifications daily at 10:00 AM
        $schedule->command('challenges:deadline-notifications')
                 ->dailyAt('10:00')
                 ->withoutOverlapping(30)
                 ->runInBackground();

        // =================================================================
        // NOTIFICATIONS & COMMUNICATIONS - Low Priority
        // =================================================================

        // Send daily digest emails at 8:00 AM
        $schedule->command('notifications:send-daily-digest')
                 ->dailyAt('08:00')
                 ->withoutOverlapping(60)
                 ->runInBackground();

        // Send weekly summary emails on Sundays at 9:00 AM
        $schedule->command('notifications:send-weekly-summary')
                 ->weeklyOn(0, '09:00')
                 ->withoutOverlapping(90)
                 ->runInBackground();

        // Process pending notifications every 5 minutes
        $schedule->command('notifications:process-queue')
                 ->everyFiveMinutes()
                 ->withoutOverlapping(3)
                 ->runInBackground();

        // =================================================================
        // SYSTEM MAINTENANCE - Low Priority
        // =================================================================

        // Clean up old logs weekly on Mondays at 2:00 AM
        $schedule->command('logs:cleanup')
                 ->weeklyOn(1, '02:00')
                 ->withoutOverlapping(120)
                 ->runInBackground();

        // Clean up expired sessions daily at 3:30 AM
        $schedule->command('session:gc')
                 ->dailyAt('03:30')
                 ->withoutOverlapping(30);

        // Clear expired password reset tokens daily at 4:30 AM
        $schedule->command('auth:clear-resets')
                 ->dailyAt('04:30')
                 ->withoutOverlapping(15);

        // Backup database daily at 1:30 AM
        $schedule->command('backup:run --only-db')
                 ->dailyAt('01:30')
                 ->withoutOverlapping(180)
                 ->onSuccess(function () {
                     Log::info('Database backup completed successfully');
                 })
                 ->onFailure(function () {
                     Log::error('Database backup failed');
                     $this->notifyAdmins('Database Backup Failed', 'The daily database backup has failed.');
                 });

        // Full backup weekly on Sundays at 1:00 AM
        $schedule->command('backup:run')
                 ->weeklyOn(0, '01:00')
                 ->withoutOverlapping(300) // 5 hours max
                 ->onSuccess(function () {
                     Log::info('Full backup completed successfully');
                 })
                 ->onFailure(function () {
                     Log::error('Full backup failed');
                     $this->notifyAdmins('Full Backup Failed', 'The weekly full backup has failed.');
                 });

        // =================================================================
        // SECURITY & MONITORING - High Priority
        // =================================================================

        // Monitor system health every 5 minutes
        $schedule->command('system:health-check')
                 ->everyFiveMinutes()
                 ->withoutOverlapping(3)
                 ->runInBackground()
                 ->onFailure(function () {
                     Log::critical('System health check failed');
                     $this->notifyAdmins('System Health Check Failed', 'Critical system health check has failed.');
                 });

        // Check for suspicious activity daily at 6:00 AM
        $schedule->command('security:scan-activity')
                 ->dailyAt('06:00')
                 ->withoutOverlapping(60)
                 ->runInBackground()
                 ->onFailure(function () {
                     Log::error('Security activity scan failed');
                 });

        // Monitor payment transactions every 10 minutes
        $schedule->command('payments:monitor-transactions')
                 ->everyTenMinutes()
                 ->withoutOverlapping(5)
                 ->runInBackground()
                 ->onFailure(function () {
                     Log::error('Payment transaction monitoring failed');
                 });

        // =================================================================
        // PERFORMANCE OPTIMIZATION - Low Priority
        // =================================================================

        // Clear application cache daily at 5:00 AM
        $schedule->command('cache:clear')
                 ->dailyAt('05:00')
                 ->withoutOverlapping(15);

        // Optimize application caches daily at 5:15 AM
        $schedule->command('optimize:clear')
                 ->dailyAt('05:15')
                 ->withoutOverlapping(15);

        // Rebuild search indexes weekly on Tuesdays at 3:00 AM
        $schedule->command('scout:import')
                 ->weeklyOn(2, '03:00')
                 ->withoutOverlapping(240) // 4 hours max
                 ->runInBackground();

        // =================================================================
        // DEVELOPMENT & TESTING (Non-Production Only)
        // =================================================================

        if (!app()->environment('production')) {
            // Generate test data for development
            $schedule->command('dev:generate-test-data')
                     ->dailyAt('00:30')
                     ->withoutOverlapping(30)
                     ->runInBackground()
                     ->environments(['staging', 'development']);

            // Clean up test data weekly
            $schedule->command('dev:cleanup-test-data')
                     ->weeklyOn(0, '00:30')
                     ->withoutOverlapping(60)
                     ->runInBackground()
                     ->environments(['staging', 'development']);
        }

        // =================================================================
        // QUEUE WORKERS - Critical for Application Performance
        // =================================================================

        // Ensure queue workers are always running
        $schedule->command('queue:work --sleep=3 --tries=3 --max-time=3600 --stop-when-empty')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->runInBackground();

        // Restart queue workers daily at 0:00 AM to prevent memory leaks
        $schedule->command('queue:restart')
                 ->dailyAt('00:00');

        // Process failed jobs retry every hour
        $schedule->command('queue:retry all')
                 ->hourly()
                 ->withoutOverlapping(30);

        // =================================================================
        // CUSTOM MAINTENANCE COMMANDS
        // =================================================================

        // Auto-expire old reputation points (if enabled)
        if (config('reputation.expiration.enabled')) {
            $schedule->command('reputation:expire-points')
                     ->dailyAt('02:30')
                     ->withoutOverlapping(60)
                     ->runInBackground();
        }

        // Process seasonal events and special campaigns
        if (config('reputation.advanced.seasonal_events.enabled')) {
            $schedule->command('events:process-seasonal')
                     ->dailyAt('07:00')
                     ->withoutOverlapping(30)
                     ->runInBackground();
        }

        // Sync with external Islamic calendar for special events
        $schedule->command('calendar:sync-islamic-events')
                 ->weekly()
                 ->withoutOverlapping(60)
                 ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Get the timezone that should be used by default for scheduled events.
     */
    protected function scheduleTimezone(): string
    {
        return config('app.timezone', 'UTC');
    }

    /**
     * Notify administrators of critical failures.
     */
    private function notifyAdmins(string $subject, string $message): void
    {
        try {
            $adminEmail = config('mim.admin.notification_emails.critical_errors');
            
            if ($adminEmail) {
                \Notification::route('mail', $adminEmail)
                    ->notify(new \App\Notifications\SystemMaintenanceAlert($subject, $message));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send admin notification', [
                'subject' => $subject,
                'message' => $message,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
```

Now I'll create the Service Provider files to complete the foundation:

## File: app/Providers/AppServiceProvider.php
### Purpose: Main application service provider with core bindings and configurations
### Dependencies: Laravel service container, platform services
### Key Features: Service bindings, configuration, macros, performance optimizations

```php
<?php

namespace App\Providers;

use App\Services\AnalyticsService;
use App\Services\ChallengeService;
use App\Services\PaymentService;
use App\Services\ReputationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // =================================================================
        // SERVICE BINDINGS
        // =================================================================

        // Bind core platform services as singletons
        $this->app->singleton(PaymentService::class, function ($app) {
            return new PaymentService(
                config: $app['config'],
                binanceConfig: config('binance'),
                logger: $app['log']
            );
        });

        $this->app->singleton(ReputationService::class, function ($app) {
            return new ReputationService(
                config: config('reputation'),
                cache: $app['cache']
            );
        });

        $this->app->singleton(ChallengeService::class, function ($app) {
            return new ChallengeService(
                reputationService: $app[ReputationService::class],
                config: config('mim')
            );
        });

        $this->app->singleton(AnalyticsService::class, function ($app) {
            return new AnalyticsService(
                cache: $app['cache'],
                config: config('mim.analytics')
            );
        });

        // Bind interfaces to implementations
        $this->app->bind(
            \App\Contracts\PaymentProcessorInterface::class,
            \App\Services\BinancePaymentProcessor::class
        );

        $this->app->bind(
            \App\Contracts\NotificationChannelInterface::class,
            \App\Services\NotificationChannelService::class
        );

        // =================================================================
        // DEVELOPMENT BINDINGS
        // =================================================================

        if ($this->app->environment('local', 'testing')) {
            // Register development-only services
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }

        // =================================================================
        // TESTING BINDINGS
        // =================================================================

        if ($this->app->environment('testing')) {
            // Mock external services in testing
            $this->app->bind(PaymentService::class, function () {
                return new \App\Services\MockPaymentService();
            });
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // =================================================================
        // SECURITY CONFIGURATIONS
        // =================================================================

        // Force HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Set default string length for MySQL compatibility
        Schema::defaultStringLength(191);

        // =================================================================
        // MODEL CONFIGURATIONS
        // =================================================================

        // Prevent lazy loading in non-production environments
        Model::preventLazyLoading(!$this->app->isProduction());

        // Prevent silently discarding attributes
        Model::preventSilentlyDiscardingAttributes(!$this->app->isProduction());

        // Prevent accessing missing attributes
        Model::preventAccessingMissingAttributes(!$this->app->isProduction());

        // =================================================================
        // PAGINATION CONFIGURATION
        // =================================================================

        // Use Bootstrap 5 for pagination views
        Paginator::defaultView('pagination::bootstrap-5');
        Paginator::defaultSimpleView('pagination::simple-bootstrap-5');

        // =================================================================
        // SANCTUM CONFIGURATION
        // =================================================================

        // Use custom personal access token model if needed
        // Sanctum::usePersonalAccessTokenModel(\App\Models\PersonalAccessToken::class);

        // =================================================================
        // CUSTOM VALIDATION RULES
        // =================================================================

        // Binance wallet address validation
        Validator::extend('binance_address', function ($attribute, $value, $parameters, $validator) {
            return app(PaymentService::class)->validateWalletAddress($value, $parameters[0] ?? 'USDT');
        });

        Validator::replacer('binance_address', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, 'The :attribute must be a valid cryptocurrency wallet address.');
        });

        // Challenge category validation
        Validator::extend('valid_challenge_category', function ($attribute, $value, $parameters, $validator) {
            return \App\Models\Category::where('id', $value)
                ->where('is_active', true)
                ->exists();
        });

        // Subscription requirement validation
        Validator::extend('requires_subscription', function ($attribute, $value, $parameters, $validator) {
            $user = auth()->user();
            return $user && $user->hasActiveSubscription();
        });

        // =================================================================
        // CUSTOM MACROS
        // =================================================================

        // Request macro for getting user's subscription status
        \Illuminate\Http\Request::macro('userHasActiveSubscription', function () {
            return $this->user() && $this->user()->hasActiveSubscription();
        });

        // Collection macro for reputation calculations
        \Illuminate\Support\Collection::macro('calculateReputation', function () {
            return $this->sum('points');
        });

        // String macro for Arabic text handling
        \Illuminate\Support\Str::macro('isArabic', function ($text) {
            return preg_match('/[\x{0600}-\x{06FF}]/u', $text);
        });

        // =================================================================
        // GATES & POLICIES
        // =================================================================

        // Define gates for platform features
        Gate::define('submit-challenge', function ($user) {
            return $user->hasActiveSubscription() && 
                   $user->canSubmitChallenge() &&
                   !$user->isRestricted();
        });

        Gate::define('vote-on-content', function ($user) {
            return $user->hasActiveSubscription() && 
                   $user->reputation_score >= config('mim.moderation.min_reputation_for_voting', 10);
        });

        Gate::define('access-expert-features', function ($user) {
            return $user->hasRole(['expert', 'admin']) || 
                   $user->reputation_score >= config('reputation.levels.expert.min_points', 2000);
        });

        Gate::define('moderate-content', function ($user) {
            return $user->hasRole(['moderator', 'admin']) ||
                   $user->hasPermissionTo('moderate_content');
        });

        Gate::define('access-admin-panel', function ($user) {
            return $user->hasRole(['admin', 'moderator']);
        });

        // =================================================================
        // VIEW COMPOSERS
        // =================================================================

        // Share common data with all views
        view()->composer('*', function ($view) {
            $view->with([
                'currentLocale' => app()->getLocale(),
                'isRtl' => in_array(app()->getLocale(), config('mim.localization.rtl_locales', ['ar'])),
                'appName' => config('app.name'),
                'platformFeatures' => config('mim.features'),
            ]);
        });

        // Share user-specific data with authenticated views
        view()->composer(['layouts.app', 'dashboard.*'], function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                $view->with([
                    'userSubscriptionStatus' => $user->subscription_status,
                    'userReputationLevel' => $user->getReputationLevel(),
                    'unreadNotificationsCount' => $user->unreadNotifications()->count(),
                    'userCanSubmitChallenge' => $user->canSubmitChallenge(),
                ]);
            }
        });

        // Share statistics with public views
        view()->composer(['pages.home', 'challenges.index'], function ($view) {
            $analytics = app(AnalyticsService::class);
            $view->with([
                'platformStats' => $analytics->getPlatformStatistics(),
                'recentChallenges' => $analytics->getRecentChallenges(5),
            ]);
        });

        // =================================================================
        // CUSTOM CONFIGURATIONS
        // =================================================================

        // Configure application locale based on user preference or browser
        if (auth()->check() && auth()->user()->preferred_locale) {
            app()->setLocale(auth()->user()->preferred_locale);
        } elseif (request()->hasHeader('Accept-Language')) {
            $acceptedLanguages = request()->getLanguages();
            $availableLocales = config('mim.localization.available_locales', ['en']);
            
            foreach ($acceptedLanguages as $language) {
                $locale = substr($language, 0, 2);
                if (in_array($locale, $availableLocales)) {
                    app()->setLocale($locale);
                    break;
                }
            }
        }

        // =================================================================
        // PERFORMANCE OPTIMIZATIONS
        // =================================================================

        // Enable query caching in production
        if ($this->app->environment('production')) {
            \DB::enableQueryLog();
        }

        // Configure memory limits for large operations
        if (config('mim.performance.increase_memory_limit', false)) {
            ini_set('memory_limit', '512M');
        }

        // =================================================================
        // LOGGING CONFIGURATIONS
        // =================================================================

        // Log slow queries in development
        if ($this->app->environment('local', 'staging')) {
            \DB::listen(function ($query) {
                if ($query->time > 1000) { // Log queries taking more than 1 second
                    \Log::warning('Slow Query Detected', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time . 'ms',
                    ]);
                }
            });
        }

        // =================================================================
        // MAINTENANCE MODE HANDLING
        // =================================================================

        // Customize maintenance mode response
        $this->app->maintenanceMode()->response(function () {
            return response()->view('errors.503', [
                'message' => 'The MIM platform is currently undergoing maintenance. We\'ll be back shortly.',
                'estimatedTime' => config('mim.maintenance.estimated_duration', '30 minutes'),
            ], 503);
        });
    }
}
```

## File: app/Providers/AuthServiceProvider.php
### Purpose: Authentication and authorization service provider
### Dependencies: Laravel auth system, Spatie permissions
### Key Features: Policy registration, gate definitions, permission setup

```php
<?php

namespace App\Providers;

use App\Models\Challenge;
use App\Models\ChallengeResponse;
use App\Models\User;
use App\Policies\AdminPolicy;
use App\Policies\ChallengePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\Sanctum;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Challenge::class => ChallengePolicy::class,
        ChallengeResponse::class => \App\Policies\ChallengeResponsePolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // =================================================================
        // SANCTUM CONFIGURATION
        // =================================================================

        // Configure Sanctum token expiration
        Sanctum::actingAs(
            \App\Models\User::factory()->create(),
            ['*']
        );

        // =================================================================
        // SUPER ADMIN GATE
        // =================================================================

        // Super admin bypasses all authorization checks
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super-admin')) {
                return true;
            }
        });

        // =================================================================
        // PLATFORM FEATURE GATES
        // =================================================================

        // Challenge submission gate
        Gate::define('submit-challenge', function (User $user) {
            return $user->hasActiveSubscription() && 
                   $user->canSubmitChallenge() &&
                   !$user->isRestricted() &&
                   config('mim.features.challenge_submission', true);
        });

        // Challenge response gate
        Gate::define('respond-to-challenge', function (User $user, Challenge $challenge = null) {
            $canRespond = $user->hasActiveSubscription() && 
                         !$user->isRestricted() &&
                         $user->reputation_score >= config('mim.moderation.min_reputation_for_responses', 5);

            // Additional checks if specific challenge is provided
            if ($challenge) {
                $canRespond = $canRespond && 
                             $challenge->status === 'under_review' &&
                             $challenge->submitted_by !== $user->id;
            }

            return $canRespond;
        });

        // Voting gate
        Gate::define('vote-on-content', function (User $user) {
            return $user->hasActiveSubscription() && 
                   $user->reputation_score >= config('mim.moderation.min_reputation_for_voting', 10) &&
                   !$user->isRestricted() &&
                   config('mim.features.voting_system', true);
        });

        // Expert features gate
        Gate::define('access-expert-features', function (User $user) {
            return $user->hasRole(['expert', 'admin']) || 
                   $user->reputation_score >= config('reputation.levels.expert.min_points', 2000);
        });

        // Content moderation gate
        Gate::define('moderate-content', function (User $user) {
            return $user->hasRole(['moderator', 'admin']) ||
                   $user->hasPermissionTo('moderate_content') ||
                   ($user->hasRole('expert') && $user->reputation_score >= 5000);
        });

        // Flag content gate
        Gate::define('flag-content', function (User $user) {
            return $user->hasActiveSubscription() && 
                   $user->reputation_score >= config('reputation.levels.advanced.min_points', 500) &&
                   !$user->isRestricted();
        });

        // =================================================================
        // ADMINISTRATIVE GATES
        // =================================================================

        // Admin panel access
        Gate::define('access-admin-panel', function (User $user) {
            return $user->hasRole(['admin', 'moderator']) ||
                   $user->hasPermissionTo('access_admin_panel');
        });

        // User management
        Gate::define('manage-users', function (User $user) {
            return $user->hasRole('admin') ||
                   $user->hasPermissionTo('manage_users');
        });

        // Payment management
        Gate::define('manage-payments', function (User $user) {
            return $user->hasRole('admin') ||
                   $user->hasPermissionTo('manage_payments');
        });

        // System settings
        Gate::define('manage-settings', function (User $user) {
            return $user->hasRole('admin') ||
                   $user->hasPermissionTo('manage_settings');
        });

        // View analytics
        Gate::define('view-analytics', function (User $user) {
            return $user->hasRole(['admin', 'moderator']) ||
                   $user->hasPermissionTo('view_analytics');
        });

        // =================================================================
        // REWARD & PAYMENT GATES
        // =================================================================

        // Claim reward gate
        Gate::define('claim-reward', function (User $user, Challenge $challenge) {
            return $user->id === $challenge->submitted_by &&
                   $challenge->status === 'resolved' &&
                   $challenge->resolution_type === 'valid_mistake' &&
                   !$challenge->reward_claimed &&
                   $user->hasActiveSubscription();
        });

        // Process payout gate
        Gate::define('process-payout', function (User $user) {
            return $user->hasRole('admin') ||
                   $user->hasPermissionTo('process_payouts');
        });

        // View financial data
        Gate::define('view-financial-data', function (User $user) {
            return $user->hasRole('admin') ||
                   $user->hasPermissionTo('view_financial_data');
        });

        // =================================================================
        // CONTENT-SPECIFIC GATES
        // =================================================================

        // Edit own content
        Gate::define('edit-own-content', function (User $user, $content) {
            return $user->id === $content->user_id &&
                   !$user->isRestricted() &&
                   $content->created_at->diffInHours(now()) <= 24; // 24-hour edit window
        });

        // Delete own content
        Gate::define('delete-own-content', function (User $user, $content) {
            return $user->id === $content->user_id &&
                   !$user->isRestricted() &&
                   $content->created_at->diffInHours(now()) <= 1; // 1-hour delete window
        });

        // View private content
        Gate::define('view-private-content', function (User $user) {
            return $user->hasActiveSubscription() ||
                   $user->hasRole(['admin', 'moderator', 'expert']);
        });

        // =================================================================
        // REPUTATION & GAMIFICATION GATES
        // =================================================================

        // Award badges
        Gate::define('award-badges', function (User $user) {
            return $user->hasRole('admin') ||
                   $user->hasPermissionTo('award_badges');
        });

        // Adjust reputation
        Gate::define('adjust-reputation', function (User $user) {
            return $user->hasRole('admin') ||
                   $user->hasPermissionTo('adjust_reputation');
        });

        // View detailed reputation
        Gate::define('view-detailed-reputation', function (User $user, User $targetUser) {
            return $user->id === $targetUser->id ||
                   $user->hasRole(['admin', 'moderator']) ||
                   $user->hasPermissionTo('view_user_details');
        });

        // =================================================================
        // SUBSCRIPTION & BILLING GATES
        // =================================================================

        // Manage subscription
        Gate::define('manage-subscription', function (User $user, User $targetUser = null) {
            if ($targetUser) {
                return $user->id === $targetUser->id ||
                       $user->hasRole('admin') ||
                       $user->hasPermissionTo('manage_user_subscriptions');
            }
            return true; // Users can always manage their own subscription
        });

        // View billing history
        Gate::define('view-billing-history', function (User $user, User $targetUser = null) {
            if ($targetUser) {
                return $user->id === $targetUser->id ||
                       $user->hasRole('admin') ||
                       $user->hasPermissionTo('view_user_billing');
            }
            return true;
        });

        // Process refunds
        Gate::define('process-refunds', function (User $user) {
            return $user->hasRole('admin') ||
                   $user->hasPermissionTo('process_refunds');
        });

        // =================================================================
        // API ACCESS GATES
        // =================================================================

        // API access
        Gate::define('access-api', function (User $user) {
            return $user->hasActiveSubscription() ||
                   $user->hasRole(['admin', 'expert']) ||
                   config('mim.api.require_authentication', false) === false;
        });

        // API write access
        Gate::define('api-write-access', function (User $user) {
            return $user->hasActiveSubscription() &&
                   !$user->isRestricted() &&
                   $user->hasVerifiedEmail();
        });

        // =================================================================
        // RATE LIMITING GATES
        // =================================================================

        // Check rate limits
        Gate::define('within-rate-limits', function (User $user, string $action) {
            $limits = config('mim.limits');
            
            return match($action) {
                'challenge_submission' => $user->getDailySubmissionCount() < $limits['challenges_per_day'],
                'voting' => $user->getDailyVoteCount() < $limits['votes_per_day'],
                'responses' => $user->getHourlyResponseCount() < $limits['responses_per_hour'],
                default => true,
            };
        });

        // =================================================================
        // SECURITY GATES
        // =================================================================

        // Two-factor authentication required
        Gate::define('requires-2fa', function (User $user) {
            return $user->two_factor_secret !== null ||
                   config('mim.security.require_2fa_for_admins', true) === false ||
                   !$user->hasRole(['admin', 'moderator']);
        });

        // Account security actions
        Gate::define('perform-security-action', function (User $user) {
            return !$user->isRestricted() &&
                   $user->hasVerifiedEmail() &&
                   $user->two_factor_confirmed_at !== null;
        });

        // =================================================================
        // SPECIAL PERMISSIONS
        // =================================================================

        // Platform maintenance
        Gate::define('toggle-maintenance-mode', function (User $user) {
            return $user->hasRole('admin') ||
                   $user->hasPermissionTo('toggle_maintenance_mode');
        });

        // System backups
        Gate::define('manage-backups', function (User $user) {
            return $user->hasRole('admin') ||
                   $user->hasPermissionTo('manage_backups');
        });

        // Export data
        Gate::define('export-platform-data', function (User $user) {
            return $user->hasRole('admin') ||
                   $user->hasPermissionTo('export_data');
        });

        // Import data
        Gate::define('import-platform-data', function (User $user) {
            return $user->hasRole('admin') ||
                   $user->hasPermissionTo('import_data');
        });
    }
}
```

## File: app/Providers/EventServiceProvider.php
### Purpose: Event and listener registration for the MIM platform
### Dependencies: Laravel event system, custom events and listeners
### Key Features: Event-listener mappings, observer registration, automatic discovery

```php
<?php

namespace App\Providers;

use App\Events\ChallengeResolved;
use App\Events\ChallengeSubmitted;
use App\Events\PaymentProcessed;
use App\Events\RewardAwarded;
use App\Listeners\ProcessRewardPayment;
use App\Listeners\UpdateLeaderboards;
use App\Listeners\UpdateUserReputation;
use App\Models\Challenge;
use App\Models\ChallengeResponse;
use App\Models\ChallengeVote;
use App\Models\PaymentTransaction;
use App\Models\RewardClaim;
use App\Models\User;
use App\Models\UserSubscription;
use App\Observers\ChallengeObserver;
use App\Observers\PaymentTransactionObserver;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // =================================================================
        // LARAVEL CORE EVENTS
        // =================================================================

        Registered::class => [
            SendEmailVerificationNotification::class,
            \App\Listeners\CreateUserProfile::class,
            \App\Listeners\InitializeUserReputation::class,
            \App\Listeners\SendWelcomeNotification::class,
        ],

        \Illuminate\Auth\Events\Login::class => [
            \App\Listeners\UpdateLastActivity::class,
            \App\Listeners\LogSuccessfulLogin::class,
            \App\Listeners\CheckDailyStreak::class,
        ],

        \Illuminate\Auth\Events\Logout::class => [
            \App\Listeners\LogUserLogout::class,
        ],

        \Illuminate\Auth\Events\Failed::class => [
            \App\Listeners\LogFailedLogin::class,
        ],

        \Illuminate\Auth\Events\Verified::class => [
            \App\Listeners\HandleEmailVerification::class,
            \App\Listeners\AwardEmailVerificationPoints::class,
        ],

        // =================================================================
        // CHALLENGE SYSTEM EVENTS
        // =================================================================

        ChallengeSubmitted::class => [
            \App\Listeners\NotifyExpertsOfNewChallenge::class,
            \App\Listeners\UpdateChallengeStatistics::class,
            \App\Listeners\AwardChallengeSubmissionPoints::class,
        ],

        ChallengeResolved::class => [
            \App\Listeners\NotifyChallengeParticipants::class,
            UpdateUserReputation::class,
            \App\Listeners\UpdateChallengeArchive::class,
        ],

        // =================================================================
        // PAYMENT & SUBSCRIPTION EVENTS
        // =================================================================

        PaymentProcessed::class => [
            \App\Listeners\UpdateSubscriptionStatus::class,
            \App\Listeners\SendPaymentConfirmation::class,
            \App\Listeners\LogPaymentActivity::class,
        ],

        // =================================================================
        // REWARD SYSTEM EVENTS
        // =================================================================

        RewardAwarded::class => [
            ProcessRewardPayment::class,
            \App\Listeners\SendRewardNotification::class,
            \App\Listeners\UpdateRewardStatistics::class,
        ],

        // =================================================================
        // REPUTATION & GAMIFICATION EVENTS
        // =================================================================

        \App\Events\ReputationUpdated::class => [
            UpdateUserReputation::class,
            UpdateLeaderboards::class,
            \App\Listeners\CheckBadgeEligibility::class,
        ],
    ];

    /**
     * The model observers for the application.
     */
    protected $observers = [
        User::class => [UserObserver::class],
        Challenge::class => [ChallengeObserver::class],
        PaymentTransaction::class => [PaymentTransactionObserver::class],
        UserSubscription::class => [\App\Observers\UserSubscriptionObserver::class],
        RewardClaim::class => [\App\Observers\RewardClaimObserver::class],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        // Handle queue job processing events
        Event::listen(\Illuminate\Queue\Events\JobProcessing::class, function ($event) {
            \Log::debug('Queue job started', [
                'job' => $event->job->getName(),
                'queue' => $event->job->getQueue(),
            ]);
        });

        Event::listen(\Illuminate\Queue\Events\JobProcessed::class, function ($event) {
            \Log::debug('Queue job completed', [
                'job' => $event->job->getName(),
                'queue' => $event->job->getQueue(),
            ]);
        });

        Event::listen(\Illuminate\Queue\Events\JobFailed::class, function ($event) {
            \Log::error('Queue job failed', [
                'job' => $event->job->getName(),
                'queue' => $event->job->getQueue(),
                'exception' => $event->exception->getMessage(),
            ]);
        });

        // Automatic event discovery for development
        if ($this->app->environment('local')) {
            Event::listen('App\\Events\\*', function ($eventName, array $data) {
                \Log::debug('Event fired', [
                    'event' => $eventName,
                    'data' => $data,
                ]);
            });
        }
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return true;
    }

    /**
     * Get the listener directories that should be used to discover events.
     */
    protected function discoverEventsWithin(): array
    {
        return [
            $this->app->path('Listeners'),
        ];
    }
}
```

## File: app/Providers/RouteServiceProvider.php
### Purpose: Route configuration and model binding for MIM platform
### Dependencies: Laravel routing, custom middleware
### Key Features: Route caching, model binding, rate limiting, API versioning

```php
<?php

namespace App\Providers;

use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\EnsureUserRole;
use App\Models\Challenge;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
        $this->configureModelBindings();

        $this->routes(function () {
            // API Routes
            Route::middleware('api')
                ->prefix('api/v1')
                ->name('api.')
                ->group(base_path('routes/api.php'));

            // Web Routes
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // Admin Routes
            Route::middleware(['web', 'auth', 'role:admin,moderator'])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));

            // Authentication Routes (Laravel Breeze)
            Route::middleware('web')
                ->group(base_path('routes/auth.php'));

            // Console Routes
            Route::middleware('api')
                ->prefix('console')
                ->group(base_path('routes/console.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // General API rate limiting
        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(60)->by($request->user()->id)
                : Limit::perMinute(10)->by($request->ip());
        });

        // Challenge submission rate limiting
        RateLimiter::for('challenges', function (Request $request) {
            if (!$request->user()) {
                return Limit::none();
            }

            $dailyLimit = config('mim.limits.challenges_per_day', 3);
            return Limit::perDay($dailyLimit)->by($request->user()->id);
        });

        // Voting rate limiting
        RateLimiter::for('votes', function (Request $request) {
            if (!$request->user()) {
                return Limit::none();
            }

            $dailyLimit = config('mim.limits.votes_per_day', 50);
            return Limit::perDay($dailyLimit)->by($request->user()->id);
        });

        // Response submission rate limiting
        RateLimiter::for('responses', function (Request $request) {
            if (!$request->user()) {
                return Limit::none();
            }

            $hourlyLimit = config('mim.limits.responses_per_hour', 20);
            return Limit::perHour($hourlyLimit)->by($request->user()->id);
        });

        // Payment processing rate limiting
        RateLimiter::for('payments', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(5)->by($request->user()->id)
                : Limit::perMinute(2)->by($request->ip());
        });

        // Authentication rate limiting
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Password reset rate limiting
        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perHour(3)->by($request->ip());
        });

        // Search rate limiting
        RateLimiter::for('search', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(30)->by($request->user()->id)
                : Limit::perMinute(10)->by($request->ip());
        });

        // Admin operations rate limiting
        RateLimiter::for('admin', function (Request $request) {
            return $request->user() && $request->user()->hasRole(['admin', 'moderator'])
                ? Limit::perMinute(100)->by($request->user()->id)
                : Limit::none();
        });

        // File upload rate limiting
        RateLimiter::for('uploads', function (Request $request) {
            return $request->user()
                ? Limit::perHour(10)->by($request->user()->id)
                : Limit::none();
        });
    }

    /**
     * Configure model bindings for the application.
     */
    protected function configureModelBindings(): void
    {
        // User binding with UUID support
        Route::bind('user', function ($value) {
            if (is_numeric($value)) {
                return User::findOrFail($value);
            }
            
            return User::where('uuid', $value)
                      ->orWhere('username', $value)
                      ->firstOrFail();
        });

        // Challenge binding with UUID support
        Route::bind('challenge', function ($value) {
            if (is_numeric($value)) {
                return Challenge::with(['category', 'submittedBy', 'responses'])
                               ->findOrFail($value);
            }
            
            return Challenge::with(['category', 'submittedBy', 'responses'])
                           ->where('uuid', $value)
                           ->firstOrFail();
        });

        // Category binding with slug support
        Route::bind('category', function ($value) {
            return \App\Models\Category::where('slug', $value)
                                     ->orWhere('id', $value)
                                     ->firstOrFail();
        });

        // Response binding with UUID support
        Route::bind('response', function ($value) {
            return \App\Models\ChallengeResponse::with(['user', 'challenge'])
                                               ->where('uuid', $value)
                                               ->orWhere('id', $value)
                                               ->firstOrFail();
        });

        // Payment transaction binding
        Route::bind('payment', function ($value) {
            return \App\Models\PaymentTransaction::where('uuid', $value)
                                                ->orWhere('id', $value)
                                                ->firstOrFail();
        });

        // Reward claim binding
        Route::bind('reward', function ($value) {
            return \App\Models\RewardClaim::with(['user', 'challenge'])
                                         ->where('uuid', $value)
                                         ->orWhere('id', $value)
                                         ->firstOrFail();
        });

        // Page binding with slug support
        Route::bind('page', function ($value) {
            return \App\Models\Page::where('slug', $value)
                                  ->where('is_published', true)
                                  ->firstOrFail();
        });

        // Achievement badge binding
        Route::bind('badge', function ($value) {
            return \App\Models\AchievementBadge::where('slug', $value)
                                              ->orWhere('id', $value)
                                              ->firstOrFail();
        });
    }

    /**
     * Define additional route patterns.
     */
    public function map(): void
    {
        // UUID pattern for route parameters
        Route::pattern('uuid', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        
        // Username pattern
        Route::pattern('username', '[a-zA-Z0-9_]{3,20}');
        
        // Slug pattern
        Route::pattern('slug', '[a-z0-9-]+');
        
        // Language code pattern
        Route::pattern('locale', '[a-z]{2}');
        
        // Numeric ID pattern
        Route::pattern('id', '[0-9]+');
    }

    /**
     * Get route middleware groups.
     */
    public function getMiddlewareGroups(): array
    {
        return [
            'subscription' => [
                'auth',
                CheckSubscription::class,
            ],
            'admin' => [
                'auth',
                EnsureUserRole::class . ':admin,moderator',
            ],
            'expert' => [
                'auth',
                EnsureUserRole::class . ':expert,admin',
            ],
            'api.auth' => [
                'auth:sanctum',
                'throttle:api',
            ],
            'api.public' => [
                'throttle:api',
            ],
        ];
    }
}
```

## File: app/Providers/PaymentServiceProvider.php
### Purpose: Payment service configuration and Binance API integration
### Dependencies: Binance connector, Laravel service container
### Key Features: API client setup, webhook routes, event handling

```php
<?php

namespace App\Providers;

use App\Services\PaymentService;
use App\Services\BinanceApiService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Binance API service
        $this->app->singleton(BinanceApiService::class, function ($app) {
            $config = config('binance');
            
            return new BinanceApiService(
                apiKey: $config['api_key'],
                secretKey: $config['secret_key'],
                testnet: $config['testnet'],
                baseUrl: $config['base_url'],
                timeout: $config['http']['timeout'] ?? 30
            );
        });

        // Register Payment service
        $this->app->singleton(PaymentService::class, function ($app) {
            return new PaymentService(
                binanceApi: $app->make(BinanceApiService::class),
                config: config('mim'),
                logger: $app->make('log')
            );
        });

        // Register payment-related contracts
        $this->app->bind(
            \App\Contracts\PaymentProcessorInterface::class,
            PaymentService::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register webhook routes
        $this->registerWebhookRoutes();

        // Register payment event listeners
        $this->registerPaymentEvents();

        // Configure payment validation rules
        $this->configureValidationRules();
    }

    /**
     * Register webhook routes for payment processing.
     */
    protected function registerWebhookRoutes(): void
    {
        Route::group([
            'prefix' => 'api/webhooks',
            'middleware' => ['api'],
        ], function () {
            Route::post('binance/payment', [\App\Http\Controllers\Api\WebhookController::class, 'binancePayment'])
                 ->name('webhooks.binance.payment');
            
            Route::post('binance/payout', [\App\Http\Controllers\Api\WebhookController::class, 'binancePayout'])
                 ->name('webhooks.binance.payout');
        });
    }

    /**
     * Register payment-related event listeners.
     */
    protected function registerPaymentEvents(): void
    {
        // Listen for payment events and trigger appropriate actions
        $this->app['events']->listen(
            \App\Events\PaymentProcessed::class,
            \App\Listeners\UpdateSubscriptionStatus::class
        );

        $this->app['events']->listen(
            \App\Events\PaymentFailed::class,
            \App\Listeners\HandlePaymentFailure::class
        );

        $this->app['events']->listen(
            \App\Events\RewardAwarded::class,
            \App\Listeners\ProcessRewardPayment::class
        );
    }

    /**
     * Configure custom validation rules for payments.
     */
    protected function configureValidationRules(): void
    {
        \Validator::extend('valid_wallet_address', function ($attribute, $value, $parameters, $validator) {
            $currency = $parameters[0] ?? 'USDT';
            return $this->app->make(PaymentService::class)->validateWalletAddress($value, $currency);
        });

        \Validator::extend('sufficient_balance', function ($attribute, $value, $parameters, $validator) {
            $userId = $parameters[0] ?? null;
            if (!$userId) {
                return false;
            }
            
            return $this->app->make(PaymentService::class)->checkUserBalance($userId, (float) $value);
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            BinanceApiService::class,
            PaymentService::class,
            \App\Contracts\PaymentProcessorInterface::class,
        ];
    }
}
```
