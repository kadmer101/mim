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

## 10. Public Landing Views

### resources/views/welcome.blade.php

```php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ __('WebBloc - Dynamic Components for Static Websites') }}</title>
    <meta name="description" content="{{ __('Transform your static website with dynamic components. Add authentication, comments, reviews, and more with just a few lines of code.') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="antialiased bg-white" x-data="landingPage()">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-2xl font-bold text-blue-600">WebBloc</h1>
                    </div>
                    <div class="hidden md:block ml-10">
                        <div class="flex items-baseline space-x-4">
                            <a href="#features" class="text-gray-600 hover:text-blue-600 px-3 py-2 text-sm font-medium">{{ __('Features') }}</a>
                            <a href="#pricing" class="text-gray-600 hover:text-blue-600 px-3 py-2 text-sm font-medium">{{ __('Pricing') }}</a>
                            <a href="#docs" class="text-gray-600 hover:text-blue-600 px-3 py-2 text-sm font-medium">{{ __('Documentation') }}</a>
                            <a href="{{ route('contact') }}" class="text-gray-600 hover:text-blue-600 px-3 py-2 text-sm font-medium">{{ __('Contact') }}</a>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Language Switcher -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-gray-600 hover:text-blue-600">
                            <span class="text-sm font-medium">{{ app()->getLocale() == 'ar' ? 'العربية' : 'English' }}</span>
                            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-32 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5">
                            <div class="py-1">
                                <a href="{{ route('locale.switch', 'en') }}" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ app()->getLocale() == 'en' ? 'bg-gray-50' : '' }}">
                                    English
                                </a>
                                <a href="{{ route('locale.switch', 'ar') }}" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ app()->getLocale() == 'ar' ? 'bg-gray-50' : '' }}">
                                    العربية
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    @auth
                        <a href="{{ route('dashboard') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">
                            {{ __('Dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-blue-600 px-3 py-2 text-sm font-medium">{{ __('Sign In') }}</a>
                        <a href="{{ route('register') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">
                            {{ __('Get Started') }}
                        </a>
                    @endauth
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-600 hover:text-blue-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div x-show="mobileMenuOpen" x-transition class="md:hidden bg-white shadow-lg">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="#features" class="block px-3 py-2 text-base font-medium text-gray-600 hover:text-blue-600">{{ __('Features') }}</a>
                <a href="#pricing" class="block px-3 py-2 text-base font-medium text-gray-600 hover:text-blue-600">{{ __('Pricing') }}</a>
                <a href="#docs" class="block px-3 py-2 text-base font-medium text-gray-600 hover:text-blue-600">{{ __('Documentation') }}</a>
                <a href="{{ route('contact') }}" class="block px-3 py-2 text-base font-medium text-gray-600 hover:text-blue-600">{{ __('Contact') }}</a>
                @guest
                    <a href="{{ route('login') }}" class="block px-3 py-2 text-base font-medium text-gray-600 hover:text-blue-600">{{ __('Sign In') }}</a>
                    <a href="{{ route('register') }}" class="block px-3 py-2 text-base font-medium bg-blue-600 text-white rounded-md hover:bg-blue-700">{{ __('Get Started') }}</a>
                @endguest
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-purple-700 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold mb-6" x-data x-init="$el.style.opacity = 0; $el.style.transform = 'translateY(20px)'; setTimeout(() => { $el.style.transition = 'all 0.8s ease'; $el.style.opacity = 1; $el.style.transform = 'translateY(0)'; }, 100)">
                    {{ __('Transform Your Static Website') }}
                </h1>
                <p class="text-xl md:text-2xl mb-8 opacity-90" x-data x-init="$el.style.opacity = 0; setTimeout(() => { $el.style.transition = 'opacity 0.8s ease'; $el.style.opacity = 0.9; }, 300)">
                    {{ __('Add dynamic components like authentication, comments, and reviews with just a few lines of code') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center" x-data x-init="$el.style.opacity = 0; setTimeout(() => { $el.style.transition = 'opacity 0.8s ease'; $el.style.opacity = 1; }, 500)">
                    <a href="{{ route('register') }}" class="bg-white text-blue-600 px-8 py-3 rounded-lg text-lg font-semibold hover:bg-gray-100 transition-colors">
                        {{ __('Start Free Trial') }}
                    </a>
                    <a href="#demo" class="border-2 border-white text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors">
                        {{ __('See Demo') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ __('Powerful Features') }}</h2>
                <p class="text-xl text-gray-600">{{ __('Everything you need to make your static website dynamic') }}</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-lg transition-shadow" x-intersect="$el.classList.add('animate-fade-in-up')">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">{{ __('Secure Authentication') }}</h3>
                    <p class="text-gray-600">{{ __('Complete user authentication system with registration, login, password reset, and social authentication.') }}</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-lg transition-shadow" x-intersect="$el.classList.add('animate-fade-in-up')">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">{{ __('Interactive Comments') }}</h3>
                    <p class="text-gray-600">{{ __('Engage your audience with threaded comments, replies, moderation, and real-time updates.') }}</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-lg transition-shadow" x-intersect="$el.classList.add('animate-fade-in-up')">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">{{ __('Review System') }}</h3>
                    <p class="text-gray-600">{{ __('Collect star ratings and detailed reviews with photo uploads and helpful voting.') }}</p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-lg transition-shadow" x-intersect="$el.classList.add('animate-fade-in-up')">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">{{ __('Reactions & Social') }}</h3>
                    <p class="text-gray-600">{{ __('Add like buttons, emoji reactions, and social sharing to increase engagement.') }}</p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-lg transition-shadow" x-intersect="$el.classList.add('animate-fade-in-up')">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">{{ __('Multi-language') }}</h3>
                    <p class="text-gray-600">{{ __('Built-in support for multiple languages with RTL layout for Arabic and other languages.') }}</p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-lg transition-shadow" x-intersect="$el.classList.add('animate-fade-in-up')">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">{{ __('Lightning Fast') }}</h3>
                    <p class="text-gray-600">{{ __('Optimized for performance with CDN delivery, caching, and minimal impact on your site speed.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Demo Section -->
    <section id="demo" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ __('See It In Action') }}</h2>
                <p class="text-xl text-gray-600">{{ __('Simple integration that works with any static site generator') }}</p>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <!-- Code Example -->
                <div>
                    <h3 class="text-2xl font-semibold mb-6">{{ __('Add Components in Minutes') }}</h3>
                    <div class="bg-gray-900 text-green-400 p-6 rounded-lg font-mono text-sm overflow-x-auto">
                        <div class="mb-4">
                            <span class="text-gray-500">// {{ __('1. Include WebBloc') }}</span><br>
                            <span class="text-blue-400">&lt;script</span> <span class="text-yellow-300">src=</span><span class="text-green-300">"https://cdn.webbloc.com/js/webbloc.min.js"</span><span class="text-blue-400">&gt;&lt;/script&gt;</span>
                        </div>
                        <div class="mb-4">
                            <span class="text-gray-500">// {{ __('2. Initialize') }}</span><br>
                            <span class="text-purple-400">WebBloc</span>.<span class="text-yellow-300">init</span>({<br>
                            &nbsp;&nbsp;<span class="text-red-400">apiUrl:</span> <span class="text-green-300">'https://api.webbloc.com'</span>,<br>
                            &nbsp;&nbsp;<span class="text-red-400">publicKey:</span> <span class="text-green-300">'your_public_key'</span><br>
                            });
                        </div>
                        <div>
                            <span class="text-gray-500">// {{ __('3. Add Components') }}</span><br>
                            <span class="text-blue-400">&lt;div</span> <span class="text-yellow-300">webbloc=</span><span class="text-green-300">"comments"</span> <span class="text-yellow-300">data-page-id=</span><span class="text-green-300">"blog-post-1"</span><span class="text-blue-400">&gt;&lt;/div&gt;</span><br>
                            <span class="text-blue-400">&lt;div</span> <span class="text-yellow-300">webbloc=</span><span class="text-green-300">"auth"</span><span class="text-blue-400">&gt;&lt;/div&gt;</span>
                        </div>
                    </div>
                </div>
                
                <!-- Live Demo -->
                <div>
                    <h3 class="text-2xl font-semibold mb-6">{{ __('Live Example') }}</h3>
                    <div class="bg-gray-50 border-2 border-gray-200 rounded-lg p-6">
                        <!-- Simulated Browser -->
                        <div class="bg-white rounded-md shadow-md">
                            <div class="bg-gray-100 px-4 py-2 rounded-t-md border-b">
                                <div class="flex items-center space-x-2">
                                    <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                                    <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
                                    <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                                    <div class="flex-1 text-center text-gray-600 text-sm">{{ __('yourwebsite.com') }}</div>
                                </div>
                            </div>
                            <div class="p-6">
                                <h4 class="text-lg font-semibold mb-4">{{ __('Sample Blog Post') }}</h4>
                                <p class="text-gray-600 mb-6">{{ __('This is where your content goes. Below you can see the WebBloc components in action.') }}</p>
                                
                                <!-- Simulated Auth Component -->
                                <div class="border rounded-lg p-4 mb-4 bg-blue-50">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">{{ __('Authentication Component') }}</span>
                                        <button class="bg-blue-600 text-white px-3 py-1 rounded text-sm">{{ __('Sign In') }}</button>
                                    </div>
                                </div>
                                
                                <!-- Simulated Comments -->
                                <div class="border rounded-lg p-4 bg-gray-50">
                                    <h5 class="font-semibold mb-3">{{ __('Comments (2)') }}</h5>
                                    <div class="space-y-3">
                                        <div class="bg-white p-3 rounded border-l-4 border-blue-400">
                                            <div class="flex items-center mb-2">
                                                <div class="w-6 h-6 bg-gray-300 rounded-full mr-2"></div>
                                                <span class="text-sm font-medium">{{ __('John Doe') }}</span>
                                                <span class="text-xs text-gray-500 ml-2">{{ __('2 hours ago') }}</span>
                                            </div>
                                            <p class="text-sm text-gray-700">{{ __('Great post! This WebBloc system looks amazing.') }}</p>
                                        </div>
                                        <div class="bg-white p-3 rounded border-l-4 border-green-400 ml-4">
                                            <div class="flex items-center mb-2">
                                                <div class="w-6 h-6 bg-gray-300 rounded-full mr-2"></div>
                                                <span class="text-sm font-medium">{{ __('Jane Smith') }}</span>
                                                <span class="text-xs text-gray-500 ml-2">{{ __('1 hour ago') }}</span>
                                            </div>
                                            <p class="text-sm text-gray-700">{{ __('I agree! The integration is so simple.') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Preview -->
    <section id="pricing" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ __('Simple, Transparent Pricing') }}</h2>
                <p class="text-xl text-gray-600">{{ __('Start free, scale as you grow') }}</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Free Plan -->
                <div class="bg-white rounded-lg shadow-md p-8 border-2 border-gray-200">
                    <div class="text-center">
                        <h3 class="text-2xl font-semibold mb-4">{{ __('Free') }}</h3>
                        <div class="text-4xl font-bold mb-6">$0<span class="text-lg font-normal text-gray-600">/mo</span></div>
                        <ul class="text-left space-y-3 mb-8">
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('1 Website') }}
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('1,000 API Calls/month') }}
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('Basic Components') }}
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('Community Support') }}
                            </li>
                        </ul>
                        <a href="{{ route('register') }}" class="block w-full bg-gray-600 text-white py-3 rounded-lg font-semibold hover:bg-gray-700 transition-colors">
                            {{ __('Get Started') }}
                        </a>
                    </div>
                </div>

                <!-- Pro Plan -->
                <div class="bg-white rounded-lg shadow-md p-8 border-2 border-blue-500 relative">
                    <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                        <span class="bg-blue-500 text-white px-4 py-1 rounded-full text-sm font-semibold">{{ __('Popular') }}</span>
                    </div>
                    <div class="text-center">
                        <h3 class="text-2xl font-semibold mb-4">{{ __('Pro') }}</h3>
                        <div class="text-4xl font-bold mb-6">$29<span class="text-lg font-normal text-gray-600">/mo</span></div>
                        <ul class="text-left space-y-3 mb-8">
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('10 Websites') }}
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('100,000 API Calls/month') }}
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('All Components') }}
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('Priority Support') }}
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('Custom Styling') }}
                            </li>
                        </ul>
                        <a href="{{ route('register') }}" class="block w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                            {{ __('Start Pro Trial') }}
                        </a>
                    </div>
                </div>

                <!-- Enterprise Plan -->
                <div class="bg-white rounded-lg shadow-md p-8 border-2 border-gray-200">
                    <div class="text-center">
                        <h3 class="text-2xl font-semibold mb-4">{{ __('Enterprise') }}</h3>
                        <div class="text-4xl font-bold mb-6">{{ __('Custom') }}</div>
                        <ul class="text-left space-y-3 mb-8">
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('Unlimited Websites') }}
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('Unlimited API Calls') }}
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('Custom Components') }}
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('Dedicated Support') }}
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('SLA Guarantee') }}
                            </li>
                        </ul>
                        <a href="{{ route('contact') }}" class="block w-full bg-gray-600 text-white py-3 rounded-lg font-semibold hover:bg-gray-700 transition-colors">
                            {{ __('Contact Sales') }}
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-12">
                <a href="{{ route('pricing') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                    {{ __('View detailed pricing →') }}
                </a>
            </div>
        </div>
    </section>

    <!-- Documentation Section -->
    <section id="docs" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ __('Developer-Friendly') }}</h2>
                <p class="text-xl text-gray-600">{{ __('Comprehensive documentation and examples to get you started quickly') }}</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">{{ __('Complete Documentation') }}</h3>
                    <p class="text-gray-600 mb-6">{{ __('Step-by-step guides, API reference, and integration examples for all components.') }}</p>
                    <a href="{{ route('docs.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">{{ __('Browse Docs →') }}</a>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">{{ __('Code Examples') }}</h3>
                    <p class="text-gray-600 mb-6">{{ __('Ready-to-use code snippets for popular static site generators like Jekyll, Hugo, and Gatsby.') }}</p>
                    <a href="{{ route('docs.integration') }}" class="text-blue-600 hover:text-blue-800 font-medium">{{ __('View Examples →') }}</a>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 109.5 9.75A9.75 9.75 0 0012 2.25z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">{{ __('API Reference') }}</h3>
                    <p class="text-gray-600 mb-6">{{ __('Comprehensive API documentation with request/response examples and authentication details.') }}</p>
                    <a href="{{ route('docs.components') }}" class="text-blue-600 hover:text-blue-800 font-medium">{{ __('API Docs →') }}</a>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-blue-600 to-purple-700 text-white">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">{{ __('Ready to Transform Your Website?') }}</h2>
            <p class="text-xl mb-8 opacity-90">{{ __('Join thousands of developers who are already using WebBloc to add dynamic features to their static websites.') }}</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" class="bg-white text-blue-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-100 transition-colors">
                    {{ __('Start Your Free Trial') }}
                </a>
                <a href="{{ route('docs.index') }}" class="border-2 border-white text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors">
                    {{ __('View Documentation') }}
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-2xl font-bold mb-4">WebBloc</h3>
                    <p class="text-gray-400 mb-4">{{ __('Transform your static website with dynamic components. Simple integration, powerful features.') }}</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.097.118.112.221.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.746-1.378l-.747 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.624 0 11.99-5.367 11.99-11.989C24.007 5.367 18.641.001 12.017.001z"/></svg>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">{{ __('Product') }}</h4>
                    <ul class="space-y-2">
                        <li><a href="#features" class="text-gray-400 hover:text-white">{{ __('Features') }}</a></li>
                        <li><a href="{{ route('pricing') }}" class="text-gray-400 hover:text-white">{{ __('Pricing') }}</a></li>
                        <li><a href="{{ route('docs.index') }}" class="text-gray-400 hover:text-white">{{ __('Documentation') }}</a></li>
                        <li><a href="{{ route('docs.components') }}" class="text-gray-400 hover:text-white">{{ __('API Reference') }}</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">{{ __('Company') }}</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">{{ __('About') }}</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">{{ __('Blog') }}</a></li>
                        <li><a href="{{ route('contact') }}" class="text-gray-400 hover:text-white">{{ __('Contact') }}</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">{{ __('Careers') }}</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">{{ __('Support') }}</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">{{ __('Help Center') }}</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">{{ __('Status') }}</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">{{ __('Privacy Policy') }}</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">{{ __('Terms of Service') }}</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-12 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="text-gray-400 text-sm">
                        © {{ date('Y') }} WebBloc. {{ __('All rights reserved.') }}
                    </div>
                    <div class="text-gray-400 text-sm mt-4 md:mt-0">
                        {{ __('Made with ❤️ for developers') }}
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
    function landingPage() {
        return {
            mobileMenuOpen: false,
            
            init() {
                // Add smooth scrolling behavior
                this.setupSmoothScrolling();
                
                // Add intersection observer for animations
                this.setupAnimations();
            },
            
            setupSmoothScrolling() {
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
            },
            
            setupAnimations() {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('animate-fade-in-up');
                        }
                    });
                }, {
                    threshold: 0.1,
                    rootMargin: '0px 0px -50px 0px'
                });
                
                // Observe feature cards
                document.querySelectorAll('.bg-white.p-8').forEach(card => {
                    observer.observe(card);
                });
            }
        }
    }
    </script>

    <style>
    @keyframes fade-in-up {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-fade-in-up {
        animation: fade-in-up 0.6s ease-out forwards;
    }
    
    /* Smooth transitions */
    * {
        transition: background-color 0.3s ease, color 0.3s ease, transform 0.3s ease;
    }
    
    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    ::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }
    
