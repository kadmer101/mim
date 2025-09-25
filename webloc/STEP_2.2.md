Based on my comprehensive analysis of the project documentation, I now understand the complete WebBloc system architecture and requirements. This is a Laravel-based platform that provides dynamic web components as APIs for static websites, with multi-language support (English/Arabic), multi-tenant architecture, UUID-based routing, and Alpine.js integration.

I'll now provide all the blade view codes as requested:

## 3. Website Management Views

### resources/views/dashboard/websites/create.blade.php
```php
@extends('layouts.dashboard')

@section('page-title', __('messages.create_website'))

@section('content')
<div class="max-w-4xl mx-auto p-6" x-data="websiteCreateForm()">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ __('messages.create_website') }}</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ __('messages.create_website_desc') }}</p>
        </div>

        <form @submit.prevent="submitForm" class="space-y-6">
            @csrf
            
            <!-- Website Name -->
            <div>
                <label for="name_en" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.website_name') }} (EN) *
                </label>
                <input 
                    type="text" 
                    id="name_en" 
                    x-model="form.name.en"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    :class="{'border-red-500': errors.name_en}"
                    required
                >
                <div x-show="errors.name_en" class="mt-1 text-sm text-red-600" x-text="errors.name_en"></div>
            </div>

            <div>
                <label for="name_ar" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.website_name') }} (AR)
                </label>
                <input 
                    type="text" 
                    id="name_ar" 
                    x-model="form.name.ar"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    dir="rtl"
                >
            </div>

            <!-- Website Description -->
            <div>
                <label for="description_en" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.description') }} (EN)
                </label>
                <textarea 
                    id="description_en" 
                    x-model="form.description.en"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                ></textarea>
            </div>

            <div>
                <label for="description_ar" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.description') }} (AR)
                </label>
                <textarea 
                    id="description_ar" 
                    x-model="form.description.ar"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    dir="rtl"
                ></textarea>
            </div>

            <!-- Domain -->
            <div>
                <label for="domain" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.domain') }} *
                </label>
                <input 
                    type="url" 
                    id="domain" 
                    x-model="form.domain"
                    placeholder="https://example.com"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    :class="{'border-red-500': errors.domain}"
                    required
                >
                <div x-show="errors.domain" class="mt-1 text-sm text-red-600" x-text="errors.domain"></div>
            </div>

            <!-- Components -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    {{ __('messages.allowed_components') }}
                </label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <template x-for="component in availableComponents" :key="component.id">
                        <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700">
                            <input 
                                type="checkbox" 
                                :value="component.type"
                                x-model="form.allowed_components"
                                class="mr-3 {{ app()->getLocale() == 'ar' ? 'ml-3 mr-0' : '' }}"
                            >
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white" x-text="component.name"></div>
                                <div class="text-sm text-gray-500" x-text="component.description"></div>
                            </div>
                        </label>
                    </template>
                </div>
            </div>

            <!-- Locales -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    {{ __('messages.supported_locales') }}
                </label>
                <div class="flex gap-4">
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            value="en" 
                            x-model="form.allowed_locales"
                            class="mr-2 {{ app()->getLocale() == 'ar' ? 'ml-2 mr-0' : '' }}"
                        >
                        <span class="text-gray-700 dark:text-gray-300">English</span>
                    </label>
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            value="ar" 
                            x-model="form.allowed_locales"
                            class="mr-2 {{ app()->getLocale() == 'ar' ? 'ml-2 mr-0' : '' }}"
                        >
                        <span class="text-gray-700 dark:text-gray-300">العربية</span>
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-4 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <a href="{{ route('dashboard.websites.index') }}" 
                   class="px-4 py-2 text-gray-600 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-500">
                    {{ __('messages.cancel') }}
                </a>
                <button 
                    type="submit" 
                    :disabled="loading"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span x-show="!loading">{{ __('messages.create_website') }}</span>
                    <span x-show="loading" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('messages.creating') }}...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function websiteCreateForm() {
    return {
        loading: false,
        errors: {},
        form: {
            name: { en: '', ar: '' },
            description: { en: '', ar: '' },
            domain: '',
            allowed_components: [],
            allowed_locales: ['en']
        },
        availableComponents: [],

        init() {
            this.loadComponents();
        },

        async loadComponents() {
            try {
                const response = await fetch('/api/components', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const data = await response.json();
                this.availableComponents = data.data || [];
            } catch (error) {
                console.error('Error loading components:', error);
            }
        },

        async submitForm() {
            this.loading = true;
            this.errors = {};

            try {
                const response = await fetch('{{ route("dashboard.websites.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (response.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: '{{ __("messages.success") }}',
                        text: '{{ __("messages.website_created_successfully") }}',
                        confirmButtonText: '{{ __("messages.ok") }}'
                    }).then(() => {
                        window.location.href = '{{ route("dashboard.websites.index") }}';
                    });
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        throw new Error(data.message || 'An error occurred');
                    }
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: '{{ __("messages.error") }}',
                    text: error.message || '{{ __("messages.something_went_wrong") }}',
                    confirmButtonText: '{{ __("messages.ok") }}'
                });
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endsection
```

