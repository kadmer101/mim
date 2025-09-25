Based on my comprehensive analysis of all the WebBloc project documentation, I now understand this is a sophisticated Laravel-based multi-tenant system that provides dynamic web components as APIs for static websites. The system features multi-language support (English/Arabic), UUID-based routing, Alpine.js integration, and comprehensive authentication mechanisms.

Now I'll provide all the requested blade views, language files, and configuration files:

## Mission 1: All Blade Views - Full Codes

### 11. Email Templates

#### resources/views/emails/usage-alert.blade.php
```blade
@component('mail::message')
# {{ __('Usage Limit Alert') }}

{{ __('Hello') }} {{ $user->name }},

@if($alertType === 'warning')
{{ __('Your website') }} **{{ $website->name }}** {{ __('has reached') }} **{{ $usagePercentage }}%** {{ __('of your monthly API request limit.') }}

{{ __('Current usage:') }} **{{ number_format($currentUsage) }}** {{ __('requests') }}
{{ __('Monthly limit:') }} **{{ number_format($monthlyLimit) }}** {{ __('requests') }}
{{ __('Remaining:') }} **{{ number_format($monthlyLimit - $currentUsage) }}** {{ __('requests') }}

@elseif($alertType === 'limit_exceeded')
{{ __('Your website') }} **{{ $website->name }}** {{ __('has exceeded your monthly API request limit.') }}

{{ __('Current usage:') }} **{{ number_format($currentUsage) }}** {{ __('requests') }}
{{ __('Monthly limit:') }} **{{ number_format($monthlyLimit) }}** {{ __('requests') }}
{{ __('Overage:') }} **{{ number_format($currentUsage - $monthlyLimit) }}** {{ __('requests') }}

⚠️ {{ __('Your API requests may be throttled or suspended to prevent additional charges.') }}

@elseif($alertType === 'approaching_limit')
{{ __('Your website') }} **{{ $website->name }}** {{ __('is approaching your monthly API request limit.') }}

{{ __('Current usage:') }} **{{ number_format($currentUsage) }}** {{ __('requests') }}
{{ __('Monthly limit:') }} **{{ number_format($monthlyLimit) }}** {{ __('requests') }}
{{ __('Percentage used:') }} **{{ $usagePercentage }}%**
@endif

## {{ __('Recommended Actions') }}

@if($alertType === 'limit_exceeded')
- {{ __('Upgrade your plan to increase your monthly limit') }}
- {{ __('Optimize your components to reduce API calls') }}
- {{ __('Contact support if you believe this is an error') }}
@else
- {{ __('Monitor your usage in the dashboard') }}
- {{ __('Consider upgrading your plan if needed') }}
- {{ __('Optimize your components for better performance') }}
@endif

@component('mail::button', ['url' => route('dashboard.websites.show', $website->uuid)])
{{ __('View Website Dashboard') }}
@endcomponent

@component('mail::button', ['url' => route('pricing')])
{{ __('Upgrade Plan') }}
@endcomponent

## {{ __('Need Help?') }}

{{ __('If you have any questions or need assistance optimizing your usage, our support team is here to help.') }}

@component('mail::button', ['url' => route('contact')])
{{ __('Contact Support') }}
@endcomponent

{{ __('Thank you for using WebBloc!') }}

{{ __('The WebBloc Team') }}

---

<small>
{{ __('You received this email because you are the owner of the website') }} "{{ $website->name }}". 
{{ __('You can manage your notification preferences in your') }} 
<a href="{{ route('dashboard.profile') }}">{{ __('account settings') }}</a>.
</small>
@endcomponent
```

### 12. Error Pages

#### resources/views/errors/404.blade.php
```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Page Not Found') }} - WebBloc</title>
    
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
<body class="antialiased bg-gray-50 dark:bg-gray-900 min-h-screen flex items-center justify-center" 
      x-data="{ 
          darkMode: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
          locale: '{{ app()->getLocale() }}',
          searchVisible: false,
          searchQuery: ''
      }"
      x-init="document.documentElement.classList.toggle('dark', darkMode)"
      :class="{ 'dark': darkMode }">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <!-- Error Illustration -->
        <div class="mb-8">
            <svg class="mx-auto h-64 w-64 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.5" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="0.5" fill="none"/>
                <text x="12" y="16" text-anchor="middle" font-size="8" font-weight="bold" fill="currentColor">404</text>
            </svg>
        </div>

        <!-- Error Message -->
        <div class="mb-8">
            <h1 class="text-4xl sm:text-6xl font-bold text-gray-900 dark:text-white mb-4">
                {{ __('Page Not Found') }}
            </h1>
            <p class="text-xl text-gray-600 dark:text-gray-300 mb-4">
                {{ __('Sorry, we couldn\'t find the page you\'re looking for.') }}
            </p>
            <p class="text-gray-500 dark:text-gray-400">
                {{ __('The page might have been moved, deleted, or you entered the wrong URL.') }}
            </p>
        </div>

        <!-- Search Section -->
        <div class="mb-8">
            <button @click="searchVisible = !searchVisible" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                <svg class="w-5 h-5 mr-2 {{ app()->getLocale() === 'ar' ? 'ml-2 mr-0' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                {{ __('Search Our Site') }}
            </button>
            
            <div x-show="searchVisible" x-transition class="mt-4 max-w-md mx-auto">
                <div class="relative">
                    <input type="text" 
                           x-model="searchQuery"
                           @keydown.enter="performSearch()"
                           placeholder="{{ __('What are you looking for?') }}"
                           class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           :class="{ 'pr-10 pl-4': locale === 'ar' }">
                    <div class="absolute inset-y-0 {{ app()->getLocale() === 'ar' ? 'right-0 pr-3' : 'left-0 pl-3' }} flex items-center">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
                <button @click="performSearch()" 
                        class="mt-3 w-full px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                    {{ __('Search') }}
                </button>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-4 mb-8" :class="{ 'sm:space-x-reverse': locale === 'ar' }">
            <a href="{{ route('welcome') }}" 
               class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                <svg class="w-5 h-5 mr-2 {{ app()->getLocale() === 'ar' ? 'ml-2 mr-0' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                {{ __('Go Home') }}
            </a>
            
            <button @click="history.back()" 
                    class="inline-flex items-center px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-lg font-medium transition-colors">
                <svg class="w-5 h-5 mr-2 {{ app()->getLocale() === 'ar' ? 'ml-2 mr-0' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('Go Back') }}
            </button>
        </div>

        <!-- Helpful Links -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                {{ __('Popular Pages') }}
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('welcome') }}" 
                   class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 p-3 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                    <div class="font-medium">{{ __('Home') }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Main landing page') }}</div>
                </a>
                
                @auth
                    <a href="{{ route('dashboard') }}" 
                       class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 p-3 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                        <div class="font-medium">{{ __('Dashboard') }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Your control panel') }}</div>
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 p-3 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                        <div class="font-medium">{{ __('Login') }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Access your account') }}</div>
                    </a>
                @endauth
                
                <a href="{{ route('docs.index') }}" 
                   class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 p-3 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                    <div class="font-medium">{{ __('Documentation') }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('API guides & tutorials') }}</div>
                </a>
                
                <a href="{{ route('contact') }}" 
                   class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 p-3 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                    <div class="font-medium">{{ __('Contact') }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Get help & support') }}</div>
                </a>
            </div>
        </div>

        <!-- Dark Mode Toggle -->
        <div class="mt-8">
            <button @click="darkMode = !darkMode; localStorage.setItem('theme', darkMode ? 'dark' : 'light')" 
                    class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 rounded-lg">
                <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
                <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </button>
        </div>
    </div>

    <script>
        function performSearch() {
            if (this.searchQuery.trim()) {
                // Redirect to search results or documentation
                window.location.href = `/search?q=${encodeURIComponent(this.searchQuery)}`;
            }
        }
    </script>
</body>
</html>
```

