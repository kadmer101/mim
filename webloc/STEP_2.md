Based on my analysis of the complete project documentation, I now understand this is a comprehensive Laravel-based WebBloc system that provides dynamic web components as an API for static websites. The system features multi-language support (English/Arabic), multi-tenant architecture, UUID-based routing, soft deletes, and Alpine.js integration.

Now I'll provide all the blade view codes as requested:

## 1. Dashboard Layout Views

### resources/views/layouts/dashboard.blade.php
```php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - @yield('title', __('messages.dashboard'))</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- RTL Support -->
    @if(app()->getLocale() == 'ar')
    <style>
        body { font-family: 'Tajawal', sans-serif; }
        .rtl-flip { transform: scaleX(-1); }
    </style>
    @endif
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <x-sidebar />
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <x-navbar />
            
            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 dark:bg-gray-900">
                <div class="container mx-auto px-6 py-8">
                    <!-- Flash Messages -->
                    @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-transition class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                        <span class="absolute top-0 bottom-0 {{ app()->getLocale() == 'ar' ? 'left-0 pl-4' : 'right-0 pr-4' }}" @click="show = false">
                            <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <title>{{ __('messages.close') }}</title>
                                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                            </svg>
                        </span>
                    </div>
                    @endif
                    
                    @if(session('error'))
                    <div x-data="{ show: true }" x-show="show" x-transition class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                        <span class="absolute top-0 bottom-0 {{ app()->getLocale() == 'ar' ? 'left-0 pl-4' : 'right-0 pr-4' }}" @click="show = false">
                            <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <title>{{ __('messages.close') }}</title>
                                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                            </svg>
                        </span>
                    </div>
                    @endif
                    
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    
    <!-- Global Alpine.js Data -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('globalData', () => ({
                locale: '{{ app()->getLocale() }}',
                isRtl: {{ app()->getLocale() == 'ar' ? 'true' : 'false' }},
                showToast(message, type = 'success') {
                    Swal.fire({
                        toast: true,
                        position: this.isRtl ? 'top-start' : 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        icon: type,
                        title: message
                    });
                },
                confirmDelete(url, message = '{{ __("messages.confirm_delete") }}') {
                    Swal.fire({
                        title: '{{ __("messages.are_you_sure") }}',
                        text: message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: '{{ __("messages.yes_delete") }}',
                        cancelButtonText: '{{ __("messages.cancel") }}'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(url, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    this.showToast(data.message);
                                    setTimeout(() => window.location.reload(), 1500);
                                } else {
                                    this.showToast(data.message, 'error');
                                }
                            })
                            .catch(error => {
                                this.showToast('{{ __("messages.error_occurred") }}', 'error');
                            });
                        }
                    });
                }
            }));
        });
    </script>
    
    @stack('scripts')
</body>
</html>
```

