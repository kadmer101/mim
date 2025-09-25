Based on my comprehensive analysis of all the WebBloc project documentation, I now understand the complete system architecture. This is a Laravel-based multi-tenant system that provides dynamic web components as APIs for static websites, featuring multi-language support (English/Arabic), UUID-based routing, Alpine.js integration, and sophisticated authentication mechanisms.

Now I'll provide all the requested blade views with complete implementation:

## 9. API Documentation Views

### resources/views/docs/components.blade.php

```php
@extends('layouts.docs')

@section('title', __('Components API Documentation'))

@section('content')
<div class="min-h-screen bg-gray-50" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ __('Components API') }}</h1>
            <p class="text-xl text-gray-600">{{ __('Complete reference for all available WebBloc components') }}</p>
        </div>

        <!-- Navigation -->
        <div class="mb-8">
            <nav class="flex flex-wrap gap-4">
                <a href="#overview" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">{{ __('Overview') }}</a>
                <a href="#auth" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">{{ __('Authentication') }}</a>
                <a href="#comments" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">{{ __('Comments') }}</a>
                <a href="#reviews" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">{{ __('Reviews') }}</a>
                <a href="#reactions" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">{{ __('Reactions') }}</a>
                <a href="#profiles" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">{{ __('Profiles') }}</a>
                <a href="#testimonials" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">{{ __('Testimonials') }}</a>
            </nav>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="sticky top-8">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold mb-4">{{ __('Quick Links') }}</h3>
                        <ul class="space-y-2">
                            <li><a href="#overview" class="text-blue-600 hover:text-blue-800">{{ __('Overview') }}</a></li>
                            <li><a href="#webbloc-standard" class="text-blue-600 hover:text-blue-800">{{ __('WebBloc Standard') }}</a></li>
                            <li><a href="#integration" class="text-blue-600 hover:text-blue-800">{{ __('Integration') }}</a></li>
                            <li><a href="#customization" class="text-blue-600 hover:text-blue-800">{{ __('Customization') }}</a></li>
                            <li><a href="#multilang" class="text-blue-600 hover:text-blue-800">{{ __('Multi-language') }}</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-3">
                <!-- Overview Section -->
                <section id="overview" class="mb-12">
                    <div class="bg-white rounded-lg shadow-md p-8">
                        <h2 class="text-3xl font-bold mb-6">{{ __('Components Overview') }}</h2>
                        
                        <div class="mb-8">
                            <h3 class="text-xl font-semibold mb-4">{{ __('What are WebBloc Components?') }}</h3>
                            <p class="text-gray-700 mb-4">{{ __('WebBloc components are dynamic, interactive elements that can be embedded into static websites. Each component follows the WebBloc standard and provides specific functionality like authentication, comments, reviews, and more.') }}</p>
                            
                            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
                                            {{ __('All components are fully responsive, support RTL languages, and can be customized to match your website\'s design.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="webbloc-standard" class="mb-8">
                            <h3 class="text-xl font-semibold mb-4">{{ __('WebBloc Standard') }}</h3>
                            <p class="text-gray-700 mb-4">{{ __('Every WebBloc component follows a standardized structure:') }}</p>
                            
                            <div class="bg-gray-100 rounded-lg p-4 mb-4">
                                <pre class="text-sm"><code>{
    "type": "component_type",
    "attributes": {
        "id": "unique_identifier", 
        "class": "custom_classes",
        "data-*": "custom_attributes"
    },
    "crud": {
        "create": true,
        "read": true, 
        "update": true,
        "delete": true
    },
    "metadata": {
        "version": "1.0.0",
        "requires_auth": false,
        "multi_language": true
    }
}</code></pre>
                            </div>
                        </div>

                        <div id="integration" class="mb-8">
                            <h3 class="text-xl font-semibold mb-4">{{ __('Basic Integration') }}</h3>
                            <p class="text-gray-700 mb-4">{{ __('To integrate any component into your static website:') }}</p>
                            
                            <div class="space-y-4">
                                <div>
                                    <h4 class="font-semibold mb-2">{{ __('1. Include WebBloc CDN') }}</h4>
                                    <div class="bg-gray-100 rounded-lg p-4">
                                        <pre class="text-sm"><code>&lt;!-- Add to your HTML head --&gt;
&lt;script src="https://webbloc.example.com/js/webbloc.min.js"&gt;&lt;/script&gt;
&lt;link href="https://webbloc.example.com/css/webbloc.min.css" rel="stylesheet"&gt;</code></pre>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="font-semibold mb-2">{{ __('2. Initialize WebBloc') }}</h4>
                                    <div class="bg-gray-100 rounded-lg p-4">
                                        <pre class="text-sm"><code>&lt;script&gt;
WebBloc.init({
    apiUrl: 'https://webbloc.example.com/api',
    publicKey: 'your_public_key',
    websiteId: 'your_website_uuid',
    locale: 'en' // or 'ar'
});
&lt;/script&gt;</code></pre>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="font-semibold mb-2">{{ __('3. Add Component') }}</h4>
                                    <div class="bg-gray-100 rounded-lg p-4">
                                        <pre class="text-sm"><code>&lt;div webbloc="component_type" data-config='{...}'&gt;&lt;/div&gt;</code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Authentication Component -->
                <section id="auth" class="mb-12">
                    <div class="bg-white rounded-lg shadow-md p-8">
                        <h2 class="text-3xl font-bold mb-6">{{ __('Authentication Component') }}</h2>
                        
                        <div class="mb-6">
                            <p class="text-gray-700 mb-4">{{ __('Provides complete user authentication functionality including login, registration, password reset, and user profile management.') }}</p>
                            
                            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                                <h4 class="font-semibold text-green-800 mb-2">{{ __('Features') }}</h4>
                                <ul class="list-disc list-inside text-green-700 space-y-1">
                                    <li>{{ __('User registration and login') }}</li>
                                    <li>{{ __('Password reset functionality') }}</li>
                                    <li>{{ __('Profile management') }}</li>
                                    <li>{{ __('Social authentication (Google, GitHub)') }}</li>
                                    <li>{{ __('Multi-language support') }}</li>
                                    <li>{{ __('Responsive design') }}</li>
                                </ul>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-xl font-semibold mb-4">{{ __('Usage') }}</h3>
                            <div class="bg-gray-100 rounded-lg p-4">
                                <pre class="text-sm"><code>&lt;div webbloc="auth" 
     data-config='{
        "style": "modal", 
        "show_social": true,
        "redirect_after_login": "/dashboard"
     }'&gt;
&lt;/div&gt;</code></pre>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-xl font-semibold mb-4">{{ __('Configuration Options') }}</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Option') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Type') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Default') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Description') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">style</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">string</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">"inline"</td>
                                            <td class="px-6 py-4 text-sm text-gray-500">"inline" or "modal"</td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">show_social</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">boolean</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">true</td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ __('Show social login buttons') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">redirect_after_login</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">string</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">null</td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ __('URL to redirect after login') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xl font-semibold mb-4">{{ __('API Endpoints') }}</h3>
                            <div class="space-y-4">
                                <div class="border rounded-lg p-4">
                                    <div class="flex items-center mb-2">
                                        <span class="bg-green-500 text-white px-2 py-1 text-xs rounded mr-2">POST</span>
                                        <code class="text-sm">/api/auth/register</code>
                                    </div>
                                    <p class="text-sm text-gray-600">{{ __('Register a new user') }}</p>
                                </div>
                                <div class="border rounded-lg p-4">
                                    <div class="flex items-center mb-2">
                                        <span class="bg-blue-500 text-white px-2 py-1 text-xs rounded mr-2">POST</span>
                                        <code class="text-sm">/api/auth/login</code>
                                    </div>
                                    <p class="text-sm text-gray-600">{{ __('Authenticate user') }}</p>
                                </div>
                                <div class="border rounded-lg p-4">
                                    <div class="flex items-center mb-2">
                                        <span class="bg-red-500 text-white px-2 py-1 text-xs rounded mr-2">POST</span>
                                        <code class="text-sm">/api/auth/logout</code>
                                    </div>
                                    <p class="text-sm text-gray-600">{{ __('Logout user') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Comments Component -->
                <section id="comments" class="mb-12">
                    <div class="bg-white rounded-lg shadow-md p-8">
                        <h2 class="text-3xl font-bold mb-6">{{ __('Comments Component') }}</h2>
                        
                        <div class="mb-6">
                            <p class="text-gray-700 mb-4">{{ __('Allows users to add, edit, delete, and reply to comments on any page or content.') }}</p>
                            
                            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                                <h4 class="font-semibold text-green-800 mb-2">{{ __('Features') }}</h4>
                                <ul class="list-disc list-inside text-green-700 space-y-1">
                                    <li>{{ __('Threaded comments with replies') }}</li>
                                    <li>{{ __('Real-time comment submission') }}</li>
                                    <li>{{ __('Edit and delete own comments') }}</li>
                                    <li>{{ __('Moderation capabilities') }}</li>
                                    <li>{{ __('Anonymous or authenticated commenting') }}</li>
                                    <li>{{ __('Rich text editor support') }}</li>
                                </ul>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-xl font-semibold mb-4">{{ __('Usage') }}</h3>
                            <div class="bg-gray-100 rounded-lg p-4">
                                <pre class="text-sm"><code>&lt;div webbloc="comments" 
     data-config='{
        "page_id": "unique-page-identifier",
        "allow_anonymous": false,
        "max_depth": 3,
        "moderation": true
     }'&gt;
&lt;/div&gt;</code></pre>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xl font-semibold mb-4">{{ __('Configuration Options') }}</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Option') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Type') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Default') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Description') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">page_id</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">string</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">required</td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ __('Unique identifier for the page/content') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">allow_anonymous</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">boolean</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">false</td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ __('Allow comments from non-authenticated users') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">max_depth</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">number</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">3</td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ __('Maximum reply depth') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Reviews Component -->
                <section id="reviews" class="mb-12">
                    <div class="bg-white rounded-lg shadow-md p-8">
                        <h2 class="text-3xl font-bold mb-6">{{ __('Reviews Component') }}</h2>
                        
                        <div class="mb-6">
                            <p class="text-gray-700 mb-4">{{ __('Enables users to leave star ratings and detailed reviews for products, services, or content.') }}</p>
                            
                            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                                <h4 class="font-semibold text-green-800 mb-2">{{ __('Features') }}</h4>
                                <ul class="list-disc list-inside text-green-700 space-y-1">
                                    <li>{{ __('Star rating system (1-5 stars)') }}</li>
                                    <li>{{ __('Detailed review text') }}</li>
                                    <li>{{ __('Review statistics and averages') }}</li>
                                    <li>{{ __('Photo upload support') }}</li>
                                    <li>{{ __('Helpful/unhelpful voting') }}</li>
                                    <li>{{ __('Review moderation') }}</li>
                                </ul>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-xl font-semibold mb-4">{{ __('Usage') }}</h3>
                            <div class="bg-gray-100 rounded-lg p-4">
                                <pre class="text-sm"><code>&lt;div webbloc="reviews" 
     data-config='{
        "item_id": "product-123",
        "allow_photos": true,
        "require_purchase": false,
        "show_statistics": true
     }'&gt;
&lt;/div&gt;</code></pre>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xl font-semibold mb-4">{{ __('Configuration Options') }}</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Option') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Type') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Default') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Description') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">item_id</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">string</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">required</td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ __('Unique identifier for the item being reviewed') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">allow_photos</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">boolean</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">false</td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ __('Allow photo uploads with reviews') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">show_statistics</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">boolean</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">true</td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ __('Show review statistics and averages') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Additional Components -->
                <section id="reactions" class="mb-12">
                    <div class="bg-white rounded-lg shadow-md p-8">
                        <h2 class="text-3xl font-bold mb-6">{{ __('Reactions Component') }}</h2>
                        <p class="text-gray-700 mb-4">{{ __('Simple like/dislike or emoji reactions for any content.') }}</p>
                        
                        <div class="mb-6">
                            <h3 class="text-xl font-semibold mb-4">{{ __('Usage') }}</h3>
                            <div class="bg-gray-100 rounded-lg p-4">
                                <pre class="text-sm"><code>&lt;div webbloc="reactions" 
     data-config='{
        "content_id": "article-456",
        "reaction_types": ["like", "love", "laugh", "angry"]
     }'&gt;
&lt;/div&gt;</code></pre>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="profiles" class="mb-12">
                    <div class="bg-white rounded-lg shadow-md p-8">
                        <h2 class="text-3xl font-bold mb-6">{{ __('Profiles Component') }}</h2>
                        <p class="text-gray-700 mb-4">{{ __('User profile display and management functionality.') }}</p>
                        
                        <div class="mb-6">
                            <h3 class="text-xl font-semibold mb-4">{{ __('Usage') }}</h3>
                            <div class="bg-gray-100 rounded-lg p-4">
                                <pre class="text-sm"><code>&lt;div webbloc="profiles" 
     data-config='{
        "user_id": "user-789",
        "show_activity": true,
        "editable": false
     }'&gt;
&lt;/div&gt;</code></pre>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="testimonials" class="mb-12">
                    <div class="bg-white rounded-lg shadow-md p-8">
                        <h2 class="text-3xl font-bold mb-6">{{ __('Testimonials Component') }}</h2>
                        <p class="text-gray-700 mb-4">{{ __('Display customer testimonials and success stories.') }}</p>
                        
                        <div class="mb-6">
                            <h3 class="text-xl font-semibold mb-4">{{ __('Usage') }}</h3>
                            <div class="bg-gray-100 rounded-lg p-4">
                                <pre class="text-sm"><code>&lt;div webbloc="testimonials" 
     data-config='{
        "category": "product-reviews",
        "limit": 5,
        "auto_rotate": true
     }'&gt;
&lt;/div&gt;</code></pre>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Customization Guide -->
                <section id="customization" class="mb-12">
                    <div class="bg-white rounded-lg shadow-md p-8">
                        <h2 class="text-3xl font-bold mb-6">{{ __('Customization') }}</h2>
                        
                        <div class="mb-8">
                            <h3 class="text-xl font-semibold mb-4">{{ __('CSS Customization') }}</h3>
                            <p class="text-gray-700 mb-4">{{ __('Override default styles by targeting WebBloc CSS classes:') }}</p>
                            
                            <div class="bg-gray-100 rounded-lg p-4 mb-4">
                                <pre class="text-sm"><code>/* Override button styles */
.webbloc-button {
    background-color: #your-color;
    border-radius: 8px;
}

/* Override form styles */
.webbloc-form input {
    border: 2px solid #your-border-color;
}

/* Override component container */
.webbloc-component {
    font-family: 'Your Font', sans-serif;
}</code></pre>
                            </div>
                        </div>

                        <div id="multilang" class="mb-8">
                            <h3 class="text-xl font-semibold mb-4">{{ __('Multi-language Support') }}</h3>
                            <p class="text-gray-700 mb-4">{{ __('All components automatically support multiple languages. Set the locale during initialization:') }}</p>
                            
                            <div class="bg-gray-100 rounded-lg p-4 mb-4">
                                <pre class="text-sm"><code>WebBloc.init({
    // ... other config
    locale: 'ar', // Arabic
    // or
    locale: 'en'  // English
});</code></pre>
                            </div>

                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">
                                            {{ __('Arabic language automatically enables RTL (Right-to-Left) layout for all components.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Update active navigation
    const navLinks = document.querySelectorAll('nav a[href^="#"]');
    const sections = document.querySelectorAll('section[id]');
    
    function updateActiveNav() {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (window.pageYOffset >= sectionTop - 200) {
                current = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('bg-blue-500', 'text-white');
            link.classList.add('bg-gray-200', 'text-gray-700');
            if (link.getAttribute('href') === '#' + current) {
                link.classList.remove('bg-gray-200', 'text-gray-700');
                link.classList.add('bg-blue-500', 'text-white');
            }
        });
    }
    
    window.addEventListener('scroll', updateActiveNav);
    updateActiveNav(); // Initial call
});
</script>
@endsection
```