#### resources/views/errors/500.blade.php
```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Server Error') }} - WebBloc</title>
    
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
<body class="antialiased bg-gray-50 dark:bg-gray-900 min-h-screen flex items-center justify-center" 
      x-data="{ 
          darkMode: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
          locale: '{{ app()->getLocale() }}',
          reportSent: false,
          errorDetails: {
              url: window.location.href,
              userAgent: navigator.userAgent,
              timestamp: new Date().toISOString(),
              referrer: document.referrer
          }
      }"
      x-init="document.documentElement.classList.toggle('dark', darkMode)"
      :class="{ 'dark': darkMode }">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <!-- Error Illustration -->
        <div class="mb-8">
            <svg class="mx-auto h-64 w-64 text-red-500 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="0.5" fill="none"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4m0 4h.01"/>
                <text x="12" y="20" text-anchor="middle" font-size="3" font-weight="bold" fill="currentColor">500</text>
            </svg>
        </div>

        <!-- Error Message -->
        <div class="mb-8">
            <h1 class="text-4xl sm:text-6xl font-bold text-gray-900 dark:text-white mb-4">
                {{ __('Server Error') }}
            </h1>
            <p class="text-xl text-gray-600 dark:text-gray-300 mb-4">
                {{ __('Oops! Something went wrong on our end.') }}
            </p>
            <p class="text-gray-500 dark:text-gray-400 mb-6">
                {{ __('We\'re experiencing some technical difficulties. Our team has been notified and is working to fix this issue.') }}
            </p>
        </div>

        <!-- Status Information -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm mb-8">
            <div class="flex items-center justify-center mb-4">
                <div class="flex items-center space-x-2" :class="{ 'space-x-reverse': locale === 'ar' }">
                    <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Service Status') }}</span>
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
                <div>
                    <div class="text-2xl font-bold text-red-500 dark:text-red-400">{{ __('API') }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Experiencing Issues') }}</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-yellow-500 dark:text-yellow-400">{{ __('Dashboard') }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Partial Outage') }}</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-green-500 dark:text-green-400">{{ __('CDN') }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Operational') }}</div>
                </div>
            </div>
        </div>

        <!-- Troubleshooting Steps -->
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6 mb-8 text-left">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 text-center">
                {{ __('What can you do?') }}
            </h2>
            <div class="space-y-3">
                <div class="flex items-start space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                    <div class="flex-shrink-0">
                        <div class="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-bold">1</div>
                    </div>
                    <div>
                        <p class="text-gray-700 dark:text-gray-300">
                            <strong>{{ __('Wait a few minutes') }}</strong> - {{ __('This might be a temporary issue that resolves itself.') }}
                        </p>
                    </div>
                </div>
                
                <div class="flex items-start space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                    <div class="flex-shrink-0">
                        <div class="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-bold">2</div>
                    </div>
                    <div>
                        <p class="text-gray-700 dark:text-gray-300">
                            <strong>{{ __('Refresh the page') }}</strong> - {{ __('Press F5 or Ctrl+R (Cmd+R on Mac) to reload the page.') }}
                        </p>
                    </div>
                </div>
                
                <div class="flex items-start space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                    <div class="flex-shrink-0">
                        <div class="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-bold">3</div>
                    </div>
                    <div>
                        <p class="text-gray-700 dark:text-gray-300">
                            <strong>{{ __('Clear your browser cache') }}</strong> - {{ __('Old cached data might be causing conflicts.') }}
                        </p>
                    </div>
                </div>
                
                <div class="flex items-start space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                    <div class="flex-shrink-0">
                        <div class="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-bold">4</div>
                    </div>
                    <div>
                        <p class="text-gray-700 dark:text-gray-300">
                            <strong>{{ __('Check our status page') }}</strong> - {{ __('Visit our status page for real-time updates.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error Reporting -->
        <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                {{ __('Help us fix this faster') }}
            </h3>
            <p class="text-gray-600 dark:text-gray-300 mb-4">
                {{ __('If this error persists, you can send us additional details to help us resolve it quickly.') }}
            </p>
            
            <div x-show="!reportSent">
                <button @click="sendErrorReport()" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-2 {{ app()->getLocale() === 'ar' ? 'ml-2 mr-0' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    {{ __('Send Error Report') }}
                </button>
            </div>
            
            <div x-show="reportSent" class="text-green-600 dark:text-green-400">
                <svg class="w-5 h-5 inline mr-2 {{ app()->getLocale() === 'ar' ? 'ml-2 mr-0' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ __('Error report sent! Thank you for helping us improve.') }}
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-4 mb-8" :class="{ 'sm:space-x-reverse': locale === 'ar' }">
            <button @click="location.reload()" 
                    class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                <svg class="w-5 h-5 mr-2 {{ app()->getLocale() === 'ar' ? 'ml-2 mr-0' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                {{ __('Try Again') }}
            </button>
            
            <a href="{{ route('welcome') }}" 
               class="inline-flex items-center px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-lg font-medium transition-colors">
                <svg class="w-5 h-5 mr-2 {{ app()->getLocale() === 'ar' ? 'ml-2 mr-0' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                {{ __('Go Home') }}
            </button>
            
            <a href="{{ route('contact') }}" 
               class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                <svg class="w-5 h-5 mr-2 {{ app()->getLocale() === 'ar' ? 'ml-2 mr-0' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                {{ __('Contact Support') }}
            </button>
        </div>

        <!-- Dark Mode Toggle -->
        <div class="mt-8">
            <button @click="darkMode = !darkMode; localStorage.setItem('theme', darkMode ? 'dark' : 'light')" 
                    class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 rounded-lg">
                <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
                <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </button>
        </div>
    </div>

    <script>
        async function sendErrorReport() {
            try {
                const response = await fetch('/api/error-reports', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify({
                        error_type: '500',
                        error_details: this.errorDetails,
                        user_message: 'Server Error encountered'
                    })
                });
                
                if (response.ok) {
                    this.reportSent = true;
                }
            } catch (error) {
                console.error('Failed to send error report:', error);
            }
        }
    </script>
</body>
</html>
```