### resources/views/components/sidebar.blade.php
```php
<div class="bg-gray-900 text-white w-64 min-h-screen px-4 py-6 {{ app()->getLocale() == 'ar' ? 'border-l border-gray-700' : 'border-r border-gray-700' }}">
    <!-- Logo -->
    <div class="flex items-center justify-center mb-8">
        <a href="{{ route('dashboard.index') }}" class="text-2xl font-bold text-white">
            WebBloc
        </a>
    </div>
    
    <!-- Navigation Menu -->
    <nav x-data="{ activeMenu: '{{ request()->route()->getName() }}' }">
        <!-- Dashboard -->
        <div class="mb-2">
            <a href="{{ route('dashboard.index') }}" 
               class="flex items-center py-3 px-4 rounded-lg transition-colors {{ request()->routeIs('dashboard.index') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"/>
                </svg>
                {{ __('messages.dashboard') }}
            </a>
        </div>
        
        <!-- Websites -->
        <div class="mb-2" x-data="{ open: {{ request()->routeIs('dashboard.websites.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" 
                    class="flex items-center justify-between w-full py-3 px-4 rounded-lg transition-colors {{ request()->routeIs('dashboard.websites.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <div class="flex items-center">
                    <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"/>
                    </svg>
                    {{ __('messages.websites') }}
                </div>
                <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <ul x-show="open" x-transition class="ml-8 mt-2 space-y-1">
                <li>
                    <a href="{{ route('dashboard.websites.index') }}" 
                       class="block py-2 px-4 rounded-lg text-sm {{ request()->routeIs('dashboard.websites.index') ? 'bg-blue-500 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-700' }}">
                        {{ __('messages.all_websites') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('dashboard.websites.create') }}" 
                       class="block py-2 px-4 rounded-lg text-sm {{ request()->routeIs('dashboard.websites.create') ? 'bg-blue-500 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-700' }}">
                        {{ __('messages.add_website') }}
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Components -->
        <div class="mb-2" x-data="{ open: {{ request()->routeIs('dashboard.components.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" 
                    class="flex items-center justify-between w-full py-3 px-4 rounded-lg transition-colors {{ request()->routeIs('dashboard.components.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <div class="flex items-center">
                    <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    {{ __('messages.components') }}
                </div>
                <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <ul x-show="open" x-transition class="ml-8 mt-2 space-y-1">
                <li>
                    <a href="{{ route('dashboard.components.index') }}" 
                       class="block py-2 px-4 rounded-lg text-sm {{ request()->routeIs('dashboard.components.index') ? 'bg-blue-500 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-700' }}">
                        {{ __('messages.all_components') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('dashboard.components.create') }}" 
                       class="block py-2 px-4 rounded-lg text-sm {{ request()->routeIs('dashboard.components.create') ? 'bg-blue-500 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-700' }}">
                        {{ __('messages.add_component') }}
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Statistics -->
        <div class="mb-2" x-data="{ open: {{ request()->routeIs('dashboard.statistics.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" 
                    class="flex items-center justify-between w-full py-3 px-4 rounded-lg transition-colors {{ request()->routeIs('dashboard.statistics.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <div class="flex items-center">
                    <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 00-2-2z"/>
                    </svg>
                    {{ __('messages.statistics') }}
                </div>
                <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <ul x-show="open" x-transition class="ml-8 mt-2 space-y-1">
                <li>
                    <a href="{{ route('dashboard.statistics.index') }}" 
                       class="block py-2 px-4 rounded-lg text-sm {{ request()->routeIs('dashboard.statistics.index') ? 'bg-blue-500 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-700' }}">
                        {{ __('messages.overview') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('dashboard.statistics.website') }}" 
                       class="block py-2 px-4 rounded-lg text-sm {{ request()->routeIs('dashboard.statistics.website') ? 'bg-blue-500 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-700' }}">
                        {{ __('messages.website_stats') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('dashboard.statistics.component') }}" 
                       class="block py-2 px-4 rounded-lg text-sm {{ request()->routeIs('dashboard.statistics.component') ? 'bg-blue-500 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-700' }}">
                        {{ __('messages.component_stats') }}
                    </a>
                </li>
            </ul>
        </div>
        
        @hasrole('admin')
        <!-- User Management -->
        <div class="mb-2" x-data="{ open: {{ request()->routeIs('dashboard.users.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" 
                    class="flex items-center justify-between w-full py-3 px-4 rounded-lg transition-colors {{ request()->routeIs('dashboard.users.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <div class="flex items-center">
                    <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
                    </svg>
                    {{ __('messages.users') }}
                </div>
                <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <ul x-show="open" x-transition class="ml-8 mt-2 space-y-1">
                <li>
                    <a href="{{ route('dashboard.users.index') }}" 
                       class="block py-2 px-4 rounded-lg text-sm {{ request()->routeIs('dashboard.users.index') ? 'bg-blue-500 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-700' }}">
                        {{ __('messages.all_users') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('dashboard.users.create') }}" 
                       class="block py-2 px-4 rounded-lg text-sm {{ request()->routeIs('dashboard.users.create') ? 'bg-blue-500 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-700' }}">
                        {{ __('messages.add_user') }}
                    </a>
                </li>
            </ul>
        </div>
        @endhasrole
        
        <!-- API Documentation -->
        <div class="mb-2">
            <a href="{{ route('docs.index') }}" 
               class="flex items-center py-3 px-4 rounded-lg transition-colors {{ request()->routeIs('docs.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                {{ __('messages.api_docs') }}
            </a>
        </div>
        
        <!-- Profile -->
        <div class="mb-2">
            <a href="{{ route('dashboard.profile') }}" 
               class="flex items-center py-3 px-4 rounded-lg transition-colors {{ request()->routeIs('dashboard.profile') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                {{ __('messages.profile') }}
            </a>
        </div>
    </nav>
</div>
```