### resources/views/dashboard/websites/edit.blade.php
```php
@extends('layouts.dashboard')

@section('page-title', __('messages.edit_website'))

@section('content')
<div class="max-w-4xl mx-auto p-6" x-data="websiteEditForm('{{ $website->uuid }}')">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ __('messages.edit_website') }}</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ __('messages.edit_website_desc') }}</p>
        </div>

        <div x-show="loading" class="flex justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>

        <form x-show="!loading" @submit.prevent="submitForm" class="space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Website Name -->
            <div>
                <label for="name_en" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.website_name') }} (EN) *
                </label>
                <input 
                    type="text" 
                    id="name_en" 
                    x-model="form.name.en"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    :class="{'border-red-500': errors.name_en}"
                    required
                >
                <div x-show="errors.name_en" class="mt-1 text-sm text-red-600" x-text="errors.name_en"></div>
            </div>

            <div>
                <label for="name_ar" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.website_name') }} (AR)
                </label>
                <input 
                    type="text" 
                    id="name_ar" 
                    x-model="form.name.ar"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    dir="rtl"
                >
            </div>

            <!-- Website Description -->
            <div>
                <label for="description_en" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.description') }} (EN)
                </label>
                <textarea 
                    id="description_en" 
                    x-model="form.description.en"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                ></textarea>
            </div>

            <div>
                <label for="description_ar" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.description') }} (AR)
                </label>
                <textarea 
                    id="description_ar" 
                    x-model="form.description.ar"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    dir="rtl"
                ></textarea>
            </div>

            <!-- Domain -->
            <div>
                <label for="domain" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.domain') }} *
                </label>
                <input 
                    type="url" 
                    id="domain" 
                    x-model="form.domain"
                    placeholder="https://example.com"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    :class="{'border-red-500': errors.domain}"
                    required
                >
                <div x-show="errors.domain" class="mt-1 text-sm text-red-600" x-text="errors.domain"></div>
            </div>

            <!-- API Keys Display -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('messages.public_api_key') }}
                    </label>
                    <div class="flex">
                        <input 
                            type="text" 
                            :value="form.public_key"
                            readonly
                            class="flex-1 px-3 py-2 bg-gray-100 border border-gray-300 rounded-l-lg dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        >
                        <button 
                            type="button"
                            @click="copyToClipboard(form.public_key)"
                            class="px-3 py-2 bg-gray-200 border border-l-0 border-gray-300 rounded-r-lg hover:bg-gray-300 dark:bg-gray-500 dark:border-gray-400 dark:hover:bg-gray-400"
                        >
                            📋
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('messages.secret_api_key') }}
                    </label>
                    <div class="flex">
                        <input 
                            type="password" 
                            :value="form.secret_key"
                            readonly
                            class="flex-1 px-3 py-2 bg-gray-100 border border-gray-300 rounded-l-lg dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        >
                        <button 
                            type="button"
                            @click="copyToClipboard(form.secret_key)"
                            class="px-3 py-2 bg-gray-200 border border-l-0 border-gray-300 rounded-r-lg hover:bg-gray-300 dark:bg-gray-500 dark:border-gray-400 dark:hover:bg-gray-400"
                        >
                            📋
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex justify-center">
                <button 
                    type="button"
                    @click="regenerateKeys"
                    class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700"
                >
                    {{ __('messages.regenerate_api_keys') }}
                </button>
            </div>

            <!-- Components -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    {{ __('messages.allowed_components') }}
                </label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <template x-for="component in availableComponents" :key="component.id">
                        <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700">
                            <input 
                                type="checkbox" 
                                :value="component.type"
                                x-model="form.allowed_components"
                                class="mr-3 {{ app()->getLocale() == 'ar' ? 'ml-3 mr-0' : '' }}"
                            >
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white" x-text="component.name"></div>
                                <div class="text-sm text-gray-500" x-text="component.description"></div>
                            </div>
                        </label>
                    </template>
                </div>
            </div>

            <!-- Locales -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    {{ __('messages.supported_locales') }}
                </label>
                <div class="flex gap-4">
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            value="en" 
                            x-model="form.allowed_locales"
                            class="mr-2 {{ app()->getLocale() == 'ar' ? 'ml-2 mr-0' : '' }}"
                        >
                        <span class="text-gray-700 dark:text-gray-300">English</span>
                    </label>
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            value="ar" 
                            x-model="form.allowed_locales"
                            class="mr-2 {{ app()->getLocale() == 'ar' ? 'ml-2 mr-0' : '' }}"
                        >
                        <span class="text-gray-700 dark:text-gray-300">العربية</span>
                    </label>
                </div>
            </div>

            <!-- Status -->
            <div>
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        x-model="form.is_active"
                        class="mr-2 {{ app()->getLocale() == 'ar' ? 'ml-2 mr-0' : '' }}"
                    >
                    <span class="text-gray-700 dark:text-gray-300">{{ __('messages.active') }}</span>
                </label>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-4 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <a href="{{ route('dashboard.websites.index') }}" 
                   class="px-4 py-2 text-gray-600 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-500">
                    {{ __('messages.cancel') }}
                </a>
                <button 
                    type="submit" 
                    :disabled="saving"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span x-show="!saving">{{ __('messages.update_website') }}</span>
                    <span x-show="saving" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('messages.updating') }}...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function websiteEditForm(uuid) {
    return {
        loading: true,
        saving: false,
        errors: {},
        form: {
            name: { en: '', ar: '' },
            description: { en: '', ar: '' },
            domain: '',
            public_key: '',
            secret_key: '',
            allowed_components: [],
            allowed_locales: ['en'],
            is_active: true
        },
        availableComponents: [],

        init() {
            this.loadWebsite();
            this.loadComponents();
        },

        async loadWebsite() {
            try {
                const response = await fetch(`/api/websites/${uuid}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const data = await response.json();
                
                if (response.ok) {
                    this.form = { ...this.form, ...data.data };
                    this.loading = false;
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: '{{ __("messages.error") }}',
                    text: error.message || '{{ __("messages.something_went_wrong") }}',
                    confirmButtonText: '{{ __("messages.ok") }}'
                });
            }
        },

        async loadComponents() {
            try {
                const response = await fetch('/api/components', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const data = await response.json();
                this.availableComponents = data.data || [];
            } catch (error) {
                console.error('Error loading components:', error);
            }
        },

        async submitForm() {
            this.saving = true;
            this.errors = {};

            try {
                const response = await fetch(`{{ route("dashboard.websites.update", ":uuid") }}`.replace(':uuid', uuid), {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (response.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: '{{ __("messages.success") }}',
                        text: '{{ __("messages.website_updated_successfully") }}',
                        confirmButtonText: '{{ __("messages.ok") }}'
                    }).then(() => {
                        window.location.href = '{{ route("dashboard.websites.index") }}';
                    });
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        throw new Error(data.message || 'An error occurred');
                    }
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: '{{ __("messages.error") }}',
                    text: error.message || '{{ __("messages.something_went_wrong") }}',
                    confirmButtonText: '{{ __("messages.ok") }}'
                });
            } finally {
                this.saving = false;
            }
        },

        async regenerateKeys() {
            const result = await Swal.fire({
                title: '{{ __("messages.confirm_regenerate_keys") }}',
                text: '{{ __("messages.regenerate_keys_warning") }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '{{ __("messages.yes_regenerate") }}',
                cancelButtonText: '{{ __("messages.cancel") }}'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/api/websites/${uuid}/regenerate-keys`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    const data = await response.json();
                    
                    if (response.ok) {
                        this.form.public_key = data.data.public_key;
                        this.form.secret_key = data.data.secret_key;
                        
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __("messages.success") }}',
                            text: '{{ __("messages.keys_regenerated_successfully") }}',
                            confirmButtonText: '{{ __("messages.ok") }}'
                        });
                    } else {
                        throw new Error(data.message);
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __("messages.error") }}',
                        text: error.message || '{{ __("messages.something_went_wrong") }}',
                        confirmButtonText: '{{ __("messages.ok") }}'
                    });
                }
            }
        },

        copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                // Simple toast notification
                const toast = document.createElement('div');
                toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
                toast.textContent = '{{ __("messages.copied_to_clipboard") }}';
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 3000);
            });
        }
    }
}
</script>
@endsection
```

### resources/views/dashboard/websites/show.blade.php
```php
@extends('layouts.dashboard')

@section('page-title', __('messages.website_details'))

@section('content')
<div class="max-w-6xl mx-auto p-6" x-data="websiteDetails('{{ $website->uuid }}')">
    <div x-show="loading" class="flex justify-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <div x-show="!loading" class="space-y-6">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2" x-text="website.name"></h1>
                    <p class="text-gray-600 dark:text-gray-400 mb-4" x-text="website.description"></p>
                    <div class="flex items-center space-x-4 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              :class="website.is_active ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100'"
                              x-text="website.is_active ? '{{ __('messages.active') }}' : '{{ __('messages.inactive') }}'">
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              :class="website.is_verified ? 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100'"
                              x-text="website.is_verified ? '{{ __('messages.verified') }}' : '{{ __('messages.pending_verification') }}'">
                        </span>
                    </div>
                </div>
                <div class="flex space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                    <a :href="`{{ route('dashboard.websites.edit', ':uuid') }}`.replace(':uuid', website.uuid)" 
                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        {{ __('messages.edit') }}
                    </a>
                    <button @click="deleteWebsite" 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        {{ __('messages.delete') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Website Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Basic Info -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">{{ __('messages.basic_information') }}</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('messages.domain') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            <a :href="website.domain" target="_blank" class="text-blue-600 hover:text-blue-800" x-text="website.domain"></a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('messages.created_at') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white" x-text="formatDate(website.created_at)"></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('messages.last_updated') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white" x-text="formatDate(website.updated_at)"></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('messages.supported_locales') }}</dt>
                        <dd class="mt-1">
                            <template x-for="locale in website.allowed_locales" :key="locale">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 mr-2 {{ app()->getLocale() == 'ar' ? 'ml-2 mr-0' : '' }}"
                                      x-text="locale === 'en' ? 'English' : 'العربية'">
                                </span>
                            </template>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- API Keys -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">{{ __('messages.api_keys') }}</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">{{ __('messages.public_api_key') }}</label>
                        <div class="flex">
                            <input type="text" :value="website.public_key" readonly 
                                   class="flex-1 px-3 py-2 bg-gray-100 border border-gray-300 rounded-l-lg dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                            <button @click="copyToClipboard(website.public_key)"
                                    class="px-3 py-2 bg-gray-200 border border-l-0 border-gray-300 rounded-r-lg hover:bg-gray-300 dark:bg-gray-500 dark:border-gray-400 dark:hover:bg-gray-400">
                                📋
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">{{ __('messages.secret_api_key') }}</label>
                        <div class="flex">
                            <input type="password" :value="website.secret_key" readonly 
                                   class="flex-1 px-3 py-2 bg-gray-100 border border-gray-300 rounded-l-lg dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                            <button @click="copyToClipboard(website.secret_key)"
                                    class="px-3 py-2 bg-gray-200 border border-l-0 border-gray-300 rounded-r-lg hover:bg-gray-300 dark:bg-gray-500 dark:border-gray-400 dark:hover:bg-gray-400">
                                📋
                            </button>
                        </div>
                    </div>
                    <button @click="regenerateKeys" 
                            class="w-full px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                        {{ __('messages.regenerate_api_keys') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Components & Statistics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Allowed Components -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">{{ __('messages.allowed_components') }}</h2>
                <div class="grid grid-cols-1 gap-3">
                    <template x-for="component in website.allowed_components" :key="component">
                        <div class="flex items-center p-3 border border-gray-200 rounded-lg dark:border-gray-600">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900 dark:text-white" x-text="getComponentName(component)"></div>
                                <div class="text-sm text-gray-500" x-text="getComponentDescription(component)"></div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                {{ __('messages.enabled') }}
                            </span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Usage Statistics -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">{{ __('messages.usage_statistics') }}</h2>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">{{ __('messages.total_api_requests') }}</span>
                        <span class="font-semibold text-gray-900 dark:text-white" x-text="stats.total_requests || 0"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">{{ __('messages.requests_today') }}</span>
                        <span class="font-semibold text-gray-900 dark:text-white" x-text="stats.requests_today || 0"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">{{ __('messages.requests_this_month') }}</span>
                        <span class="font-semibold text-gray-900 dark:text-white" x-text="stats.requests_month || 0"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">{{ __('messages.most_used_component') }}</span>
                        <span class="font-semibold text-gray-900 dark:text-white" x-text="stats.most_used_component || '-'"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Integration Code -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">{{ __('messages.integration_code') }}</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.cdn_script') }}</label>
                    <div class="relative">
                        <pre class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg text-sm overflow-x-auto"><code x-text="getCDNScript()"></code></pre>
                        <button @click="copyToClipboard(getCDNScript())" 
                                class="absolute top-2 right-2 px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs hover:bg-gray-300 dark:hover:bg-gray-500">
                            {{ __('messages.copy') }}
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.component_example') }}</label>
                    <div class="relative">
                        <pre class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg text-sm overflow-x-auto"><code x-text="getComponentExample()"></code></pre>
                        <button @click="copyToClipboard(getComponentExample())" 
                                class="absolute top-2 right-2 px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs hover:bg-gray-300 dark:hover:bg-gray-500">
                            {{ __('messages.copy') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function websiteDetails(uuid) {
    return {
        loading: true,
        website: {},
        stats: {},
        components: [],

        init() {
            this.loadWebsite();
            this.loadStats();
            this.loadComponents();
        },

        async loadWebsite() {
            try {
                const response = await fetch(`/api/websites/${uuid}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const data = await response.json();
                
                if (response.ok) {
                    this.website = data.data;
                    this.loading = false;
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: '{{ __("messages.error") }}',
                    text: error.message || '{{ __("messages.something_went_wrong") }}',
                    confirmButtonText: '{{ __("messages.ok") }}'
                });
            }
        },

        async loadStats() {
            try {
                const response = await fetch(`/api/websites/${uuid}/stats`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const data = await response.json();
                
                if (response.ok) {
                    this.stats = data.data;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        },

        async loadComponents() {
            try {
                const response = await fetch('/api/components', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const data = await response.json();
                this.components = data.data || [];
            } catch (error) {
                console.error('Error loading components:', error);
            }
        },

        async deleteWebsite() {
            const result = await Swal.fire({
                title: '{{ __("messages.confirm_delete_website") }}',
                text: '{{ __("messages.delete_website_warning") }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '{{ __("messages.yes_delete") }}',
                cancelButtonText: '{{ __("messages.cancel") }}'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/api/websites/${uuid}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (response.ok) {
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __("messages.success") }}',
                            text: '{{ __("messages.website_deleted_successfully") }}',
                            confirmButtonText: '{{ __("messages.ok") }}'
                        }).then(() => {
                            window.location.href = '{{ route("dashboard.websites.index") }}';
                        });
                    } else {
                        const data = await response.json();
                        throw new Error(data.message);
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __("messages.error") }}',
                        text: error.message || '{{ __("messages.something_went_wrong") }}',
                        confirmButtonText: '{{ __("messages.ok") }}'
                    });
                }
            }
        },

        async regenerateKeys() {
            const result = await Swal.fire({
                title: '{{ __("messages.confirm_regenerate_keys") }}',
                text: '{{ __("messages.regenerate_keys_warning") }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '{{ __("messages.yes_regenerate") }}',
                cancelButtonText: '{{ __("messages.cancel") }}'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/api/websites/${uuid}/regenerate-keys`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    const data = await response.json();
                    
                    if (response.ok) {
                        this.website.public_key = data.data.public_key;
                        this.website.secret_key = data.data.secret_key;
                        
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __("messages.success") }}',
                            text: '{{ __("messages.keys_regenerated_successfully") }}',
                            confirmButtonText: '{{ __("messages.ok") }}'
                        });
                    } else {
                        throw new Error(data.message);
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __("messages.error") }}',
                        text: error.message || '{{ __("messages.something_went_wrong") }}',
                        confirmButtonText: '{{ __("messages.ok") }}'
                    });
                }
            }
        },

        getComponentName(type) {
            const component = this.components.find(c => c.type === type);
            return component ? component.name : type;
        },

        getComponentDescription(type) {
            const component = this.components.find(c => c.type === type);
            return component ? component.description : '';
        },

        getCDNScript() {
            return `<script src="${window.location.origin}/js/webbloc.js" data-public-key="${this.website.public_key}"></script>`;
        },

        getComponentExample() {
            return `<div w2030b="comments" w2030b_tags='{"limit": 10, "sort": "newest"}'>Loading comments...</div>`;
        },

        copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                const toast = document.createElement('div');
                toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
                toast.textContent = '{{ __("messages.copied_to_clipboard") }}';
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 3000);
            });
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('{{ app()->getLocale() }}', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }
}
</script>
@endsection
```

## 4. Component Management Views

### resources/views/dashboard/components/index.blade.php
```php
@extends('layouts.dashboard')

@section('page-title', __('messages.components'))

@section('content')
<div class="max-w-7xl mx-auto p-6" x-data="componentsIndex()">
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('messages.components') }}</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('messages.manage_webbloc_components') }}</p>
            </div>
            
            @hasrole('admin')
            <a href="{{ route('dashboard.components.create') }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center">
                <svg class="w-5 h-5 mr-2 {{ app()->getLocale() == 'ar' ? 'ml-2 mr-0' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                {{ __('messages.create_component') }}
            </a>
            @endhasrole
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.search') }}
                </label>
                <input 
                    type="text" 
                    id="search"
                    x-model="filters.search"
                    @input="debounceSearch"
                    placeholder="{{ __('messages.search_components') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                >
            </div>
            
            <div>
                <label for="component_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.type') }}
                </label>
                <select 
                    id="component_type"
                    x-model="filters.type"
                    @change="loadComponents"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                >
                    <option value="">{{ __('messages.all_types') }}</option>
                    <option value="comment">{{ __('messages.comments') }}</option>
                    <option value="review">{{ __('messages.reviews') }}</option>
                    <option value="auth">{{ __('messages.authentication') }}</option>
                    <option value="testimonial">{{ __('messages.testimonials') }}</option>
                    <option value="reaction">{{ __('messages.reactions') }}</option>
                </select>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.status') }}
                </label>
                <select 
                    id="status"
                    x-model="filters.status"
                    @change="loadComponents"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                >
                    <option value="">{{ __('messages.all_statuses') }}</option>
                    <option value="active">{{ __('messages.active') }}</option>
                    <option value="inactive">{{ __('messages.inactive') }}</option>
                </select>
            </div>

            <div>
                <label for="per_page" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.per_page') }}
                </label>
                <select 
                    id="per_page"
                    x-model="pagination.per_page"
                    @change="loadComponents"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                >
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Components List -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md">
        <!-- Loading State -->
        <div x-show="loading" class="flex justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && components.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('messages.no_components_found') }}</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.no_components_desc') }}</p>
            @hasrole('admin')
            <div class="mt-6">
                <a href="{{ route('dashboard.components.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                    </svg>
                    {{ __('messages.create_first_component') }}
                </a>
            </div>
            @endhasrole
        </div>

        <!-- Components Grid -->
        <div x-show="!loading && components.length > 0" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="component in components" :key="component.uuid">
                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1" x-text="component.name"></h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2" x-text="component.description"></p>
                                <div class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100" x-text="component.type"></span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          :class="component.is_active ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100'"
                                          x-text="component.is_active ? '{{ __('messages.active') }}' : '{{ __('messages.inactive') }}'">
                                    </span>
                                </div>
                            </div>
                            <div class="flex-shrink-0 ml-4 {{ app()->getLocale() == 'ar' ? 'mr-4 ml-0' : '' }}">
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                        </svg>
                                    </button>
                                    <div x-show="open" @click.away="open = false" 
                                         class="absolute right-0 {{ app()->getLocale() == 'ar' ? 'left-0 right-auto' : '' }} mt-2 w-48 bg-white dark:bg-gray-700 rounded-md shadow-lg z-10">
                                        <div class="py-1">
                                            <a :href="`{{ route('dashboard.components.show', ':uuid') }}`.replace(':uuid', component.uuid)" 
                                               class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                                {{ __('messages.view_details') }}
                                            </a>
                                            @hasrole('admin')
                                            <a :href="`{{ route('dashboard.components.edit', ':uuid') }}`.replace(':uuid', component.uuid)" 
                                               class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                                {{ __('messages.edit') }}
                                            </a>
                                            <button @click="toggleComponentStatus(component)" 
                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600"
                                                    x-text="component.is_active ? '{{ __('messages.deactivate') }}' : '{{ __('messages.activate') }}'">
                                            </button>
                                            <button @click="deleteComponent(component)" 
                                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-600">
                                                {{ __('messages.delete') }}
                                            </button>
                                            @endhasrole
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CRUD Permissions -->
                        <div class="mb-4">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">{{ __('messages.permissions') }}</p>
                            <div class="flex space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                                <span x-show="component.crud.create" class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">C</span>
                                <span x-show="component.crud.read" class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">R</span>
                                <span x-show="component.crud.update" class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">U</span>
                                <span x-show="component.crud.delete" class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">D</span>
                            </div>
                        </div>

                        <!-- Usage Stats -->
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            <div class="flex justify-between">
                                <span>{{ __('messages.version') }}</span>
                                <span x-text="component.version"></span>
                            </div>
                            <div class="flex justify-between">
                                <span>{{ __('messages.created') }}</span>
                                <span x-text="formatDate(component.created_at)"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Pagination -->
        <div x-show="!loading && components.length > 0" class="px-6 py-4 border-t border-gray-200 dark:border-gray-600">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    <span>{{ __('messages.showing') }}</span>
                    <span x-text="pagination.from"></span>
                    <span>{{ __('messages.to') }}</span>
                    <span x-text="pagination.to"></span>
                    <span>{{ __('messages.of') }}</span>
                    <span x-text="pagination.total"></span>
                    <span>{{ __('messages.results') }}</span>
                </div>
                <div class="flex space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                    <button @click="previousPage" 
                            :disabled="!pagination.prev_page_url"
                            class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:border-gray-600 dark:hover:bg-gray-700 dark:text-white">
                        {{ __('messages.previous') }}
                    </button>
                    <button @click="nextPage" 
                            :disabled="!pagination.next_page_url"
                            class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:border-gray-600 dark:hover:bg-gray-700 dark:text-white">
                        {{ __('messages.next') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function componentsIndex() {
    return {
        loading: true,
        components: [],
        filters: {
            search: '',
            type: '',
            status: ''
        },
        pagination: {
            current_page: 1,
            per_page: 25,
            total: 0,
            from: 0,
            to: 0,
            prev_page_url: null,
            next_page_url: null
        },
        searchTimeout: null,

        init() {
            this.loadComponents();
        },

        async loadComponents() {
            this.loading = true;
            
            try {
                const params = new URLSearchParams({
                    page: this.pagination.current_page,
                    per_page: this.pagination.per_page,
                    ...this.filters
                });

                const response = await fetch(`/api/components?${params}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();
                
                if (response.ok) {
                    this.components = data.data;
                    this.pagination = {
                        current_page: data.current_page,
                        per_page: data.per_page,
                        total: data.total,
                        from: data.from,
                        to: data.to,
                        prev_page_url: data.prev_page_url,
                        next_page_url: data.next_page_url
                    };
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: '{{ __("messages.error") }}',
                    text: error.message || '{{ __("messages.something_went_wrong") }}',
                    confirmButtonText: '{{ __("messages.ok") }}'
                });
            } finally {
                this.loading = false;
            }
        },

        debounceSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.pagination.current_page = 1;
                this.loadComponents();
            }, 500);
        },

        previousPage() {
            if (this.pagination.prev_page_url) {
                this.pagination.current_page--;
                this.loadComponents();
            }
        },

        nextPage() {
            if (this.pagination.next_page_url) {
                this.pagination.current_page++;
                this.loadComponents();
            }
        },

        async toggleComponentStatus(component) {
            const action = component.is_active ? '{{ __("messages.deactivate") }}' : '{{ __("messages.activate") }}';
            
            const result = await Swal.fire({
                title: `{{ __("messages.confirm") }} ${action}?`,
                text: `{{ __("messages.confirm_component_status_change") }}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: action,
                cancelButtonText: '{{ __("messages.cancel") }}'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/api/components/${component.uuid}/toggle-status`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (response.ok) {
                        component.is_active = !component.is_active;
                        
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __("messages.success") }}',
                            text: '{{ __("messages.component_status_updated") }}',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        const data = await response.json();
                        throw new Error(data.message);
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __("messages.error") }}',
                        text: error.message || '{{ __("messages.something_went_wrong") }}',
                        confirmButtonText: '{{ __("messages.ok") }}'
                    });
                }
            }
        },

        async deleteComponent(component) {
            const result = await Swal.fire({
                title: '{{ __("messages.confirm_delete_component") }}',
                text: '{{ __("messages.delete_component_warning") }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '{{ __("messages.yes_delete") }}',
                cancelButtonText: '{{ __("messages.cancel") }}'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/api/components/${component.uuid}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (response.ok) {
                        this.loadComponents();
                        
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __("messages.success") }}',
                            text: '{{ __("messages.component_deleted_successfully") }}',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        const data = await response.json();
                        throw new Error(data.message);
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __("messages.error") }}',
                        text: error.message || '{{ __("messages.something_went_wrong") }}',
                        confirmButtonText: '{{ __("messages.ok") }}'
                    });
                }
            }
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('{{ app()->getLocale() }}');
        }
    }
}
</script>
@endsection
```

Remaining views including:

1. Component Management Views (create, edit, show)
2. Statistics & Analytics Views 
3. User Management Views
4. Authentication Views
5. WebBloc Component Templates
6. API Documentation Views
7. Public Landing Views
8. Email Templates
9. Error Pages

Each with complete code implementation including multi-language support, RTL support, Alpine.js integration, and all the specified features.