#### resources/views/errors/429.blade.php
```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Rate Limit Exceeded') }} - WebBloc</title>
    
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
<body class="antialiased bg-gray-50 dark:bg-gray-900 min-h-screen flex items-center justify-center" 
      x-data="{ 
          darkMode: localStorage.getItem('theme') === 'dark' || (!localStorage.getTime('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
          locale: '{{ app()->getLocale() }}',
          retryIn: {{ $retryAfter ?? 60 }},
          interval: null
      }"
      x-init="
          document.documentElement.classList.toggle('dark', darkMode);
          startCountdown();
      "
      :class="{ 'dark': darkMode }">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <!-- Rate Limit Illustration -->
        <div class="mb-8">
            <svg class="mx-auto h-64 w-64 text-yellow-500 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="0.5" fill="none"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6v6l4 2"/>
                <text x="12" y="20" text-anchor="middle" font-size="3" font-weight="bold" fill="currentColor">429</text>
            </svg>
        </div>

        <!-- Error Message -->
        <div class="mb-8">
            <h1 class="text-4xl sm:text-6xl font-bold text-gray-900 dark:text-white mb-4">
                {{ __('Rate Limit Exceeded') }}
            </h1>
            <p class="text-xl text-gray-600 dark:text-gray-300 mb-4">
                {{ __('Whoa there! You\'re making requests too quickly.') }}
            </p>
            <p class="text-gray-500 dark:text-gray-400">
                {{ __('To ensure fair usage for all users, we\'ve temporarily limited your access. Please wait a moment before trying again.') }}
            </p>
        </div>

        <!-- Countdown Timer -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-8 shadow-sm mb-8">
            <div class="flex items-center justify-center mb-6">
                <div class="relative">
                    <svg class="w-24 h-24 transform -rotate-90" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="40" stroke="currentColor" stroke-width="8" fill="transparent" class="text-gray-200 dark:text-gray-700"/>
                        <circle cx="50" cy="50" r="40" stroke="currentColor" stroke-width="8" fill="transparent" 
                                :stroke-dasharray="251.2" 
                                :stroke-dashoffset="251.2 - (251.2 * ({{ $retryAfter ?? 60 }} - retryIn) / {{ $retryAfter ?? 60 }})" 
                                class="text-yellow-500 dark:text-yellow-400 transition-all duration-1000"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-2xl font-bold text-gray-900 dark:text-white" x-text="retryIn"></span>
                    </div>
                </div>
            </div>
            
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                {{ __('You can try again in') }}
            </h3>
            <p class="text-gray-600 dark:text-gray-300">
                <span x-text="retryIn"></span> {{ __('seconds') }}
            </p>
        </div>

        <!-- Rate Limit Information -->
        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-6 mb-8">
            <div class="flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400 mr-3 {{ app()->getLocale() === 'ar' ? 'ml-3 mr-0' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <h3 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200">
                    {{ __('Why am I seeing this?') }}
                </h3>
            </div>
            
            <div class="text-left space-y-3 text-yellow-700 dark:text-yellow-300">
                <p>{{ __('Our API has rate limits to ensure optimal performance for all users:') }}</p>
                <ul class="list-disc list-inside space-y-1 ml-4 {{ app()->getLocale() === 'ar' ? 'mr-4 ml-0' : '' }}">
                    <li>{{ __('Free accounts: 1,000 requests per hour') }}</li>
                    <li>{{ __('Starter plan: 10,000 requests per hour') }}</li>
                    <li>{{ __('Professional plan: 100,000 requests per hour') }}</li>
                    <li>{{ __('Enterprise plan: Custom limits') }}</li>
                </ul>
            </div>
        </div>

        <!-- Upgrade Notice -->
        @guest
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-3">
                {{ __('Need higher limits?') }}
            </h3>
            <p class="text-blue-700 dark:text-blue-300 mb-4">
                {{ __('Create an account or upgrade your plan to get higher rate limits and more features.') }}
            </p>
            <div class="flex flex-col sm:flex-row justify-center items-center space-y-2 sm:space-y-0 sm:space-x-4" :class="{ 'sm:space-x-reverse': locale === 'ar' }">
                <a href="{{ route('register') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    {{ __('Create Free Account') }}
                </a>
                <a href="{{ route('pricing') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-100 dark:bg-blue-800 text-blue-700 dark:text-blue-200 hover:bg-blue-200 dark:hover:bg-blue-700 rounded-lg transition-colors">
                    {{ __('View Pricing') }}
                </a>
            </div>
        </div>
        @else
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-3">
                {{ __('Need higher limits?') }}
            </h3>
            <p class="text-blue-700 dark:text-blue-300 mb-4">
                {{ __('Upgrade your plan to get higher rate limits and additional features.') }}
            </p>
            <div class="flex flex-col sm:flex-row justify-center items-center space-y-2 sm:space-y-0 sm:space-x-4" :class="{ 'sm:space-x-reverse': locale === 'ar' }">
                <a href="{{ route('pricing') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    {{ __('Upgrade Plan') }}
                </a>
                <a href="{{ route('dashboard') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-100 dark:bg-blue-800 text-blue-700 dark:text-blue-200 hover:bg-blue-200 dark:hover:bg-blue-700 rounded-lg transition-colors">
                    {{ __('View Dashboard') }}
                </a>
            </div>
        </div>
        @endguest

        <!-- Optimization Tips -->
        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-6 mb-8 text-left">
            <h3 class="text-lg font-semibold text-green-900 dark:text-green-100 mb-4 text-center">
                {{ __('Optimization Tips') }}
            </h3>
            <div class="space-y-3 text-green-700 dark:text-green-300">
                <div class="flex items-start space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p>{{ __('Cache API responses on your frontend to reduce duplicate requests') }}</p>
                </div>
                
                <div class="flex items-start space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p>{{ __('Implement exponential backoff when retrying failed requests') }}</p>
                </div>
                
                <div class="flex items-start space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p>{{ __('Use webhooks instead of polling when possible') }}</p>
                </div>
                
                <div class="flex items-start space-x-3" :class="{ 'space-x-reverse': locale === 'ar' }">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p>{{ __('Batch multiple operations into single API calls when supported') }}</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-4 mb-8" :class="{ 'sm:space-x-reverse': locale === 'ar' }">
            <button @click="location.reload()" 
                    :disabled="retryIn > 0"
                    :class="retryIn > 0 ? 'opacity-50 cursor-not-allowed' : ''"
                    class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors disabled:hover:bg-blue-600">
                <svg class="w-5 h-5 mr-2 {{ app()->getLocale() === 'ar' ? 'ml-2 mr-0' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span x-text="retryIn > 0 ? '{{ __('Try Again') }} (' + retryIn + 's)' : '{{ __('Try Again') }}'"></span>
            </button>
            
            <a href="{{ route('welcome') }}" 
               class="inline-flex items-center px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-lg font-medium transition-colors">
                <svg class="w-5 h-5 mr-2 {{ app()->getLocale() === 'ar' ? 'ml-2 mr-0' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                {{ __('Go Home') }}
            </a>
        </div>

        <!-- Dark Mode Toggle -->
        <div class="mt-8">
            <button @click="darkMode = !darkMode; localStorage.setItem('theme', darkMode ? 'dark' : 'light')" 
                    class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 rounded-lg">
                <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
                <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </button>
        </div>
    </div>

    <script>
        function startCountdown() {
            this.interval = setInterval(() => {
                if (this.retryIn > 0) {
                    this.retryIn--;
                } else {
                    clearInterval(this.interval);
                }
            }, 1000);
        }
    </script>
</body>
</html>
```

## Mission 2: All Language Files