### resources/views/components/navbar.blade.php
```php
<header class="bg-white shadow-lg dark:bg-gray-800 {{ app()->getLocale() == 'ar' ? 'border-l' : 'border-r' }} border-gray-200 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Left Section -->
            <div class="flex items-center">
                <!-- Page Title -->
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                    @yield('page-title', __('messages.dashboard'))
                </h1>
            </div>
            
            <!-- Right Section -->
            <div class="flex items-center space-x-4 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <!-- Language Switcher -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" 
                            class="flex items-center text-sm bg-white dark:bg-gray-800 rounded-md text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 px-3 py-2">
                        <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                        </svg>
                        <span class="uppercase">{{ app()->getLocale() }}</span>
                        <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? 'mr-1' : 'ml-1' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    
                    <div x-show="open" 
                         @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute {{ app()->getLocale() == 'ar' ? 'left-0' : 'right-0' }} mt-2 w-32 bg-white dark:bg-gray-700 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                        <div class="py-1">
                            <a href="{{ route('locale.switch', 'en') }}" 
                               class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 {{ app()->getLocale() == 'en' ? 'bg-gray-100 dark:bg-gray-600' : '' }}">
                                <img src="https://cdn.jsdelivr.net/npm/flag-icon-css@3.5.0/flags/4x3/us.svg" 
                                     alt="English" class="w-4 h-3 {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}">
                                English
                            </a>
                            <a href="{{ route('locale.switch', 'ar') }}" 
                               class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 {{ app()->getLocale() == 'ar' ? 'bg-gray-100 dark:bg-gray-600' : '' }}">
                                <img src="https://cdn.jsdelivr.net/npm/flag-icon-css@3.5.0/flags/4x3/sa.svg" 
                                     alt="العربية" class="w-4 h-3 {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}">
                                العربية
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Notifications -->
                <div x-data="{ 
                    open: false, 
                    notifications: [],
                    unreadCount: 0,
                    async fetchNotifications() {
                        try {
                            const response = await fetch('/api/notifications');
                            const data = await response.json();
                            this.notifications = data.notifications || [];
                            this.unreadCount = data.unread_count || 0;
                        } catch (error) {
                            console.error('Error fetching notifications:', error);
                        }
                    }
                }" 
                x-init="fetchNotifications()" 
                class="relative">
                    <button @click="open = !open; if(open) fetchNotifications()" 
                            class="relative p-2 text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 rounded-md">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-3.5-3.5L15 17zm-3.5-10.5L8 10l-3.5-3.5L8 3l3.5 3.5zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span x-show="unreadCount > 0" 
                              x-text="unreadCount" 
                              class="absolute -top-1 {{ app()->getLocale() == 'ar' ? '-left-1' : '-right-1' }} bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"></span>
                    </button>
                    
                    <div x-show="open" 
                         @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute {{ app()->getLocale() == 'ar' ? 'left-0' : 'right-0' }} mt-2 w-80 bg-white dark:bg-gray-700 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                        <div class="p-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">{{ __('messages.notifications') }}</h3>
                            <div x-show="notifications.length === 0" class="text-gray-500 dark:text-gray-400 text-center py-4">
                                {{ __('messages.no_notifications') }}
                            </div>
                            <div class="space-y-2 max-h-64 overflow-y-auto">
                                <template x-for="notification in notifications" :key="notification.id">
                                    <div class="p-3 bg-gray-50 dark:bg-gray-600 rounded-md">
                                        <p class="text-sm text-gray-800 dark:text-gray-200" x-text="notification.message"></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="notification.created_at"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- User Dropdown -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" 
                            class="flex items-center text-sm bg-white dark:bg-gray-800 rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <img class="h-8 w-8 rounded-full" 
                             src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&color=7F9CF5&background=EBF4FF" 
                             alt="{{ auth()->user()->name }}">
                        <span class="{{ app()->getLocale() == 'ar' ? 'mr-2' : 'ml-2' }} text-gray-700 dark:text-gray-200 font-medium">{{ auth()->user()->name }}</span>
                        <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? 'mr-1' : 'ml-1' }} text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    
                    <div x-show="open" 
                         @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute {{ app()->getLocale() == 'ar' ? 'left-0' : 'right-0' }} mt-2 w-48 bg-white dark:bg-gray-700 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                        <div class="py-1">
                            <a href="{{ route('dashboard.profile') }}" 
                               class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                {{ __('messages.profile') }}
                            </a>
                            <a href="{{ route('dashboard.settings') }}" 
                               class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ __('messages.settings') }}
                            </a>
                            <div class="border-t border-gray-100 dark:border-gray-600"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" 
                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    {{ __('messages.logout') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
```

## 2. Dashboard Main Views

