Now I have analyzed all the project documentation and fully understand the WebBloc system architecture. Based on my comprehensive analysis, I'll provide all the requested blade views with full codes. Let me create the complete set of blade views for the public landing pages, email templates, and error pages.

## Complete Analysis Understanding

After analyzing all project documentation, I understand this is a comprehensive Laravel-based WebBloc system that:

1. **Core Purpose**: Provides dynamic web components as APIs for static websites
2. **Architecture**: Multi-tenant, UUID-based routing, MySQL + SQLite databases
3. **Tech Stack**: Laravel 11+, Alpine.js, Tailwind CSS, SweetAlert2, multi-language (EN/AR)
4. **Components**: Authentication, Comments, Reviews, Reactions, Profiles, Testimonials
5. **Security**: CSRF protection, role-based access, API keys (public/secret)
6. **Features**: RTL support, responsive design, social authentication, comprehensive dashboard

## All Requested Blade Views - Full Codes

### 10. Public Landing Views

#### resources/views/welcome.blade.php
```php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('WebBloc - Dynamic Components for Static Websites') }}</title>
    
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
<body class="antialiased bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800" 
      x-data="{ 
          locale: '{{ app()->getLocale() }}', 
          darkMode: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
          stats: {
              websites: 0,
              components: 0,
              requests: 0,
              users: 0
          }
      }"
      x-init="
          $watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'));
          document.documentElement.classList.toggle('dark', darkMode);
          
          // Load stats
          fetch('/api/public/stats')
              .then(response => response.json())
              .then(data => {
                  if (data.success) {
                      stats = data.data;
                  }
              })
              .catch(error => console.log('Stats loading failed'));
      "
      :class="{ 'dark': darkMode }">

    <!-- Navigation -->
    <nav class="bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm border-b border-gray-200 dark:border-gray-700 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-2xl font-bold text-blue-600 dark:text-blue-400">WebBloc</h1>
                    </div>
                    <div class="hidden md:ml-10 md:flex md:space-x-8" :class="{ 'md:space-x-reverse': locale === 'ar' }">
                        <a href="#features" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-2 text-sm font-medium transition-colors">
                            {{ __('Features') }}
                        </a>
                        <a href="#components" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-2 text-sm font-medium transition-colors">
                            {{ __('Components') }}
                        </a>
                        <a href="{{ route('pricing') }}" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-2 text-sm font-medium transition-colors">
                            {{ __('Pricing') }}
                        </a>
                        <a href="#docs" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-2 text-sm font-medium transition-colors">
                            {{ __('Documentation') }}
                        </a>
                        <a href="{{ route('contact') }}" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-2 text-sm font-medium transition-colors">
                            {{ __('Contact') }}
                        </a>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4" :class="{ 'space-x-reverse': locale === 'ar' }">
                    <!-- Language Switcher -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:text-blue-600" :class="{ 'space-x-reverse': locale === 'ar' }">
                            <span class="text-sm">{{ app()->getLocale() === 'ar' ? 'العربية' : 'English' }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-32 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-50">
                            <a href="{{ url()->current() }}?lang=en" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">English</a>
                            <a href="{{ url()->current() }}?lang=ar" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">العربية</a>
                        </div>
                    </div>
                    
                    <!-- Dark Mode Toggle -->
                    <button @click="darkMode = !darkMode" class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 rounded-lg">
                        <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                        <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </button>
                    
                    <!-- Auth Buttons -->
                    @auth
                        <a href="{{ route('dashboard') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            {{ __('Dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 px-3 py-2 text-sm font-medium">
                            {{ __('Login') }}
                        </a>
                        <a href="{{ route('register') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            {{ __('Get Started') }}
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-16 pb-32 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl sm:text-6xl font-bold text-gray-900 dark:text-white mb-6">
                    {{ __('Dynamic Components') }}<br>
                    <span class="text-blue-600 dark:text-blue-400">{{ __('for Static Websites') }}</span>
                </h1>
                <p class="text-xl text-gray-600 dark:text-gray-300 mb-8 max-w-3xl mx-auto">
                    {{ __('Add authentication, comments, reviews, and more to your static websites with our easy-to-integrate API components. No backend required.') }}
                </p>
                <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-4" :class="{ 'sm:space-x-reverse': locale === 'ar' }">
                    @guest
                        <a href="{{ route('register') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg text-lg font-semibold transition-colors shadow-lg">
                            {{ __('Start Free Trial') }}
                        </a>
                        <a href="#demo" class="border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 px-8 py-3 rounded-lg text-lg font-semibold transition-colors">
                            {{ __('View Demo') }}
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg text-lg font-semibold transition-colors shadow-lg">
                            {{ __('Go to Dashboard') }}
                        </a>
                    @endguest
                </div>
            </div>
        </div>
        
        <!-- Background Pattern -->
        <div class="absolute inset-0 -z-10">
            <svg class="absolute inset-0 h-full w-full" fill="none" viewBox="0 0 400 400" aria-hidden="true">
                <defs>
                    <pattern id="85737c0e-0916-41d7-917f-596dc7edfa27" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                        <rect x="0" y="0" width="4" height="4" class="text-gray-200 dark:text-gray-700" fill="currentColor" />
                    </pattern>
                </defs>
                <rect width="400" height="400" fill="url(#85737c0e-0916-41d7-917f-596dc7edfa27)" />
            </svg>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 bg-white dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-3xl sm:text-4xl font-bold text-blue-600 dark:text-blue-400" x-text="stats.websites.toLocaleString()">0</div>
                    <div class="text-gray-600 dark:text-gray-400 mt-2">{{ __('Active Websites') }}</div>
                </div>
                <div>
                    <div class="text-3xl sm:text-4xl font-bold text-blue-600 dark:text-blue-400" x-text="stats.components.toLocaleString()">0</div>
                    <div class="text-gray-600 dark:text-gray-400 mt-2">{{ __('Components Deployed') }}</div>
                </div>
                <div>
                    <div class="text-3xl sm:text-4xl font-bold text-blue-600 dark:text-blue-400" x-text="stats.requests.toLocaleString()">0</div>
                    <div class="text-gray-600 dark:text-gray-400 mt-2">{{ __('API Requests Today') }}</div>
                </div>
                <div>
                    <div class="text-3xl sm:text-4xl font-bold text-blue-600 dark:text-blue-400" x-text="stats.users.toLocaleString()">0</div>
                    <div class="text-gray-600 dark:text-gray-400 mt-2">{{ __('Happy Developers') }}</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50 dark:bg-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('Everything you need to make your static site dynamic') }}
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                    {{ __('Our comprehensive suite of components covers all your dynamic website needs.') }}
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ __('User Authentication') }}</h3>
                    <p class="text-gray-600 dark:text-gray-300">{{ __('Complete user registration, login, and profile management with social authentication support.') }}</p>
                </div>
                
                <!-- Feature 2 -->
                <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ __('Comments System') }}</h3>
                    <p class="text-gray-600 dark:text-gray-300">{{ __('Threaded comments with real-time updates, moderation tools, and spam protection.') }}</p>
                </div>
                
                <!-- Feature 3 -->
                <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ __('Reviews & Ratings') }}</h3>
                    <p class="text-gray-600 dark:text-gray-300">{{ __('Star ratings, detailed reviews, photo uploads, and comprehensive analytics.') }}</p>
                </div>
                
                <!-- Feature 4 -->
                <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ __('Reactions') }}</h3>
                    <p class="text-gray-600 dark:text-gray-300">{{ __('Emoji reactions, likes, dislikes, and custom reaction types for any content.') }}</p>
                </div>
                
                <!-- Feature 5 -->
                <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ __('User Profiles') }}</h3>
                    <p class="text-gray-600 dark:text-gray-300">{{ __('Comprehensive user profiles with activity tracking and customizable display options.') }}</p>
                </div>
                
                <!-- Feature 6 -->
                <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ __('Testimonials') }}</h3>
                    <p class="text-gray-600 dark:text-gray-300">{{ __('Customer testimonials with rotating displays and category-based organization.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Components Demo Section -->
    <section id="components" class="py-20 bg-white dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('See Our Components in Action') }}
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                    {{ __('Experience how easy it is to integrate dynamic functionality into your static website.') }}
                </p>
            </div>
            
            <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-8 border border-gray-200 dark:border-gray-700">
                <div class="bg-white dark:bg-gray-900 rounded-lg p-6 shadow-inner">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Live Demo - Comments Component') }}</h3>
                    <div class="space-y-4">
                        <!-- Demo Comment -->
                        <div class="flex space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 dark:text-blue-400 text-sm font-medium">JD</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                                    <div class="flex items-center space-x-2 mb-1" :class="{ 'space-x-reverse': locale === 'ar' }">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">John Doe</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('2 minutes ago') }}</span>
                                    </div>
                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ __('This WebBloc integration is amazing! So easy to add to my static site.') }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Comment Form -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <div class="flex space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-gray-100 dark:bg-gray-800 rounded-full"></div>
                                </div>
                                <div class="flex-1">
                                    <textarea placeholder="{{ __('Write a comment...') }}" rows="2" 
                                             class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
                                    <div class="flex justify-between items-center mt-2">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('Powered by WebBloc') }}</span>
                                        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1 rounded text-sm font-medium transition-colors">
                                            {{ __('Post Comment') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Integration Section -->
    <section id="demo" class="py-20 bg-gray-50 dark:bg-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('Integration is Simple') }}
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                    {{ __('Get started in minutes with just a few lines of code.') }}
                </p>
            </div>
            
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gray-800 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                        <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    </div>
                    <span class="text-gray-300 text-sm">index.html</span>
                </div>
                <div class="p-6 bg-gray-900 text-green-400 font-mono text-sm overflow-x-auto">
                    <pre><code>&lt;!-- {{ __('Include WebBloc') }} --&gt;
&lt;script src="https://cdn.webbloc.com/js/webbloc.min.js"&gt;&lt;/script&gt;
&lt;link href="https://cdn.webbloc.com/css/webbloc.min.css" rel="stylesheet"&gt;

&lt;!-- {{ __('Initialize WebBloc') }} --&gt;
&lt;script&gt;
WebBloc.init({
    apiUrl: 'https://api.webbloc.com',
    publicKey: 'your-public-key',
    websiteId: 'your-website-uuid'
});
&lt;/script&gt;

&lt;!-- {{ __('Add Components') }} --&gt;
&lt;div webbloc="auth"&gt;&lt;/div&gt;
&lt;div webbloc="comments" data-limit="10"&gt;&lt;/div&gt;
&lt;div webbloc="reviews" data-item-id="product-1"&gt;&lt;/div&gt;</code></pre>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-blue-600 dark:bg-blue-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">
                {{ __('Ready to make your static site dynamic?') }}
            </h2>
            <p class="text-xl text-blue-100 mb-8 max-w-3xl mx-auto">
                {{ __('Join thousands of developers who trust WebBloc for their dynamic web components.') }}
            </p>
            @guest
                <a href="{{ route('register') }}" class="bg-white text-blue-600 hover:bg-gray-100 px-8 py-3 rounded-lg text-lg font-semibold transition-colors shadow-lg inline-block">
                    {{ __('Start Your Free Trial') }}
                </a>
            @else
                <a href="{{ route('dashboard') }}" class="bg-white text-blue-600 hover:bg-gray-100 px-8 py-3 rounded-lg text-lg font-semibold transition-colors shadow-lg inline-block">
                    {{ __('Go to Dashboard') }}
                </a>
            @endguest
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 dark:bg-black text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">WebBloc</h3>
                    <p class="text-gray-400 mb-4">{{ __('Dynamic components for static websites. Made simple.') }}</p>
                    <div class="flex space-x-4" :class="{ 'space-x-reverse': locale === 'ar' }">
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.750.097.118.112.22.083.343-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.161-1.499-.69-2.436-2.878-2.436-4.632 0-3.78 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.749-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641.001.012.001z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">{{ __('Product') }}</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#features" class="hover:text-white transition-colors">{{ __('Features') }}</a></li>
                        <li><a href="#components" class="hover:text-white transition-colors">{{ __('Components') }}</a></li>
                        <li><a href="{{ route('pricing') }}" class="hover:text-white transition-colors">{{ __('Pricing') }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Changelog') }}</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">{{ __('Support') }}</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#docs" class="hover:text-white transition-colors">{{ __('Documentation') }}</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white transition-colors">{{ __('Contact') }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Status') }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Community') }}</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">{{ __('Company') }}</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('About') }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Blog') }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Privacy') }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Terms') }}</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 pt-8 mt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} WebBloc. {{ __('All rights reserved.') }}</p>
            </div>
        </div>
    </footer>
</body>
</html>
```