### lang/en/messages.php
```php
<?php

return [
    // Authentication
    'login' => 'Login',
    'register' => 'Register',
    'logout' => 'Logout',
    'email' => 'Email',
    'password' => 'Password',
    'remember_me' => 'Remember Me',
    'forgot_password' => 'Forgot Password?',
    'reset_password' => 'Reset Password',
    'confirm_password' => 'Confirm Password',
    'name' => 'Full Name',
    'sign_in' => 'Sign In',
    'sign_up' => 'Sign Up',
    'sign_out' => 'Sign Out',
    'create_account' => 'Create Account',
    'already_have_account' => 'Already have an account?',
    'dont_have_account' => 'Don\'t have an account?',
    
    // Dashboard
    'dashboard' => 'Dashboard',
    'welcome_back' => 'Welcome back',
    'overview' => 'Overview',
    'statistics' => 'Statistics',
    'websites' => 'Websites',
    'components' => 'Components',
    'users' => 'Users',
    'settings' => 'Settings',
    'profile' => 'Profile',
    'notifications' => 'Notifications',
    
    // Website Management
    'website' => 'Website',
    'website_name' => 'Website Name',
    'domain' => 'Domain',
    'description' => 'Description',
    'create_website' => 'Create Website',
    'edit_website' => 'Edit Website',
    'delete_website' => 'Delete Website',
    'website_details' => 'Website Details',
    'public_api_key' => 'Public API Key',
    'secret_api_key' => 'Secret API Key',
    'regenerate_api_keys' => 'Regenerate API Keys',
    'allowed_components' => 'Allowed Components',
    'supported_locales' => 'Supported Languages',
    'active' => 'Active',
    'inactive' => 'Inactive',
    'verified' => 'Verified',
    'pending' => 'Pending',
    
    // Components
    'component' => 'Component',
    'component_type' => 'Component Type',
    'component_name' => 'Component Name',
    'create_component' => 'Create Component',
    'edit_component' => 'Edit Component',
    'delete_component' => 'Delete Component',
    'component_details' => 'Component Details',
    'requires_authentication' => 'Requires Authentication',
    'blade_template' => 'Blade Template',
    'alpine_js_code' => 'Alpine.js Code',
    'css_styles' => 'CSS Styles',
    'crud_permissions' => 'CRUD Permissions',
    'version' => 'Version',
    
    // CRUD Operations
    'create' => 'Create',
    'read' => 'Read',
    'update' => 'Update',
    'delete' => 'Delete',
    'edit' => 'Edit',
    'view' => 'View',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'submit' => 'Submit',
    'add' => 'Add',
    'remove' => 'Remove',
    
    // Statistics
    'total_websites' => 'Total Websites',
    'active_websites' => 'Active Websites',
    'total_components' => 'Total Components',
    'active_components' => 'Active Components',
    'api_requests_today' => 'API Requests Today',
    'total_users' => 'Total Users',
    'active_users' => 'Active Users',
    'usage_statistics' => 'Usage Statistics',
    'website_statistics' => 'Website Statistics',
    'component_statistics' => 'Component Statistics',
    
    // Success Messages
    'success' => 'Success',
    'website_created' => 'Website created successfully',
    'website_updated' => 'Website updated successfully',
    'website_deleted' => 'Website deleted successfully',
    'component_created' => 'Component created successfully',
    'component_updated' => 'Component updated successfully',
    'component_deleted' => 'Component deleted successfully',
    'keys_regenerated' => 'API keys regenerated successfully',
    'profile_updated' => 'Profile updated successfully',
    'settings_saved' => 'Settings saved successfully',
    
    // Error Messages
    'error' => 'Error',
    'something_went_wrong' => 'Something went wrong',
    'validation_error' => 'Validation Error',
    'unauthorized' => 'Unauthorized',
    'forbidden' => 'Forbidden',
    'not_found' => 'Not Found',
    'component_not_found' => 'Component not found',
    'website_not_found' => 'Website not found',
    'user_not_found' => 'User not found',
    'invalid_credentials' => 'Invalid credentials',
    'access_denied' => 'Access denied',
    
    // API Messages
    'component_not_creatable' => 'Component is not creatable',
    'component_not_readable' => 'Component is not readable',
    'component_not_updatable' => 'Component is not updatable',
    'component_not_deletable' => 'Component is not deletable',
    'item_not_found' => 'Item not found',
    'fetch_failed' => 'Failed to fetch data',
    'create_failed' => 'Failed to create item',
    'update_failed' => 'Failed to update item',
    'delete_failed' => 'Failed to delete item',
    
    // General
    'loading' => 'Loading',
    'no_data' => 'No data available',
    'search' => 'Search',
    'filter' => 'Filter',
    'sort' => 'Sort',
    'pagination' => 'Pagination',
    'per_page' => 'Per Page',
    'showing' => 'Showing',
    'of' => 'of',
    'results' => 'results',
    'all' => 'All',
    'none' => 'None',
    'yes' => 'Yes',
    'no' => 'No',
    'true' => 'True',
    'false' => 'False',
    'enabled' => 'Enabled',
    'disabled' => 'Disabled',
    
    // Languages
    'english' => 'English',
    'arabic' => 'العربية',
    'language' => 'Language',
    'locale' => 'Locale',
    
    // Confirmation Messages
    'are_you_sure' => 'Are you sure?',
    'confirm_delete' => 'Are you sure you want to delete this item?',
    'confirm_delete_website' => 'Are you sure you want to delete this website?',
    'confirm_regenerate_keys' => 'Are you sure you want to regenerate API keys?',
    'yes_delete' => 'Yes, Delete',
    'yes_regenerate' => 'Yes, Regenerate',
    
    // Navigation
    'home' => 'Home',
    'back' => 'Back',
    'next' => 'Next',
    'previous' => 'Previous',
    'continue' => 'Continue',
    'go_back' => 'Go Back',
    'go_home' => 'Go Home',
    
    // WebBloc Specific
    'webbloc' => 'WebBloc',
    'dynamic_components' => 'Dynamic Components',
    'static_websites' => 'Static Websites',
    'api_documentation' => 'API Documentation',
    'integration_guide' => 'Integration Guide',
    'get_started' => 'Get Started',
    'learn_more' => 'Learn More',
    'view_demo' => 'View Demo',
    'try_free' => 'Try Free',
    'upgrade' => 'Upgrade',
    'pricing' => 'Pricing',
    'contact' => 'Contact',
    'support' => 'Support',
    'documentation' => 'Documentation',
    'community' => 'Community',
    'blog' => 'Blog',
    'status' => 'Status',
    'terms' => 'Terms of Service',
    'privacy' => 'Privacy Policy',
    'about' => 'About Us',
    
    // Comments Component
    'comments' => 'Comments',
    'comment' => 'Comment',
    'write_comment' => 'Write a comment',
    'post_comment' => 'Post Comment',
    'edit_comment' => 'Edit Comment',
    'delete_comment' => 'Delete Comment',
    'reply' => 'Reply',
    'like' => 'Like',
    'unlike' => 'Unlike',
    'no_comments' => 'No comments yet',
    'comment_posted' => 'Comment posted successfully',
    'comment_updated' => 'Comment updated successfully',
    'comment_deleted' => 'Comment deleted successfully',
    
    // Reviews Component
    'reviews' => 'Reviews',
    'review' => 'Review',
    'write_review' => 'Write a review',
    'post_review' => 'Post Review',
    'edit_review' => 'Edit Review',
    'delete_review' => 'Delete Review',
    'rating' => 'Rating',
    'stars' => 'Stars',
    'average_rating' => 'Average Rating',
    'no_reviews' => 'No reviews yet',
    'review_posted' => 'Review posted successfully',
    'review_updated' => 'Review updated successfully',
    'review_deleted' => 'Review deleted successfully',
    
    // Authentication Component
    'login_required' => 'Please login to continue',
    'register_required' => 'Please register to continue',
    'logout_success' => 'Logged out successfully',
    'login_success' => 'Logged in successfully',
    'register_success' => 'Registration successful',
    'password_reset_sent' => 'Password reset link sent',
    'password_reset_success' => 'Password reset successfully',
    
    // Form Validation
    'required' => 'This field is required',
    'email_invalid' => 'Please enter a valid email address',
    'password_min' => 'Password must be at least 8 characters',
    'password_confirmation' => 'Password confirmation does not match',
    'url_invalid' => 'Please enter a valid URL',
    'numeric' => 'This field must be a number',
    'min_length' => 'Minimum length is :min characters',
    'max_length' => 'Maximum length is :max characters',
    
    // Time & Dates
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',
    'last_login' => 'Last Login',
    'never' => 'Never',
    'just_now' => 'Just now',
    'minutes_ago' => ':count minutes ago',
    'hours_ago' => ':count hours ago',
    'days_ago' => ':count days ago',
    'weeks_ago' => ':count weeks ago',
    'months_ago' => ':count months ago',
    'years_ago' => ':count years ago',
    
    // Miscellaneous
    'powered_by' => 'Powered by',
    'close' => 'Close',
    'open' => 'Open',
    'expand' => 'Expand',
    'collapse' => 'Collapse',
    'show_more' => 'Show More',
    'show_less' => 'Show Less',
    'load_more' => 'Load More',
    'refresh' => 'Refresh',
    'reset' => 'Reset',
    'clear' => 'Clear',
    'apply' => 'Apply',
    'download' => 'Download',
    'upload' => 'Upload',
    'import' => 'Import',
    'export' => 'Export',
    'copy' => 'Copy',
    'paste' => 'Paste',
    'cut' => 'Cut',
    'undo' => 'Undo',
    'redo' => 'Redo',
    'print' => 'Print',
    
    // Error Pages
    'page_not_found' => 'Page Not Found',
    'server_error' => 'Server Error',
    'forbidden_error' => 'Forbidden',
    'unauthorized_error' => 'Unauthorized',
    'rate_limit_exceeded' => 'Rate Limit Exceeded',
    'maintenance_mode' => 'Maintenance Mode',
    'coming_soon' => 'Coming Soon',
];
```