### resources/views/dashboard/index.blade.php
```php
@extends('layouts.dashboard')

@section('title', __('messages.dashboard'))
@section('page-title', __('messages.dashboard'))

@section('content')
<div x-data="dashboardData()" x-init="init()" class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Websites -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"/>
                        </svg>
                    </div>
                    <div class="{{ app()->getLocale() == 'ar' ? 'mr-5' : 'ml-5' }} w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                {{ __('messages.total_websites') }}
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white" x-text="stats.websites || 0">
                                0
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('dashboard.websites.index') }}" class="font-medium text-blue-700 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                        {{ __('messages.view_all') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Active Components -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div class="{{ app()->getLocale() == 'ar' ? 'mr-5' : 'ml-5' }} w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                {{ __('messages.active_components') }}
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white" x-text="stats.components || 0">
                                0
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('dashboard.components.index') }}" class="font-medium text-green-700 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300">
                        {{ __('messages.view_all') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- API Requests Today -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div class="{{ app()->getLocale() == 'ar' ? 'mr-5' : 'ml-5' }} w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                {{ __('messages.api_requests_today') }}
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white" x-text="stats.requests_today || 0">
                                0
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-5 py-3">
                <div class="text-sm">
                    <span class="text-yellow-700 dark:text-yellow-400" x-text="stats.requests_growth || '+0%'">
                        +0%
                    </span>
                    <span class="text-gray-500 dark:text-gray-400">{{ __('messages.from_yesterday') }}</span>
                </div>
            </div>
        </div>

        <!-- Total Users -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
                        </svg>
                    </div>
                    <div class="{{ app()->getLocale() == 'ar' ? 'mr-5' : 'ml-5' }} w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                {{ __('messages.total_users') }}
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white" x-text="stats.users || 0">
                                0
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('dashboard.users.index') }}" class="font-medium text-purple-700 dark:text-purple-400 hover:text-purple-900 dark:hover:text-purple-300">
                        {{ __('messages.view_all') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- API Usage Chart -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    {{ __('messages.api_usage_last_7_days') }}
                </h3>
                <div class="mt-5">
                    <canvas id="apiUsageChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Component Usage -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    {{ __('messages.popular_components') }}
                </h3>
                <div class="mt-5">
                    <div class="space-y-3">
                        <template x-for="component in popularComponents" :key="component.type">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-blue-500 rounded mr-3"></div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white" x-text="component.name"></span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-sm text-gray-500 dark:text-gray-400" x-text="component.usage_count"></span>
                                    <span class="text-xs text-gray-400 dark:text-gray-500 ml-2">{{ __('messages.requests') }}</span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                {{ __('messages.recent_activity') }}
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                {{ __('messages.latest_system_activity') }}
            </p>
        </div>
        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
            <template x-for="activity in recentActivity" :key="activity.id">
                <li class="px-4 py-4 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="{{ app()->getLocale() == 'ar' ? 'mr-4' : 'ml-4' }}">
                                <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="activity.message"></p>
                                <p class="text-sm text-gray-500 dark:text-gray-400" x-text="activity.created_at"></p>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                  :class="{
                                      'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': activity.type === 'success',
                                      'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': activity.type === 'warning',
                                      'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': activity.type === 'error',
                                      'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': activity.type === 'info'
                                  }"
                                  x-text="activity.type_label">
                            </span>
                        </div>
                    </div>
                </li>
            </template>
        </ul>
        <div x-show="recentActivity.length === 0" class="px-4 py-8 text-center">
            <p class="text-gray-500 dark:text-gray-400">{{ __('messages.no_recent_activity') }}</p>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function dashboardData() {
    return {
        stats: {},
        popularComponents: [],
        recentActivity: [],
        
        async init() {
            await this.loadStats();
            this.initCharts();
        },
        
        async loadStats() {
            try {
                const response = await fetch('/api/dashboard/stats', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.stats = data.stats || {};
                    this.popularComponents = data.popular_components || [];
                    this.recentActivity = data.recent_activity || [];
                }
            } catch (error) {
                console.error('Error loading dashboard stats:', error);
            }
        },
        
        initCharts() {
            // API Usage Chart
            const ctx = document.getElementById('apiUsageChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: this.stats.api_usage_labels || [],
                        datasets: [{
                            label: '{{ __("messages.api_requests") }}',
                            data: this.stats.api_usage_data || [],
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        }
    }
}
</script>
@endpush
@endsection
```