#### resources/views/pricing.blade.php
```php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Pricing - WebBloc') }}</title>
    
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
<body class="antialiased bg-gray-50 dark:bg-gray-900" 
      x-data="{ 
          locale: '{{ app()->getLocale() }}', 
          darkMode: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
          billingCycle: 'monthly',
          plans: {
              starter: {
                  monthly: { price: 9, requests: 10000 },
                  yearly: { price: 90, requests: 10000 }
              },
              professional: {
                  monthly: { price: 29, requests: 100000 },
                  yearly: { price: 290, requests: 100000 }
              },
              enterprise: {
                  monthly: { price: 99, requests: 500000 },
                  yearly: { price: 990, requests: 500000 }
              }
          }
      }"
      x-init="
          $watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'));
          document.documentElement.classList.toggle('dark', darkMode);
      "
      :class="{ 'dark': darkMode }">

    <!-- Navigation -->
    <nav class="bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm border-b border-gray-200 dark:border-gray-700 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <a href="{{ route('welcome') }}" class="text-2xl font-bold text-blue-600 dark:text-blue-400">WebBloc</a>
                    </div>
                    <div class="hidden md:ml-10 md:flex md:space-x-8" :class="{ 'md:space-x-reverse': locale === 'ar' }">
                        <a href="{{ route('welcome') }}#features" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-2 text-sm font-medium transition-colors">
                            {{ __('Features') }}
                        </a>
                        <a href="{{ route('welcome') }}#components" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-2 text-sm font-medium transition-colors">
                            {{ __('Components') }}
                        </a>
                        <a href="{{ route('pricing') }}" class="text-blue-600 dark:text-blue-400 px-3 py-2 text-sm font-medium">
                            {{ __('Pricing') }}
                        </a>
                        <a href="{{ route('contact') }}" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-2 text-sm font-medium transition-colors">
                            {{ __('Contact') }}
                        </a>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4" :class="{ 'space-x-reverse': locale === 'ar' }">
                    <!-- Language Switcher -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:text-blue-600" :class="{ 'space-x-reverse': locale === 'ar' }">
                            <span class="text-sm">{{ app()->getLocale() === 'ar' ? 'العربية' : 'English' }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-32 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-50">
                            <a href="{{ url()->current() }}?lang=en" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">English</a>
                            <a href="{{ url()->current() }}?lang=ar" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">العربية</a>
                        </div>
                    </div>
                    
                    <!-- Dark Mode Toggle -->
                    <button @click="darkMode = !darkMode" class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 rounded-lg">
                        <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                        <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </button>
                    
                    <!-- Auth Buttons -->
                    @auth
                        <a href="{{ route('dashboard') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            {{ __('Dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 px-3 py-2 text-sm font-medium">
                            {{ __('Login') }}
                        </a>
                        <a href="{{ route('register') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            {{ __('Get Started') }}
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-16 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                {{ __('Simple, Transparent Pricing') }}
            </h1>
            <p class="text-xl text-gray-600 dark:text-gray-300 mb-8 max-w-3xl mx-auto">
                {{ __('Choose the perfect plan for your website. All plans include our complete suite of components.') }}
            </p>
            
            <!-- Billing Toggle -->
            <div class="flex items-center justify-center mb-12">
                <span class="text-gray-600 dark:text-gray-300 font-medium" :class="{ 'text-blue-600 dark:text-blue-400': billingCycle === 'monthly' }">{{ __('Monthly') }}</span>
                <div class="mx-4">
                    <button @click="billingCycle = billingCycle === 'monthly' ? 'yearly' : 'monthly'" 
                            class="relative inline-flex h-6 w-11 items-center rounded-full bg-gray-200 dark:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            :class="{ 'bg-blue-600': billingCycle === 'yearly' }">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                              :class="{ 'translate-x-6': billingCycle === 'yearly', 'translate-x-1': billingCycle === 'monthly' }"></span>
                    </button>
                </div>
                <span class="text-gray-600 dark:text-gray-300 font-medium" :class="{ 'text-blue-600 dark:text-blue-400': billingCycle === 'yearly' }">
                    {{ __('Yearly') }} <span class="text-green-600 dark:text-green-400 text-sm">({{ __('Save 17%') }})</span>
                </span>
            </div>
        </div>
    </section>

    <!-- Pricing Cards -->
    <section class="pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8 lg:gap-12">
                
                <!-- Starter Plan -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-8 relative">
                    <div class="text-center">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">{{ __('Starter') }}</h3>
                        <div class="mb-6">
                            <span class="text-4xl font-bold text-gray-900 dark:text-white">$</span>
                            <span class="text-5xl font-bold text-gray-900 dark:text-white" x-text="plans.starter[billingCycle].price"></span>
                            <span class="text-gray-600 dark:text-gray-300" x-text="billingCycle === 'monthly' ? '/{{ __('month') }}' : '/{{ __('year') }}'"></span>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-8">{{ __('Perfect for small websites and personal projects') }}</p>
                    </div>
                    
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-start space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">
                                <span x-text="plans.starter[billingCycle].requests.toLocaleString()"></span> {{ __('API requests/month') }}
                            </span>
                        </li>
                        <li class="flex items-start space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">{{ __('1 Website') }}</span>
                        </li>
                        <li class="flex items-start space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">{{ __('All Components') }}</span>
                        </li>
                        <li class="flex items-start space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">{{ __('Email Support') }}</span>
                        </li>
                        <li class="flex items-start space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">{{ __('SSL & CDN Included') }}</span>
                        </li>
                    </ul>
                    
                    @guest
                        <a href="{{ route('register') }}" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors text-center block">
                            {{ __('Get Started') }}
                        </a>
                    @else
                        <button onclick="selectPlan('starter')" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                            {{ __('Select Plan') }}
                        </button>
                    @endguest
                </div>
                
                <!-- Professional Plan -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border-2 border-blue-500 p-8 relative">
                    <!-- Popular Badge -->
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                        <span class="bg-blue-500 text-white px-4 py-1 rounded-full text-sm font-medium">{{ __('Most Popular') }}</span>
                    </div>
                    
                    <div class="text-center">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">{{ __('Professional') }}</h3>
                        <div class="mb-6">
                            <span class="text-4xl font-bold text-gray-900 dark:text-white">$</span>
                            <span class="text-5xl font-bold text-gray-900 dark:text-white" x-text="plans.professional[billingCycle].price"></span>
                            <span class="text-gray-600 dark:text-gray-300" x-text="billingCycle === 'monthly' ? '/{{ __('month') }}' : '/{{ __('year') }}'"></span>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-8">{{ __('Ideal for growing businesses and agencies') }}</p>
                    </div>
                    
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-start space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">
                                <span x-text="plans.professional[billingCycle].requests.toLocaleString()"></span> {{ __('API requests/month') }}
                            </span>
                        </li>
                        <li class="flex items-start space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">{{ __('5 Websites') }}</span>
                        </li>
                        <li class="flex items-start space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">{{ __('All Components') }}</span>
                        </li>
                        <li class="flex items-start space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">{{ __('Priority Support') }}</span>
                        </li>
                        <li class="flex items-start space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">{{ __('Custom Styling') }}</span>
                        </li>
                        <li class="flex items-start space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">{{ __('Advanced Analytics') }}</span>
                        </li>
                    </ul>
                    
                    @guest
                        <a href="{{ route('register') }}" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors text-center block">
                            {{ __('Get Started') }}
                        </a>
                    @else
                        <button onclick="selectPlan('professional')" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                            {{ __('Select Plan') }}
                        </button>
                    @endguest
                </div>
                
                <!-- Enterprise Plan -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-8 relative">
                    <div class="text-center">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">{{ __('Enterprise') }}</h3>
                        <div class="mb-6">
                            <span class="text-4xl font-bold text-gray-900 dark:text-white">$</span>
                            <span class="text-5xl font-bold text-gray-900 dark:text-white" x-text="plans.enterprise[billingCycle].price"></span>
                            <span class="text-gray-600 dark:text-gray-300" x-text="billingCycle === 'monthly' ? '/{{ __('month') }}' : '/{{ __('year') }}'"></span>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-8">{{ __('For large organizations with high traffic') }}</p>
                    </div>
                    
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-start space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">
                                <span x-text="plans.enterprise[billingCycle].requests.toLocaleString()"></span> {{ __('API requests/month') }}
                            </span>
                        </li>
                        <li class="flex items-start space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">{{ __('Unlimited Websites') }}</span>
                        </li>
                        <li class="flex items-start space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">{{ __('All Components') }}</span>
                        </li>
                        <li class="flex items-start space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">{{ __('24/7 Phone Support') }}</span>
                        </li>
                        <li class="flex items-start space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">{{ __('White-label Solution') }}</span>
                        </li>
                        <li class="flex items-start space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">{{ __('SLA Guarantee') }}</span>
                        </li>
                    </ul>
                    
                    @guest
                        <a href="{{ route('contact') }}" class="w-full bg-gray-900 dark:bg-white dark:text-gray-900 hover:bg-gray-800 dark:hover:bg-gray-100 text-white px-6 py-3 rounded-lg font-semibold transition-colors text-center block">
                            {{ __('Contact Sales') }}
                        </a>
                    @else
                        <button onclick="selectPlan('enterprise')" class="w-full bg-gray-900 dark:bg-white dark:text-gray-900 hover:bg-gray-800 dark:hover:bg-gray-100 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                            {{ __('Contact Sales') }}
                        </button>
                    @endguest
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-20 bg-white dark:bg-gray-800">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('Frequently Asked Questions') }}
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-300">
                    {{ __('Everything you need to know about our pricing and plans.') }}
                </p>
            </div>
            
            <div class="space-y-6" x-data="{ openFaq: null }">
                <!-- FAQ Item -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <button @click="openFaq = openFaq === 1 ? null : 1" class="w-full px-6 py-4 text-left flex justify-between items-center">
                        <span class="font-semibold text-gray-900 dark:text-white">{{ __('Can I change my plan anytime?') }}</span>
                        <svg class="w-5 h-5 text-gray-500 transform transition-transform" :class="{ 'rotate-180': openFaq === 1 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="openFaq === 1" x-transition class="px-6 pb-4">
                        <p class="text-gray-600 dark:text-gray-300">{{ __('Yes, you can upgrade or downgrade your plan at any time. Changes will be prorated based on your billing cycle.') }}</p>
                    </div>
                </div>
                
                <!-- FAQ Item -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <button @click="openFaq = openFaq === 2 ? null : 2" class="w-full px-6 py-4 text-left flex justify-between items-center">
                        <span class="font-semibold text-gray-900 dark:text-white">{{ __('What happens if I exceed my API limits?') }}</span>
                        <svg class="w-5 h-5 text-gray-500 transform transition-transform" :class="{ 'rotate-180': openFaq === 2 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="openFaq === 2" x-transition class="px-6 pb-4">
                        <p class="text-gray-600 dark:text-gray-300">{{ __('Your components will continue to work, but additional requests will be charged at $0.10 per 1,000 requests. You\'ll be notified before any overage charges.') }}</p>
                    </div>
                </div>
                
                <!-- FAQ Item -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <button @click="openFaq = openFaq === 3 ? null : 3" class="w-full px-6 py-4 text-left flex justify-between items-center">
                        <span class="font-semibold text-gray-900 dark:text-white">{{ __('Is there a free trial?') }}</span>
                        <svg class="w-5 h-5 text-gray-500 transform transition-transform" :class="{ 'rotate-180': openFaq === 3 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="openFaq === 3" x-transition class="px-6 pb-4">
                        <p class="text-gray-600 dark:text-gray-300">{{ __('Yes! All plans come with a 14-day free trial. No credit card required to get started.') }}</p>
                    </div>
                </div>
                
                <!-- FAQ Item -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <button @click="openFaq = openFaq === 4 ? null : 4" class="w-full px-6 py-4 text-left flex justify-between items-center">
                        <span class="font-semibold text-gray-900 dark:text-white">{{ __('Do you offer refunds?') }}</span>
                        <svg class="w-5 h-5 text-gray-500 transform transition-transform" :class="{ 'rotate-180': openFaq === 4 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="openFaq === 4" x-transition class="px-6 pb-4">
                        <p class="text-gray-600 dark:text-gray-300">{{ __('We offer a 30-day money-back guarantee on all annual plans. Monthly plans can be cancelled anytime without penalty.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-blue-600 dark:bg-blue-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">
                {{ __('Ready to get started?') }}
            </h2>
            <p class="text-xl text-blue-100 mb-8 max-w-3xl mx-auto">
                {{ __('Join thousands of developers building better websites with WebBloc.') }}
            </p>
            @guest
                <a href="{{ route('register') }}" class="bg-white text-blue-600 hover:bg-gray-100 px-8 py-3 rounded-lg text-lg font-semibold transition-colors shadow-lg inline-block">
                    {{ __('Start Your Free Trial') }}
                </a>
            @else
                <a href="{{ route('dashboard') }}" class="bg-white text-blue-600 hover:bg-gray-100 px-8 py-3 rounded-lg text-lg font-semibold transition-colors shadow-lg inline-block">
                    {{ __('Go to Dashboard') }}
                </a>
            @endguest
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 dark:bg-black text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">WebBloc</h3>
                    <p class="text-gray-400 mb-4">{{ __('Dynamic components for static websites. Made simple.') }}</p>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">{{ __('Product') }}</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="{{ route('welcome') }}#features" class="hover:text-white transition-colors">{{ __('Features') }}</a></li>
                        <li><a href="{{ route('welcome') }}#components" class="hover:text-white transition-colors">{{ __('Components') }}</a></li>
                        <li><a href="{{ route('pricing') }}" class="hover:text-white transition-colors">{{ __('Pricing') }}</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">{{ __('Support') }}</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Documentation') }}</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white transition-colors">{{ __('Contact') }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Status') }}</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">{{ __('Company') }}</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('About') }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Privacy') }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Terms') }}</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 pt-8 mt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} WebBloc. {{ __('All rights reserved.') }}</p>
            </div>
        </div>
    </footer>

    @auth
    <script>
        function selectPlan(plan) {
            Swal.fire({
                title: '{{ __("Select Plan") }}',
                text: '{{ __("You will be redirected to the dashboard to manage your subscription.") }}',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: '{{ __("Continue") }}',
                cancelButtonText: '{{ __("Cancel") }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '{{ route("dashboard") }}?plan=' + plan;
                }
            });
        }
    </script>
    @endauth
</body>
</html>
```