### lang/ar/messages.php
```php
<?php

return [
    // المصادقة
    'login' => 'تسجيل الدخول',
    'register' => 'إنشاء حساب',
    'logout' => 'تسجيل الخروج',
    'email' => 'البريد الإلكتروني',
    'password' => 'كلمة المرور',
    'remember_me' => 'تذكرني',
    'forgot_password' => 'نسيت كلمة المرور؟',
    'reset_password' => 'إعادة تعيين كلمة المرور',
    'confirm_password' => 'تأكيد كلمة المرور',
    'name' => 'الاسم الكامل',
    'sign_in' => 'دخول',
    'sign_up' => 'التسجيل',
    'sign_out' => 'خروج',
    'create_account' => 'إنشاء حساب',
    'already_have_account' => 'لديك حساب بالفعل؟',
    'dont_have_account' => 'ليس لديك حساب؟',
    
    // لوحة التحكم
    'dashboard' => 'لوحة التحكم',
    'welcome_back' => 'أهلاً بعودتك',
    'overview' => 'نظرة عامة',
    'statistics' => 'الإحصائيات',
    'websites' => 'المواقع',
    'components' => 'المكونات',
    'users' => 'المستخدمون',
    'settings' => 'الإعدادات',
    'profile' => 'الملف الشخصي',
    'notifications' => 'الإشعارات',
    
    // إدارة المواقع
    'website' => 'الموقع',
    'website_name' => 'اسم الموقع',
    'domain' => 'النطاق',
    'description' => 'الوصف',
    'create_website' => 'إنشاء موقع',
    'edit_website' => 'تحرير الموقع',
    'delete_website' => 'حذف الموقع',
    'website_details' => 'تفاصيل الموقع',
    'public_api_key' => 'مفتاح API العام',
    'secret_api_key' => 'مفتاح API السري',
    'regenerate_api_keys' => 'إعادة إنشاء مفاتيح API',
    'allowed_components' => 'المكونات المسموحة',
    'supported_locales' => 'اللغات المدعومة',
    'active' => 'نشط',
    'inactive' => 'غير نشط',
    'verified' => 'موثق',
    'pending' => 'في الانتظار',
    
    // المكونات
    'component' => 'المكون',
    'component_type' => 'نوع المكون',
    'component_name' => 'اسم المكون',
    'create_component' => 'إنشاء مكون',
    'edit_component' => 'تحرير المكون',
    'delete_component' => 'حذف المكون',
    'component_details' => 'تفاصيل المكون',
    'requires_authentication' => 'يتطلب المصادقة',
    'blade_template' => 'قالب Blade',
    'alpine_js_code' => 'كود Alpine.js',
    'css_styles' => 'أنماط CSS',
    'crud_permissions' => 'صلاحيات CRUD',
    'version' => 'الإصدار',
    
    // عمليات CRUD
    'create' => 'إنشاء',
    'read' => 'قراءة',
    'update' => 'تحديث',
    'delete' => 'حذف',
    'edit' => 'تحرير',
    'view' => 'عرض',
    'save' => 'حفظ',
    'cancel' => 'إلغاء',
    'submit' => 'إرسال',
    'add' => 'إضافة',
    'remove' => 'إزالة',
    
    // الإحصائيات
    'total_websites' => 'إجمالي المواقع',
    'active_websites' => 'المواقع النشطة',
    'total_components' => 'إجمالي المكونات',
    'active_components' => 'المكونات النشطة',
    'api_requests_today' => 'طلبات API اليوم',
    'total_users' => 'إجمالي المستخدمين',
    'active_users' => 'المستخدمون النشطون',
    'usage_statistics' => 'إحصائيات الاستخدام',
    'website_statistics' => 'إحصائيات المواقع',
    'component_statistics' => 'إحصائيات المكونات',
    
    // رسائل النجاح
    'success' => 'نجح',
    'website_created' => 'تم إنشاء الموقع بنجاح',
    'website_updated' => 'تم تحديث الموقع بنجاح',
    'website_deleted' => 'تم حذف الموقع بنجاح',
    'component_created' => 'تم إنشاء المكون بنجاح',
    'component_updated' => 'تم تحديث المكون بنجاح',
    'component_deleted' => 'تم حذف المكون بنجاح',
    'keys_regenerated' => 'تم إعادة إنشاء مفاتيح API بنجاح',
    'profile_updated' => 'تم تحديث الملف الشخصي بنجاح',
    'settings_saved' => 'تم حفظ الإعدادات بنجاح',
    
    // رسائل الخطأ
    'error' => 'خطأ',
    'something_went_wrong' => 'حدث خطأ ما',
    'validation_error' => 'خطأ في التحقق',
    'unauthorized' => 'غير مخول',
    'forbidden' => 'محظور',
    'not_found' => 'غير موجود',
    'component_not_found' => 'المكون غير موجود',
    'website_not_found' => 'الموقع غير موجود',
    'user_not_found' => 'المستخدم غير موجود',
    'invalid_credentials' => 'بيانات اعتماد غير صحيحة',
    'access_denied' => 'تم رفض الوصول',
    
    // رسائل API
    'component_not_creatable' => 'لا يمكن إنشاء المكون',
    'component_not_readable' => 'لا يمكن قراءة المكون',
    'component_not_updatable' => 'لا يمكن تحديث المكون',
    'component_not_deletable' => 'لا يمكن حذف المكون',
    'item_not_found' => 'العنصر غير موجود',
    'fetch_failed' => 'فشل في جلب البيانات',
    'create_failed' => 'فشل في إنشاء العنصر',
    'update_failed' => 'فشل في تحديث العنصر',
    'delete_failed' => 'فشل في حذف العنصر',
    
    // عام
    'loading' => 'جاري التحميل',
    'no_data' => 'لا توجد بيانات متاحة',
    'search' => 'بحث',
    'filter' => 'تصفية',
    'sort' => 'ترتيب',
    'pagination' => 'ترقيم الصفحات',
    'per_page' => 'لكل صفحة',
    'showing' => 'عرض',
    'of' => 'من',
    'results' => 'النتائج',
    'all' => 'الكل',
    'none' => 'لا شيء',
    'yes' => 'نعم',
    'no' => 'لا',
    'true' => 'صحيح',
    'false' => 'خطأ',
    'enabled' => 'مفعل',
    'disabled' => 'معطل',
    
    // اللغات
    'english' => 'English',
    'arabic' => 'العربية',
    'language' => 'اللغة',
    'locale' => 'المنطقة',
    
    // رسائل التأكيد
    'are_you_sure' => 'هل أنت متأكد؟',
    'confirm_delete' => 'هل أنت متأكد من حذف هذا العنصر؟',
    'confirm_delete_website' => 'هل أنت متأكد من حذف هذا الموقع؟',
    'confirm_regenerate_keys' => 'هل أنت متأكد من إعادة إنشاء مفاتيح API؟',
    'yes_delete' => 'نعم، احذف',
    'yes_regenerate' => 'نعم، أعد الإنشاء',
    
    // التنقل
    'home' => 'الرئيسية',
    'back' => 'العودة',
    'next' => 'التالي',
    'previous' => 'السابق',
    'continue' => 'متابعة',
    'go_back' => 'العودة للخلف',
    'go_home' => 'الذهاب للرئيسية',
    
    // خاص بـ WebBloc
    'webbloc' => 'ويب بلوك',
    'dynamic_components' => 'المكونات الديناميكية',
    'static_websites' => 'المواقع الثابتة',
    'api_documentation' => 'وثائق API',
    'integration_guide' => 'دليل التكامل',
    'get_started' => 'ابدأ الآن',
    'learn_more' => 'اعرف المزيد',
    'view_demo' => 'عرض تجريبي',
    'try_free' => 'جرب مجاناً',
    'upgrade' => 'ترقية',
    'pricing' => 'الأسعار',
    'contact' => 'اتصل بنا',
    'support' => 'الدعم',
    'documentation' => 'الوثائق',
    'community' => 'المجتمع',
    'blog' => 'المدونة',
    'status' => 'الحالة',
    'terms' => 'شروط الخدمة',
    'privacy' => 'سياسة الخصوصية',
    'about' => 'من نحن',
    
    // مكون التعليقات
    'comments' => 'التعليقات',
    'comment' => 'تعليق',
    'write_comment' => 'اكتب تعليقاً',
    'post_comment' => 'نشر التعليق',
    'edit_comment' => 'تحرير التعليق',
    'delete_comment' => 'حذف التعليق',
    'reply' => 'رد',
    'like' => 'إعجاب',
    'unlike' => 'إلغاء الإعجاب',
    'no_comments' => 'لا توجد تعليقات بعد',
    'comment_posted' => 'تم نشر التعليق بنجاح',
    'comment_updated' => 'تم تحديث التعليق بنجاح',
    'comment_deleted' => 'تم حذف التعليق بنجاح',
    
    // مكون المراجعات
    'reviews' => 'المراجعات',
    'review' => 'مراجعة',
    'write_review' => 'اكتب مراجعة',
    'post_review' => 'نشر المراجعة',
    'edit_review' => 'تحرير المراجعة',
    'delete_review' => 'حذف المراجعة',
    'rating' => 'التقييم',
    'stars' => 'نجوم',
    'average_rating' => 'متوسط التقييم',
    'no_reviews' => 'لا توجد مراجعات بعد',
    'review_posted' => 'تم نشر المراجعة بنجاح',
    'review_updated' => 'تم تحديث المراجعة بنجاح',
    'review_deleted' => 'تم حذف المراجعة بنجاح',
    
    // مكون المصادقة
    'login_required' => 'يرجى تسجيل الدخول للمتابعة',
    'register_required' => 'يرجى التسجيل للمتابعة',
    'logout_success' => 'تم تسجيل الخروج بنجاح',
    'login_success' => 'تم تسجيل الدخول بنجاح',
    'register_success' => 'تم التسجيل بنجاح',
    'password_reset_sent' => 'تم إرسال رابط إعادة تعيين كلمة المرور',
    'password_reset_success' => 'تم إعادة تعيين كلمة المرور بنجاح',
    
    // التحقق من النموذج
    'required' => 'هذا الحقل مطلوب',
    'email_invalid' => 'يرجى إدخال عنوان بريد إلكتروني صحيح',
    'password_min' => 'يجب أن تكون كلمة المرور 8 أحرف على الأقل',
    'password_confirmation' => 'تأكيد كلمة المرور غير مطابق',
    'url_invalid' => 'يرجى إدخال رابط صحيح',
    'numeric' => 'يجب أن يكون هذا الحقل رقماً',
    'min_length' => 'الحد الأدنى للطول هو :min حرف',
    'max_length' => 'الحد الأقصى للطول هو :max حرف',
    
    // الوقت والتواريخ
    'created_at' => 'أنشئ في',
    'updated_at' => 'حُدث في',
    'last_login' => 'آخر دخول',
    'never' => 'أبداً',
    'just_now' => 'الآن',
    'minutes_ago' => 'منذ :count دقيقة',
    'hours_ago' => 'منذ :count ساعة',
    'days_ago' => 'منذ :count يوم',
    'weeks_ago' => 'منذ :count أسبوع',
    'months_ago' => 'منذ :count شهر',
    'years_ago' => 'منذ :count سنة',
    
    // متفرقات
    'powered_by' => 'مدعوم من',
    'close' => 'إغلاق',
    'open' => 'فتح',
    'expand' => 'توسيع',
    'collapse' => 'طي',
    'show_more' => 'عرض المزيد',
    'show_less' => 'عرض أقل',
    'load_more' => 'تحميل المزيد',
    'refresh' => 'تحديث',
    'reset' => 'إعادة تعيين',
    'clear' => 'مسح',
    'apply' => 'تطبيق',
    'download' => 'تحميل',
    'upload' => 'رفع',
    'import' => 'استيراد',
    'export' => 'تصدير',
    'copy' => 'نسخ',
    'paste' => 'لصق',
    'cut' => 'قص',
    'undo' => 'تراجع',
    'redo' => 'إعادة',
    'print' => 'طباعة',
    
    // صفحات الخطأ
    'page_not_found' => 'الصفحة غير موجودة',
    'server_error' => 'خطأ في الخادم',
    'forbidden_error' => 'محظور',
    'unauthorized_error' => 'غير مخول',
    'rate_limit_exceeded' => 'تم تجاوز حد المعدل',
    'maintenance_mode' => 'وضع الصيانة',
    'coming_soon' => 'قريباً',
];
```