### resources/views/dashboard/profile.blade.php
```php
@extends('layouts.dashboard')

@section('title', __('messages.profile'))
@section('page-title', __('messages.profile'))

@section('content')
<div x-data="profileData()" class="max-w-4xl mx-auto space-y-6">
    <!-- Profile Header -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4">
            <div class="flex items-center space-x-4 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <img class="h-16 w-16 rounded-full" 
                     src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&color=7F9CF5&background=EBF4FF&size=128" 
                     alt="{{ auth()->user()->name }}">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ auth()->user()->name }}</h1>
                    <p class="text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</p>
                    <p class="text-sm text-blue-600 dark:text-blue-400">
                        {{ __('messages.member_since') }} {{ auth()->user()->created_at->format('M Y') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Information -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('messages.profile_information') }}</h2>
        </div>
        <form @submit.prevent="updateProfile" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('messages.name') }}
                    </label>
                    <input type="text" 
                           id="name" 
                           x-model="form.name"
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <div x-show="errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400" x-text="errors.name"></div>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('messages.email') }}
                    </label>
                    <input type="email" 
                           id="email" 
                           x-model="form.email"
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <div x-show="errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400" x-text="errors.email"></div>
                </div>

                <!-- Locale -->
                <div>
                    <label for="locale" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('messages.preferred_language') }}
                    </label>
                    <select id="locale" 
                            x-model="form.locale"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="en">English</option>
                        <option value="ar">العربية</option>
                    </select>
                </div>

                <!-- Timezone -->
                <div>
                    <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('messages.timezone') }}
                    </label>
                    <select id="timezone" 
                            x-model="form.timezone"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="UTC">UTC</option>
                        <option value="America/New_York">Eastern Time</option>
                        <option value="America/Chicago">Central Time</option>
                        <option value="America/Denver">Mountain Time</option>
                        <option value="America/Los_Angeles">Pacific Time</option>
                        <option value="Europe/London">London</option>
                        <option value="Europe/Paris">Paris</option>
                        <option value="Asia/Dubai">Dubai</option>
                        <option value="Asia/Riyadh">Riyadh</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" 
                        :disabled="loading"
                        class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-bold py-2 px-4 rounded-md transition-colors">
                    <span x-show="!loading">{{ __('messages.update_profile') }}</span>
                    <span x-show="loading">{{ __('messages.updating') }}...</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Change Password -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('messages.change_password') }}</h2>
        </div>
        <form @submit.prevent="changePassword" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Current Password -->
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('messages.current_password') }}
                    </label>
                    <input type="password" 
                           id="current_password" 
                           x-model="passwordForm.current_password"
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <div x-show="passwordForm.errors.current_password" class="mt-1 text-sm text-red-600 dark:text-red-400" x-text="passwordForm.errors.current_password"></div>
                </div>

                <div></div>

                <!-- New Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('messages.new_password') }}
                    </label>
                    <input type="password" 
                           id="password" 
                           x-model="passwordForm.password"
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <div x-show="passwordForm.errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400" x-text="passwordForm.errors.password"></div>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('messages.confirm_password') }}
                    </label>
                    <input type="password" 
                           id="password_confirmation" 
                           x-model="passwordForm.password_confirmation"
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <div x-show="passwordForm.errors.password_confirmation" class="mt-1 text-sm text-red-600 dark:text-red-400" x-text="passwordForm.errors.password_confirmation"></div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" 
                        :disabled="passwordForm.loading"
                        class="bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white font-bold py-2 px-4 rounded-md transition-colors">
                    <span x-show="!passwordForm.loading">{{ __('messages.change_password') }}</span>
                    <span x-show="passwordForm.loading">{{ __('messages.changing') }}...</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Account Statistics -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('messages.account_statistics') }}</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400" x-text="accountStats.websites || 0">0</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.websites') }}</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400" x-text="accountStats.api_requests || 0">0</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.api_requests') }}</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400" x-text="accountStats.components_used || 0">0</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.components_used') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function profileData() {
    return {
        loading: false,
        errors: {},
        form: {
            name: '{{ auth()->user()->name }}',
            email: '{{ auth()->user()->email }}',
            locale: '{{ auth()->user()->locale ?? app()->getLocale() }}',
            timezone: 'UTC'
        },
        passwordForm: {
            loading: false,
            errors: {},
            current_password: '',
            password: '',
            password_confirmation: ''
        },
        accountStats: {},
        
        async updateProfile() {
            this.loading = true;
            this.errors = {};
            
            try {
                const response = await fetch('{{ route("dashboard.profile.update") }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.form)
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.$dispatch('show-toast', { 
                        message: data.message || '{{ __("messages.profile_updated") }}', 
                        type: 'success' 
                    });
                    
                    // Refresh page if locale changed
                    if (this.form.locale !== '{{ app()->getLocale() }}') {
                        setTimeout(() => window.location.reload(), 1500);
                    }
                } else {
                    this.errors = data.errors || {};
                    this.$dispatch('show-toast', { 
                        message: data.message || '{{ __("messages.error_occurred") }}', 
                        type: 'error' 
                    });
                }
            } catch (error) {
                console.error('Error updating profile:', error);
                this.$dispatch('show-toast', { 
                    message: '{{ __("messages.error_occurred") }}', 
                    type: 'error' 
                });
            }
            
            this.loading = false;
        },
        
        async changePassword() {
            this.passwordForm.loading = true;
            this.passwordForm.errors = {};
            
            try {
                const response = await fetch('{{ route("dashboard.profile.password") }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        current_password: this.passwordForm.current_password,
                        password: this.passwordForm.password,
                        password_confirmation: this.passwordForm.password_confirmation
                    })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.$dispatch('show-toast', { 
                        message: data.message || '{{ __("messages.password_changed") }}', 
                        type: 'success' 
                    });
                    
                    // Clear form
                    this.passwordForm.current_password = '';
                    this.passwordForm.password = '';
                    this.passwordForm.password_confirmation = '';
                } else {
                    this.passwordForm.errors = data.errors || {};
                    this.$dispatch('show-toast', { 
                        message: data.message || '{{ __("messages.error_occurred") }}', 
                        type: 'error' 
                    });
                }
            } catch (error) {
                console.error('Error changing password:', error);
                this.$dispatch('show-toast', { 
                    message: '{{ __("messages.error_occurred") }}', 
                    type: 'error' 
                });
            }
            
            this.passwordForm.loading = false;
        },
        
        async loadAccountStats() {
            try {
                const response = await fetch('/api/profile/stats', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.accountStats = data.stats || {};
                }
            } catch (error) {
                console.error('Error loading account stats:', error);
            }
        },
        
        init() {
            this.loadAccountStats();
        }
    }
}
</script>
@endpush
@endsection
```

