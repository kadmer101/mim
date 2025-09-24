<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WebBloc System Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the core configuration for the WebBloc system,
    | including response formats, database settings, and component standards.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Response Format Distribution
    |--------------------------------------------------------------------------
    |
    | WebBloc server serves content in the following distribution:
    | 75% HTML, 15% JSON, 10% other formats
    |
    */
    'response_formats' => [
        'html' => 75,
        'json' => 15,
        'other' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Response Format
    |--------------------------------------------------------------------------
    |
    | The default response format for WebBloc API endpoints
    |
    */
    'default_response_format' => 'html',

    /*
    |--------------------------------------------------------------------------
    | SQLite Database Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for per-website SQLite databases
    |
    */
    'sqlite' => [
        'path' => storage_path('databases'),
        'prefix' => 'website_',
        'suffix' => '.sqlite',
        'auto_create' => true,
        'backup_enabled' => true,
        'backup_path' => storage_path('databases/backups'),
    ],

    /*
    |--------------------------------------------------------------------------
    | WebBloc Standards
    |--------------------------------------------------------------------------
    |
    | Standard definitions for WebBloc components
    |
    */
    'webbloc_standards' => [
        'required_fields' => ['type', 'attributes', 'crud', 'metadata'],
        'supported_types' => [
            'auth' => 'Authentication WebBloc',
            'comment' => 'Comments System',
            'review' => 'Reviews System',
            'testimonial' => 'Testimonials',
            'reaction' => 'Reactions',
            'social_share' => 'Social Sharing',
            'profile' => 'User Profiles',
            'notification' => 'Notifications',
        ],
        'crud_operations' => ['create', 'read', 'update', 'delete'],
        'default_attributes' => [
            'limit' => 10,
            'sort' => 'newest',
            'pagination' => true,
            'cache_ttl' => 300,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for WebBloc API endpoints and authentication
    |
    */
    'api' => [
        'version' => 'v1',
        'rate_limit' => [
            'requests_per_minute' => 60,
            'requests_per_hour' => 1000,
            'requests_per_day' => 10000,
        ],
        'authentication' => [
            'guard' => 'sanctum',
            'token_expiration' => 60 * 24 * 7, // 7 days in minutes
            'refresh_token_expiration' => 60 * 24 * 30, // 30 days in minutes
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CDN Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for WebBloc CDN assets and integration
    |
    */
    'cdn' => [
        'enabled' => true,
        'base_url' => env('WEBBLOC_CDN_URL', '/cdn'),
        'version' => '1.0.0',
        'assets' => [
            'js' => [
                'webbloc.min.js',
                'alpine.min.js',
                'sweetalert2.min.js',
            ],
            'css' => [
                'webbloc.min.css',
                'themes.min.css',
            ],
        ],
        'cache_control' => [
            'max_age' => 31536000, // 1 year
            'public' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Security settings for WebBloc system
    |
    */
    'security' => [
        'api_key_length' => 64,
        'secret_key_length' => 128,
        'encryption_algorithm' => 'AES-256-CBC',
        'hash_algorithm' => 'sha256',
        'input_validation' => [
            'max_content_length' => 1048576, // 1MB
            'allowed_html_tags' => '<p><br><strong><em><u><ol><ul><li><a><img>',
            'sanitize_input' => true,
        ],
        'cors' => [
            'allowed_origins' => ['*'],
            'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            'allowed_headers' => ['Content-Type', 'Authorization', 'X-API-Key', 'X-Website-ID'],
            'exposed_headers' => ['X-Total-Count', 'X-Page-Count'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | Performance and caching settings
    |
    */
    'performance' => [
        'cache' => [
            'enabled' => true,
            'default_ttl' => 300, // 5 minutes
            'store' => 'redis',
            'key_prefix' => 'webbloc:',
        ],
        'database' => [
            'connection_pool_size' => 10,
            'query_timeout' => 30,
            'enable_query_log' => false,
        ],
        'compression' => [
            'enabled' => true,
            'level' => 6,
            'threshold' => 1024, // Compress responses > 1KB
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Integration Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for static website integration
    |
    */
    'integration' => [
        'tag_prefix' => 'w2030b',
        'attribute_prefix' => 'w2030b_tags',
        'loading_text' => 'Content Loading...',
        'error_text' => 'Failed to load content',
        'alpine_version' => '3.x',
        'sweetalert_version' => '11.x',
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for system notifications
    |
    */
    'notifications' => [
        'channels' => ['mail', 'database', 'broadcast'],
        'mail' => [
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'noreply@webbloc.com'),
                'name' => env('MAIL_FROM_NAME', 'WebBloc System'),
            ],
        ],
        'types' => [
            'api_key_generated' => 'API Key Generated',
            'website_registered' => 'Website Registered',
            'webbloc_installed' => 'WebBloc Installed',
            'quota_exceeded' => 'Usage Quota Exceeded',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Default validation rules for WebBloc components
    |
    */
    'validation' => [
        'website' => [
            'url' => 'required|url|max:255',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ],
        'webbloc' => [
            'type' => 'required|string|max:50',
            'data' => 'required|json',
            'page_url' => 'required|string|max:500',
        ],
        'api_key' => [
            'name' => 'required|string|max:100',
            'permissions' => 'required|array',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for file uploads in WebBloc components
    |
    */
    'uploads' => [
        'disk' => 'public',
        'max_size' => 2048, // 2MB in KB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'],
        'image_quality' => 85,
        'resize' => [
            'max_width' => 1200,
            'max_height' => 800,
        ],
        'thumbnails' => [
            'enabled' => true,
            'sizes' => [
                'small' => [150, 150],
                'medium' => [300, 300],
                'large' => [600, 600],
            ],
        ],
    ],
];