## Mission 3: Generate Configuration Files

### bootstrap/app.php
```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global middleware
        $middleware->web([
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->api([
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Middleware aliases
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
            
            // Custom middleware
            'locale' => \App\Http\Middleware\SetLocale::class,
            'webbloc.auth' => \App\Http\Middleware\WebBlocAuthentication::class,
            'webbloc.cors' => \App\Http\Middleware\WebBlocCors::class,
            'role' => \Spatie\Permission\Middlewares\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
        ]);

        // Rate limiting for API
        $middleware->throttleApi([
            'api' => 1000, // requests per hour for authenticated users
            'public' => 100, // requests per hour for public API
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Custom exception handling
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.not_found'),
                    'error' => 'Resource not found'
                ], 404);
            }
            
            return response()->view('errors.404', [], 404);
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.forbidden'),
                    'error' => 'Access denied'
                ], 403);
            }
            
            return response()->view('errors.403', [], 403);
        });

        $exceptions->render(function (TooManyRequestsHttpException $e, Request $request) {
            $retryAfter = $e->getHeaders()['Retry-After'] ?? 60;
            
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.rate_limit_exceeded'),
                    'error' => 'Too many requests',
                    'retry_after' => $retryAfter
                ], 429);
            }
            
            return response()->view('errors.429', compact('retryAfter'), 429);
        });

        // General server error handling
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') && !config('app.debug')) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.server_error'),
                    'error' => 'Internal server error'
                ], 500);
            }
            
            if (!config('app.debug') && !$request->is('api/*')) {
                return response()->view('errors.500', [], 500);
            }
        });
    })
    ->create();
```

### bootstrap/providers.php
```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\WebBlocServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
];
```

