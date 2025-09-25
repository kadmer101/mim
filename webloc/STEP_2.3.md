Based on my comprehensive analysis of all the project documentation, I now understand the complete WebBloc project requirements. This is a Laravel-based multi-tenant system that provides dynamic web components as an API for static websites, with multi-language support (English/Arabic), UUID-based routing, SQLite per-website databases, and Alpine.js frontend integration.

Now I'll provide all the requested blade views with full code implementation:

## 4. Component Management Views

### resources/views/dashboard/components/create.blade.php

```blade
@extends('layouts.dashboard')

@section('title', __('messages.create_component'))

@section('content')
<div x-data="createComponentForm()" class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ __('messages.create_component') }}
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                {{ __('messages.create_component_description') }}
            </p>
        </div>
        <a href="{{ route('dashboard.components.index') }}" 
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }} space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>{{ __('messages.back_to_components') }}</span>
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <form @submit.prevent="submitForm" class="space-y-6">
            <!-- Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.component_type') }} <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       x-model="form.type"
                       :class="errors.type ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                       class="w-full px-3 py-2 rounded-lg border bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="{{ __('messages.enter_component_type') }}"
                       required>
                <p x-show="errors.type" x-text="errors.type?.[0]" class="text-red-500 text-sm mt-1"></p>
            </div>

            <!-- Multi-language Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.component_name') }} <span class="text-red-500">*</span>
                </label>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('messages.english') }}</label>
                        <input type="text" 
                               x-model="form.name.en"
                               :class="errors['name.en'] ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                               class="w-full px-3 py-2 rounded-lg border bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="{{ __('messages.enter_name_english') }}"
                               required>
                        <p x-show="errors['name.en']" x-text="errors['name.en']?.[0]" class="text-red-500 text-sm mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('messages.arabic') }}</label>
                        <input type="text" 
                               x-model="form.name.ar"
                               :class="errors['name.ar'] ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                               class="w-full px-3 py-2 rounded-lg border bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-right"
                               placeholder="{{ __('messages.enter_name_arabic') }}"
                               dir="rtl"
                               required>
                        <p x-show="errors['name.ar']" x-text="errors['name.ar']?.[0]" class="text-red-500 text-sm mt-1"></p>
                    </div>
                </div>
            </div>

            <!-- Multi-language Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.description') }}
                </label>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('messages.english') }}</label>
                        <textarea x-model="form.description.en"
                                  :class="errors['description.en'] ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                  class="w-full px-3 py-2 rounded-lg border bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  rows="3"
                                  placeholder="{{ __('messages.enter_description_english') }}"></textarea>
                        <p x-show="errors['description.en']" x-text="errors['description.en']?.[0]" class="text-red-500 text-sm mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('messages.arabic') }}</label>
                        <textarea x-model="form.description.ar"
                                  :class="errors['description.ar'] ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                  class="w-full px-3 py-2 rounded-lg border bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-right"
                                  rows="3"
                                  placeholder="{{ __('messages.enter_description_arabic') }}"
                                  dir="rtl"></textarea>
                        <p x-show="errors['description.ar']" x-text="errors['description.ar']?.[0]" class="text-red-500 text-sm mt-1"></p>
                    </div>
                </div>
            </div>

            <!-- Attributes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.attributes') }}
                </label>
                <div x-data="attributesManager()" class="space-y-3">
                    <template x-for="(attr, index) in attributes" :key="index">
                        <div class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                            <input type="text" 
                                   x-model="attr.key"
                                   placeholder="{{ __('messages.attribute_key') }}"
                                   class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <input type="text" 
                                   x-model="attr.value"
                                   placeholder="{{ __('messages.attribute_value') }}"
                                   class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <button type="button" 
                                    @click="removeAttribute(index)"
                                    class="text-red-500 hover:text-red-700 p-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                    <button type="button" 
                            @click="addAttribute()"
                            class="text-blue-600 hover:text-blue-800 text-sm flex items-center {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }} space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>{{ __('messages.add_attribute') }}</span>
                    </button>
                </div>
            </div>

            <!-- CRUD Permissions -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.crud_permissions') }}
                </label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <label class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                        <input type="checkbox" x-model="form.crud_permissions.create" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.create') }}</span>
                    </label>
                    <label class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                        <input type="checkbox" x-model="form.crud_permissions.read" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.read') }}</span>
                    </label>
                    <label class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                        <input type="checkbox" x-model="form.crud_permissions.update" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.update') }}</span>
                    </label>
                    <label class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                        <input type="checkbox" x-model="form.crud_permissions.delete" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.delete') }}</span>
                    </label>
                </div>
            </div>

            <!-- Blade Template -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.blade_template') }}
                </label>
                <textarea x-model="form.blade_template"
                          :class="errors.blade_template ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                          class="w-full px-3 py-2 rounded-lg border bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                          rows="8"
                          placeholder="{{ __('messages.enter_blade_template') }}"></textarea>
                <p x-show="errors.blade_template" x-text="errors.blade_template?.[0]" class="text-red-500 text-sm mt-1"></p>
            </div>

            <!-- Alpine.js Code -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.alpine_js_code') }}
                </label>
                <textarea x-model="form.alpine_js_code"
                          :class="errors.alpine_js_code ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                          class="w-full px-3 py-2 rounded-lg border bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                          rows="6"
                          placeholder="{{ __('messages.enter_alpine_js_code') }}"></textarea>
                <p x-show="errors.alpine_js_code" x-text="errors.alpine_js_code?.[0]" class="text-red-500 text-sm mt-1"></p>
            </div>

            <!-- CSS Styles -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.css_styles') }}
                </label>
                <textarea x-model="form.css_styles"
                          :class="errors.css_styles ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                          class="w-full px-3 py-2 rounded-lg border bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                          rows="6"
                          placeholder="{{ __('messages.enter_css_styles') }}"></textarea>
                <p x-show="errors.css_styles" x-text="errors.css_styles?.[0]" class="text-red-500 text-sm mt-1"></p>
            </div>

            <!-- Options -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <label class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                    <input type="checkbox" x-model="form.requires_auth" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.requires_authentication') }}</span>
                </label>
                <label class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                    <input type="checkbox" x-model="form.is_active" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.active') }}</span>
                </label>
            </div>

            <!-- Version -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.version') }}
                </label>
                <input type="text" 
                       x-model="form.version"
                       :class="errors.version ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                       class="w-full px-3 py-2 rounded-lg border bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="1.0.0">
                <p x-show="errors.version" x-text="errors.version?.[0]" class="text-red-500 text-sm mt-1"></p>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-4 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                <a href="{{ route('dashboard.components.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                    {{ __('messages.cancel') }}
                </a>
                <button type="submit" 
                        :disabled="loading"
                        :class="loading ? 'opacity-50 cursor-not-allowed' : ''"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }} space-x-2">
                    <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="loading ? '{{ __('messages.creating') }}...' : '{{ __('messages.create_component') }}'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function createComponentForm() {
    return {
        loading: false,
        errors: {},
        form: {
            type: '',
            name: { en: '', ar: '' },
            description: { en: '', ar: '' },
            attributes: {},
            crud_permissions: {
                create: true,
                read: true,
                update: false,
                delete: false
            },
            blade_template: '',
            alpine_js_code: '',
            css_styles: '',
            requires_auth: false,
            is_active: true,
            version: '1.0.0'
        },

        async submitForm() {
            this.loading = true;
            this.errors = {};

            try {
                const response = await fetch('/api/components', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (response.ok) {
                    Swal.fire({
                        title: '{{ __('messages.success') }}',
                        text: data.message || '{{ __('messages.component_created_successfully') }}',
                        icon: 'success',
                        confirmButtonText: '{{ __('messages.ok') }}'
                    }).then(() => {
                        window.location.href = '{{ route('dashboard.components.index') }}';
                    });
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    }
                    throw new Error(data.message || '{{ __('messages.something_went_wrong') }}');
                }
            } catch (error) {
                Swal.fire({
                    title: '{{ __('messages.error') }}',
                    text: error.message,
                    icon: 'error',
                    confirmButtonText: '{{ __('messages.ok') }}'
                });
            } finally {
                this.loading = false;
            }
        }
    }
}

function attributesManager() {
    return {
        attributes: [{ key: '', value: '' }],
        
        addAttribute() {
            this.attributes.push({ key: '', value: '' });
        },
        
        removeAttribute(index) {
            if (this.attributes.length > 1) {
                this.attributes.splice(index, 1);
            }
        }
    }
}
</script>
@endsection
```

