Based on my comprehensive analysis of all the project documentation, I now understand the complete WebBloc system architecture and requirements. This is a Laravel-based platform that provides dynamic web components as APIs for static websites, with multi-language support (English/Arabic), multi-tenant architecture, UUID-based routing, and Alpine.js integration.

I'll now provide all the blade view codes as requested:

## 6. User Management Views

### resources/views/dashboard/users/edit.blade.php

```php
@extends('layouts.dashboard')

@section('title', __('messages.edit_user'))
@section('page-title', __('messages.edit_user'))

@section('content')
<div class="max-w-4xl mx-auto">
    <div x-data="editUser()" x-init="init()">
        <!-- Loading Overlay -->
        <div x-show="loading" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-sm w-full mx-4">
                <div class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-gray-900 dark:text-gray-100">{{ __('messages.loading') }}...</span>
                </div>
            </div>
        </div>

        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('messages.edit_user') }}</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('messages.update_user_information') }}</p>
                </div>
                <a href="{{ route('dashboard.users.index') }}" 
                   class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>
                    {{ __('messages.back_to_users') }}
                </a>
            </div>
        </div>

        <!-- Edit User Form -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
            <form @submit.prevent="updateUser()" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('messages.full_name') }} *
                        </label>
                        <input type="text" 
                               id="name" 
                               x-model="form.name"
                               class="form-input"
                               :class="{ 'border-red-500': errors.name }"
                               required>
                        <p x-show="errors.name" x-text="errors.name" class="mt-1 text-sm text-red-600"></p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('messages.email_address') }} *
                        </label>
                        <input type="email" 
                               id="email" 
                               x-model="form.email"
                               class="form-input"
                               :class="{ 'border-red-500': errors.email }"
                               required>
                        <p x-show="errors.email" x-text="errors.email" class="mt-1 text-sm text-red-600"></p>
                    </div>
                </div>

                <!-- Role and Status -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Role -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('messages.user_role') }} *
                        </label>
                        <select id="role" 
                                x-model="form.role"
                                @change="handleRoleChange()"
                                class="form-select"
                                :class="{ 'border-red-500': errors.role }"
                                required>
                            <option value="">{{ __('messages.select_role') }}</option>
                            <option value="user">{{ __('messages.user') }}</option>
                            <option value="admin">{{ __('messages.admin') }}</option>
                        </select>
                        <p x-show="errors.role" x-text="errors.role" class="mt-1 text-sm text-red-600"></p>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('messages.account_status') }}
                        </label>
                        <select id="status" 
                                x-model="form.status"
                                class="form-select"
                                :class="{ 'border-red-500': errors.status }">
                            <option value="active">{{ __('messages.active') }}</option>
                            <option value="suspended">{{ __('messages.suspended') }}</option>
                        </select>
                        <p x-show="errors.status" x-text="errors.status" class="mt-1 text-sm text-red-600"></p>
                    </div>
                </div>

                <!-- Locale -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="locale" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('messages.preferred_language') }}
                        </label>
                        <select id="locale" 
                                x-model="form.locale"
                                class="form-select"
                                :class="{ 'border-red-500': errors.locale }">
                            <option value="en">{{ __('messages.english') }}</option>
                            <option value="ar">{{ __('messages.arabic') }}</option>
                        </select>
                        <p x-show="errors.locale" x-text="errors.locale" class="mt-1 text-sm text-red-600"></p>
                    </div>
                </div>

                <!-- Password Section -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.change_password') }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ __('messages.leave_blank_keep_current') }}</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- New Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('messages.new_password') }}
                            </label>
                            <div class="relative">
                                <input :type="showPassword ? 'text' : 'password'" 
                                       id="password" 
                                       x-model="form.password"
                                       class="form-input pr-10"
                                       :class="{ 'border-red-500': errors.password }">
                                <button type="button" 
                                        @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'" 
                                       class="text-gray-400 hover:text-gray-600"></i>
                                </button>
                            </div>
                            <p x-show="errors.password" x-text="errors.password" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('messages.confirm_password') }}
                            </label>
                            <input :type="showPassword ? 'text' : 'password'" 
                                   id="password_confirmation" 
                                   x-model="form.password_confirmation"
                                   class="form-input"
                                   :class="{ 'border-red-500': errors.password_confirmation }">
                            <p x-show="errors.password_confirmation" x-text="errors.password_confirmation" class="mt-1 text-sm text-red-600"></p>
                        </div>
                    </div>
                </div>

                <!-- Admin Permissions (only for admin role) -->
                <div x-show="form.role === 'admin'" class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.admin_permissions') }}</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <template x-for="permission in permissions" :key="permission.name">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       :value="permission.name"
                                       x-model="form.permissions"
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <div class="ml-3">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="permission.display_name"></span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400" x-text="permission.description"></p>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- Settings -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.account_settings') }}</h3>
                    
                    <div class="space-y-4">
                        <!-- Email Verified -->
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   x-model="form.email_verified"
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                            <div class="ml-3">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.email_verified') }}</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.mark_email_verified') }}</p>
                            </div>
                        </label>

                        <!-- Force Password Change -->
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   x-model="form.force_password_change"
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                            <div class="ml-3">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.force_password_change') }}</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.user_must_change_password') }}</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('dashboard.users.index') }}" class="btn btn-secondary">
                        {{ __('messages.cancel') }}
                    </a>
                    <button type="submit" 
                            :disabled="submitting"
                            class="btn btn-primary"
                            :class="{ 'opacity-50 cursor-not-allowed': submitting }">
                        <span x-show="!submitting">{{ __('messages.update_user') }}</span>
                        <span x-show="submitting" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
</div>

<script>
function editUser() {
    return {
        loading: false,
        submitting: false,
        showPassword: false,
        user: null,
        form: {
            name: '',
            email: '',
            role: 'user',
            status: 'active',
            locale: 'en',
            password: '',
            password_confirmation: '',
            permissions: [],
            email_verified: false,
            force_password_change: false
        },
        errors: {},
        permissions: [
            {
                name: 'manage_users',
                display_name: '{{ __("messages.manage_users") }}',
                description: '{{ __("messages.manage_users_desc") }}'
            },
            {
                name: 'manage_websites',
                display_name: '{{ __("messages.manage_websites") }}',
                description: '{{ __("messages.manage_websites_desc") }}'
            },
            {
                name: 'manage_components',
                display_name: '{{ __("messages.manage_components") }}',
                description: '{{ __("messages.manage_components_desc") }}'
            },
            {
                name: 'view_statistics',
                display_name: '{{ __("messages.view_statistics") }}',
                description: '{{ __("messages.view_statistics_desc") }}'
            }
        ],

        async init() {
            await this.loadUser();
        },

        async loadUser() {
            this.loading = true;
            try {
                const response = await fetch(`/api/dashboard/users/{{ $user->uuid }}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    this.user = await response.json();
                    this.form = {
                        name: this.user.name,
                        email: this.user.email,
                        role: this.user.role || 'user',
                        status: this.user.status || 'active',
                        locale: this.user.locale || 'en',
                        password: '',
                        password_confirmation: '',
                        permissions: this.user.permissions || [],
                        email_verified: !!this.user.email_verified_at,
                        force_password_change: !!this.user.force_password_change
                    };
                } else {
                    throw new Error('Failed to load user');
                }
            } catch (error) {
                console.error('Error loading user:', error);
                this.showToast('{{ __("messages.error_loading_user") }}', 'error');
            } finally {
                this.loading = false;
            }
        },

        handleRoleChange() {
            if (this.form.role !== 'admin') {
                this.form.permissions = [];
            }
        },

        async updateUser() {
            this.submitting = true;
            this.errors = {};

            try {
                const response = await fetch(`/dashboard/users/{{ $user->uuid }}`, {
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
                    Swal.fire({
                        title: '{{ __("messages.success") }}',
                        text: '{{ __("messages.user_updated_successfully") }}',
                        icon: 'success',
                        confirmButtonText: '{{ __("messages.ok") }}'
                    }).then(() => {
                        window.location.href = '{{ route("dashboard.users.index") }}';
                    });
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        throw new Error(data.message || 'Validation failed');
                    }
                }
            } catch (error) {
                console.error('Error updating user:', error);
                this.showToast(error.message || '{{ __("messages.error_updating_user") }}', 'error');
            } finally {
                this.submitting = false;
            }
        },

        showToast(message, type = 'info') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });

            Toast.fire({
                icon: type,
                title: message
            });
        }
    }
}
</script>
@endsection
```

### resources/views/dashboard/users/show.blade.php

```php
@extends('layouts.dashboard')