### routes/api.php
```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WebBlocController;
use App\Http\Controllers\Api\WebsiteController;
use App\Http\Controllers\Api\ComponentController;
use App\Http\Controllers\Api\StatisticsController;
use App\Http\Controllers\Api\UserController;

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

// Public API Routes
Route::prefix('public')->group(function () {
    Route::get('/stats', [StatisticsController::class, 'publicStats']);
    Route::get('/components/types', [ComponentController::class, 'publicTypes']);
    Route::post('/contact', [\App\Http\Controllers\ContactController::class, 'store']);
});

// WebBloc Components API (for static websites)
Route::prefix('webblocs')->middleware(['webbloc.cors', 'throttle:api'])->group(function () {
    // Authentication for website users
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'webBlocRegister']);
        Route::post('/login', [AuthController::class, 'webBlocLogin']);
        Route::post('/logout', [AuthController::class, 'webBlocLogout'])->middleware('webbloc.auth');
        Route::get('/user', [AuthController::class, 'webBlocUser'])->middleware('webbloc.auth');
        Route::put('/user', [AuthController::class, 'webBlocUpdateUser'])->middleware('webbloc.auth');
        Route::post('/password/forgot', [AuthController::class, 'webBlocForgotPassword']);
        Route::post('/password/reset', [AuthController::class, 'webBlocResetPassword']);
        Route::get('/check', [AuthController::class, 'webBlocCheckAuth']);
    });

    // Component CRUD operations
    Route::middleware('webbloc.auth:optional')->group(function () {
        // Comments
        Route::get('/comments', [WebBlocController::class, 'index']);
        Route::post('/comments', [WebBlocController::class, 'store'])->middleware('webbloc.auth');
        Route::get('/comments/{id}', [WebBlocController::class, 'show']);
        Route::put('/comments/{id}', [WebBlocController::class, 'update'])->middleware('webbloc.auth');
        Route::delete('/comments/{id}', [WebBlocController::class, 'destroy'])->middleware('webbloc.auth');
        Route::post('/comments/{id}/like', [WebBlocController::class, 'toggleLike'])->middleware('webbloc.auth');

        // Reviews
        Route::get('/reviews', [WebBlocController::class, 'index']);
        Route::post('/reviews', [WebBlocController::class, 'store'])->middleware('webbloc.auth');
        Route::get('/reviews/{id}', [WebBlocController::class, 'show']);
        Route::put('/reviews/{id}', [WebBlocController::class, 'update'])->middleware('webbloc.auth');
        Route::delete('/reviews/{id}', [WebBlocController::class, 'destroy'])->middleware('webbloc.auth');
        Route::post('/reviews/{id}/helpful', [WebBlocController::class, 'markHelpful'])->middleware('webbloc.auth');

        // Reactions
        Route::get('/reactions', [WebBlocController::class, 'index']);
        Route::post('/reactions', [WebBlocController::class, 'store'])->middleware('webbloc.auth');
        Route::delete('/reactions/{id}', [WebBlocController::class, 'destroy'])->middleware('webbloc.auth');

        // Profiles
        Route::get('/profiles/{id}', [WebBlocController::class, 'showProfile']);
        Route::put('/profiles/{id}', [WebBlocController::class, 'updateProfile'])->middleware('webbloc.auth');

        // Testimonials
        Route::get('/testimonials', [WebBlocController::class, 'testimonials']);
        
        // Generic component endpoint
        Route::get('/{type}', [WebBlocController::class, 'index']);
        Route::post('/{type}', [WebBlocController::class, 'store'])->middleware('webbloc.auth');
        Route::get('/{type}/{id}', [WebBlocController::class, 'show']);
        Route::put('/{type}/{id}', [WebBlocController::class, 'update'])->middleware('webbloc.auth');
        Route::delete('/{type}/{id}', [WebBlocController::class, 'destroy'])->middleware('webbloc.auth');
    });
});

// Dashboard API Routes (for authenticated users)
Route::middleware(['auth:sanctum', 'verified'])->prefix('dashboard')->group(function () {
    // User routes
    Route::get('/user', function (Request $request) {
        return $request->user()->load(['websites', 'roles', 'permissions']);
    });
    
    Route::put('/user', [UserController::class, 'update']);
    Route::post('/user/avatar', [UserController::class, 'updateAvatar']);
    Route::delete('/user/avatar', [UserController::class, 'deleteAvatar']);
    Route::put('/user/password', [UserController::class, 'updatePassword']);
    
    // Websites management
    Route::apiResource('websites', WebsiteController::class)->parameters([
        'websites' => 'website:uuid'
    ]);
    Route::post('/websites/{website:uuid}/regenerate-keys', [WebsiteController::class, 'regenerateKeys']);
    Route::get('/websites/{website:uuid}/stats', [WebsiteController::class, 'statistics']);
    Route::get('/websites/{website:uuid}/activities', [WebsiteController::class, 'activities']);
    
    // Components management (Admin only)
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('components', ComponentController::class)->parameters([
            'components' => 'component:uuid'
        ]);
        Route::post('/components/{component:uuid}/duplicate', [ComponentController::class, 'duplicate']);
        Route::put('/components/{component:uuid}/toggle-status', [ComponentController::class, 'toggleStatus']);
    });
    
    // Statistics
    Route::prefix('statistics')->group(function () {
        Route::get('/overview', [StatisticsController::class, 'overview']);
        Route::get('/websites', [StatisticsController::class, 'websiteStats']);
        Route::get('/components', [StatisticsController::class, 'componentStats']);
        Route::get('/users', [StatisticsController::class, 'userStats'])->middleware('role:admin');
        Route::get('/export', [StatisticsController::class, 'export']);
    });
    
    // Admin only routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // User management
        Route::apiResource('users', \App\Http\Controllers\Dashboard\Admin\UserController::class)->parameters([
            'users' => 'user:uuid'
        ]);
        Route::post('/users/{user:uuid}/toggle-status', [\App\Http\Controllers\Dashboard\Admin\UserController::class, 'toggleStatus']);
        Route::post('/users/{user:uuid}/reset-password', [\App\Http\Controllers\Dashboard\Admin\UserController::class, 'resetPassword']);
        
        // System settings
        Route::get('/settings', [\App\Http\Controllers\Dashboard\Admin\SettingsController::class, 'index']);
        Route::put('/settings', [\App\Http\Controllers\Dashboard\Admin\SettingsController::class, 'update']);
        
        // System logs
        Route::get('/logs', [\App\Http\Controllers\Dashboard\Admin\LogController::class, 'index']);
        Route::delete('/logs', [\App\Http\Controllers\Dashboard\Admin\LogController::class, 'clear']);
        
        // Database management
        Route::post('/database/backup', [\App\Http\Controllers\Dashboard\Admin\DatabaseController::class, 'backup']);
        Route::post('/database/optimize', [\App\Http\Controllers\Dashboard\Admin\DatabaseController::class, 'optimize']);
        Route::get('/database/status', [\App\Http\Controllers\Dashboard\Admin\DatabaseController::class, 'status']);
    });
    
    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index']);
        Route::put('/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
        Route::put('/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy']);
    });
});

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:sanctum');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->middleware('auth:sanctum');
    Route::post('/resend-verification', [AuthController::class, 'resendVerification'])->middleware('auth:sanctum');
    
    // Social authentication
    Route::get('/{provider}/redirect', [AuthController::class, 'redirectToProvider']);
    Route::get('/{provider}/callback', [AuthController::class, 'handleProviderCallback']);
});

// Error reporting
Route::post('/error-reports', [\App\Http\Controllers\ErrorReportController::class, 'store']);

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version', '1.0.0'),
        'environment' => config('app.env'),
    ]);
});

// Fallback route for API
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found',
        'error' => 'The requested API endpoint does not exist'
    ], 404);
});
```