### resources/views/dashboard/components/edit.blade.php

```blade
@extends('layouts.dashboard')

@section('title', __('messages.edit_component'))

@section('content')
<div x-data="editComponentForm('{{ $component->uuid }}')" class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ __('messages.edit_component') }}
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                {{ __('messages.edit_component_description') }}
            </p>
        </div>
        <div class="flex items-center space-x-4 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
            <a href="{{ route('dashboard.components.show', $component->uuid) }}" 
               class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }} space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <span>{{ __('messages.view') }}</span>
            </a>
            <a href="{{ route('dashboard.components.index') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }} space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>{{ __('messages.back_to_components') }}</span>
            </a>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="!loaded" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Form -->
    <div x-show="loaded" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <form @submit.prevent="submitForm" class="space-y-6">
            <!-- Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.component_type') }} <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       x-model="form.type"
                       :class="errors.type ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                       class="w-full px-3 py-2 rounded-lg border bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed"
                       readonly>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.component_type_readonly') }}</p>
                <p x-show="errors.type" x-text="errors.type?.[0]" class="text-red-500 text-sm mt-1"></p>
            </div>

            <!-- Multi-language Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.component_name') }} <span class="text-red-500">*</span>
                </label>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('messages.english') }}</label>
                        <input type="text" 
                               x-model="form.name.en"
                               :class="errors['name.en'] ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                               class="w-full px-3 py-2 rounded-lg border bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="{{ __('messages.enter_name_english') }}"
                               required>
                        <p x-show="errors['name.en']" x-text="errors['name.en']?.[0]" class="text-red-500 text-sm mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('messages.arabic') }}</label>
                        <input type="text" 
                               x-model="form.name.ar"
                               :class="errors['name.ar'] ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                               class="w-full px-3 py-2 rounded-lg border bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-right"
                               placeholder="{{ __('messages.enter_name_arabic') }}"
                               dir="rtl"
                               required>
                        <p x-show="errors['name.ar']" x-text="errors['name.ar']?.[0]" class="text-red-500 text-sm mt-1"></p>
                    </div>
                </div>
            </div>

            <!-- Multi-language Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.description') }}
                </label>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('messages.english') }}</label>
                        <textarea x-model="form.description.en"
                                  :class="errors['description.en'] ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                  class="w-full px-3 py-2 rounded-lg border bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  rows="3"
                                  placeholder="{{ __('messages.enter_description_english') }}"></textarea>
                        <p x-show="errors['description.en']" x-text="errors['description.en']?.[0]" class="text-red-500 text-sm mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('messages.arabic') }}</label>
                        <textarea x-model="form.description.ar"
                                  :class="errors['description.ar'] ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                  class="w-full px-3 py-2 rounded-lg border bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-right"
                                  rows="3"
                                  placeholder="{{ __('messages.enter_description_arabic') }}"
                                  dir="rtl"></textarea>
                        <p x-show="errors['description.ar']" x-text="errors['description.ar']?.[0]" class="text-red-500 text-sm mt-1"></p>
                    </div>
                </div>
            </div>

            <!-- Attributes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.attributes') }}
                </label>
                <div x-data="attributesManager()" x-init="initAttributes(form.attributes)" class="space-y-3">
                    <template x-for="(attr, index) in attributes" :key="index">
                        <div class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                            <input type="text" 
                                   x-model="attr.key"
                                   placeholder="{{ __('messages.attribute_key') }}"
                                   class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <input type="text" 
                                   x-model="attr.value"
                                   placeholder="{{ __('messages.attribute_value') }}"
                                   class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <button type="button" 
                                    @click="removeAttribute(index)"
                                    class="text-red-500 hover:text-red-700 p-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                    <button type="button" 
                            @click="addAttribute()"
                            class="text-blue-600 hover:text-blue-800 text-sm flex items-center {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }} space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>{{ __('messages.add_attribute') }}</span>
                    </button>
                </div>
            </div>

            <!-- CRUD Permissions -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.crud_permissions') }}
                </label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <label class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                        <input type="checkbox" x-model="form.crud_permissions.create" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.create') }}</span>
                    </label>
                    <label class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                        <input type="checkbox" x-model="form.crud_permissions.read" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.read') }}</span>
                    </label>
                    <label class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                        <input type="checkbox" x-model="form.crud_permissions.update" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.update') }}</span>
                    </label>
                    <label class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                        <input type="checkbox" x-model="form.crud_permissions.delete" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.delete') }}</span>
                    </label>
                </div>
            </div>

            <!-- Blade Template -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.blade_template') }}
                </label>
                <textarea x-model="form.blade_template"
                          :class="errors.blade_template ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                          class="w-full px-3 py-2 rounded-lg border bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                          rows="8"
                          placeholder="{{ __('messages.enter_blade_template') }}"></textarea>
                <p x-show="errors.blade_template" x-text="errors.blade_template?.[0]" class="text-red-500 text-sm mt-1"></p>
            </div>

            <!-- Alpine.js Code -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.alpine_js_code') }}
                </label>
                <textarea x-model="form.alpine_js_code"
                          :class="errors.alpine_js_code ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                          class="w-full px-3 py-2 rounded-lg border bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                          rows="6"
                          placeholder="{{ __('messages.enter_alpine_js_code') }}"></textarea>
                <p x-show="errors.alpine_js_code" x-text="errors.alpine_js_code?.[0]" class="text-red-500 text-sm mt-1"></p>
            </div>

            <!-- CSS Styles -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.css_styles') }}
                </label>
                <textarea x-model="form.css_styles"
                          :class="errors.css_styles ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                          class="w-full px-3 py-2 rounded-lg border bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                          rows="6"
                          placeholder="{{ __('messages.enter_css_styles') }}"></textarea>
                <p x-show="errors.css_styles" x-text="errors.css_styles?.[0]" class="text-red-500 text-sm mt-1"></p>
            </div>

            <!-- Options -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <label class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                    <input type="checkbox" x-model="form.requires_auth" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.requires_authentication') }}</span>
                </label>
                <label class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                    <input type="checkbox" x-model="form.is_active" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.active') }}</span>
                </label>
            </div>

            <!-- Version -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.version') }}
                </label>
                <input type="text" 
                       x-model="form.version"
                       :class="errors.version ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                       class="w-full px-3 py-2 rounded-lg border bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="1.0.0">
                <p x-show="errors.version" x-text="errors.version?.[0]" class="text-red-500 text-sm mt-1"></p>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-4 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                <a href="{{ route('dashboard.components.show', $component->uuid) }}" 
                   class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                    {{ __('messages.cancel') }}
                </a>
                <button type="submit" 
                        :disabled="loading"
                        :class="loading ? 'opacity-50 cursor-not-allowed' : ''"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }} space-x-2">
                    <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="loading ? '{{ __('messages.updating') }}...' : '{{ __('messages.update_component') }}'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editComponentForm(uuid) {
    return {
        loading: false,
        loaded: false,
        errors: {},
        form: {
            type: '',
            name: { en: '', ar: '' },
            description: { en: '', ar: '' },
            attributes: {},
            crud_permissions: {
                create: false,
                read: false,
                update: false,
                delete: false
            },
            blade_template: '',
            alpine_js_code: '',
            css_styles: '',
            requires_auth: false,
            is_active: true,
            version: '1.0.0'
        },

        async init() {
            await this.loadComponent();
        },

        async loadComponent() {
            try {
                const response = await fetch(`/api/components/${uuid}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.form = { ...this.form, ...data.component };
                    this.loaded = true;
                } else {
                    throw new Error('{{ __('messages.failed_to_load_component') }}');
                }
            } catch (error) {
                Swal.fire({
                    title: '{{ __('messages.error') }}',
                    text: error.message,
                    icon: 'error',
                    confirmButtonText: '{{ __('messages.ok') }}'
                }).then(() => {
                    window.location.href = '{{ route('dashboard.components.index') }}';
                });
            }
        },

        async submitForm() {
            this.loading = true;
            this.errors = {};

            try {
                const response = await fetch(`/api/components/${uuid}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (response.ok) {
                    Swal.fire({
                        title: '{{ __('messages.success') }}',
                        text: data.message || '{{ __('messages.component_updated_successfully') }}',
                        icon: 'success',
                        confirmButtonText: '{{ __('messages.ok') }}'
                    }).then(() => {
                        window.location.href = `{{ route('dashboard.components.show', '') }}/${uuid}`;
                    });
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    }
                    throw new Error(data.message || '{{ __('messages.something_went_wrong') }}');
                }
            } catch (error) {
                Swal.fire({
                    title: '{{ __('messages.error') }}',
                    text: error.message,
                    icon: 'error',
                    confirmButtonText: '{{ __('messages.ok') }}'
                });
            } finally {
                this.loading = false;
            }
        }
    }
}

function attributesManager() {
    return {
        attributes: [{ key: '', value: '' }],
        
        initAttributes(attrs) {
            if (attrs && Object.keys(attrs).length > 0) {
                this.attributes = Object.entries(attrs).map(([key, value]) => ({ key, value }));
            }
        },
        
        addAttribute() {
            this.attributes.push({ key: '', value: '' });
        },
        
        removeAttribute(index) {
            if (this.attributes.length > 1) {
                this.attributes.splice(index, 1);
            }
        }
    }
}
</script>
@endsection
```

### resources/views/dashboard/components/show.blade.php

```blade
@extends('layouts.dashboard')

@section('title', __('messages.component_details'))

@section('content')
<div x-data="componentDetails('{{ $component->uuid }}')" x-init="init()" class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ __('messages.component_details') }}
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                {{ __('messages.component_details_description') }}
            </p>
        </div>
        <div class="flex items-center space-x-4 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
            <a href="{{ route('dashboard.components.edit', $component->uuid) }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }} space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span>{{ __('messages.edit') }}</span>
            </a>
            <a href="{{ route('dashboard.components.index') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }} space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>{{ __('messages.back_to_components') }}</span>
            </a>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="!loaded" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Component Details -->
    <div x-show="loaded" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Info -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('messages.basic_information') }}
                    </h2>
                    <div class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                        <span x-show="component.is_active" class="px-2 py-1 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 text-xs rounded-full">
                            {{ __('messages.active') }}
                        </span>
                        <span x-show="!component.is_active" class="px-2 py-1 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 text-xs rounded-full">
                            {{ __('messages.inactive') }}
                        </span>
                        <button @click="toggleStatus()" 
                                :disabled="loading"
                                class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                            <span x-text="component.is_active ? '{{ __('messages.deactivate') }}' : '{{ __('messages.activate') }}'"></span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('messages.type') }}
                        </label>
                        <p class="text-gray-900 dark:text-white font-mono bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded" x-text="component.type"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('messages.version') }}
                        </label>
                        <p class="text-gray-900 dark:text-white font-mono bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded" x-text="component.version"></p>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('messages.name') }}
                    </label>
                    <div class="space-y-2">
                        <div class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                            <span class="text-xs text-gray-500 dark:text-gray-400 w-12">EN:</span>
                            <p class="text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded flex-1" x-text="component.name?.en || '-'"></p>
                        </div>
                        <div class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                            <span class="text-xs text-gray-500 dark:text-gray-400 w-12">AR:</span>
                            <p class="text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded flex-1 text-right" x-text="component.name?.ar || '-'" dir="rtl"></p>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('messages.description') }}
                    </label>
                    <div class="space-y-2">
                        <div class="flex items-start space-x-2 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                            <span class="text-xs text-gray-500 dark:text-gray-400 w-12 mt-2">EN:</span>
                            <p class="text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded flex-1" x-text="component.description?.en || '-'"></p>
                        </div>
                        <div class="flex items-start space-x-2 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                            <span class="text-xs text-gray-500 dark:text-gray-400 w-12 mt-2">AR:</span>
                            <p class="text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded flex-1 text-right" x-text="component.description?.ar || '-'" dir="rtl"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CRUD Permissions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('messages.crud_permissions') }}
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                        <div :class="component.crud_permissions?.create ? 'bg-green-500' : 'bg-red-500'" class="w-3 h-3 rounded-full"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.create') }}</span>
                    </div>
                    <div class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                        <div :class="component.crud_permissions?.read ? 'bg-green-500' : 'bg-red-500'" class="w-3 h-3 rounded-full"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.read') }}</span>
                    </div>
                    <div class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                        <div :class="component.crud_permissions?.update ? 'bg-green-500' : 'bg-red-500'" class="w-3 h-3 rounded-full"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.update') }}</span>
                    </div>
                    <div class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                        <div :class="component.crud_permissions?.delete ? 'bg-green-500' : 'bg-red-500'" class="w-3 h-3 rounded-full"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.delete') }}</span>
                    </div>
                </div>
            </div>

            <!-- Attributes -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('messages.attributes') }}
                </h2>
                <div x-show="Object.keys(component.attributes || {}).length === 0" class="text-gray-500 dark:text-gray-400 text-center py-4">
                    {{ __('messages.no_attributes_defined') }}
                </div>
                <div x-show="Object.keys(component.attributes || {}).length > 0" class="space-y-2">
                    <template x-for="[key, value] in Object.entries(component.attributes || {})" :key="key">
                        <div class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }} p-2 bg-gray-50 dark:bg-gray-700 rounded">
                            <span class="font-mono text-sm text-blue-600 dark:text-blue-400" x-text="key"></span>
                            <span class="text-gray-500">:</span>
                            <span class="font-mono text-sm text-gray-900 dark:text-white" x-text="value"></span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Code Templates -->
            <div class="space-y-6">
                <!-- Blade Template -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('messages.blade_template') }}
                    </h2>
                    <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                        <pre><code class="text-green-400 text-sm" x-text="component.blade_template || '{{ __('messages.no_template_defined') }}'"></code></pre>
                    </div>
                </div>

                <!-- Alpine.js Code -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('messages.alpine_js_code') }}
                    </h2>
                    <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                        <pre><code class="text-yellow-400 text-sm" x-text="component.alpine_js_code || '{{ __('messages.no_code_defined') }}'"></code></pre>
                    </div>
                </div>

                <!-- CSS Styles -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('messages.css_styles') }}
                    </h2>
                    <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                        <pre><code class="text-blue-400 text-sm" x-text="component.css_styles || '{{ __('messages.no_styles_defined') }}'"></code></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('messages.quick_actions') }}
                </h2>
                <div class="space-y-3">
                    <a href="{{ route('dashboard.components.edit', $component->uuid) }}" 
                       class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center justify-center {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }} space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>{{ __('messages.edit_component') }}</span>
                    </a>
                    <button @click="toggleStatus()" 
                            :disabled="loading"
                            :class="component.is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'"
                            class="w-full text-white px-4 py-2 rounded-lg flex items-center justify-center {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }} space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"/>
                        </svg>
                        <span x-text="component.is_active ? '{{ __('messages.deactivate') }}' : '{{ __('messages.activate') }}'"></span>
                    </button>
                    <button @click="deleteComponent()" 
                            class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center justify-center {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }} space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>{{ __('messages.delete_component') }}</span>
                    </button>
                </div>
            </div>

            <!-- Component Stats -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('messages.component_info') }}
                </h2>
                <div class="space-y-3">
                    <div class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                        <div :class="component.requires_auth ? 'bg-yellow-500' : 'bg-gray-500'" class="w-3 h-3 rounded-full"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            <span x-text="component.requires_auth ? '{{ __('messages.requires_authentication') }}' : '{{ __('messages.no_authentication_required') }}'"></span>
                        </span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('messages.created_at') }}:</span>
                        <span class="text-gray-900 dark:text-white" x-text="formatDate(component.created_at)"></span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('messages.updated_at') }}:</span>
                        <span class="text-gray-900 dark:text-white" x-text="formatDate(component.updated_at)"></span>
                    </div>
                </div>
            </div>

            <!-- Usage Statistics -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('messages.usage_statistics') }}
                </h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.total_websites') }}:</span>
                        <span class="text-lg font-semibold text-gray-900 dark:text-white" x-text="stats.total_websites || 0"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.total_usage') }}:</span>
                        <span class="text-lg font-semibold text-gray-900 dark:text-white" x-text="stats.total_usage || 0"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function componentDetails(uuid) {
    return {
        loading: false,
        loaded: false,
        component: {},
        stats: {},

        async init() {
            await this.loadComponent();
            await this.loadStats();
        },

        async loadComponent() {
            try {
                const response = await fetch(`/api/components/${uuid}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.component = data.component;
                    this.loaded = true;
                } else {
                    throw new Error('{{ __('messages.failed_to_load_component') }}');
                }
            } catch (error) {
                Swal.fire({
                    title: '{{ __('messages.error') }}',
                    text: error.message,
                    icon: 'error',
                    confirmButtonText: '{{ __('messages.ok') }}'
                }).then(() => {
                    window.location.href = '{{ route('dashboard.components.index') }}';
                });
            }
        },

        async loadStats() {
            try {
                const response = await fetch(`/api/components/${uuid}/stats`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.stats = data.stats;
                }
            } catch (error) {
                console.error('Failed to load stats:', error);
            }
        },

        async toggleStatus() {
            const newStatus = !this.component.is_active;
            const action = newStatus ? '{{ __('messages.activate') }}' : '{{ __('messages.deactivate') }}';

            const result = await Swal.fire({
                title: '{{ __('messages.confirm_action') }}',
                text: `{{ __('messages.are_you_sure_you_want_to') }} ${action.toLowerCase()} {{ __('messages.this_component') }}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: action,
                cancelButtonText: '{{ __('messages.cancel') }}'
            });

            if (result.isConfirmed) {
                this.loading = true;

                try {
                    const response = await fetch(`/api/components/${uuid}/toggle-status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (response.ok) {
                        this.component.is_active = newStatus;
                        Swal.fire({
                            title: '{{ __('messages.success') }}',
                            text: data.message,
                            icon: 'success',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    } else {
                        throw new Error(data.message || '{{ __('messages.something_went_wrong') }}');
                    }
                } catch (error) {
                    Swal.fire({
                        title: '{{ __('messages.error') }}',
                        text: error.message,
                        icon: 'error',
                        confirmButtonText: '{{ __('messages.ok') }}'
                    });
                } finally {
                    this.loading = false;
                }
            }
        },

        async deleteComponent() {
            const result = await Swal.fire({
                title: '{{ __('messages.delete_component') }}',
                text: '{{ __('messages.delete_component_confirmation') }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '{{ __('messages.delete') }}',
                cancelButtonText: '{{ __('messages.cancel') }}',
                confirmButtonColor: '#ef4444'
            });

            if (result.isConfirmed) {
                this.loading = true;

                try {
                    const response = await fetch(`/api/components/${uuid}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (response.ok) {
                        Swal.fire({
                            title: '{{ __('messages.success') }}',
                            text: data.message || '{{ __('messages.component_deleted_successfully') }}',
                            icon: 'success',
                            confirmButtonText: '{{ __('messages.ok') }}'
                        }).then(() => {
                            window.location.href = '{{ route('dashboard.components.index') }}';
                        });
                    } else {
                        throw new Error(data.message || '{{ __('messages.something_went_wrong') }}');
                    }
                } catch (error) {
                    Swal.fire({
                        title: '{{ __('messages.error') }}',
                        text: error.message,
                        icon: 'error',
                        confirmButtonText: '{{ __('messages.ok') }}'
                    });
                } finally {
                    this.loading = false;
                }
            }
        },

        formatDate(dateString) {
            if (!dateString) return '-';
            return new Date(dateString).toLocaleDateString('{{ app()->getLocale() }}', {
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

## 5. Statistics & Analytics Views

### resources/views/dashboard/statistics/index.blade.php

```blade
@extends('layouts.dashboard')

@section('title', __('messages.statistics_overview'))

@section('content')
<div x-data="statisticsOverview()" x-init="init()" class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ __('messages.statistics_overview') }}
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                {{ __('messages.statistics_overview_description') }}
            </p>
        </div>
        <div class="flex items-center space-x-4 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
            <select x-model="selectedPeriod" @change="loadData()" 
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="7d">{{ __('messages.last_7_days') }}</option>
                <option value="30d">{{ __('messages.last_30_days') }}</option>
                <option value="3m">{{ __('messages.last_3_months') }}</option>
                <option value="1y">{{ __('messages.last_year') }}</option>
            </select>
            <button @click="refreshData()" 
                    :disabled="loading" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }} space-x-2">
                <svg :class="loading ? 'animate-spin' : ''" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span>{{ __('messages.refresh') }}</span>
            </button>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading && !loaded" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Overview Cards -->
    <div x-show="loaded" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                </div>
                <div class="{{ app()->getLocale() === 'ar' ? 'mr-4' : 'ml-4' }}">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('messages.total_websites') }}</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.total_websites || 0"></p>
                    <p class="text-sm text-green-600 dark:text-green-400" x-show="stats.websites_growth >= 0">
                        +<span x-text="stats.websites_growth?.toFixed(1) || 0"></span>% {{ __('messages.from_last_period') }}
                    </p>
                    <p class="text-sm text-red-600 dark:text-red-400" x-show="stats.websites_growth < 0">
                        <span x-text="stats.websites_growth?.toFixed(1) || 0"></span>% {{ __('messages.from_last_period') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="{{ app()->getLocale() === 'ar' ? 'mr-4' : 'ml-4' }}">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('messages.total_components') }}</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.total_components || 0"></p>
                    <p class="text-sm text-green-600 dark:text-green-400" x-show="stats.components_growth >= 0">
                        +<span x-text="stats.components_growth?.toFixed(1) || 0"></span>% {{ __('messages.from_last_period') }}
                    </p>
                    <p class="text-sm text-red-600 dark:text-red-400" x-show="stats.components_growth < 0">
                        <span x-text="stats.components_growth?.toFixed(1) || 0"></span>% {{ __('messages.from_last_period') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="{{ app()->getLocale() === 'ar' ? 'mr-4' : 'ml-4' }}">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('messages.api_requests') }}</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="formatNumber(stats.total_requests || 0)"></p>
                    <p class="text-sm text-green-600 dark:text-green-400" x-show="stats.requests_growth >= 0">
                        +<span x-text="stats.requests_growth?.toFixed(1) || 0"></span>% {{ __('messages.from_last_period') }}
                    </p>
                    <p class="text-sm text-red-600 dark:text-red-400" x-show="stats.requests_growth < 0">
                        <span x-text="stats.requests_growth?.toFixed(1) || 0"></span>% {{ __('messages.from_last_period') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="{{ app()->getLocale() === 'ar' ? 'mr-4' : 'ml-4' }}">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('messages.active_users') }}</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.active_users || 0"></p>
                    <p class="text-sm text-green-600 dark:text-green-400" x-show="stats.users_growth >= 0">
                        +<span x-text="stats.users_growth?.toFixed(1) || 0"></span>% {{ __('messages.from_last_period') }}
                    </p>
                    <p class="text-sm text-red-600 dark:text-red-400" x-show="stats.users_growth < 0">
                        <span x-text="stats.users_growth?.toFixed(1) || 0"></span>% {{ __('messages.from_last_period') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div x-show="loaded" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- API Requests Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('messages.api_requests_trend') }}
                </h2>
                <div class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                    <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.requests') }}</span>
                </div>
            </div>
            <div class="h-64" id="requests-chart"></div>
        </div>

        <!-- Components Usage Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('messages.components_usage') }}
                </h2>
            </div>
            <div class="h-64" id="components-chart"></div>
        </div>
    </div>

    <!-- Tables Row -->
    <div x-show="loaded" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Websites -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('messages.top_websites') }}
                </h2>
                <a href="{{ route('dashboard.statistics.website') }}" 
                   class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 text-sm">
                    {{ __('messages.view_all') }}
                </a>
            </div>
            <div class="space-y-3">
                <template x-for="website in stats.top_websites || []" :key="website.id">
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }} space-x-3">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-medium" x-text="website.name?.charAt(0) || 'W'"></span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="website.name"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="website.domain"></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="formatNumber(website.requests_count)"></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.requests') }}</p>
                        </div>
                    </div>
                </template>
                <div x-show="!stats.top_websites?.length" class="text-center py-4 text-gray-500 dark:text-gray-400">
                    {{ __('messages.no_data_available') }}
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                {{ __('messages.recent_activity') }}
            </h2>
            <div class="space-y-3">
                <template x-for="activity in stats.recent_activity || []" :key="activity.id">
                    <div class="flex items-start {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }} space-x-3">
                        <div class="flex-shrink-0">
                            <div :class="getActivityIcon(activity.type).class" class="w-8 h-8 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getActivityIcon(activity.type).path"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 dark:text-white" x-text="activity.description"></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="formatDate(activity.created_at)"></p>
                        </div>
                    </div>
                </template>
                <div x-show="!stats.recent_activity?.length" class="text-center py-4 text-gray-500 dark:text-gray-400">
                    {{ __('messages.no_recent_activity') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div x-show="loaded" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            {{ __('messages.performance_metrics') }}
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400" x-text="stats.avg_response_time || '0'">0</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.avg_response_time') }} (ms)</p>
            </div>
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <p class="text-2xl font-bold text-green-600 dark:text-green-400" x-text="(stats.success_rate || 0).toFixed(1) + '%'">0%</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.success_rate') }}</p>
            </div>
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400" x-text="formatNumber(stats.error_count || 0)">0</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.errors') }}</p>
            </div>
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <p class="text-2xl font-bold text-purple-600 dark:text-purple-400" x-text="formatBytes(stats.bandwidth_used || 0)">0 B</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.bandwidth_used') }}</p>
            </div>
        </div>
    </div>
</div>

<script>
function statisticsOverview() {
    return {
        loading: false,
        loaded: false,
        selectedPeriod: '30d',
        stats: {},

        async init() {
            await this.loadData();
        },

        async loadData() {
            this.loading = true;
            
            try {
                const response = await fetch(`/api/dashboard/statistics?period=${this.selectedPeriod}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.stats = data.stats;
                    this.loaded = true;
                    
                    // Initialize charts after data is loaded
                    this.$nextTick(() => {
                        this.initCharts();
                    });
                } else {
                    throw new Error('{{ __('messages.failed_to_load_statistics') }}');
                }
            } catch (error) {
                Swal.fire({
                    title: '{{ __('messages.error') }}',
                    text: error.message,
                    icon: 'error',
                    confirmButtonText: '{{ __('messages.ok') }}'
                });
            } finally {
                this.loading = false;
            }
        },

        async refreshData() {
            await this.loadData();
            Swal.fire({
                title: '{{ __('messages.success') }}',
                text: '{{ __('messages.data_refreshed_successfully') }}',
                icon: 'success',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        },

        initCharts() {
            // Initialize requests chart
            if (this.stats.requests_trend) {
                // Chart implementation would go here
                // Using Chart.js, ApexCharts, or similar library
            }

            // Initialize components usage chart
            if (this.stats.components_usage) {
                // Chart implementation would go here
            }
        },

        formatNumber(num) {
            if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
            if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
            return num.toString();
        },

        formatBytes(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        formatDate(dateString) {
            if (!dateString) return '-';
            return new Date(dateString).toLocaleDateString('{{ app()->getLocale() }}', {
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        getActivityIcon(type) {
            const icons = {
                'website_created': {
                    class: 'bg-green-500',
                    path: 'M12 4v16m8-8H4'
                },
                'component_used': {
                    class: 'bg-blue-500',
                    path: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'
                },
                'error': {
                    class: 'bg-red-500',
                    path: 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
                },
                'default': {
                    class: 'bg-gray-500',
                    path: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
                }
            };
            return icons[type] || icons.default;
        }
    }
}
</script>
@endsection
```

The remaining views (Statistics website/component, User Management, Authentication Views, WebBloc Component Templates, API Documentation, Public Landing, Email Templates, and Error Pages).