#### resources/views/contact.blade.php
```php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Contact Us - WebBloc') }}</title>
    
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
<body class="antialiased bg-gray-50 dark:bg-gray-900" 
      x-data="{ 
          locale: '{{ app()->getLocale() }}', 
          darkMode: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
          form: {
              name: '',
              email: '',
              subject: '',
              message: '',
              type: 'general'
          },
          submitting: false,
          errors: {}
      }"
      x-init="
          $watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'));
          document.documentElement.classList.toggle('dark', darkMode);
      "
      :class="{ 'dark': darkMode }">

    <!-- Navigation -->
    <nav class="bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm border-b border-gray-200 dark:border-gray-700 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <a href="{{ route('welcome') }}" class="text-2xl font-bold text-blue-600 dark:text-blue-400">WebBloc</a>
                    </div>
                    <div class="hidden md:ml-10 md:flex md:space-x-8" :class="{ 'md:space-x-reverse': locale === 'ar' }">
                        <a href="{{ route('welcome') }}#features" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-2 text-sm font-medium transition-colors">
                            {{ __('Features') }}
                        </a>
                        <a href="{{ route('welcome') }}#components" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-2 text-sm font-medium transition-colors">
                            {{ __('Components') }}
                        </a>
                        <a href="{{ route('pricing') }}" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-2 text-sm font-medium transition-colors">
                            {{ __('Pricing') }}
                        </a>
                        <a href="{{ route('contact') }}" class="text-blue-600 dark:text-blue-400 px-3 py-2 text-sm font-medium">
                            {{ __('Contact') }}
                        </a>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4" :class="{ 'space-x-reverse': locale === 'ar' }">
                    <!-- Language Switcher -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:text-blue-600" :class="{ 'space-x-reverse': locale === 'ar' }">
                            <span class="text-sm">{{ app()->getLocale() === 'ar' ? 'العربية' : 'English' }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-32 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-50">
                            <a href="{{ url()->current() }}?lang=en" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">English</a>
                            <a href="{{ url()->current() }}?lang=ar" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">العربية</a>
                        </div>
                    </div>
                    
                    <!-- Dark Mode Toggle -->
                    <button @click="darkMode = !darkMode" class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 rounded-lg">
                        <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                        <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </button>
                    
                    <!-- Auth Buttons -->
                    @auth
                        <a href="{{ route('dashboard') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            {{ __('Dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 px-3 py-2 text-sm font-medium">
                            {{ __('Login') }}
                        </a>
                        <a href="{{ route('register') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            {{ __('Get Started') }}
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-16 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                {{ __('Get in Touch') }}
            </h1>
            <p class="text-xl text-gray-600 dark:text-gray-300 mb-8 max-w-3xl mx-auto">
                {{ __('Have questions about WebBloc? We\'re here to help you get the most out of our platform.') }}
            </p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-3 gap-12">
                <!-- Contact Info -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8 border border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ __('Contact Information') }}</h2>
                        
                        <div class="space-y-6">
                            <!-- Email -->
                            <div class="flex items-start space-x-4" :class="{ 'space-x-reverse': locale === 'ar' }">
                                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('Email') }}</h3>
                                    <p class="text-gray-600 dark:text-gray-300">support@webbloc.com</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('We typically respond within 24 hours') }}</p>
                                </div>
                            </div>
                            
                            <!-- Live Chat -->
                            <div class="flex items-start space-x-4" :class="{ 'space-x-reverse': locale === 'ar' }">
                                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('Live Chat') }}</h3>
                                    <p class="text-gray-600 dark:text-gray-300">{{ __('Available 9 AM - 6 PM EST') }}</p>
                                    <button class="text-sm text-blue-600 dark:text-blue-400 hover:underline mt-1">{{ __('Start a conversation') }}</button>
                                </div>
                            </div>
                            
                            <!-- Documentation -->
                            <div class="flex items-start space-x-4" :class="{ 'space-x-reverse': locale === 'ar' }">
                                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('Documentation') }}</h3>
                                    <p class="text-gray-600 dark:text-gray-300">{{ __('Comprehensive guides and API docs') }}</p>
                                    <a href="#" class="text-sm text-blue-600 dark:text-blue-400 hover:underline mt-1">{{ __('Browse documentation') }}</a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Social Links -->
                        <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">{{ __('Follow Us') }}</h3>
                            <div class="flex space-x-4" :class="{ 'space-x-reverse': locale === 'ar' }">
                                <a href="#" class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-blue-100 dark:hover:bg-blue-900 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                                    </svg>
                                </a>
                                <a href="#" class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-blue-100 dark:hover:bg-blue-900 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.750.097.118.112.22.083.343-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.161-1.499-.69-2.436-2.878-2.436-4.632 0-3.78 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.749-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641.001.012.001z"/>
                                    </svg>
                                </a>
                                <a href="#" class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-blue-100 dark:hover:bg-blue-900 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8 border border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ __('Send us a message') }}</h2>
                        
                        <form @submit.prevent="submitForm" class="space-y-6">
                            <!-- Name and Email -->
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('Full Name') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input x-model="form.name" type="text" id="name" required
                                           class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                                           :class="{ 'border-red-500': errors.name }"
                                           placeholder="{{ __('Enter your full name') }}">
                                    <p x-show="errors.name" x-text="errors.name" class="mt-1 text-sm text-red-500"></p>
                                </div>
                                
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('Email Address') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input x-model="form.email" type="email" id="email" required
                                           class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                                           :class="{ 'border-red-500': errors.email }"
                                           placeholder="{{ __('Enter your email address') }}">
                                    <p x-show="errors.email" x-text="errors.email" class="mt-1 text-sm text-red-500"></p>
                                </div>
                            </div>
                            
                            <!-- Subject Type -->
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('Inquiry Type') }}
                                </label>
                                <select x-model="form.type" id="type"
                                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                                    <option value="general">{{ __('General Question') }}</option>
                                    <option value="technical">{{ __('Technical Support') }}</option>
                                    <option value="billing">{{ __('Billing & Pricing') }}</option>
                                    <option value="partnership">{{ __('Partnership') }}</option>
                                    <option value="feature">{{ __('Feature Request') }}</option>
                                    <option value="bug">{{ __('Bug Report') }}</option>
                                </select>
                            </div>
                            
                            <!-- Subject -->
                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('Subject') }} <span class="text-red-500">*</span>
                                </label>
                                <input x-model="form.subject" type="text" id="subject" required
                                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                                       :class="{ 'border-red-500': errors.subject }"
                                       placeholder="{{ __('Brief description of your inquiry') }}">
                                <p x-show="errors.subject" x-text="errors.subject" class="mt-1 text-sm text-red-500"></p>
                            </div>
                            
                            <!-- Message -->
                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('Message') }} <span class="text-red-500">*</span>
                                </label>
                                <textarea x-model="form.message" id="message" rows="6" required
                                          class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 resize-none"
                                          :class="{ 'border-red-500': errors.message }"
                                          placeholder="{{ __('Please provide as much detail as possible...') }}"></textarea>
                                <p x-show="errors.message" x-text="errors.message" class="mt-1 text-sm text-red-500"></p>
                            </div>
                            
                            <!-- Submit Button -->
                            <div>
                                <button type="submit" :disabled="submitting"
                                        class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-6 py-3 rounded-lg font-semibold transition-colors flex items-center justify-center space-x-2"
                                        :class="{ 'space-x-reverse': locale === 'ar' }">
                                    <svg x-show="submitting" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-text="submitting ? '{{ __("Sending...") }}' : '{{ __("Send Message") }}'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-20 bg-white dark:bg-gray-800">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('Frequently Asked Questions') }}
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-300">
                    {{ __('Quick answers to common questions about WebBloc.') }}
                </p>
            </div>
            
            <div class="space-y-6" x-data="{ openFaq: null }">
                <!-- FAQ Item -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <button @click="openFaq = openFaq === 1 ? null : 1" class="w-full px-6 py-4 text-left flex justify-between items-center">
                        <span class="font-semibold text-gray-900 dark:text-white">{{ __('How long does it take to integrate WebBloc?') }}</span>
                        <svg class="w-5 h-5 text-gray-500 transform transition-transform" :class="{ 'rotate-180': openFaq === 1 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="openFaq === 1" x-transition class="px-6 pb-4">
                        <p class="text-gray-600 dark:text-gray-300">{{ __('Most developers can integrate WebBloc components in under 30 minutes. Simply include our CDN files, initialize with your API key, and add component tags to your HTML.') }}</p>
                    </div>
                </div>
                
                <!-- FAQ Item -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <button @click="openFaq = openFaq === 2 ? null : 2" class="w-full px-6 py-4 text-left flex justify-between items-center">
                        <span class="font-semibold text-gray-900 dark:text-white">{{ __('Do you provide technical support?') }}</span>
                        <svg class="w-5 h-5 text-gray-500 transform transition-transform" :class="{ 'rotate-180': openFaq === 2 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="openFaq === 2" x-transition class="px-6 pb-4">
                        <p class="text-gray-600 dark:text-gray-300">{{ __('Yes! We offer email support for all plans, priority support for Professional plans, and 24/7 phone support for Enterprise customers. We also have comprehensive documentation and community forums.') }}</p>
                    </div>
                </div>
                
                <!-- FAQ Item -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <button @click="openFaq = openFaq === 3 ? null : 3" class="w-full px-6 py-4 text-left flex justify-between items-center">
                        <span class="font-semibold text-gray-900 dark:text-white">{{ __('Can I customize the appearance of components?') }}</span>
                        <svg class="w-5 h-5 text-gray-500 transform transition-transform" :class="{ 'rotate-180': openFaq === 3 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="openFaq === 3" x-transition class="px-6 pb-4">
                        <p class="text-gray-600 dark:text-gray-300">{{ __('Absolutely! All components support custom CSS styling and theming. Professional and Enterprise plans include advanced customization options and white-label solutions.') }}</p>
                    </div>
                </div>
                
                <!-- FAQ Item -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <button @click="openFaq = openFaq === 4 ? null : 4" class="w-full px-6 py-4 text-left flex justify-between items-center">
                        <span class="font-semibold text-gray-900 dark:text-white">{{ __('Is my data secure with WebBloc?') }}</span>
                        <svg class="w-5 h-5 text-gray-500 transform transition-transform" :class="{ 'rotate-180': openFaq === 4 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="openFaq === 4" x-transition class="px-6 pb-4">
                        <p class="text-gray-600 dark:text-gray-300">{{ __('Security is our top priority. We use industry-standard encryption, secure API authentication, and follow GDPR compliance. Each website has its own isolated database for maximum data protection.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 dark:bg-black text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">WebBloc</h3>
                    <p class="text-gray-400 mb-4">{{ __('Dynamic components for static websites. Made simple.') }}</p>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">{{ __('Product') }}</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="{{ route('welcome') }}#features" class="hover:text-white transition-colors">{{ __('Features') }}</a></li>
                        <li><a href="{{ route('welcome') }}#components" class="hover:text-white transition-colors">{{ __('Components') }}</a></li>
                        <li><a href="{{ route('pricing') }}" class="hover:text-white transition-colors">{{ __('Pricing') }}</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">{{ __('Support') }}</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Documentation') }}</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white transition-colors">{{ __('Contact') }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Status') }}</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">{{ __('Company') }}</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('About') }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Privacy') }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Terms') }}</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 pt-8 mt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} WebBloc. {{ __('All rights reserved.') }}</p>
            </div>
        </div>
    </footer>

    <script>
        function submitForm() {
            this.submitting = true;
            this.errors = {};
            
            fetch('/api/contact', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(this.form)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: '{{ __("Message Sent!") }}',
                        text: '{{ __("Thank you for contacting us. We\'ll get back to you within 24 hours.") }}',
                        icon: 'success',
                        confirmButtonText: '{{ __("OK") }}'
                    });
                    
                    // Reset form
                    this.form = {
                        name: '',
                        email: '',
                        subject: '',
                        message: '',
                        type: 'general'
                    };
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        Swal.fire({
                            title: '{{ __("Error") }}',
                            text: data.message || '{{ __("Something went wrong. Please try again.") }}',
                            icon: 'error',
                            confirmButtonText: '{{ __("OK") }}'
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: '{{ __("Error") }}',
                    text: '{{ __("Something went wrong. Please try again.") }}',
                    icon: 'error',
                    confirmButtonText: '{{ __("OK") }}'
                });
            })
            .finally(() => {
                this.submitting = false;
            });
        }
    </script>
</body>
</html>
```

