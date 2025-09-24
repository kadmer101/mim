<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WebBloc;

class WebBlocSeeder extends Seeder
{
    public function run(): void
    {
        $webBlocs = [
            [
                'type' => 'auth',
                'name' => 'Authentication',
                'description' => 'Complete user authentication system with login, registration, and profile management',
                'version' => '1.0.0',
                'attributes' => [
                    'registration_enabled' => [
                        'type' => 'boolean',
                        'default' => true,
                        'description' => 'Allow new user registrations'
                    ],
                    'social_login' => [
                        'type' => 'array',
                        'default' => [],
                        'description' => 'Enabled social login providers'
                    ],
                    'email_verification' => [
                        'type' => 'boolean',
                        'default' => true,
                        'description' => 'Require email verification for new accounts'
                    ],
                    'password_requirements' => [
                        'type' => 'object',
                        'default' => [
                            'min_length' => 8,
                            'require_uppercase' => true,
                            'require_numbers' => true,
                            'require_symbols' => false
                        ],
                        'description' => 'Password complexity requirements'
                    ],
                    'mode' => [
                        'type' => 'string',
                        'default' => 'modal',
                        'options' => ['modal', 'inline', 'redirect'],
                        'description' => 'Display mode for authentication forms'
                    ],
                    'theme' => [
                        'type' => 'string',
                        'default' => 'default',
                        'options' => ['default', 'minimal', 'corporate'],
                        'description' => 'Visual theme for authentication components'
                    ]
                ],
                'crud_operations' => [
                    'create' => true,
                    'read' => true,
                    'update' => true,
                    'delete' => false
                ],
                'api_endpoints' => [
                    'POST /auth/register',
                    'POST /auth/login',
                    'POST /auth/logout',
                    'GET /auth/profile',
                    'PUT /auth/profile',
                    'POST /auth/password/email',
                    'POST /auth/password/reset'
                ],
                'required_permissions' => ['auth.login', 'auth.register', 'auth.profile'],
                'metadata' => [
                    'category' => 'authentication',
                    'tags' => ['user', 'login', 'registration', 'security'],
                    'compatibility' => [
                        'laravel' => '>=10.0',
                        'php' => '>=8.1'
                    ],
                    'dependencies' => ['Laravel Sanctum'],
                    'file_structure' => [
                        'blade' => 'resources/views/components/webbloc/auth.blade.php',
                        'js' => 'resources/js/components/auth.js',
                        'css' => 'resources/css/components/auth.css'
                    ]
                ],
                'status' => 'active'
            ],
            [
                'type' => 'comments',
                'name' => 'Comments System',
                'description' => 'Interactive commenting system with nested replies, moderation, and real-time updates',
                'version' => '1.0.0',
                'attributes' => [
                    'allow_guest' => [
                        'type' => 'boolean',
                        'default' => false,
                        'description' => 'Allow guest users to post comments'
                    ],
                    'require_approval' => [
                        'type' => 'boolean',
                        'default' => true,
                        'description' => 'Require admin approval for new comments'
                    ],
                    'max_depth' => [
                        'type' => 'integer',
                        'default' => 3,
                        'min' => 1,
                        'max' => 10,
                        'description' => 'Maximum nesting depth for replies'
                    ],
                    'enable_reactions' => [
                        'type' => 'boolean',
                        'default' => true,
                        'description' => 'Enable like/dislike reactions on comments'
                    ],
                    'enable_mentions' => [
                        'type' => 'boolean',
                        'default' => true,
                        'description' => 'Enable @mention functionality'
                    ],
                    'spam_protection' => [
                        'type' => 'boolean',
                        'default' => true,
                        'description' => 'Enable automated spam detection'
                    ],
                    'sort_order' => [
                        'type' => 'string',
                        'default' => 'newest',
                        'options' => ['newest', 'oldest', 'popular', 'controversial'],
                        'description' => 'Default comment sorting order'
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'default' => 20,
                        'min' => 5,
                        'max' => 100,
                        'description' => 'Number of comments to display per page'
                    ]
                ],
                'crud_operations' => [
                    'create' => true,
                    'read' => true,
                    'update' => true,
                    'delete' => true
                ],
                'api_endpoints' => [
                    'GET /webblocs/comments',
                    'POST /webblocs/comments',
                    'GET /webblocs/comments/{id}',
                    'PUT /webblocs/comments/{id}',
                    'DELETE /webblocs/comments/{id}',
                    'POST /webblocs/comments/{id}/reactions'
                ],
                'required_permissions' => ['webbloc.read', 'webbloc.create'],
                'metadata' => [
                    'category' => 'engagement',
                    'tags' => ['comments', 'discussion', 'social', 'engagement'],
                    'compatibility' => [
                        'laravel' => '>=10.0',
                        'php' => '>=8.1'
                    ],
                    'dependencies' => [],
                    'file_structure' => [
                        'blade' => 'resources/views/components/webbloc/comments.blade.php',
                        'js' => 'resources/js/components/comments.js',
                        'css' => 'resources/css/components/comments.css'
                    ]
                ],
                'status' => 'active'
            ],
            [
                'type' => 'reviews',
                'name' => 'Reviews & Ratings',
                'description' => 'Customer review and rating system with photos, verification, and analytics',
                'version' => '1.0.0',
                'attributes' => [
                    'rating_scale' => [
                        'type' => 'integer',
                        'default' => 5,
                        'options' => [3, 5, 10],
                        'description' => 'Rating scale (e.g., 1-5 stars)'
                    ],
                    'require_purchase_verification' => [
                        'type' => 'boolean',
                        'default' => false,
                        'description' => 'Require purchase verification for reviews'
                    ],
                    'allow_anonymous' => [
                        'type' => 'boolean',
                        'default' => false,
                        'description' => 'Allow anonymous reviews'
                    ],
                    'allow_media' => [
                        'type' => 'boolean',
                        'default' => true,
                        'description' => 'Allow photo and video uploads'
                    ],
                    'require_review_text' => [
                        'type' => 'boolean',
                        'default' => true,
                        'description' => 'Require text content in addition to rating'
                    ],
                    'auto_approve' => [
                        'type' => 'boolean',
                        'default' => false,
                        'description' => 'Automatically approve new reviews'
                    ],
                    'show_reviewer_info' => [
                        'type' => 'boolean',
                        'default' => true,
                        'description' => 'Display reviewer name and profile'
                    ],
                    'enable_helpful_votes' => [
                        'type' => 'boolean',
                        'default' => true,
                        'description' => 'Enable "helpful" voting on reviews'
                    ]
                ],
                'crud_operations' => [
                    'create' => true,
                    'read' => true,
                    'update' => true,
                    'delete' => true
                ],
                'api_endpoints' => [
                    'GET /webblocs/reviews',
                    'POST /webblocs/reviews',
                    'GET /webblocs/reviews/{id}',
                    'PUT /webblocs/reviews/{id}',
                    'DELETE /webblocs/reviews/{id}',
                    'GET /webblocs/reviews/summary',
                    'POST /webblocs/reviews/{id}/helpful'
                ],
                'required_permissions' => ['webbloc.read', 'webbloc.create'],
                'metadata' => [
                    'category' => 'feedback',
                    'tags' => ['reviews', 'ratings', 'feedback', 'testimonials'],
                    'compatibility' => [
                        'laravel' => '>=10.0',
                        'php' => '>=8.1'
                    ],
                    'dependencies' => ['Intervention Image'],
                    'file_structure' => [
                        'blade' => 'resources/views/components/webbloc/reviews.blade.php',
                        'js' => 'resources/js/components/reviews.js',
                        'css' => 'resources/css/components/reviews.css'
                    ]
                ],
                'status' => 'active'
            ],
            [
                'type' => 'notifications',
                'name' => 'Notification System',
                'description' => 'Real-time notification system with multiple display options and delivery methods',
                'version' => '1.0.0',
                'attributes' => [
                    'position' => [
                        'type' => 'string',
                        'default' => 'top-right',
                        'options' => ['top-left', 'top-right', 'top-center', 'bottom-left', 'bottom-right', 'bottom-center'],
                        'description' => 'Display position for toast notifications'
                    ],
                    'auto_dismiss' => [
                        'type' => 'boolean',
                        'default' => true,
                        'description' => 'Automatically dismiss notifications'
                    ],
                    'dismiss_delay' => [
                        'type' => 'integer',
                        'default' => 5000,
                        'min' => 1000,
                        'max' => 30000,
                        'description' => 'Auto-dismiss delay in milliseconds'
                    ],
                    'sound_enabled' => [
                        'type' => 'boolean',
                        'default' => false,
                        'description' => 'Enable notification sounds'
                    ],
                    'max_visible' => [
                        'type' => 'integer',
                        'default' => 5,
                        'min' => 1,
                        'max' => 20,
                        'description' => 'Maximum number of visible notifications'
                    ],
                    'enable_push' => [
                        'type' => 'boolean',
                        'default' => false,
                        'description' => 'Enable browser push notifications'
                    ],
                    'categories' => [
                        'type' => 'array',
                        'default' => ['info', 'success', 'warning', 'error'],
                        'description' => 'Available notification categories'
                    ]
                ],
                'crud_operations' => [
                    'create' => true,
                    'read' => true,
                    'update' => false,
                    'delete' => true
                ],
                'api_endpoints' => [
                    'GET /webblocs/notifications',
                    'POST /webblocs/notifications',
                    'PUT /webblocs/notifications/{id}/read',
                    'DELETE /webblocs/notifications/{id}',
                    'POST /webblocs/notifications/mark-all-read'
                ],
                'required_permissions' => ['webbloc.read'],
                'metadata' => [
                    'category' => 'communication',
                    'tags' => ['notifications', 'alerts', 'messaging', 'real-time'],
                    'compatibility' => [
                        'laravel' => '>=10.0',
                        'php' => '>=8.1'
                    ],
                    'dependencies' => ['Laravel Broadcasting'],
                    'file_structure' => [
                        'blade' => 'resources/views/components/webbloc/notifications.blade.php',
                        'js' => 'resources/js/components/notifications.js',
                        'css' => 'resources/css/components/notifications.css'
                    ]
                ],
                'status' => 'active'
            ]
        ];

        foreach ($webBlocs as $webBlocData) {
            WebBloc::updateOrCreate(
                ['type' => $webBlocData['type']],
                $webBlocData
            );
        }

        $this->command->info('WebBloc definitions seeded successfully!');
    }
}