### routes/web.php
```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\ComponentController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ContactController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Language switching
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/pricing', function () {
    return view('pricing');
})->name('pricing');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/terms', function () {
    return view('legal.terms');
})->name('terms');

Route::get('/privacy', function () {
    return view('legal.privacy');
})->name('privacy');

// Documentation routes
Route::prefix('docs')->name('docs.')->group(function () {
    Route::get('/', function () {
        return view('docs.index');
    })->name('index');
    
    Route::get('/authentication', function () {
        return view('docs.authentication');
    })->name('authentication');
    
    Route::get('/components', function () {
        return view('docs.components');
    })->name('components');
    
    Route::get('/integration', function () {
        return view('docs.integration');
    })->name('integration');
    
    Route::get('/examples', function () {
        return view('docs.examples');
    })->name('examples');
    
    Route::get('/troubleshooting', function () {
        return view('docs.troubleshooting');
    })->name('troubleshooting');
});

// Search functionality
Route::get('/search', function () {
    return view('search.results');
})->name('search');

// Authentication routes (Laravel Breeze)
require __DIR__.'/auth.php';

// Dashboard routes (require authentication and verification)
Route::middleware(['auth', 'verified', 'locale'])->group(function () {
    // Main dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile management
    Route::prefix('dashboard/profile')->name('dashboard.profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
        Route::post('/avatar', [ProfileController::class, 'updateAvatar'])->name('avatar.update');
        Route::delete('/avatar', [ProfileController::class, 'deleteAvatar'])->name('avatar.delete');
    });
    
    // Settings
    Route::prefix('dashboard/settings')->name('dashboard.settings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SettingsController::class, 'index'])->name('index');
        Route::put('/', [\App\Http\Controllers\SettingsController::class, 'update'])->name('update');
        Route::get('/api-keys', [\App\Http\Controllers\SettingsController::class, 'apiKeys'])->name('api-keys');
        Route::post('/api-keys/regenerate', [\App\Http\Controllers\SettingsController::class, 'regenerateApiKeys'])->name('api-keys.regenerate');
    });
    
    // Website management
    Route::prefix('dashboard/websites')->name('dashboard.websites.')->group(function () {
        Route::get('/', [WebsiteController::class, 'index'])->name('index');
        Route::get('/create', [WebsiteController::class, 'create'])->name('create');
        Route::post('/', [WebsiteController::class, 'store'])->name('store');
        Route::get('/{website:uuid}', [WebsiteController::class, 'show'])->name('show');
        Route::get('/{website:uuid}/edit', [WebsiteController::class, 'edit'])->name('edit');
        Route::put('/{website:uuid}', [WebsiteController::class, 'update'])->name('update');
        Route::delete('/{website:uuid}', [WebsiteController::class, 'destroy'])->name('destroy');
        Route::post('/{website:uuid}/regenerate-keys', [WebsiteController::class, 'regenerateKeys'])->name('regenerate-keys');
        Route::get('/{website:uuid}/statistics', [WebsiteController::class, 'statistics'])->name('statistics');
        Route::get('/{website:uuid}/logs', [WebsiteController::class, 'logs'])->name('logs');
    });
    
    // Component management (Admin only)
    Route::middleware('role:admin')->prefix('dashboard/components')->name('dashboard.components.')->group(function () {
        Route::get('/', [ComponentController::class, 'index'])->name('index');
        Route::get('/create', [ComponentController::class, 'create'])->name('create');
        Route::post('/', [ComponentController::class, 'store'])->name('store');
        Route::get('/{component:uuid}', [ComponentController::class, 'show'])->name('show');
        Route::get('/{component:uuid}/edit', [ComponentController::class, 'edit'])->name('edit');
        Route::put('/{component:uuid}', [ComponentController::class, 'update'])->name('update');
        Route::delete('/{component:uuid}', [ComponentController::class, 'destroy'])->name('destroy');
        Route::post('/{component:uuid}/duplicate', [ComponentController::class, 'duplicate'])->name('duplicate');
        Route::put('/{component:uuid}/toggle-status', [ComponentController::class, 'toggleStatus'])->name('toggle-status');
    });
    
    // Statistics and analytics
    Route::prefix('dashboard/statistics')->name('dashboard.statistics.')->group(function () {
        Route::get('/', [StatisticsController::class, 'index'])->name('index');
        Route::get('/website', [StatisticsController::class, 'website'])->name('website');
        Route::get('/component', [StatisticsController::class, 'component'])->name('component');
        Route::get('/export', [StatisticsController::class, 'export'])->name('export');
    });
    
    // User management (Admin only)
    Route::middleware('role:admin')->prefix('dashboard/users')->name('dashboard.users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user:uuid}', [UserController::class, 'show'])->name('show');
        Route::get('/{user:uuid}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user:uuid}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user:uuid}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('/{user:uuid}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{user:uuid}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
    });
    
    // Admin panel (Super Admin only)
    Route::middleware('role:admin')->prefix('dashboard/admin')->name('dashboard.admin.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AdminController::class, 'index'])->name('index');
        
        // System settings
        Route::get('/settings', [\App\Http\Controllers\AdminController::class, 'settings'])->name('settings');
        Route::put('/settings', [\App\Http\Controllers\AdminController::class, 'updateSettings'])->name('settings.update');
        
        // System logs
        Route::get('/logs', [\App\Http\Controllers\AdminController::class, 'logs'])->name('logs');
        Route::delete('/logs', [\App\Http\Controllers\AdminController::class, 'clearLogs'])->name('logs.clear');
        
        // Database management
        Route::get('/database', [\App\Http\Controllers\AdminController::class, 'database'])->name('database');
        Route::post('/database/backup', [\App\Http\Controllers\AdminController::class, 'backup'])->name('database.backup');
        Route::post('/database/optimize', [\App\Http\Controllers\AdminController::class, 'optimize'])->name('database.optimize');
        
        // Cache management
        Route::post('/cache/clear', [\App\Http\Controllers\AdminController::class, 'clearCache'])->name('cache.clear');
        Route::post('/cache/config', [\App\Http\Controllers\AdminController::class, 'clearConfigCache'])->name('cache.config');
        Route::post('/cache/route', [\App\Http\Controllers\AdminController::class, 'clearRouteCache'])->name('cache.route');
        Route::post('/cache/view', [\App\Http\Controllers\AdminController::class, 'clearViewCache'])->name('cache.view');
        
        // Queue management
        Route::get('/queue', [\App\Http\Controllers\AdminController::class, 'queue'])->name('queue');
        Route::post('/queue/restart', [\App\Http\Controllers\AdminController::class, 'restartQueue'])->name('queue.restart');
        
        // Maintenance mode
        Route::post('/maintenance/enable', [\App\Http\Controllers\AdminController::class, 'enableMaintenance'])->name('maintenance.enable');
        Route::post('/maintenance/disable', [\App\Http\Controllers\AdminController::class, 'disableMaintenance'])->name('maintenance.disable');
    });
    
    // Notifications
    Route::prefix('dashboard/notifications')->name('dashboard.notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::put('/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::put('/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
    });
});

// CDN Routes for WebBloc assets
Route::prefix('cdn')->name('cdn.')->group(function () {
    Route::get('/js/webbloc.min.js', [\App\Http\Controllers\CDNController::class, 'javascript'])->name('js');
    Route::get('/css/webbloc.min.css', [\App\Http\Controllers\CDNController::class, 'css'])->name('css');
    Route::get('/components/{type}.js', [\App\Http\Controllers\CDNController::class, 'component'])->name('component');
});

// Webhook routes
Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::post('/stripe', [\App\Http\Controllers\WebhookController::class, 'stripe'])->name('stripe');
    Route::post('/github', [\App\Http\Controllers\WebhookController::class, 'github'])->name('github');
    Route::post('/mailgun', [\App\Http\Controllers\WebhookController::class, 'mailgun'])->name('mailgun');
});

// Status page
Route::get('/status', [\App\Http\Controllers\StatusController::class, 'index'])->name('status');

// Sitemap
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

// Robots.txt
Route::get('/robots.txt', function () {
    $content = "User-agent: *\n";
    $content .= "Disallow: /dashboard\n";
    $content .= "Disallow: /api\n";
    $content .= "Allow: /\n";
    $content .= "Sitemap: " . route('sitemap') . "\n";
    
    return response($content)->header('Content-Type', 'text/plain');
})->name('robots');

// Fallback route
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
```

This completes all the requested blade views, language files, and configuration files for the WebBloc project. The system now has:

1. **Complete Blade Views**: All dashboard, authentication, error pages, email templates, and component templates with full multi-language support and Alpine.js integration.

2. **Language Files**: Comprehensive English and Arabic translations for all UI elements and messages.

3. **Configuration Files**: Properly configured bootstrap files and complete routing setup for both web and API endpoints.

The implementation includes:
- Multi-language support (English/Arabic) with RTL layout
- UUID-based routing for enhanced security
- Alpine.js integration for dynamic functionality
- SweetAlert2 for beautiful notifications
- Comprehensive error handling with custom error pages
- Email templates for usage alerts
- Complete API and web routing structure
- Proper middleware configuration
- CSRF protection and security measures

All views are responsive, accessible, and follow modern web development best practices while maintaining the WebBloc project's specific requirements and architecture.