### 11. Email Templates

#### resources/views/emails/website-verified.blade.php
```php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Website Verified - WebBloc') }}</title>
    <style>
        /* Email-safe CSS */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #ffffff;
            padding: 30px 40px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .content {
            padding: 40px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #1f2937;
        }
        .message {
            font-size: 16px;
            margin-bottom: 30px;
            color: #4b5563;
        }
        .website-info {
            background-color: #f3f4f6;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            border-left: 4px solid #3b82f6;
        }
        .website-info h3 {
            margin: 0 0 10px 0;
            color: #1f2937;
            font-size: 18px;
        }
        .website-info p {
            margin: 5px 0;
            color: #6b7280;
        }
        .button {
            display: inline-block;
            background-color: #3b82f6;
            color: #ffffff;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #2563eb;
        }
        .next-steps {
            background-color: #ecfdf5;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            border-left: 4px solid #10b981;
        }
        .next-steps h3 {
            color: #047857;
            margin: 0 0 15px 0;
        }
        .next-steps ul {
            margin: 0;
            padding-left: 20px;
            color: #065f46;
        }
        .next-steps li {
            margin-bottom: 8px;
        }
        .code-block {
            background-color: #1f2937;
            color: #e5e7eb;
            padding: 20px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            overflow-x: auto;
            margin: 20px 0;
        }
        .footer {
            background-color: #f9fafb;
            padding: 30px 40px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            border-top: 1px solid #e5e7eb;
        }
        .footer a {
            color: #3b82f6;
            text-decoration: none;
        }
        .social-links {
            margin: 20px 0;
        }
        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #6b7280;
            text-decoration: none;
        }
        
        /* RTL Support */
        [dir="rtl"] .website-info {
            border-left: none;
            border-right: 4px solid #3b82f6;
        }
        [dir="rtl"] .next-steps {
            border-left: none;
            border-right: 4px solid #10b981;
        }
        [dir="rtl"] .next-steps ul {
            padding-left: 0;
            padding-right: 20px;
        }
        
        /* Mobile Responsive */
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 0;
                border-radius: 0;
            }
            .header, .content, .footer {
                padding: 20px;
            }
            .header h1 {
                font-size: 24px;
            }
            .code-block {
                font-size: 12px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>🎉 {{ __('Website Verified!') }}</h1>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="greeting">
                {{ __('Hello') }} {{ $user->name }},
            </div>
            
            <div class="message">
                {{ __('Congratulations! Your website has been successfully verified and is now ready to use WebBloc components.') }}
            </div>
            
            <!-- Website Information -->
            <div class="website-info">
                <h3>{{ __('Website Details') }}</h3>
                <p><strong>{{ __('Name') }}:</strong> {{ $website->name }}</p>
                <p><strong>{{ __('Domain') }}:</strong> {{ $website->domain }}</p>
                <p><strong>{{ __('Website ID') }}:</strong> {{ $website->uuid }}</p>
                <p><strong>{{ __('Verified At') }}:</strong> {{ $website->verified_at->format('F j, Y \a\t g:i A') }}</p>
            </div>
            
            <!-- CTA Button -->
            <div style="text-align: center;">
                <a href="{{ route('dashboard.websites.show', $website->uuid) }}" class="button">
                    {{ __('View Dashboard') }}
                </a>
            </div>
            
            <!-- Next Steps -->
            <div class="next-steps">
                <h3>{{ __('What\'s Next?') }}</h3>
                <ul>
                    <li>{{ __('Get your API keys from the dashboard') }}</li>
                    <li>{{ __('Add WebBloc components to your website') }}</li>
                    <li>{{ __('Customize component settings and appearance') }}</li>
                    <li>{{ __('Monitor usage and analytics') }}</li>
                </ul>
            </div>
            
            <!-- Integration Code Example -->
            <div class="message">
                {{ __('To get started, add this code to your website:') }}
            </div>
            
            <div class="code-block" dir="ltr">
&lt;!-- {{ __('WebBloc CSS') }} --&gt;
&lt;link href="{{ config('app.url') }}/webbloc/css/webbloc.min.css" rel="stylesheet"&gt;

&lt;!-- {{ __('WebBloc JavaScript') }} --&gt;
&lt;script src="{{ config('app.url') }}/webbloc/js/webbloc.min.js"&gt;&lt;/script&gt;

&lt;!-- {{ __('Initialize WebBloc') }} --&gt;
&lt;script&gt;
WebBloc.init({
    apiUrl: '{{ config('app.url') }}/api',
    publicKey: 'your-public-api-key',
    websiteId: '{{ $website->uuid }}',
    locale: '{{ app()->getLocale() }}'
});
&lt;/script&gt;

&lt;!-- {{ __('Add Components') }} --&gt;
&lt;div webbloc="auth"&gt;&lt;/div&gt;
&lt;div webbloc="comments" data-limit="10"&gt;&lt;/div&gt;
&lt;div webbloc="reviews" data-item-id="product-1"&gt;&lt;/div&gt;
            </div>
            
            <div class="message">
                {{ __('Need help? Check out our') }} <a href="{{ config('app.url') }}/docs" style="color: #3b82f6;">{{ __('documentation') }}</a> {{ __('or') }} <a href="{{ route('contact') }}" style="color: #3b82f6;">{{ __('contact our support team') }}</a>.
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>{{ __('Thank you for choosing WebBloc!') }}</p>
            
            <div class="social-links">
                <a href="#">{{ __('Documentation') }}</a> |
                <a href="{{ route('contact') }}">{{ __('Support') }}</a> |
                <a href="#">{{ __('Community') }}</a>
            </div>
            
            <p>
                {{ __('WebBloc - Dynamic components for static websites') }}<br>
                <a href="{{ config('app.url') }}">{{ config('app.url') }}</a>
            </p>
            
            <p style="font-size: 12px; color: #9ca3af;">
                {{ __('You received this email because your website was verified on WebBloc.') }}<br>
                {{ __('If you have any questions, please') }} <a href="{{ route('contact') }}">{{ __('contact us') }}</a>.
            </p>
        </div>
    </div>
</body>
</html>
```