## 3. Website Management Views

### resources/views/dashboard/websites/index.blade.php
```php
@extends('layouts.dashboard')

@section('title', __('messages.websites'))
@section('page-title', __('messages.websites'))

@section('content')
<div x-data="websitesData()" x-init="loadWebsites()" class="space-y-6">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('messages.websites') }}</h1>
            <p class="text-gray-500 dark:text-gray-400">{{ __('messages.manage_your_websites') }}</p>
        </div>
        <div class="flex space-x-4 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
            <a href="{{ route('dashboard.websites.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md transition-colors inline-flex items-center">
                <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                {{ __('messages.add_website') }}
            </a>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.search') }}
                </label>
                <input type="text" 
                       id="search" 
                       x-model="filters.search"
                       @input.debounce.500ms="loadWebsites()"
                       placeholder="{{ __('messages.search_websites') }}"
                       class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.status') }}
                </label>
                <select x-model="filters.status" 
                        @change="loadWebsites()"
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">{{ __('messages.all_statuses') }}</option>
                    <option value="active">{{ __('messages.active') }}</option>
                    <option value="inactive">{{ __('messages.inactive') }}</option>
                    <option value="verified">{{ __('messages.verified') }}</option>
                    <option value="unverified">{{ __('messages.unverified') }}</option>
                </select>
            </div>

            <!-- Per Page -->
            <div>
                <label for="per_page" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.per_page') }}
                </label>
                <select x-model="perPage" 
                        @change="loadWebsites()"
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Websites List -->
    <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
        <!-- Loading State -->
        <div x-show="loading" class="p-8 text-center">
            <div class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-white bg-blue-500 transition ease-in-out duration-150">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ __('messages.loading') }}...
            </div>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && websites.length === 0" class="p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('messages.no_websites') }}</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.get_started_by_creating_website') }}</p>
            <div class="mt-6">
                <a href="{{ route('dashboard.websites.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    {{ __('messages.add_website') }}
                </a>
            </div>
        </div>

        <!-- Websites Table -->
        <div x-show="!loading && websites.length > 0">
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                <template x-for="website in websites" :key="website.uuid">
                    <li class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                                <!-- Website Icon -->
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 rounded-lg bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"/>
                                        </svg>
                                    </div>
                                </div>

                                <!-- Website Info -->
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                                        <h3 class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="website.name"></h3>
                                        
                                        <!-- Status Badge -->
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                              :class="{
                                                  'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': website.is_active && website.verified_at,
                                                  'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': website.is_active && !website.verified_at,
                                                  'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': !website.is_active
                                              }">
                                            <span x-show="website.is_active && website.verified_at">{{ __('messages.active') }}</span>
                                            <span x-show="website.is_active && !website.verified_at">{{ __('messages.pending') }}</span>
                                            <span x-show="!website.is_active">{{ __('messages.inactive') }}</span>
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center space-x-4 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }} mt-1">
                                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate" x-text="website.domain"></p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500" x-text="website.created_at"></p>
                                    </div>

                                    <!-- Components Count -->
                                    <div class="mt-2 flex items-center space-x-4 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                                        <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                                            <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? 'ml-1' : 'mr-1' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                            </svg>
                                            <span x-text="website.components_count || 0"></span> {{ __('messages.components') }}
                                        </div>
                                        <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                                            <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? 'ml-1' : 'mr-1' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                            <span x-text="website.requests_count || 0"></span> {{ __('messages.requests') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                                <!-- View Details -->
                                <a :href="`/dashboard/websites/${website.uuid}`" 
                                   class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 text-sm font-medium">
                                    {{ __('messages.view') }}
                                </a>

                                <!-- Edit -->
                                <a :href="`/dashboard/websites/${website.uuid}/edit`" 
                                   class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 text-sm font-medium">
                                    {{ __('messages.edit') }}
                                </a>

                                <!-- Delete -->
                                <button @click="deleteWebsite(website.uuid, website.name)" 
                                        class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 text-sm font-medium">
                                    {{ __('messages.delete') }}
                                </button>

                                <!-- Dropdown Menu -->
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open" 
                                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                        </svg>
                                    </button>
                                    
                                    <div x-show="open" 
                                         @click.outside="open = false"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         class="absolute {{ app()->getLocale() == 'ar' ? 'left-0' : 'right-0' }} mt-2 w-48 bg-white dark:bg-gray-700 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-10">
                                        <div class="py-1">
                                            <button @click="regenerateKeys(website.uuid)" 
                                                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                                <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-3.586l6.879-6.879A6 6 0 0121 9z"/>
                                                </svg>
                                                {{ __('messages.regenerate_keys') }}
                                            </button>
                                            <button @click="toggleStatus(website.uuid, website.is_active)" 
                                                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                                <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="website.is_active ? 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L5.636 5.636' : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'"/>
                                                </svg>
                                                <span x-text="website.is_active ? '{{ __("messages.deactivate") }}' : '{{ __("messages.activate") }}'"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </template>
            </ul>

            <!-- Pagination -->
            <div x-show="pagination.last_page > 1" class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <button @click="loadPage(pagination.current_page - 1)" 
                                :disabled="pagination.current_page <= 1"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ __('messages.previous') }}
                        </button>
                        <button @click="loadPage(pagination.current_page + 1)" 
                                :disabled="pagination.current_page >= pagination.last_page"
                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ __('messages.next') }}
                        </button>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                {{ __('messages.showing') }}
                                <span class="font-medium" x-text="pagination.from || 0"></span>
                                {{ __('messages.to') }}
                                <span class="font-medium" x-text="pagination.to || 0"></span>
                                {{ __('messages.of') }}
                                <span class="font-medium" x-text="pagination.total || 0"></span>
                                {{ __('messages.results') }}
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <!-- Previous Page -->
                                <button @click="loadPage(pagination.current_page - 1)" 
                                        :disabled="pagination.current_page <= 1"
                                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </button>

                                <!-- Page Numbers -->
                                <template x-for="page in paginationPages" :key="page">
                                    <button @click="loadPage(page)" 
                                            :class="{
                                                'bg-blue-50 dark:bg-blue-900 border-blue-500 dark:border-blue-400 text-blue-600 dark:text-blue-200': page === pagination.current_page,
                                                'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-600': page !== pagination.current_page
                                            }"
                                            class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                            x-text="page">
                                    </button>
                                </template>

                                <!-- Next Page -->
                                <button @click="loadPage(pagination.current_page + 1)" 
                                        :disabled="pagination.current_page >= pagination.last_page"
                                        class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function websitesData() {
    return {
        loading: false,
        websites: [],
        pagination: {},
        filters: {
            search: '',
            status: ''
        },
        perPage: 25,
        
        async loadWebsites(page = 1) {
            this.loading = true;
            
            try {
                const params = new URLSearchParams({
                    page: page,
                    per_page: this.perPage,
                    search: this.filters.search,
                    status: this.filters.status
                });
                
                const response = await fetch(`{{ route('dashboard.websites.index') }}?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.websites = data.data || [];
                    this.pagination = {
                        current_page: data.current_page || 1,
                        last_page: data.last_page || 1,
                        from: data.from || 0,
                        to: data.to || 0,
                        total: data.total || 0
                    };
                }
            } catch (error) {
                console.error('Error loading websites:', error);
                this.$dispatch('show-toast', { 
                    message: '{{ __("messages.error_loading_websites") }}', 
                    type: 'error' 
                });
            }
            
            this.loading = false;
        },
        
        loadPage(page) {
            if (page >= 1 && page <= this.pagination.last_page && page !== this.pagination.current_page) {
                this.loadWebsites(page);
            }
        },
        
        get paginationPages() {
            const current = this.pagination.current_page;
            const last = this.pagination.last_page;
            const pages = [];
            
            // Always show first page
            if (current > 3) pages.push(1);
            if (current > 4) pages.push('...');
            
            // Show pages around current
            for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
                pages.push(i);
            }
            
            // Always show last page
            if (current < last - 3) pages.push('...');
            if (current < last - 2) pages.push(last);
            
            return pages.filter(p => p !== '...' || pages.indexOf(p) !== pages.lastIndexOf(p));
        },
        
        async deleteWebsite(uuid, name) {
            const result = await Swal.fire({
                title: '{{ __("messages.are_you_sure") }}',
                text: `{{ __("messages.delete_website_confirm") }} "${name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '{{ __("messages.yes_delete") }}',
                cancelButtonText: '{{ __("messages.cancel") }}'
            });
            
            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/dashboard/websites/${uuid}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok) {
                        this.$dispatch('show-toast', { 
                            message: data.message || '{{ __("messages.website_deleted") }}', 
                            type: 'success' 
                        });
                        this.loadWebsites(this.pagination.current_page);
                    } else {
                        this.$dispatch('show-toast', { 
                            message: data.message || '{{ __("messages.error_occurred") }}', 
                            type: 'error' 
                        });
                    }
                } catch (error) {
                    console.error('Error deleting website:', error);
                    this.$dispatch('show-toast', { 
                        message: '{{ __("messages.error_occurred") }}', 
                        type: 'error' 
                    });
                }
            }
        },
        
        async regenerateKeys(uuid) {
            const result = await Swal.fire({
                title: '{{ __("messages.regenerate_api_keys") }}',
                text: '{{ __("messages.regenerate_keys_warning") }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '{{ __("messages.yes_regenerate") }}',
                cancelButtonText: '{{ __("messages.cancel") }}'
            });
            
            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/dashboard/websites/${uuid}/regenerate-keys`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok) {
                        this.$dispatch('show-toast', { 
                            message: data.message || '{{ __("messages.keys_regenerated") }}', 
                            type: 'success' 
                        });
                        this.loadWebsites(this.pagination.current_page);
                    } else {
                        this.$dispatch('show-toast', { 
                            message: data.message || '{{ __("messages.error_occurred") }}', 
                            type: 'error' 
                        });
                    }
                } catch (error) {
                    console.error('Error regenerating keys:', error);
                    this.$dispatch('show-toast', { 
                        message: '{{ __("messages.error_occurred") }}', 
                        type: 'error' 
                    });
                }
            }
        },
        
        async toggleStatus(uuid, currentStatus) {
            const action = currentStatus ? 'deactivate' : 'activate';
            const actionText = currentStatus ? '{{ __("messages.deactivate") }}' : '{{ __("messages.activate") }}';
            
            const result = await Swal.fire({
                title: `${actionText} {{ __("messages.website") }}?`,
                text: `{{ __("messages.are_you_sure_to") }} ${actionText.toLowerCase()} {{ __("messages.this_website") }}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: currentStatus ? '#ef4444' : '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: actionText,
                cancelButtonText: '{{ __("messages.cancel") }}'
            });
            
            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/dashboard/websites/${uuid}/toggle-status`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok) {
                        this.$dispatch('show-toast', { 
                            message: data.message || '{{ __("messages.status_updated") }}', 
                            type: 'success' 
                        });
                        this.loadWebsites(this.pagination.current_page);
                    } else {
                        this.$dispatch('show-toast', { 
                            message: data.message || '{{ __("messages.error_occurred") }}', 
                            type: 'error' 
                        });
                    }
                } catch (error) {
                    console.error('Error toggling status:', error);
                    this.$dispatch('show-toast', { 
                        message: '{{ __("messages.error_occurred") }}', 
                        type: 'error' 
                    });
                }
            }
        }
    }
}
</script>
@endpush
@endsection
```

Due to length constraints, I'll continue with the remaining blade views in the next part. The views I've provided so far include:

1. **Dashboard Layout Views** - Complete with sidebar, navbar, and main layout
2. **Dashboard Main Views** - Dashboard index and profile pages with full functionality
3. **Website Management Views** - Index page with comprehensive listing, search, filtering, and management features

Each view includes:
- ✅ Multi-language support using `__()` helper
- ✅ RTL support for Arabic language  
- ✅ Responsive design with Tailwind CSS
- ✅ Alpine.js integration for dynamic behavior
- ✅ SweetAlert2 for notifications
- ✅ CSRF protection
- ✅ UUID-based routing
- ✅ Proper error handling and loading states