### resources/views/docs/integration.blade.php

```php
@extends('layouts.docs')

@section('title', __('Integration Guide'))

@section('content')
<div class="min-h-screen bg-gray-50" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ __('Integration Guide') }}</h1>
            <p class="text-xl text-gray-600">{{ __('Step-by-step guide to integrate WebBloc components into your static website') }}</p>
        </div>

        <!-- Progress Steps -->
        <div class="mb-12">
            <div class="flex flex-wrap justify-center items-center space-x-4 mb-8" dir="ltr">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-semibold">1</div>
                    <span class="ml-2 text-sm font-medium">{{ __('Setup') }}</span>
                </div>
                <div class="w-8 h-0.5 bg-gray-300"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-semibold">2</div>
                    <span class="ml-2 text-sm font-medium">{{ __('Configure') }}</span>
                </div>
                <div class="w-8 h-0.5 bg-gray-300"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-semibold">3</div>
                    <span class="ml-2 text-sm font-medium">{{ __('Deploy') }}</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="sticky top-8">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold mb-4">{{ __('Quick Navigation') }}</h3>
                        <ul class="space-y-2">
                            <li><a href="#prerequisites" class="text-blue-600 hover:text-blue-800">{{ __('Prerequisites') }}</a></li>
                            <li><a href="#step1" class="text-blue-600 hover:text-blue-800">{{ __('Step 1: Setup') }}</a></li>
                            <li><a href="#step2" class="text-blue-600 hover:text-blue-800">{{ __('Step 2: Configuration') }}</a></li>
                            <li><a href="#step3" class="text-blue-600 hover:text-blue-800">{{ __('Step 3: Implementation') }}</a></li>
                            <li><a href="#advanced" class="text-blue-600 hover:text-blue-800">{{ __('Advanced Usage') }}</a></li>
                            <li><a href="#troubleshooting" class="text-blue-600 hover:text-blue-800">{{ __('Troubleshooting') }}</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-3">
                <!-- Prerequisites -->
                <section id="prerequisites" class="mb-12">
                    <div class="bg-white rounded-lg shadow-md p-8">
                        <h2 class="text-3xl font-bold mb-6">{{ __('Prerequisites') }}</h2>
                        
                        <div class="mb-6">
                            <h3 class="text-xl font-semibold mb-4">{{ __('Before You Start') }}</h3>
                            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                                <h4 class="font-semibold text-blue-800 mb-2">{{ __('Requirements') }}</h4>
                                <ul class="list-disc list-inside text-blue-700 space-y-1">
                                    <li>{{ __('A static website (HTML, Jekyll, Hugo, Gatsby, etc.)') }}</li>
                                    <li>{{ __('Valid WebBloc account and API keys') }}</li>
                                    <li>{{ __('Basic knowledge of HTML and JavaScript') }}</li>
                                    <li>{{ __('HTTPS enabled website (required for security)') }}</li>
                                </ul>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-xl font-semibold mb-4">{{ __('Getting Your API Keys') }}</h3>
                            <p class="text-gray-700 mb-4">{{ __('If you don\'t have API keys yet, follow these steps:') }}</p>
                            
                            <ol class="list-decimal list-inside space-y-2 text-gray-700">
                                <li>{{ __('Visit') }} <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-800">{{ __('WebBloc Registration') }}</a></li>
                                <li>{{ __('Create your account and verify your email') }}</li>
                                <li>{{ __('Add your website in the dashboard') }}</li>
                                <li>{{ __('Copy your Public and Secret API keys') }}</li>
                            </ol>
                        </div>
                    </div>
                </section>

                <!-- Step 1: Setup -->
                <section id="step1" class="mb-12">
                    <div class="bg-white rounded-lg shadow-md p-8">
                        <h2 class="text-3xl font-bold mb-6">{{ __('Step 1: Setup WebBloc CDN') }}</h2>
                        
                        <div class="mb-8">
                            <h3 class="text-xl font-semibold mb-4">{{ __('1.1 Include WebBloc Assets') }}</h3>
                            <p class="text-gray-700 mb-4">{{ __('Add the following script and stylesheet tags to your HTML head section:') }}</p>
                            
                            <div class="bg-gray-100 rounded-lg p-4 mb-4">
                                <pre class="text-sm overflow-x-auto"><code>&lt;!-- WebBloc CSS --&gt;
&lt;link href="https://cdn.webbloc.com/css/webbloc.min.css" rel="stylesheet"&gt;

&lt;!-- WebBloc JavaScript --&gt;
&lt;script src="https://cdn.webbloc.com/js/webbloc.min.js"&gt;&lt;/script&gt;

&lt;!-- Alpine.js (required for WebBloc components) --&gt;
&lt;script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"&gt;&lt;/script&gt;</code></pre>
                            </div>

                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">
                                            {{ __('Make sure to include these assets in the head of every page where you want to use WebBloc components.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-xl font-semibold mb-4">{{ __('1.2 Initialize WebBloc') }}</h3>
                            <p class="text-gray-700 mb-4">{{ __('Add the initialization script before the closing body tag:') }}</p>
                            
                            <div class="bg-gray-100 rounded-lg p-4 mb-4">
                                <pre class="text-sm overflow-x-auto"><code>&lt;script&gt;
document.addEventListener('DOMContentLoaded', function() {
    WebBloc.init({
        apiUrl: 'https://api.webbloc.com',
        publicKey: 'your_public_key_here',
        secretKey: 'your_secret_key_here', // Only for server-side
        websiteId: 'your_website_uuid_here',
        locale: 'en', // 'en' for English, 'ar' for Arabic
        debug: false // Set to true for development
    });
});
&lt;/script&gt;</code></pre>
                            </div>

                            <div class="bg-red-50 border-l-4 border-red-400 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-red-700">
                                            <strong>{{ __('Security Warning:') }}</strong> {{ __('Never expose your secret key in client-side code. Only use it for server-side operations.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Step 2: Configuration -->
                <section id="step2" class="mb-12">
                    <div class="bg-white rounded-lg shadow-md p-8">
                        <h2 class="text-3xl font-bold mb-6">{{ __('Step 2: Configuration Options') }}</h2>
                        
                        <div class="mb-8">
                            <h3 class="text-xl font-semibold mb-4">{{ __('2.1 Basic Configuration') }}</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Parameter') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Required') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Description') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">apiUrl</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">✓</td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ __('WebBloc API endpoint URL') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">publicKey</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">✓</td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ __('Your website\'s public API key') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">websiteId</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">✓</td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ __('Your website\'s UUID from the dashboard') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">locale</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">—</td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ __('Language code (en, ar). Defaults to en') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">debug</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">—</td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ __('Enable debug mode for development') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-xl font-semibold mb-4">{{ __('2.2 Advanced Configuration') }}</h3>
                            <p class="text-gray-700 mb-4">{{ __('For advanced use cases, you can customize WebBloc behavior:') }}</p>
                            
                            <div class="bg-gray-100 rounded-lg p-4 mb-4">
                                <pre class="text-sm overflow-x-auto"><code>WebBloc.init({
    // Basic config...
    apiUrl: 'https://api.webbloc.com',
    publicKey: 'your_public_key',
    websiteId: 'your_website_uuid',
    
    // Advanced options
    theme: 'light', // 'light' or 'dark'
    rtl: false, // Auto-detected from locale
    loadingTimeout: 5000, // API request timeout (ms)
    retryAttempts: 3, // Number of retry attempts
    
    // Event callbacks
    onReady: function() {
        console.log('WebBloc initialized successfully');
    },
    onError: function(error) {
        console.error('WebBloc error:', error);
    },
    
    // Custom API endpoints (if using self-hosted)
    endpoints: {
        auth: '/custom/auth',
        comments: '/custom/comments',
        reviews: '/custom/reviews'
    }
});</code></pre>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Step 3: Implementation -->
                <section id="step3" class="mb-12">
                    <div class="bg-white rounded-lg shadow-md p-8">
                        <h2 class="text-3xl font-bold mb-6">{{ __('Step 3: Adding Components') }}</h2>
                        
                        <div class="mb-8">
                            <h3 class="text-xl font-semibold mb-4">{{ __('3.1 Basic Component Usage') }}</h3>
                            <p class="text-gray-700 mb-4">{{ __('Add components to your HTML using the webbloc attribute:') }}</p>
                            
                            <div class="space-y-6">
                                <div>
                                    <h4 class="font-semibold mb-2">{{ __('Authentication Component') }}</h4>
                                    <div class="bg-gray-100 rounded-lg p-4">
                                        <pre class="text-sm"><code>&lt;div webbloc="auth"&gt;&lt;/div&gt;</code></pre>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="font-semibold mb-2">{{ __('Comments Component') }}</h4>
                                    <div class="bg-gray-100 rounded-lg p-4">
                                        <pre class="text-sm"><code>&lt;div webbloc="comments" data-page-id="article-123"&gt;&lt;/div&gt;</code></pre>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="font-semibold mb-2">{{ __('Reviews Component') }}</h4>
                                    <div class="bg-gray-100 rounded-lg p-4">
                                        <pre class="text-sm"><code>&lt;div webbloc="reviews" data-item-id="product-456"&gt;&lt;/div&gt;</code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-xl font-semibold mb-4">{{ __('3.2 Component Configuration') }}</h3>
                            <p class="text-gray-700 mb-4">{{ __('Customize component behavior using data attributes or config objects:') }}</p>
                            
                            <div class="bg-gray-100 rounded-lg p-4 mb-4">
                                <pre class="text-sm overflow-x-auto"><code>&lt;!-- Using data attributes --&gt;
&lt;div webbloc="comments" 
     data-page-id="blog-post-1"
     data-allow-anonymous="false"
     data-max-depth="3"
     data-moderation="true"&gt;
&lt;/div&gt;

&lt;!-- Using config object --&gt;
&lt;div webbloc="reviews" 
     data-config='{
        "item_id": "product-123",
        "allow_photos": true,
        "show_statistics": true,
        "require_purchase": false
     }'&gt;
&lt;/div&gt;</code></pre>
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-xl font-semibold mb-4">{{ __('3.3 Multiple Components') }}</h3>
                            <p class="text-gray-700 mb-4">{{ __('You can use multiple components on the same page:') }}</p>
                            
                            <div class="bg-gray-100 rounded-lg p-4 mb-4">
                                <pre class="text-sm overflow-x-auto"><code>&lt;!-- Article page example --&gt;
&lt;article&gt;
    &lt;h1&gt;{{ __('My Blog Post') }}&lt;/h1&gt;
    &lt;p&gt;{{ __('Article content...') }}&lt;/p&gt;
    
    &lt;!-- Reactions for the article --&gt;
    &lt;div webbloc="reactions" data-content-id="article-123"&gt;&lt;/div&gt;
    
    &lt;!-- Comments section --&gt;
    &lt;div webbloc="comments" data-page-id="article-123"&gt;&lt;/div&gt;
&lt;/article&gt;

&lt;!-- Authentication in sidebar --&gt;
&lt;aside&gt;
    &lt;div webbloc="auth" data-style="modal"&gt;&lt;/div&gt;
&lt;/aside&gt;</code></pre>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Advanced Usage -->
                <section id="advanced" class="mb-12">
                    <div class="bg-white rounded-lg shadow-md p-8">
                        <h2 class="text-3xl font-bold mb-6">{{ __('Advanced Usage') }}</h2>
                        
                        <div class="mb-8">
                            <h3 class="text-xl font-semibold mb-4">{{ __('Dynamic Component Loading') }}</h3>
                            <p class="text-gray-700 mb-4">{{ __('Load components dynamically using JavaScript:') }}</p>
                            
                            <div class="bg-gray-100 rounded-lg p-4 mb-4">
                                <pre class="text-sm overflow-x-auto"><code>// Load a component programmatically
WebBloc.loadComponent('comments', {
    container: '#comments-section',
    config: {
        page_id: 'dynamic-page-123',
        allow_anonymous: false
    }
});

// Remove a component
WebBloc.removeComponent('#comments-section');

// Reload a component with new config
WebBloc.reloadComponent('#comments-section', {
    page_id: 'updated-page-id'
});</code></pre>
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-xl font-semibold mb-4">{{ __('Event Handling') }}</h3>
                            <p class="text-gray-700 mb-4">{{ __('Listen to component events:') }}</p>
                            
                            <div class="bg-gray-100 rounded-lg p-4 mb-4">
                                <pre class="text-sm overflow-x-auto"><code>// Listen to authentication events
document.addEventListener('webbloc:auth:login', function(event) {
    console.log('User logged in:', event.detail.user);
    // Update your UI accordingly
});

document.addEventListener('webbloc:auth:logout', function(event) {
    console.log('User logged out');
    // Handle logout
});

// Listen to comment events
document.addEventListener('webbloc:comments:added', function(event) {
    console.log('New comment added:', event.detail.comment);
});

// Listen to review events
document.addEventListener('webbloc:reviews:submitted', function(event) {
    console.log('Review submitted:', event.detail.review);
});</code></pre>
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-xl font-semibold mb-4">{{ __('Custom Styling') }}</h3>
                            <p class="text-gray-700 mb-4">{{ __('Override default styles to match your design:') }}</p>
                            
                            <div class="bg-gray-100 rounded-lg p-4 mb-4">
                                <pre class="text-sm overflow-x-auto"><code>/* Custom CSS for WebBloc components */
.webbloc-component {
    font-family: 'Your Font Family', sans-serif;
}

.webbloc-button {
    background: linear-gradient(45deg, #your-color-1, #your-color-2);
    border-radius: 12px;
    font-weight: 600;
}

.webbloc-input {
    border: 2px solid #your-border-color;
    border-radius: 8px;
    padding: 12px 16px;
}

.webbloc-comment {
    border-left: 3px solid #your-accent-color;
    background: #your-background-color;
}

/* Dark theme customization */
[data-theme="dark"] .webbloc-component {
    background: #1a1a1a;
    color: #ffffff;
}</code></pre>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Troubleshooting -->
                <section id="troubleshooting" class="mb-12">
                    <div class="bg-white rounded-lg shadow-md p-8">
                        <h2 class="text-3xl font-bold mb-6">{{ __('Troubleshooting') }}</h2>
                        
                        <div class="space-y-8">
                            <div>
                                <h3 class="text-xl font-semibold mb-4">{{ __('Common Issues') }}</h3>
                                
                                <div class="space-y-6">
                                    <div class="border-l-4 border-red-400 pl-4">
                                        <h4 class="font-semibold text-red-800 mb-2">{{ __('Components not loading') }}</h4>
                                        <p class="text-gray-700 mb-2"><strong>{{ __('Cause:') }}</strong> {{ __('Missing or incorrect API keys') }}</p>
                                        <p class="text-gray-700 mb-2"><strong>{{ __('Solution:') }}</strong></p>
                                        <ul class="list-disc list-inside text-gray-600 ml-4">
                                            <li>{{ __('Verify your API keys in the dashboard') }}</li>
                                            <li>{{ __('Check browser console for error messages') }}</li>
                                            <li>{{ __('Ensure your website domain is registered') }}</li>
                                        </ul>
                                    </div>

                                    <div class="border-l-4 border-yellow-400 pl-4">
                                        <h4 class="font-semibold text-yellow-800 mb-2">{{ __('CORS errors') }}</h4>
                                        <p class="text-gray-700 mb-2"><strong>{{ __('Cause:') }}</strong> {{ __('Domain not whitelisted or HTTPS required') }}</p>
                                        <p class="text-gray-700 mb-2"><strong>{{ __('Solution:') }}</strong></p>
                                        <ul class="list-disc list-inside text-gray-600 ml-4">
                                            <li>{{ __('Add your domain to allowed origins in dashboard') }}</li>
                                            <li>{{ __('Use HTTPS for your website') }}</li>
                                            <li>{{ __('Check subdomain configuration') }}</li>
                                        </ul>
                                    </div>

                                    <div class="border-l-4 border-blue-400 pl-4">
                                        <h4 class="font-semibold text-blue-800 mb-2">{{ __('Styling conflicts') }}</h4>
                                        <p class="text-gray-700 mb-2"><strong>{{ __('Cause:') }}</strong> {{ __('CSS conflicts with existing styles') }}</p>
                                        <p class="text-gray-700 mb-2"><strong>{{ __('Solution:') }}</strong></p>
                                        <ul class="list-disc list-inside text-gray-600 ml-4">
                                            <li>{{ __('Use CSS specificity to override styles') }}</li>
                                            <li>{{ __('Load WebBloc CSS after your main stylesheet') }}</li>
                                            <li>{{ __('Use !important sparingly for critical overrides') }}</li>
                                        </ul>
                                    </div>

                                    <div class="border-l-4 border-green-400 pl-4">
                                        <h4 class="font-semibold text-green-800 mb-2">{{ __('Rate limiting') }}</h4>
                                        <p class="text-gray-700 mb-2"><strong>{{ __('Cause:') }}</strong> {{ __('Too many API requests') }}</p>
                                        <p class="text-gray-700 mb-2"><strong>{{ __('Solution:') }}</strong></p>
                                        <ul class="list-disc list-inside text-gray-600 ml-4">
                                            <li>{{ __('Implement request caching') }}</li>
                                            <li>{{ __('Reduce component refresh frequency') }}</li>
                                            <li>{{ __('Upgrade to higher tier plan if needed') }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-xl font-semibold mb-4">{{ __('Debug Mode') }}</h3>
                                <p class="text-gray-700 mb-4">{{ __('Enable debug mode for detailed logging:') }}</p>
                                
                                <div class="bg-gray-100 rounded-lg p-4 mb-4">
                                    <pre class="text-sm"><code>WebBloc.init({
    // ... other config
    debug: true
});

// Check debug information
console.log(WebBloc.getDebugInfo());

// Test component loading
WebBloc.testComponent('comments');</code></pre>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-xl font-semibold mb-4">{{ __('Performance Tips') }}</h3>
                                <ul class="list-disc list-inside space-y-2 text-gray-700">
                                    <li>{{ __('Load WebBloc assets asynchronously when possible') }}</li>
                                    <li>{{ __('Use lazy loading for components below the fold') }}</li>
                                    <li>{{ __('Implement proper caching headers') }}</li>
                                    <li>{{ __('Minimize the number of components per page') }}</li>
                                    <li>{{ __('Use CDN for faster asset delivery') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Support -->
                <section class="mb-12">
                    <div class="bg-white rounded-lg shadow-md p-8">
                        <h2 class="text-3xl font-bold mb-6">{{ __('Getting Help') }}</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="border rounded-lg p-6">
                                <h3 class="text-xl font-semibold mb-4">{{ __('Documentation') }}</h3>
                                <p class="text-gray-700 mb-4">{{ __('Browse our comprehensive documentation and API reference.') }}</p>
                                <a href="{{ route('docs.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">{{ __('View Documentation →') }}</a>
                            </div>

                            <div class="border rounded-lg p-6">
                                <h3 class="text-xl font-semibold mb-4">{{ __('Contact Support') }}</h3>
                                <p class="text-gray-700 mb-4">{{ __('Get help from our technical support team.') }}</p>
                                <a href="{{ route('contact') }}" class="text-blue-600 hover:text-blue-800 font-medium">{{ __('Contact Us →') }}</a>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Copy to clipboard functionality
    document.querySelectorAll('pre code').forEach(block => {
        const button = document.createElement('button');
        button.className = 'absolute top-2 right-2 px-2 py-1 text-xs bg-gray-500 text-white rounded opacity-0 group-hover:opacity-100 transition-opacity';
        button.textContent = '{{ __("Copy") }}';
        
        const container = block.closest('div');
        container.style.position = 'relative';
        container.classList.add('group');
        container.appendChild(button);
        
        button.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(block.textContent);
                button.textContent = '{{ __("Copied!") }}';
                setTimeout(() => {
                    button.textContent = '{{ __("Copy") }}';
                }, 2000);
            } catch (err) {
                console.error('Failed to copy: ', err);
            }
        });
    });

    // Smooth scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});
</script>
@endsection
```