#### resources/views/emails/api-key-generated.blade.php
```php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('New API Key Generated - WebBloc') }}</title>
    <style>
        /* Email-safe CSS */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #ffffff;
            padding: 30px 40px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .content {
            padding: 40px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #1f2937;
        }
        .message {
            font-size: 16px;
            margin-bottom: 30px;
            color: #4b5563;
        }
        .api-key-info {
            background-color: #fef3c7;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            border-left: 4px solid #f59e0b;
        }
        .api-key-info h3 {
            margin: 0 0 15px 0;
            color: #92400e;
            font-size: 18px;
        }
        .key-container {
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            word-break: break-all;
            position: relative;
        }
        .key-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .key-value {
            color: #1f2937;
            background-color: #f9fafb;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
        }
        .security-notice {
            background-color: #fef2f2;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            border-left: 4px solid #ef4444;
        }
        .security-notice h3 {
            color: #dc2626;
            margin: 0 0 15px 0;
            display: flex;
            align-items: center;
        }
        .security-notice ul {
            margin: 0;
            padding-left: 20px;
            color: #991b1b;
        }
        .security-notice li {
            margin-bottom: 8px;
        }
        .button {
            display: inline-block;
            background-color: #3b82f6;
            color: #ffffff;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #2563eb;
        }
        .usage-info {
            background-color: #f0f9ff;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            border-left: 4px solid #0ea5e9;
        }
        .usage-info h3 {
            color: #0c4a6e;
            margin: 0 0 15px 0;
        }
        .code-block {
            background-color: #1f2937;
            color: #e5e7eb;
            padding: 20px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            overflow-x: auto;
            margin: 20px 0;
        }
        .footer {
            background-color: #f9fafb;
            padding: 30px 40px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            border-top: 1px solid #e5e7eb;
        }
        .footer a {
            color: #3b82f6;
            text-decoration: none;
        }
        
        /* RTL Support */
        [dir="rtl"] .api-key-info {
            border-left: none;
            border-right: 4px solid #f59e0b;
        }
        [dir="rtl"] .security-notice {
            border-left: none;
            border-right: 4px solid #ef4444;
        }
        [dir="rtl"] .usage-info {
            border-left: none;
            border-right: 4px solid #0ea5e9;
        }
        [dir="rtl"] .security-notice ul {
            padding-left: 0;
            padding-right: 20px;
        }
        
        /* Mobile Responsive */
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 0;
                border-radius: 0;
            }
            .header, .content, .footer {
                padding: 20px;
            }
            .header h1 {
                font-size: 24px;
            }
            .key-container {
                font-size: 12px;
                padding: 12px;
            }
            .code-block {
                font-size: 12px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>🔑 {{ __('New API Key Generated') }}</h1>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="greeting">
                {{ __('Hello') }} {{ $user->name }},
            </div>
            
            <div class="message">
                {{ __('A new API key has been generated for your website. Please store these keys securely as they provide access to your WebBloc components.') }}
            </div>
            
            <!-- API Key Information -->
            <div class="api-key-info">
                <h3>⚠️ {{ __('Important: Save Your API Keys') }}</h3>
                <p style="margin: 0; color: #92400e;">{{ __('These keys will only be shown once. Make sure to copy and store them securely before closing this email.') }}</p>
            </div>
            
            <!-- Website Information -->
            <div style="background-color: #f3f4f6; border-radius: 8px; padding: 20px; margin: 20px 0;">
                <h3 style="margin: 0 0 10px 0; color: #1f2937;">{{ __('Website Details') }}</h3>
                <p style="margin: 5px 0; color: #6b7280;"><strong>{{ __('Name') }}:</strong> {{ $website->name }}</p>
                <p style="margin: 5px 0; color: #6b7280;"><strong>{{ __('Domain') }}:</strong> {{ $website->domain }}</p>
                <p style="margin: 5px 0; color: #6b7280;"><strong>{{ __('Website ID') }}:</strong> {{ $website->uuid }}</p>
            </div>
            
            <!-- API Keys -->
            <div class="key-container">
                <div class="key-label">{{ __('Public API Key') }} ({{ __('for client-side use') }})</div>
                <div class="key-value">{{ $publicKey }}</div>
            </div>
            
            <div class="key-container">
                <div class="key-label">{{ __('Secret API Key') }} ({{ __('for server-side use only') }})</div>
                <div class="key-value">{{ $secretKey }}</div>
            </div>
            
            <!-- Security Notice -->
            <div class="security-notice">
                <h3>🔒 {{ __('Security Best Practices') }}</h3>
                <ul>
                    <li>{{ __('Never expose your Secret API Key in client-side code') }}</li>
                    <li>{{ __('Store API keys securely in environment variables') }}</li>
                    <li>{{ __('Use the Public Key only for frontend components') }}</li>
                    <li>{{ __('Use the Secret Key only for server-side API calls') }}</li>
                    <li>{{ __('Regenerate keys immediately if compromised') }}</li>
                    <li>{{ __('Monitor API usage regularly in your dashboard') }}</li>
                </ul>
            </div>
            
            <!-- CTA Button -->
            <div style="text-align: center;">
                <a href="{{ route('dashboard.websites.show', $website->uuid) }}" class="button">
                    {{ __('Go to Dashboard') }}
                </a>
            </div>
            
            <!-- Usage Information -->
            <div class="usage-info">
                <h3>📚 {{ __('How to Use Your API Keys') }}</h3>
                <p style="margin: 0 0 15px 0; color: #0c4a6e;">{{ __('Here\'s how to integrate WebBloc into your website:') }}</p>
                
                <div class="message">
                    <strong>{{ __('1. Client-side Integration (Public Key):') }}</strong>
                </div>
                
                <div class="code-block" dir="ltr">
&lt;script&gt;
WebBloc.init({
    apiUrl: '{{ config('app.url') }}/api',
    publicKey: '{{ $publicKey }}',
    websiteId: '{{ $website->uuid }}',
    locale: '{{ app()->getLocale() }}'
});
&lt;/script&gt;
                </div>
                
                <div class="message">
                    <strong>{{ __('2. Server-side API Calls (Secret Key):') }}</strong>
                </div>
                
                <div class="code-block" dir="ltr">
// {{ __('Example: PHP cURL request') }}
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => '{{ config('app.url') }}/api/auth/user',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer {{ $secretKey }}',
        'X-Website-UUID: {{ $website->uuid }}',
        'Content-Type: application/json',
        'Accept: application/json'
    ]
]);
$response = curl_exec($curl);
curl_close($curl);
                </div>
            </div>
            
            <div class="message">
                {{ __('Need help? Check out our') }} <a href="{{ config('app.url') }}/docs" style="color: #3b82f6;">{{ __('documentation') }}</a> {{ __('or') }} <a href="{{ route('contact') }}" style="color: #3b82f6;">{{ __('contact our support team') }}</a>.
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>{{ __('Your API keys have been generated successfully!') }}</p>
            
            <p style="margin: 20px 0;">
                <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a> |
                <a href="{{ config('app.url') }}/docs">{{ __('Documentation') }}</a> |
                <a href="{{ route('contact') }}">{{ __('Support') }}</a>
            </p>
            
            <p>
                {{ __('WebBloc - Dynamic components for static websites') }}<br>
                <a href="{{ config('app.url') }}">{{ config('app.url') }}</a>
            </p>
            
            <p style="font-size: 12px; color: #9ca3af;">
                {{ __('You received this email because new API keys were generated for your WebBloc website.') }}<br>
                {{ __('If you didn\'t request this, please') }} <a href="{{ route('contact') }}">{{ __('contact us immediately') }}</a>.
            </p>
        </div>
    </div>
</body>
</html>
```