@section('title', __('messages.user_details'))
@section('page-title', $user->name)

@section('content')
<div class="max-w-6xl mx-auto">
    <div x-data="userDetails()" x-init="init()">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('messages.user_details') }}</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('messages.view_user_information') }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('dashboard.users.edit', $user->uuid) }}" 
                       class="btn btn-primary">
                        <i class="fas fa-edit mr-2"></i>
                        {{ __('messages.edit_user') }}
                    </a>
                    <a href="{{ route('dashboard.users.index') }}" 
                       class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>
                        {{ __('messages.back_to_users') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- User Information Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Basic Information -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <div class="flex items-center mb-6">
                        <div class="flex-shrink-0">
                            <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center text-white text-xl font-bold">
                                {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $user->name }}</h2>
                            <p class="text-gray-600 dark:text-gray-400">{{ $user->email }}</p>
                            <div class="flex items-center mt-2 space-x-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $user->getRoleColor() }}">
                                    {{ ucfirst($user->role ?? 'user') }}
                                </span>
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $user->getStatusColor() }}">
                                    {{ ucfirst($user->status ?? 'active') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.account_information') }}</h3>
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.user_id') }}</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $user->uuid }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.email_verified') }}</dt>
                                    <dd class="text-sm">
                                        @if($user->email_verified_at)
                                            <span class="text-green-600 dark:text-green-400">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                {{ __('messages.verified') }}
                                            </span>
                                        @else
                                            <span class="text-red-600 dark:text-red-400">
                                                <i class="fas fa-times-circle mr-1"></i>
                                                {{ __('messages.not_verified') }}
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.preferred_language') }}</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ $user->locale === 'ar' ? __('messages.arabic') : __('messages.english') }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.activity_information') }}</h3>
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.joined_date') }}</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ $user->created_at?->format('M d, Y') }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.last_login') }}</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ $user->last_login_at?->format('M d, Y H:i') ?? __('messages.never') }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.last_updated') }}</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ $user->updated_at?->format('M d, Y H:i') }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="space-y-6">
                <!-- Websites Count -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                <i class="fas fa-globe text-blue-600 dark:text-blue-400"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('messages.websites') }}</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100" x-text="stats.websites_count">{{ $user->websites_count ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- API Requests Today -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                <i class="fas fa-chart-line text-green-600 dark:text-green-400"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('messages.api_requests_today') }}</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100" x-text="stats.api_requests_today">0</p>
                        </div>
                    </div>
                </div>

                <!-- Storage Used -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                <i class="fas fa-database text-purple-600 dark:text-purple-400"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('messages.storage_used') }}</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100" x-text="stats.storage_used">0 MB</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Permissions (Admin Only) -->
        @if($user->role === 'admin')
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.admin_permissions') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($user->permissions ?? [] as $permission)
                <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $permission->display_name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $permission->description }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Websites -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('messages.user_websites') }}</h3>
                <span class="text-sm text-gray-500 dark:text-gray-400" x-text="`${websites.length} {{ __('messages.websites') }}`"></span>
            </div>

            <div x-show="websites.length === 0" class="text-center py-8">
                <i class="fas fa-globe text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
                <p class="text-gray-500 dark:text-gray-400">{{ __('messages.no_websites_found') }}</p>
            </div>

            <div x-show="websites.length > 0" class="space-y-4">
                <template x-for="website in websites" :key="website.uuid">
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100" x-text="website.name.{{ app()->getLocale() }} || website.name.en || 'Unnamed Website'"></h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400" x-text="website.domain"></p>
                                <div class="flex items-center mt-2 space-x-3">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full" 
                                          :class="website.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'">
                                        <span x-text="website.is_active ? '{{ __('messages.active') }}' : '{{ __('messages.inactive') }}'"></span>
                                    </span>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full" 
                                          :class="website.verified_at ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'">
                                        <span x-text="website.verified_at ? '{{ __('messages.verified') }}' : '{{ __('messages.pending') }}'"></span>
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <a :href="`/dashboard/websites/${website.uuid}`" 
                                   class="btn btn-sm btn-outline-primary">
                                    {{ __('messages.view') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.recent_activity') }}</h3>
            
            <div x-show="activities.length === 0" class="text-center py-8">
                <i class="fas fa-clock text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
                <p class="text-gray-500 dark:text-gray-400">{{ __('messages.no_recent_activity') }}</p>
            </div>

            <div x-show="activities.length > 0" class="space-y-4">
                <template x-for="activity in activities" :key="activity.id">
                    <div class="flex items-start space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs" 
                                 :class="getActivityColor(activity.type)">
                                <i :class="getActivityIcon(activity.type)"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900 dark:text-gray-100" x-text="activity.description"></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="formatDate(activity.created_at)"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function userDetails() {
    return {
        stats: {
            websites_count: {{ $user->websites_count ?? 0 }},
            api_requests_today: 0,
            storage_used: '0 MB'
        },
        websites: [],
        activities: [],

        async init() {
            await Promise.all([
                this.loadStats(),
                this.loadWebsites(),
                this.loadActivities()
            ]);
        },

        async loadStats() {
            try {
                const response = await fetch(`/api/dashboard/users/{{ $user->uuid }}/stats`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    this.stats = await response.json();
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        },

        async loadWebsites() {
            try {
                const response = await fetch(`/api/dashboard/users/{{ $user->uuid }}/websites`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.websites = data.data || [];
                }
            } catch (error) {
                console.error('Error loading websites:', error);
            }
        },

        async loadActivities() {
            try {
                const response = await fetch(`/api/dashboard/users/{{ $user->uuid }}/activities`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.activities = data.data || [];
                }
            } catch (error) {
                console.error('Error loading activities:', error);
            }
        },

        getActivityColor(type) {
            const colors = {
                'login': 'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400',
                'website_created': 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400',
                'api_request': 'bg-purple-100 text-purple-600 dark:bg-purple-900 dark:text-purple-400',
                'profile_updated': 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-400',
                'default': 'bg-gray-100 text-gray-600 dark:bg-gray-900 dark:text-gray-400'
            };
            return colors[type] || colors.default;
        },

        getActivityIcon(type) {
            const icons = {
                'login': 'fas fa-sign-in-alt',
                'website_created': 'fas fa-plus',
                'api_request': 'fas fa-exchange-alt',
                'profile_updated': 'fas fa-edit',
                'default': 'fas fa-circle'
            };
            return icons[type] || icons.default;
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('{{ app()->getLocale() }}', {
                year: 'numeric',
                month: 'short',
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

## 7. Authentication Views (Enhanced)

### resources/views/auth/login.blade.php

```php
@extends('layouts.app')

@section('title', __('messages.login'))

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8" 
     x-data="loginForm()" 
     :class="{ 'rtl': locale === 'ar' }">
    
    <!-- Language Switcher -->
    <div class="absolute top-4 {{ app()->getLocale() === 'ar' ? 'left-4' : 'right-4' }}">
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" 
                    class="flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                <i class="fas fa-globe mr-2"></i>
                <span>{{ app()->getLocale() === 'ar' ? 'العربية' : 'English' }}</span>
                <i class="fas fa-chevron-down ml-2"></i>
            </button>
            <div x-show="open" 
                 @click.away="open = false"
                 x-transition
                 class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-32 bg-white dark:bg-gray-800 rounded-md shadow-lg z-10">
                <a href="{{ route('locale.switch', 'en') }}" 
                   class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    English
                </a>
                <a href="{{ route('locale.switch', 'ar') }}" 
                   class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    العربية
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-md w-full space-y-8">
        <div>
            <!-- Logo -->
            <div class="mx-auto h-12 w-12 flex items-center justify-center bg-blue-600 rounded-lg">
                <i class="fas fa-cube text-white text-xl"></i>
            </div>
            <h2 class="mt-6 text-center text-3xl font-bold text-gray-900 dark:text-gray-100">
                {{ __('messages.sign_in_account') }}
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.dont_have_account') }}
                <a href="{{ route('register') }}" class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400">
                    {{ __('messages.register_now') }}
                </a>
            </p>
        </div>

        <form @submit.prevent="submitLogin()" class="mt-8 space-y-6">
            @csrf
            
            <div class="space-y-4">
                <!-- Email -->
                <div>
                    <label for="email" class="sr-only">{{ __('messages.email_address') }}</label>
                    <input id="email" 
                           type="email" 
                           x-model="form.email"
                           :class="{ 'border-red-500': errors.email }"
                           class="relative block w-full px-3 py-3 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           :placeholder="'{{ __('messages.email_address') }}'"
                           required>
                    <p x-show="errors.email" x-text="errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                </div>

                <!-- Password -->  
                <div>
                    <label for="password" class="sr-only">{{ __('messages.password') }}</label>
                    <div class="relative">
                        <input id="password" 
                               :type="showPassword ? 'text' : 'password'"
                               x-model="form.password"
                               :class="{ 'border-red-500': errors.password }"
                               class="relative block w-full px-3 py-3 pr-10 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               :placeholder="'{{ __('messages.password') }}'"
                               required>
                        <button type="button" 
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 {{ app()->getLocale() === 'ar' ? 'left-0 pl-3' : 'right-0 pr-3' }} flex items-center">
                            <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'" 
                               class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"></i>
                        </button>
                    </div>
                    <p x-show="errors.password" x-text="errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                </div>
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" 
                           type="checkbox" 
                           x-model="form.remember"
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded">
                    <label for="remember" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                        {{ __('messages.remember_me') }}
                    </label>
                </div>

                <div class="text-sm">
                    <a href="{{ route('password.request') }}" 
                       class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400">
                        {{ __('messages.forgot_password') }}
                    </a>
                </div>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" 
                        :disabled="submitting"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="{ 'opacity-50 cursor-not-allowed': submitting }">
                    <span x-show="!submitting">{{ __('messages.sign_in') }}</span>
                    <span x-show="submitting" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('messages.signing_in') }}...
                    </span>
                </button>
            </div>

            <!-- Social Login (Optional) -->
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400">
                            {{ __('messages.or_continue_with') }}
                        </span>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3">
                    <button type="button" 
                            @click="loginWithProvider('google')"
                            class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <i class="fab fa-google text-red-500"></i>
                        <span class="ml-2">Google</span>
                    </button>

                    <button type="button" 
                            @click="loginWithProvider('github')"
                            class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <i class="fab fa-github text-gray-900 dark:text-gray-100"></i>
                        <span class="ml-2">GitHub</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function loginForm() {
    return {
        locale: '{{ app()->getLocale() }}',
        submitting: false,
        showPassword: false,
        form: {
            email: '',
            password: '',
            remember: false
        },
        errors: {},

        async submitLogin() {
            this.submitting = true;
            this.errors = {};

            try {
                const response = await fetch('{{ route("login") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (response.ok) {
                    // Show success message
                    this.showToast('{{ __("messages.login_successful") }}', 'success');
                    
                    // Redirect after short delay
                    setTimeout(() => {
                        window.location.href = data.redirect || '{{ route("dashboard.index") }}';
                    }, 1000);
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        this.showToast(data.message || '{{ __("messages.login_failed") }}', 'error');
                    }
                }
            } catch (error) {
                console.error('Login error:', error);
                this.showToast('{{ __("messages.something_went_wrong") }}', 'error');
            } finally {
                this.submitting = false;
            }
        },

        async loginWithProvider(provider) {
            try {
                window.location.href = `/auth/${provider}/redirect`;
            } catch (error) {
                console.error('Social login error:', error);
                this.showToast('{{ __("messages.social_login_error") }}', 'error');
            }
        },

        showToast(message, type = 'info') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });

            Toast.fire({
                icon: type,
                title: message
            });
        }
    }
}
</script>
@endsection
```

### resources/views/auth/register.blade.php

```php
@extends('layouts.app')

@section('title', __('messages.register'))

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8" 
     x-data="registerForm()" 
     :class="{ 'rtl': locale === 'ar' }">
    
    <!-- Language Switcher -->
    <div class="absolute top-4 {{ app()->getLocale() === 'ar' ? 'left-4' : 'right-4' }}">
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" 
                    class="flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                <i class="fas fa-globe mr-2"></i>
                <span>{{ app()->getLocale() === 'ar' ? 'العربية' : 'English' }}</span>
                <i class="fas fa-chevron-down ml-2"></i>
            </button>
            <div x-show="open" 
                 @click.away="open = false"
                 x-transition
                 class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-32 bg-white dark:bg-gray-800 rounded-md shadow-lg z-10">
                <a href="{{ route('locale.switch', 'en') }}" 
                   class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    English
                </a>
                <a href="{{ route('locale.switch', 'ar') }}" 
                   class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    العربية
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-md w-full space-y-8">
        <div>
            <!-- Logo -->
            <div class="mx-auto h-12 w-12 flex items-center justify-center bg-blue-600 rounded-lg">
                <i class="fas fa-cube text-white text-xl"></i>
            </div>
            <h2 class="mt-6 text-center text-3xl font-bold text-gray-900 dark:text-gray-100">
                {{ __('messages.create_account') }}
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.already_have_account') }}
                <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400">
                    {{ __('messages.sign_in') }}
                </a>
            </p>
        </div>

        <form @submit.prevent="submitRegister()" class="mt-8 space-y-6">
            @csrf
            
            <div class="space-y-4">
                <!-- Full Name -->
                <div>
                    <label for="name" class="sr-only">{{ __('messages.full_name') }}</label>
                    <input id="name" 
                           type="text" 
                           x-model="form.name"
                           :class="{ 'border-red-500': errors.name }"
                           class="relative block w-full px-3 py-3 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           :placeholder="'{{ __('messages.full_name') }}'"
                           required>
                    <p x-show="errors.name" x-text="errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="sr-only">{{ __('messages.email_address') }}</label>
                    <input id="email" 
                           type="email" 
                           x-model="form.email"
                           :class="{ 'border-red-500': errors.email }"
                           class="relative block w-full px-3 py-3 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           :placeholder="'{{ __('messages.email_address') }}'"
                           required>
                    <p x-show="errors.email" x-text="errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                </div>

                <!-- Password -->  
                <div>
                    <label for="password" class="sr-only">{{ __('messages.password') }}</label>
                    <div class="relative">
                        <input id="password" 
                               :type="showPassword ? 'text' : 'password'"
                               x-model="form.password"
                               :class="{ 'border-red-500': errors.password }"
                               class="relative block w-full px-3 py-3 pr-10 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               :placeholder="'{{ __('messages.password') }}'"
                               required>
                        <button type="button" 
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 {{ app()->getLocale() === 'ar' ? 'left-0 pl-3' : 'right-0 pr-3' }} flex items-center">
                            <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'" 
                               class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"></i>
                        </button>
                    </div>
                    <p x-show="errors.password" x-text="errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                </div>

                <!-- Confirm Password -->  
                <div>
                    <label for="password_confirmation" class="sr-only">{{ __('messages.confirm_password') }}</label>
                    <div class="relative">
                        <input id="password_confirmation" 
                               :type="showPassword ? 'text' : 'password'"
                               x-model="form.password_confirmation"
                               :class="{ 'border-red-500': errors.password_confirmation }"
                               class="relative block w-full px-3 py-3 pr-10 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               :placeholder="'{{ __('messages.confirm_password') }}'"
                               required>
                    </div>
                    <p x-show="errors.password_confirmation" x-text="errors.password_confirmation" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                </div>

                <!-- Preferred Language -->
                <div>
                    <label for="locale" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('messages.preferred_language') }}
                    </label>
                    <select id="locale" 
                            x-model="form.locale"
                            class="relative block w-full px-3 py-3 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="en">{{ __('messages.english') }}</option>
                        <option value="ar">{{ __('messages.arabic') }}</option>
                    </select>
                </div>
            </div>

            <!-- Terms & Privacy -->
            <div class="flex items-start">
                <input id="terms" 
                       type="checkbox" 
                       x-model="form.terms"
                       :class="{ 'border-red-500': errors.terms }"
                       class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded"
                       required>
                <label for="terms" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                    {{ __('messages.agree_to') }}
                    <a href="{{ route('terms') }}" target="_blank" class="text-blue-600 hover:text-blue-500 dark:text-blue-400">
                        {{ __('messages.terms_of_service') }}
                    </a>
                    {{ __('messages.and') }}
                    <a href="{{ route('privacy') }}" target="_blank" class="text-blue-600 hover:text-blue-500 dark:text-blue-400">
                        {{ __('messages.privacy_policy') }}
                    </a>
                </label>
            </div>
            <p x-show="errors.terms" x-text="errors.terms" class="text-sm text-red-600 dark:text-red-400"></p>

            <!-- Submit Button -->
            <div>
                <button type="submit" 
                        :disabled="submitting || !form.terms"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="{ 'opacity-50 cursor-not-allowed': submitting || !form.terms }">
                    <span x-show="!submitting">{{ __('messages.create_account') }}</span>
                    <span x-show="submitting" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('messages.creating_account') }}...
                    </span>
                </button>
            </div>

            <!-- Social Registration (Optional) -->
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400">
                            {{ __('messages.or_register_with') }}
                        </span>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3">
                    <button type="button" 
                            @click="registerWithProvider('google')"
                            class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <i class="fab fa-google text-red-500"></i>
                        <span class="ml-2">Google</span>
                    </button>

                    <button type="button" 
                            @click="registerWithProvider('github')"
                            class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <i class="fab fa-github text-gray-900 dark:text-gray-100"></i>
                        <span class="ml-2">GitHub</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function registerForm() {
    return {
        locale: '{{ app()->getLocale() }}',
        submitting: false,
        showPassword: false,
        form: {
            name: '',
            email: '',
            password: '',
            password_confirmation: '',
            locale: '{{ app()->getLocale() }}',
            terms: false
        },
        errors: {},

        async submitRegister() {
            this.submitting = true;
            this.errors = {};

            try {
                const response = await fetch('{{ route("register") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (response.ok) {
                    // Show success message
                    Swal.fire({
                        title: '{{ __("messages.registration_successful") }}',
                        text: '{{ __("messages.check_email_verification") }}',
                        icon: 'success',
                        confirmButtonText: '{{ __("messages.ok") }}'
                    }).then(() => {
                        window.location.href = data.redirect || '{{ route("login") }}';
                    });
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        this.showToast(data.message || '{{ __("messages.registration_failed") }}', 'error');
                    }
                }
            } catch (error) {
                console.error('Registration error:', error);
                this.showToast('{{ __("messages.something_went_wrong") }}', 'error');
            } finally {
                this.submitting = false;
            }
        },

        async registerWithProvider(provider) {
            try {
                window.location.href = `/auth/${provider}/redirect`;
            } catch (error) {
                console.error('Social registration error:', error);
                this.showToast('{{ __("messages.social_registration_error") }}', 'error');
            }
        },

        showToast(message, type = 'info') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });

            Toast.fire({
                icon: type,
                title: message
            });
        }
    }
}
</script>
@endsection
```

### resources/views/auth/forgot-password.blade.php

```php
@extends('layouts.app')

@section('title', __('messages.forgot_password'))

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8" 
     x-data="forgotPasswordForm()" 
     :class="{ 'rtl': locale === 'ar' }">
    
    <!-- Language Switcher -->
    <div class="absolute top-4 {{ app()->getLocale() === 'ar' ? 'left-4' : 'right-4' }}">
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" 
                    class="flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                <i class="fas fa-globe mr-2"></i>
                <span>{{ app()->getLocale() === 'ar' ? 'العربية' : 'English' }}</span>
                <i class="fas fa-chevron-down ml-2"></i>
            </button>
            <div x-show="open" 
                 @click.away="open = false"
                 x-transition
                 class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-32 bg-white dark:bg-gray-800 rounded-md shadow-lg z-10">
                <a href="{{ route('locale.switch', 'en') }}" 
                   class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    English
                </a>
                <a href="{{ route('locale.switch', 'ar') }}" 
                   class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    العربية
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-md w-full space-y-8">
        <div>
            <!-- Logo -->
            <div class="mx-auto h-12 w-12 flex items-center justify-center bg-blue-600 rounded-lg">
                <i class="fas fa-key text-white text-xl"></i>
            </div>
            <h2 class="mt-6 text-center text-3xl font-bold text-gray-900 dark:text-gray-100">
                {{ __('messages.forgot_password') }}
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.forgot_password_description') }}
            </p>
        </div>

        <!-- Success Message -->
        <div x-show="emailSent" 
             x-transition
             class="rounded-md bg-green-50 dark:bg-green-900 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
                        {{ __('messages.reset_link_sent') }}
                    </h3>
                    <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                        <p>{{ __('messages.check_email_reset_link') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <form x-show="!emailSent" @submit.prevent="submitForgotPassword()" class="mt-8 space-y-6">
            @csrf
            
            <div>
                <!-- Email -->
                <label for="email" class="sr-only">{{ __('messages.email_address') }}</label>
                <input id="email" 
                       type="email" 
                       x-model="form.email"
                       :class="{ 'border-red-500': errors.email }"
                       class="relative block w-full px-3 py-3 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       :placeholder="'{{ __('messages.email_address') }}'"
                       required>
                <p x-show="errors.email" x-text="errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" 
                        :disabled="submitting"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="{ 'opacity-50 cursor-not-allowed': submitting }">
                    <span x-show="!submitting">{{ __('messages.send_reset_link') }}</span>
                    <span x-show="submitting" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('messages.sending') }}...
                    </span>
                </button>
            </div>

            <!-- Back to Login -->
            <div class="text-center">
                <a href="{{ route('login') }}" 
                   class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 text-sm">
                    <i class="fas fa-arrow-left mr-2"></i>
                    {{ __('messages.back_to_login') }}
                </a>
            </div>
        </form>

        <!-- After Email Sent Actions -->
        <div x-show="emailSent" x-transition class="space-y-4">
            <div class="text-center">
                <button @click="resendEmail()" 
                        :disabled="resending || cooldownActive"
                        class="text-sm text-blue-600 hover:text-blue-500 dark:text-blue-400 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!resending && !cooldownActive">{{ __('messages.resend_email') }}</span>
                    <span x-show="resending">{{ __('messages.sending') }}...</span>
                    <span x-show="cooldownActive" x-text="`{{ __('messages.resend_in') }} ${cooldownTime}s`"></span>
                </button>
            </div>
            
            <div class="text-center">
                <a href="{{ route('login') }}" 
                   class="font-medium text-gray-600 hover:text-gray-500 dark:text-gray-400 text-sm">
                    {{ __('messages.back_to_login') }}
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function forgotPasswordForm() {
    return {
        locale: '{{ app()->getLocale() }}',
        submitting: false,
        resending: false,
        emailSent: false,
        cooldownActive: false,
        cooldownTime: 60,
        form: {
            email: ''
        },
        errors: {},

        async submitForgotPassword() {
            this.submitting = true;
            this.errors = {};

            try {
                const response = await fetch('{{ route("password.email") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (response.ok) {
                    this.emailSent = true;
                    this.startCooldown();
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        this.showToast(data.message || '{{ __("messages.email_send_failed") }}', 'error');
                    }
                }
            } catch (error) {
                console.error('Forgot password error:', error);
                this.showToast('{{ __("messages.something_went_wrong") }}', 'error');
            } finally {
                this.submitting = false;
            }
        },

        async resendEmail() {
            this.resending = true;

            try {
                const response = await fetch('{{ route("password.email") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.form)
                });

                if (response.ok) {
                    this.showToast('{{ __("messages.reset_link_resent") }}', 'success');
                    this.startCooldown();
                } else {
                    this.showToast('{{ __("messages.email_send_failed") }}', 'error');
                }
            } catch (error) {
                console.error('Resend email error:', error);
                this.showToast('{{ __("messages.something_went_wrong") }}', 'error');
            } finally {
                this.resending = false;
            }
        },

        startCooldown() {
            this.cooldownActive = true;
            this.cooldownTime = 60;
            
            const countdown = setInterval(() => {
                this.cooldownTime--;
                
                if (this.cooldownTime <= 0) {
                    this.cooldownActive = false;
                    clearInterval(countdown);
                }
            }, 1000);
        },

        showToast(message, type = 'info') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });

            Toast.fire({
                icon: type,
                title: message
            });
        }
    }
}
</script>
@endsection
```

### resources/views/auth/reset-password.blade.php

```php
@extends('layouts.app')

@section('title', __('messages.reset_password'))

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8" 
     x-data="resetPasswordForm()" 
     :class="{ 'rtl': locale === 'ar' }">
    
    <!-- Language Switcher -->
    <div class="absolute top-4 {{ app()->getLocale() === 'ar' ? 'left-4' : 'right-4' }}">
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" 
                    class="flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                <i class="fas fa-globe mr-2"></i>
                <span>{{ app()->getLocale() === 'ar' ? 'العربية' : 'English' }}</span>
                <i class="fas fa-chevron-down ml-2"></i>
            </button>
            <div x-show="open" 
                 @click.away="open = false"
                 x-transition
                 class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-32 bg-white dark:bg-gray-800 rounded-md shadow-lg z-10">
                <a href="{{ route('locale.switch', 'en') }}" 
                   class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    English
                </a>
                <a href="{{ route('locale.switch', 'ar') }}" 
                   class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    العربية
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-md w-full space-y-8">
        <div>
            <!-- Logo -->
            <div class="mx-auto h-12 w-12 flex items-center justify-center bg-blue-600 rounded-lg">
                <i class="fas fa-lock text-white text-xl"></i>
            </div>
            <h2 class="mt-6 text-center text-3xl font-bold text-gray-900 dark:text-gray-100">
                {{ __('messages.reset_password') }}
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.enter_new_password') }}
            </p>
        </div>

        <form @submit.prevent="submitResetPassword()" class="mt-8 space-y-6">
            @csrf
            
            <!-- Hidden Fields -->
            <input type="hidden" name="token" :value="form.token">
            
            <div class="space-y-4">
                <!-- Email -->
                <div>
                    <label for="email" class="sr-only">{{ __('messages.email_address') }}</label>
                    <input id="email" 
                           type="email" 
                           x-model="form.email"
                           :class="{ 'border-red-500': errors.email }"
                           class="relative block w-full px-3 py-3 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           :placeholder="'{{ __('messages.email_address') }}'"
                           readonly
                           required>
                    <p x-show="errors.email" x-text="errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                </div>

                <!-- New Password -->  
                <div>
                    <label for="password" class="sr-only">{{ __('messages.new_password') }}</label>
                    <div class="relative">
                        <input id="password" 
                               :type="showPassword ? 'text' : 'password'"
                               x-model="form.password"
                               :class="{ 'border-red-500': errors.password }"
                               class="relative block w-full px-3 py-3 pr-10 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               :placeholder="'{{ __('messages.new_password') }}'"
                               required>
                        <button type="button" 
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 {{ app()->getLocale() === 'ar' ? 'left-0 pl-3' : 'right-0 pr-3' }} flex items-center">
                            <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'" 
                               class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"></i>
                        </button>
                    </div>
                    <p x-show="errors.password" x-text="errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                </div>

                <!-- Confirm Password -->  
                <div>
                    <label for="password_confirmation" class="sr-only">{{ __('messages.confirm_password') }}</label>
                    <div class="relative">
                        <input id="password_confirmation" 
                               :type="showPassword ? 'text' : 'password'"
                               x-model="form.password_confirmation"
                               :class="{ 'border-red-500': errors.password_confirmation }"
                               class="relative block w-full px-3 py-3 pr-10 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               :placeholder="'{{ __('messages.confirm_password') }}'"
                               required>
                    </div>
                    <p x-show="errors.password_confirmation" x-text="errors.password_confirmation" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                </div>
            </div>

            <!-- Password Requirements -->
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">{{ __('messages.password_requirements') }}</h4>
                <ul class="space-y-1 text-xs text-gray-600 dark:text-gray-400">
                    <li class="flex items-center" :class="{ 'text-green-600 dark:text-green-400': passwordChecks.length }">
                        <i :class="passwordChecks.length ? 'fas fa-check' : 'fas fa-times'" class="mr-2"></i>
                        {{ __('messages.password_min_8_chars') }}
                    </li>
                    <li class="flex items-center" :class="{ 'text-green-600 dark:text-green-400': passwordChecks.uppercase }">
                        <i :class="passwordChecks.uppercase ? 'fas fa-check' : 'fas fa-times'" class="mr-2"></i>
                        {{ __('messages.password_uppercase') }}
                    </li>
                    <li class="flex items-center" :class="{ 'text-green-600 dark:text-green-400': passwordChecks.lowercase }">
                        <i :class="passwordChecks.lowercase ? 'fas fa-check' : 'fas fa-times'" class="mr-2"></i>
                        {{ __('messages.password_lowercase') }}
                    </li>
                    <li class="flex items-center" :class="{ 'text-green-600 dark:text-green-400': passwordChecks.number }">
                        <i :class="passwordChecks.number ? 'fas fa-check' : 'fas fa-times'" class="mr-2"></i>
                        {{ __('messages.password_number') }}
                    </li>
                    <li class="flex items-center" :class="{ 'text-green-600 dark:text-green-400': passwordChecks.match }">
                        <i :class="passwordChecks.match ? 'fas fa-check' : 'fas fa-times'" class="mr-2"></i>
                        {{ __('messages.passwords_match') }}
                    </li>
                </ul>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" 
                        :disabled="submitting || !isPasswordValid"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="{ 'opacity-50 cursor-not-allowed': submitting || !isPasswordValid }">
                    <span x-show="!submitting">{{ __('messages.reset_password') }}</span>
                    <span x-show="submitting" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('messages.resetting_password') }}...
                    </span>
                </button>
            </div>

            <!-- Back to Login -->
            <div class="text-center">
                <a href="{{ route('login') }}" 
                   class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 text-sm">
                    <i class="fas fa-arrow-left mr-2"></i>
                    {{ __('messages.back_to_login') }}
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function resetPasswordForm() {
    return {
        locale: '{{ app()->getLocale() }}',
        submitting: false,
        showPassword: false,
        form: {
            token: '{{ request()->route("token") }}',
            email: '{{ request()->email }}',
            password: '',
            password_confirmation: ''
        },
        errors: {},

        get passwordChecks() {
            return {
                length: this.form.password.length >= 8,
                uppercase: /[A-Z]/.test(this.form.password),
                lowercase: /[a-z]/.test(this.form.password),
                number: /\d/.test(this.form.password),
                match: this.form.password === this.form.password_confirmation && this.form.password.length > 0
            };
        },

        get isPasswordValid() {
            return this.passwordChecks.length &&
                   this.passwordChecks.uppercase &&
                   this.passwordChecks.lowercase &&
                   this.passwordChecks.number &&
                   this.passwordChecks.match;
        },

        async submitResetPassword() {
            this.submitting = true;
            this.errors = {};

            try {
                const response = await fetch('{{ route("password.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (response.ok) {
                    // Show success message
                    Swal.fire({
                        title: '{{ __("messages.password_reset_successful") }}',
                        text: '{{ __("messages.password_updated_login") }}',
                        icon: 'success',
                        confirmButtonText: '{{ __("messages.login_now") }}'
                    }).then(() => {
                        window.location.href = '{{ route("login") }}';
                    });
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        this.showToast(data.message || '{{ __("messages.password_reset_failed") }}', 'error');
                    }
                }
            } catch (error) {
                console.error('Password reset error:', error);
                this.showToast('{{ __("messages.something_went_wrong") }}', 'error');
            } finally {
                this.submitting = false;
            }
        },

        showToast(message, type = 'info') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });

            Toast.fire({
                icon: type,
                title: message
            });
        }
    }
}
</script>
@endsection
```

