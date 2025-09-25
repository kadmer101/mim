Based on my complete analysis of all the WebBloc project documentation, I now understand the full context and requirements. This is a comprehensive Laravel-based multi-tenant system that provides dynamic web components as APIs for static websites, featuring multi-language support (English/Arabic), UUID-based routing, soft deletes, and Alpine.js integration.

Now I'll provide all the requested blade views with complete implementation:

## 8. WebBloc Component Templates

### resources/views/webblocs/auth.blade.php

```blade
{{-- WebBloc Authentication Component Template --}}
<div 
    id="webbloc-auth-{{ $uuid ?? 'default' }}"
    class="webbloc-auth-component"
    x-data="webBlocAuth(@js($config ?? []))"
    x-show="true"
    :class="{ 'rtl': isRTL }"
>
    <!-- Loading Overlay -->
    <div x-show="loading" class="webbloc-loading-overlay">
        <div class="webbloc-spinner">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        </div>
        <p class="mt-2 text-sm text-gray-600" x-text="loadingText"></p>
    </div>

    <!-- Error Display -->
    <div x-show="error" class="webbloc-alert webbloc-alert-error mb-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3 rtl:ml-0 rtl:mr-3">
                <p class="text-sm text-red-700" x-text="error"></p>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    <div x-show="success" class="webbloc-alert webbloc-alert-success mb-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3 rtl:ml-0 rtl:mr-3">
                <p class="text-sm text-green-700" x-text="success"></p>
            </div>
        </div>
    </div>

    <!-- Authentication State: Logged Out -->
    <div x-show="!isAuthenticated && !showRegister && !showForgotPassword" class="webbloc-auth-login">
        <div class="webbloc-auth-header">
            <h3 class="text-lg font-medium text-gray-900" x-text="translations.login_title"></h3>
            <p class="mt-1 text-sm text-gray-600" x-text="translations.login_subtitle"></p>
        </div>

        <form @submit.prevent="handleLogin" class="webbloc-auth-form mt-6">
            <div class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700" x-text="translations.email"></label>
                    <input 
                        type="email" 
                        id="email" 
                        x-model="loginForm.email"
                        :class="{ 'border-red-300': errors.email }"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                        :placeholder="translations.email_placeholder"
                        required
                    />
                    <p x-show="errors.email" class="mt-1 text-sm text-red-600" x-text="errors.email"></p>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700" x-text="translations.password"></label>
                    <div class="mt-1 relative">
                        <input 
                            :type="showPassword ? 'text' : 'password'" 
                            id="password" 
                            x-model="loginForm.password"
                            :class="{ 'border-red-300': errors.password }"
                            class="block w-full px-3 py-2 pr-10 rtl:pr-3 rtl:pl-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                            :placeholder="translations.password_placeholder"
                            required
                        />
                        <button 
                            type="button" 
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 rtl:right-auto rtl:left-0 pr-3 rtl:pr-0 rtl:pl-3 flex items-center"
                        >
                            <svg x-show="!showPassword" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="showPassword" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.464 8.464m1.414 1.414L8.464 8.464m5.656 5.656l1.415 1.415m-1.415-1.415l1.415 1.415M14.828 14.828L16.243 16.243m-1.415-1.415L16.243 16.243" />
                            </svg>
                        </button>
                    </div>
                    <p x-show="errors.password" class="mt-1 text-sm text-red-600" x-text="errors.password"></p>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="remember" 
                            x-model="loginForm.remember"
                            class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                        />
                        <label for="remember" class="ml-2 rtl:ml-0 rtl:mr-2 block text-sm text-gray-700" x-text="translations.remember_me"></label>
                    </div>

                    <button 
                        type="button" 
                        @click="showForgotPassword = true"
                        class="text-sm text-primary hover:text-primary-dark"
                        x-text="translations.forgot_password"
                    ></button>
                </div>
            </div>

            <div class="mt-6">
                <button 
                    type="submit" 
                    :disabled="loginLoading"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span x-show="!loginLoading" x-text="translations.login_button"></span>
                    <span x-show="loginLoading" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 rtl:mr-0 rtl:ml-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="translations.logging_in"></span>
                    </span>
                </button>
            </div>
        </form>

        <div class="mt-6">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500" x-text="translations.or"></span>
                </div>
            </div>

            <div class="mt-6">
                <button 
                    type="button" 
                    @click="showRegister = true"
                    class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                    x-text="translations.no_account"
                ></button>
            </div>
        </div>
    </div>

    <!-- Authentication State: Register -->
    <div x-show="showRegister && !isAuthenticated" class="webbloc-auth-register">
        <div class="webbloc-auth-header">
            <h3 class="text-lg font-medium text-gray-900" x-text="translations.register_title"></h3>
            <p class="mt-1 text-sm text-gray-600" x-text="translations.register_subtitle"></p>
        </div>

        <form @submit.prevent="handleRegister" class="webbloc-auth-form mt-6">
            <div class="space-y-4">
                <div>
                    <label for="reg_name" class="block text-sm font-medium text-gray-700" x-text="translations.name"></label>
                    <input 
                        type="text" 
                        id="reg_name" 
                        x-model="registerForm.name"
                        :class="{ 'border-red-300': errors.name }"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                        :placeholder="translations.name_placeholder"
                        required
                    />
                    <p x-show="errors.name" class="mt-1 text-sm text-red-600" x-text="errors.name"></p>
                </div>

                <div>
                    <label for="reg_email" class="block text-sm font-medium text-gray-700" x-text="translations.email"></label>
                    <input 
                        type="email" 
                        id="reg_email" 
                        x-model="registerForm.email"
                        :class="{ 'border-red-300': errors.email }"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                        :placeholder="translations.email_placeholder"
                        required
                    />
                    <p x-show="errors.email" class="mt-1 text-sm text-red-600" x-text="errors.email"></p>
                </div>

                <div>
                    <label for="reg_password" class="block text-sm font-medium text-gray-700" x-text="translations.password"></label>
                    <div class="mt-1 relative">
                        <input 
                            :type="showRegPassword ? 'text' : 'password'" 
                            id="reg_password" 
                            x-model="registerForm.password"
                            :class="{ 'border-red-300': errors.password }"
                            class="block w-full px-3 py-2 pr-10 rtl:pr-3 rtl:pl-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                            :placeholder="translations.password_placeholder"
                            required
                        />
                        <button 
                            type="button" 
                            @click="showRegPassword = !showRegPassword"
                            class="absolute inset-y-0 right-0 rtl:right-auto rtl:left-0 pr-3 rtl:pr-0 rtl:pl-3 flex items-center"
                        >
                            <svg x-show="!showRegPassword" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="showRegPassword" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.464 8.464m1.414 1.414L8.464 8.464m5.656 5.656l1.415 1.415m-1.415-1.415l1.415 1.415M14.828 14.828L16.243 16.243m-1.415-1.415L16.243 16.243" />
                            </svg>
                        </button>
                    </div>
                    <p x-show="errors.password" class="mt-1 text-sm text-red-600" x-text="errors.password"></p>
                </div>

                <div>
                    <label for="reg_password_confirmation" class="block text-sm font-medium text-gray-700" x-text="translations.confirm_password"></label>
                    <input 
                        type="password" 
                        id="reg_password_confirmation" 
                        x-model="registerForm.password_confirmation"
                        :class="{ 'border-red-300': errors.password_confirmation }"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                        :placeholder="translations.confirm_password_placeholder"
                        required
                    />
                    <p x-show="errors.password_confirmation" class="mt-1 text-sm text-red-600" x-text="errors.password_confirmation"></p>
                </div>
            </div>

            <div class="mt-6">
                <button 
                    type="submit" 
                    :disabled="registerLoading"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span x-show="!registerLoading" x-text="translations.register_button"></span>
                    <span x-show="registerLoading" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 rtl:mr-0 rtl:ml-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="translations.registering"></span>
                    </span>
                </button>
            </div>
        </form>

        <div class="mt-6">
            <button 
                type="button" 
                @click="showRegister = false"
                class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                x-text="translations.have_account"
            ></button>
        </div>
    </div>

    <!-- Authentication State: Forgot Password -->
    <div x-show="showForgotPassword && !isAuthenticated" class="webbloc-auth-forgot">
        <div class="webbloc-auth-header">
            <h3 class="text-lg font-medium text-gray-900" x-text="translations.forgot_password_title"></h3>
            <p class="mt-1 text-sm text-gray-600" x-text="translations.forgot_password_subtitle"></p>
        </div>

        <form @submit.prevent="handleForgotPassword" class="webbloc-auth-form mt-6">
            <div>
                <label for="forgot_email" class="block text-sm font-medium text-gray-700" x-text="translations.email"></label>
                <input 
                    type="email" 
                    id="forgot_email" 
                    x-model="forgotForm.email"
                    :class="{ 'border-red-300': errors.email }"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                    :placeholder="translations.email_placeholder"
                    required
                />
                <p x-show="errors.email" class="mt-1 text-sm text-red-600" x-text="errors.email"></p>
            </div>

            <div class="mt-6">
                <button 
                    type="submit" 
                    :disabled="forgotLoading"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span x-show="!forgotLoading" x-text="translations.send_reset_link"></span>
                    <span x-show="forgotLoading" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 rtl:mr-0 rtl:ml-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="translations.sending"></span>
                    </span>
                </button>
            </div>
        </form>

        <div class="mt-6">
            <button 
                type="button" 
                @click="showForgotPassword = false"
                class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                x-text="translations.back_to_login"
            ></button>
        </div>
    </div>

    <!-- Authentication State: Logged In -->
    <div x-show="isAuthenticated" class="webbloc-auth-profile">
        <div class="webbloc-user-info">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <img 
                        class="h-10 w-10 rounded-full" 
                        :src="user.avatar || defaultAvatar" 
                        :alt="user.name"
                    />
                </div>
                <div class="ml-4 rtl:ml-0 rtl:mr-4">
                    <div class="text-sm font-medium text-gray-900" x-text="user.name"></div>
                    <div class="text-sm text-gray-500" x-text="user.email"></div>
                </div>
            </div>
        </div>

        <div class="mt-4 flex space-x-2 rtl:space-x-reverse">
            <button 
                @click="showProfileModal = true"
                class="flex-1 bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                x-text="translations.edit_profile"
            ></button>
            <button 
                @click="handleLogout"
                class="flex-1 bg-red-600 py-2 px-3 border border-transparent rounded-md shadow-sm text-sm leading-4 font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                x-text="translations.logout"
            ></button>
        </div>
    </div>

    <!-- Profile Edit Modal -->
    <div x-show="showProfileModal" class="webbloc-modal-overlay" @click.away="showProfileModal = false">
        <div class="webbloc-modal">
            <div class="webbloc-modal-header">
                <h3 class="text-lg font-medium text-gray-900" x-text="translations.edit_profile_title"></h3>
                <button @click="showProfileModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="handleUpdateProfile" class="webbloc-modal-body">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700" x-text="translations.name"></label>
                        <input 
                            type="text" 
                            x-model="profileForm.name"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                            required
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" x-text="translations.email"></label>
                        <input 
                            type="email" 
                            x-model="profileForm.email"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                            required
                        />
                    </div>
                </div>

                <div class="webbloc-modal-footer">
                    <button 
                        type="button" 
                        @click="showProfileModal = false"
                        class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                        x-text="translations.cancel"
                    ></button>
                    <button 
                        type="submit" 
                        :disabled="profileLoading"
                        class="w-full sm:w-auto ml-3 rtl:ml-0 rtl:mr-3 px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50"
                        x-text="translations.save_changes"
                    ></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function webBlocAuth(config = {}) {
    return {
        // Configuration
        apiUrl: config.apiUrl || '/api',
        websiteUuid: config.websiteUuid || '',
        apiKey: config.apiKey || '',
        locale: config.locale || 'en',
        
        // State
        loading: false,
        error: null,
        success: null,
        isAuthenticated: false,
        user: null,
        
        // UI State
        showRegister: false,
        showForgotPassword: false,
        showProfileModal: false,
        showPassword: false,
        showRegPassword: false,
        
        // Loading States
        loginLoading: false,
        registerLoading: false,
        forgotLoading: false,
        profileLoading: false,
        
        // Forms
        loginForm: {
            email: '',
            password: '',
            remember: false
        },
        registerForm: {
            name: '',
            email: '',
            password: '',
            password_confirmation: ''
        },
        forgotForm: {
            email: ''
        },
        profileForm: {
            name: '',
            email: ''
        },
        
        // Errors
        errors: {},
        
        // Computed
        get isRTL() {
            return this.locale === 'ar';
        },
        
        get defaultAvatar() {
            return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiNGM0Y0RjYiLz4KPHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTEyIDEyQzE0LjIwOTEgMTIgMTYgMTAuMjA5MSAxNiA4QzE2IDUuNzkwODYgMTQuMjA5MSA0IDEyIDRDOS43OTA4NiA0IDggNS43OTA4NiA4IDhDOCAxMC4yMDkxIDkuNzkwODYgMTIgMTIgMTJaIiBmaWxsPSIjOUI5QkEwIi8+CjxwYXRoIGQ9Ik0xMiAxNEM4LjEzNDAxIDE0IDUgMTcuMTM0IDUgMjFIMTlDMTkgMTcuMTM0IDE1Ljg2NiAxNCAxMiAxNFoiIGZpbGw9IiM5QjlCQTAiLz4KPC9zdmc+Cjwvc3ZnPg==';
        },
        
        get loadingText() {
            return this.translations.loading || 'Loading...';
        },
        
        // Translations
        translations: {
            // Login
            login_title: 'Sign In',
            login_subtitle: 'Sign in to your account',
            login_button: 'Sign In',
            logging_in: 'Signing In...',
            
            // Register
            register_title: 'Create Account',
            register_subtitle: 'Create a new account',
            register_button: 'Create Account',
            registering: 'Creating Account...',
            
            // Forgot Password
            forgot_password: 'Forgot Password?',
            forgot_password_title: 'Reset Password',
            forgot_password_subtitle: 'Enter your email to receive reset instructions',
            send_reset_link: 'Send Reset Link',
            sending: 'Sending...',
            
            // Profile
            edit_profile: 'Edit Profile',
            edit_profile_title: 'Edit Profile',
            logout: 'Sign Out',
            save_changes: 'Save Changes',
            
            // Form Fields
            name: 'Name',
            email: 'Email',
            password: 'Password',
            confirm_password: 'Confirm Password',
            remember_me: 'Remember me',
            
            // Placeholders
            name_placeholder: 'Enter your name',
            email_placeholder: 'Enter your email',
            password_placeholder: 'Enter your password',
            confirm_password_placeholder: 'Confirm your password',
            
            // Navigation
            no_account: 'Don\'t have an account? Sign up',
            have_account: 'Already have an account? Sign in',
            back_to_login: 'Back to Sign In',
            
            // Common
            or: 'OR',
            cancel: 'Cancel',
            loading: 'Loading...'
        },
        
        init() {
            this.loadTranslations();
            this.checkAuthStatus();
        },
        
        async loadTranslations() {
            if (this.locale === 'ar') {
                this.translations = {
                    // Login
                    login_title: 'تسجيل الدخول',
                    login_subtitle: 'قم بتسجيل الدخول إلى حسابك',
                    login_button: 'تسجيل الدخول',
                    logging_in: 'جاري تسجيل الدخول...',
                    
                    // Register
                    register_title: 'إنشاء حساب',
                    register_subtitle: 'إنشاء حساب جديد',
                    register_button: 'إنشاء حساب',
                    registering: 'جاري إنشاء الحساب...',
                    
                    // Forgot Password
                    forgot_password: 'نسيت كلمة المرور؟',
                    forgot_password_title: 'إعادة تعيين كلمة المرور',
                    forgot_password_subtitle: 'أدخل بريدك الإلكتروني لتلقي تعليمات إعادة التعيين',
                    send_reset_link: 'إرسال رابط إعادة التعيين',
                    sending: 'جاري الإرسال...',
                    
                    // Profile
                    edit_profile: 'تعديل الملف الشخصي',
                    edit_profile_title: 'تعديل الملف الشخصي',
                    logout: 'تسجيل الخروج',
                    save_changes: 'حفظ التغييرات',
                    
                    // Form Fields
                    name: 'الاسم',
                    email: 'البريد الإلكتروني',
                    password: 'كلمة المرور',
                    confirm_password: 'تأكيد كلمة المرور',
                    remember_me: 'تذكرني',
                    
                    // Placeholders
                    name_placeholder: 'أدخل اسمك',
                    email_placeholder: 'أدخل بريدك الإلكتروني',
                    password_placeholder: 'أدخل كلمة المرور',
                    confirm_password_placeholder: 'تأكيد كلمة المرور',
                    
                    // Navigation
                    no_account: 'ليس لديك حساب؟ سجل الآن',
                    have_account: 'لديك حساب بالفعل؟ سجل الدخول',
                    back_to_login: 'العودة لتسجيل الدخول',
                    
                    // Common
                    or: 'أو',
                    cancel: 'إلغاء',
                    loading: 'جاري التحميل...'
                };
            }
        },
        
        async checkAuthStatus() {
            this.loading = true;
            try {
                const response = await this.apiCall('/auth/user', 'GET');
                if (response.success) {
                    this.isAuthenticated = true;
                    this.user = response.data;
                    this.profileForm.name = this.user.name;
                    this.profileForm.email = this.user.email;
                }
            } catch (error) {
                this.isAuthenticated = false;
                this.user = null;
            } finally {
                this.loading = false;
            }
        },
        
        async handleLogin() {
            this.loginLoading = true;
            this.clearErrors();
            
            try {
                const response = await this.apiCall('/auth/login', 'POST', this.loginForm);
                if (response.success) {
                    this.isAuthenticated = true;
                    this.user = response.data.user;
                    this.profileForm.name = this.user.name;
                    this.profileForm.email = this.user.email;
                    this.success = 'Login successful!';
                    this.resetForms();
                } else {
                    this.handleApiErrors(response);
                }
            } catch (error) {
                this.error = 'Login failed. Please try again.';
            } finally {
                this.loginLoading = false;
            }
        },
        
        async handleRegister() {
            this.registerLoading = true;
            this.clearErrors();
            
            try {
                const response = await this.apiCall('/auth/register', 'POST', this.registerForm);
                if (response.success) {
                    this.isAuthenticated = true;
                    this.user = response.data.user;
                    this.profileForm.name = this.user.name;
                    this.profileForm.email = this.user.email;
                    this.success = 'Registration successful!';
                    this.showRegister = false;
                    this.resetForms();
                } else {
                    this.handleApiErrors(response);
                }
            } catch (error) {
                this.error = 'Registration failed. Please try again.';
            } finally {
                this.registerLoading = false;
            }
        },
        
        async handleForgotPassword() {
            this.forgotLoading = true;
            this.clearErrors();
            
            try {
                const response = await this.apiCall('/auth/forgot-password', 'POST', this.forgotForm);
                if (response.success) {
                    this.success = 'Password reset link sent to your email!';
                    this.showForgotPassword = false;
                    this.resetForms();
                } else {
                    this.handleApiErrors(response);
                }
            } catch (error) {
                this.error = 'Failed to send reset link. Please try again.';
            } finally {
                this.forgotLoading = false;
            }
        },
        
        async handleUpdateProfile() {
            this.profileLoading = true;
            this.clearErrors();
            
            try {
                const response = await this.apiCall('/auth/profile', 'PUT', this.profileForm);
                if (response.success) {
                    this.user = response.data;
                    this.success = 'Profile updated successfully!';
                    this.showProfileModal = false;
                } else {
                    this.handleApiErrors(response);
                }
            } catch (error) {
                this.error = 'Failed to update profile. Please try again.';
            } finally {
                this.profileLoading = false;
            }
        },
        
        async handleLogout() {
            try {
                await this.apiCall('/auth/logout', 'POST');
                this.isAuthenticated = false;
                this.user = null;
                this.success = 'Logged out successfully!';
                this.resetForms();
            } catch (error) {
                this.error = 'Logout failed. Please try again.';
            }
        },
        
        async apiCall(endpoint, method = 'GET', data = null) {
            const url = `${this.apiUrl}${endpoint}`;
            const options = {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Website-UUID': this.websiteUuid,
                    'X-API-Key': this.apiKey,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };
            
            if (data) {
                options.body = JSON.stringify(data);
            }
            
            const response = await fetch(url, options);
            return await response.json();
        },
        
        handleApiErrors(response) {
            if (response.errors) {
                this.errors = response.errors;
            } else {
                this.error = response.message || 'An error occurred';
            }
        },
        
        clearErrors() {
            this.error = null;
            this.success = null;
            this.errors = {};
        },
        
        resetForms() {
            this.loginForm = {
                email: '',
                password: '',
                remember: false
            };
            this.registerForm = {
                name: '',
                email: '',
                password: '',
                password_confirmation: ''
            };
            this.forgotForm = {
                email: ''
            };
        }
    };
}
</script>

<style>
.webbloc-auth-component {
    @apply max-w-md mx-auto bg-white rounded-lg shadow-md p-6;
}

.webbloc-auth-component.rtl {
    direction: rtl;
    text-align: right;
}

.webbloc-loading-overlay {
    @apply absolute inset-0 bg-white bg-opacity-75 flex flex-col items-center justify-center;
}

.webbloc-spinner {
    @apply flex items-center justify-center;
}

.webbloc-alert {
    @apply rounded-md p-4;
}

.webbloc-alert-error {
    @apply bg-red-50 border border-red-200;
}

.webbloc-alert-success {
    @apply bg-green-50 border border-green-200;
}

.webbloc-auth-header {
    @apply text-center;
}

.webbloc-auth-form {
    @apply space-y-6;
}

.webbloc-user-info {
    @apply bg-gray-50 rounded-lg p-4;
}

.webbloc-modal-overlay {
    @apply fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center;
}

.webbloc-modal {
    @apply bg-white rounded-lg shadow-xl max-w-md w-full mx-4;
}

.webbloc-modal-header {
    @apply flex justify-between items-center p-6 border-b border-gray-200;
}

.webbloc-modal-body {
    @apply p-6;
}

.webbloc-modal-footer {
    @apply flex justify-end space-x-3 rtl:space-x-reverse px-6 py-4 bg-gray-50 rounded-b-lg;
}

/* RTL Support */
.webbloc-auth-component.rtl input {
    text-align: right;
}

.webbloc-auth-component.rtl .webbloc-modal {
    direction: rtl;
}

/* Custom scrollbar for RTL */
.webbloc-auth-component.rtl ::-webkit-scrollbar {
    width: 6px;
}

.webbloc-auth-component.rtl ::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.webbloc-auth-component.rtl ::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.webbloc-auth-component.rtl ::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
```

## 9. API Documentation Views

### resources/views/docs/index.blade.php

```blade
@extends('layouts.app')

@section('title', __('API Documentation'))

@section('content')
<div class="min-h-screen bg-gray-50 py-8" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
            <div class="px-6 py-8 sm:px-8">
                <div class="text-center">
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">
                        {{ __('WebBloc API Documentation') }}
                    </h1>
                    <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                        {{ __('Complete guide to integrating WebBloc dynamic components into your static websites') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Quick Navigation -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
            <div class="px-6 py-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('Quick Navigation') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="{{ route('docs.authentication') }}"
                       class="block p-4 border border-gray-200 rounded-lg hover:border-indigo-300 hover:shadow-md transition-all">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div class="ml-3 {{ app()->getLocale() === 'ar' ? 'mr-3 ml-0' : '' }}">
                                <h3 class="text-sm font-medium text-gray-900">{{ __('Authentication') }}</h3>
                                <p class="text-xs text-gray-500">{{ __('API keys and auth') }}</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('docs.components') }}"
                       class="block p-4 border border-gray-200 rounded-lg hover:border-indigo-300 hover:shadow-md transition-all">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-4H5m14 8H5" />
                                </svg>
                            </div>
                            <div class="ml-3 {{ app()->getLocale() === 'ar' ? 'mr-3 ml-0' : '' }}">
                                <h3 class="text-sm font-medium text-gray-900">{{ __('Components') }}</h3>
                                <p class="text-xs text-gray-500">{{ __('Available WebBlocs') }}</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('docs.integration') }}"
                       class="block p-4 border border-gray-200 rounded-lg hover:border-indigo-300 hover:shadow-md transition-all">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="ml-3 {{ app()->getLocale() === 'ar' ? 'mr-3 ml-0' : '' }}">
                                <h3 class="text-sm font-medium text-gray-900">{{ __('Integration') }}</h3>
                                <p class="text-xs text-gray-500">{{ __('Setup guide') }}</p>
                            </div>
                        </div>
                    </a>

                    <a href="#examples"
                       class="block p-4 border border-gray-200 rounded-lg hover:border-indigo-300 hover:shadow-md transition-all">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="ml-3 {{ app()->getLocale() === 'ar' ? 'mr-3 ml-0' : '' }}">
                                <h3 class="text-sm font-medium text-gray-900">{{ __('Examples') }}</h3>
                                <p class="text-xs text-gray-500">{{ __('Code samples') }}</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Getting Started -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
            <div class="px-6 py-6">
                <h2 class="text-2xl font-semibold text-gray-900 mb-6">{{ __('Getting Started') }}</h2>
                
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-3">{{ __('1. Get Your API Key') }}</h3>
                        <p class="text-gray-600 mb-4">
                            {{ __('First, register your website and obtain your API keys from the dashboard.') }}
                        </p>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3 {{ app()->getLocale() === 'ar' ? 'mr-3 ml-0' : '' }}">
                                    <p class="text-sm text-gray-700">
                                        {{ __('You will receive two keys: a Public API Key for client-side operations and a Secret API Key for server-side operations.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-3">{{ __('2. Include WebBloc Scripts') }}</h3>
                        <p class="text-gray-600 mb-4">
                            {{ __('Add the WebBloc JavaScript and CSS files to your HTML pages.') }}
                        </p>
                        <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                            <pre class="text-green-400 text-sm"><code>&lt;!-- CSS --&gt;
&lt;link rel="stylesheet" href="{{ config('app.url') }}/webbloc/css/webbloc.min.css"&gt;

&lt;!-- JavaScript --&gt;
&lt;script src="{{ config('app.url') }}/webbloc/js/webbloc.min.js"&gt;&lt;/script&gt;</code></pre>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-3">{{ __('3. Initialize WebBloc') }}</h3>
                        <p class="text-gray-600 mb-4">
                            {{ __('Initialize WebBloc with your API credentials.') }}
                        </p>
                        <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                            <pre class="text-green-400 text-sm"><code>&lt;script&gt;
WebBloc.init({
    apiUrl: '{{ config('app.url') }}/api',
    publicKey: 'your-public-api-key',
    websiteUuid: 'your-website-uuid',
    locale: 'en' // or 'ar' for Arabic
});
&lt;/script&gt;</code></pre>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-3">{{ __('4. Add Components') }}</h3>
                        <p class="text-gray-600 mb-4">
                            {{ __('Add WebBloc components to your HTML using data attributes.') }}
                        </p>
                        <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                            <pre class="text-green-400 text-sm"><code>&lt;!-- Comment Component --&gt;
&lt;div data-webbloc="comments" 
     data-limit="10" 
     data-sort="newest"&gt;
    {{ __('Loading comments...') }}
&lt;/div&gt;

&lt;!-- Authentication Component --&gt;
&lt;div data-webbloc="auth"&gt;
    {{ __('Loading authentication...') }}
&lt;/div&gt;</code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Components -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8" id="components">
            <div class="px-6 py-6">
                <h2 class="text-2xl font-semibold text-gray-900 mb-6">{{ __('Available Components') }}</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Auth Component -->
                    <div class="border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 {{ app()->getLocale() === 'ar' ? 'mr-4 ml-0' : '' }}">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('Authentication') }}</h3>
                                <p class="text-sm text-gray-500">data-webbloc="auth"</p>
                            </div>
                        </div>
                        <p class="text-gray-600 mb-4">
                            {{ __('Complete user authentication system with login, register, and password reset functionality.') }}
                        </p>
                        <div class="space-y-2">
                            <div class="text-sm">
                                <span class="font-medium text-gray-700">{{ __('Features:') }}</span>
                                <span class="text-gray-600">{{ __('Login, Register, Password Reset, Profile Management') }}</span>
                            </div>
                            <div class="text-sm">
                                <span class="font-medium text-gray-700">{{ __('Attributes:') }}</span>
                                <span class="text-gray-600">locale, theme</span>
                            </div>
                        </div>
                    </div>

                    <!-- Comments Component -->
                    <div class="border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 {{ app()->getLocale() === 'ar' ? 'mr-4 ml-0' : '' }}">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('Comments') }}</h3>
                                <p class="text-sm text-gray-500">data-webbloc="comments"</p>
                            </div>
                        </div>
                        <p class="text-gray-600 mb-4">
                            {{ __('Interactive comment system with nested replies, reactions, and moderation features.') }}
                        </p>
                        <div class="space-y-2">
                            <div class="text-sm">
                                <span class="font-medium text-gray-700">{{ __('Features:') }}</span>
                                <span class="text-gray-600">{{ __('Nested replies, Reactions, Moderation, File uploads') }}</span>
                            </div>
                            <div class="text-sm">
                                <span class="font-medium text-gray-700">{{ __('Attributes:') }}</span>
                                <span class="text-gray-600">limit, sort, allow-guests, allow-files</span>
                            </div>
                        </div>
                    </div>

                    <!-- Reviews Component -->
                    <div class="border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 {{ app()->getLocale() === 'ar' ? 'mr-4 ml-0' : '' }}">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('Reviews') }}</h3>
                                <p class="text-sm text-gray-500">data-webbloc="reviews"</p>
                            </div>
                        </div>
                        <p class="text-gray-600 mb-4">
                            {{ __('Star rating and review system with filtering, sorting, and analytics.') }}
                        </p>
                        <div class="space-y-2">
                            <div class="text-sm">
                                <span class="font-medium text-gray-700">{{ __('Features:') }}</span>
                                <span class="text-gray-600">{{ __('Star ratings, Rich reviews, Filtering, Analytics') }}</span>
                            </div>
                            <div class="text-sm">
                                <span class="font-medium text-gray-700">{{ __('Attributes:') }}</span>
                                <span class="text-gray-600">limit, sort, show-stats, allow-images</span>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Form Component -->
                    <div class="border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.726a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 {{ app()->getLocale() === 'ar' ? 'mr-4 ml-0' : '' }}">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('Contact Form') }}</h3>
                                <p class="text-sm text-gray-500">data-webbloc="contact"</p>
                            </div>
                        </div>
                        <p class="text-gray-600 mb-4">
                            {{ __('Customizable contact form with spam protection and email notifications.') }}
                        </p>
                        <div class="space-y-2">
                            <div class="text-sm">
                                <span class="font-medium text-gray-700">{{ __('Features:') }}</span>
                                <span class="text-gray-600">{{ __('Custom fields, Spam protection, Email notifications') }}</span>
                            </div>
                            <div class="text-sm">
                                <span class="font-medium text-gray-700">{{ __('Attributes:') }}</span>
                                <span class="text-gray-600">fields, recipient, subject</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Examples -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8" id="examples">
            <div class="px-6 py-6">
                <h2 class="text-2xl font-semibold text-gray-900 mb-6">{{ __('Integration Examples') }}</h2>
                
                <div class="space-y-8">
                    <!-- Basic Example -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Basic Integration') }}</h3>
                        <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                            <pre class="text-green-400 text-sm"><code>&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;title&gt;My Static Website&lt;/title&gt;
    &lt;link rel="stylesheet" href="{{ config('app.url') }}/webbloc/css/webbloc.min.css"&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;h1&gt;Welcome to My Website&lt;/h1&gt;
    
    &lt;!-- Authentication Component --&gt;
    &lt;div data-webbloc="auth"&gt;{{ __('Loading authentication...') }}&lt;/div&gt;
    
    &lt;!-- Comments Component --&gt;
    &lt;div data-webbloc="comments" data-limit="20" data-sort="newest"&gt;
        {{ __('Loading comments...') }}
    &lt;/div&gt;
    
    &lt;script src="{{ config('app.url') }}/webbloc/js/webbloc.min.js"&gt;&lt;/script&gt;
    &lt;script&gt;
    WebBloc.init({
        apiUrl: '{{ config('app.url') }}/api',
        publicKey: 'your-public-api-key',
        websiteUuid: 'your-website-uuid',
        locale: 'en'
    });
    &lt;/script&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>
                        </div>
                    </div>

                    <!-- Advanced Example -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Advanced Configuration') }}</h3>
                        <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                            <pre class="text-green-400 text-sm"><code>&lt;!-- Review Component with Custom Configuration --&gt;
&lt;div data-webbloc="reviews" 
     data-limit="10" 
     data-sort="rating" 
     data-show-stats="true"
     data-allow-images="true"
     data-theme="dark"&gt;
    {{ __('Loading reviews...') }}
&lt;/div&gt;

&lt;!-- Contact Form with Custom Fields --&gt;
&lt;div data-webbloc="contact" 
     data-fields='["name", "email", "phone", "message"]'
     data-recipient="contact@example.com"
     data-subject="New Contact Form Submission"&gt;
    {{ __('Loading contact form...') }}
&lt;/div&gt;

&lt;script&gt;
WebBloc.init({
    apiUrl: '{{ config('app.url') }}/api',
    publicKey: 'your-public-api-key',
    websiteUuid: 'your-website-uuid',
    locale: 'en',
    theme: 'light',
    debug: false,
    callbacks: {
        onReady: function() {
            console.log('WebBloc initialized successfully');
        },
        onError: function(error) {
            console.error('WebBloc error:', error);
        }
    }
});
&lt;/script&gt;</code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Response Format -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
            <div class="px-6 py-6">
                <h2 class="text-2xl font-semibold text-gray-900 mb-6">{{ __('API Response Format') }}</h2>
                
                <p class="text-gray-600 mb-4">
                    {{ __('All API responses follow a consistent JSON format:') }}
                </p>

                <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                    <pre class="text-green-400 text-sm"><code>{
    "success": true,
    "message": "Operation completed successfully",
    "data": {
        // Response data here
    },
    "meta": {
        "pagination": {
            "current_page": 1,
            "per_page": 10,
            "total": 100,
            "last_page": 10
        }
    },
    "errors": {} // Only present when success is false
}</code></pre>
                </div>

                <div class="mt-6 space-y-4">
                    <div>
                        <h4 class="text-base font-medium text-gray-900">{{ __('Success Response') }}</h4>
                        <p class="text-sm text-gray-600">
                            {{ __('When success is true, the data field contains the requested information.') }}
                        </p>
                    </div>
                    <div>
                        <h4 class="text-base font-medium text-gray-900">{{ __('Error Response') }}</h4>
                        <p class="text-sm text-gray-600">
                            {{ __('When success is false, the errors field contains validation or system errors.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Support -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-6">
                <h2 class="text-2xl font-semibold text-gray-900 mb-6">{{ __('Support & Resources') }}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-indigo-100">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C20.832 18.477 19.246 18 17.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('Documentation') }}</h3>
                        <p class="mt-2 text-sm text-gray-600">
                            {{ __('Comprehensive guides and API reference') }}
                        </p>
                    </div>

                    <div class="text-center">
                        <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-green-100">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('24/7 Support') }}</h3>
                        <p class="mt-2 text-sm text-gray-600">
                            {{ __('Get help when you need it') }}
                        </p>
                    </div>

                    <div class="text-center">
                        <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-purple-100">
                            <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('Community') }}</h3>
                        <p class="mt-2 text-sm text-gray-600">
                            {{ __('Join our developer community') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

### resources/views/docs/authentication.blade.php

```blade
@extends('layouts.app')

@section('title', __('Authentication - API Documentation'))

@section('content')
<div class="min-h-screen bg-gray-50 py-8" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                <li class="inline-flex items-center">
                    <a href="{{ route('docs.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-indigo-600">
                        <svg class="w-4 h-4 mr-2 {{ app()->getLocale() === 'ar' ? 'ml-2 mr-0' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        {{ __('Documentation') }}
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 md:ml-2 text-sm font-medium text-gray-500 {{ app()->getLocale() === 'ar' ? 'mr-1 md:mr-2 ml-0' : '' }}">{{ __('Authentication') }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
            <div class="px-6 py-8 sm:px-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-4">
                    {{ __('Authentication') }}
                </h1>
                <p class="text-lg text-gray-600">
                    {{ __('Learn how to authenticate with the WebBloc API using API keys and manage user sessions.') }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Sidebar Navigation -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-8">
                    <nav class="space-y-2">
                        <a href="#api-keys" class="block text-sm font-medium text-indigo-600 bg-indigo-50 rounded-md px-3 py-2">
                            {{ __('API Keys') }}
                        </a>
                        <a href="#public-key" class="block text-sm text-gray-700 hover:text-indigo-600 hover:bg-gray-50 rounded-md px-3 py-2">
                            {{ __('Public Key') }}
                        </a>
                        <a href="#secret-key" class="block text-sm text-gray-700 hover:text-indigo-600 hover:bg-gray-50 rounded-md px-3 py-2">
                            {{ __('Secret Key') }}
                        </a>
                        <a href="#user-auth" class="block text-sm text-gray-700 hover:text-indigo-600 hover:bg-gray-50 rounded-md px-3 py-2">
                            {{ __('User Authentication') }}
                        </a>
                        <a href="#endpoints" class="block text-sm text-gray-700 hover:text-indigo-600 hover:bg-gray-50 rounded-md px-3 py-2">
                            {{ __('Auth Endpoints') }}
                        </a>
                        <a href="#examples" class="block text-sm text-gray-700 hover:text-indigo-600 hover:bg-gray-50 rounded-md px-3 py-2">
                            {{ __('Examples') }}
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-3 space-y-8">
                <!-- API Keys Section -->
                <div id="api-keys" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-6">{{ __('API Keys') }}</h2>
                    
                    <p class="text-gray-600 mb-6">
                        {{ __('WebBloc uses API keys to authenticate requests. You will receive two types of keys when you register your website:') }}
                    </p>

                    <div class="space-y-6">
                        <!-- Public Key -->
                        <div id="public-key" class="border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mr-3 {{ app()->getLocale() === 'ar' ? 'ml-3 mr-0' : '' }}">
                                    <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                {{ __('Public API Key') }}
                            </h3>
                            
                            <div class="space-y-4">
                                <p class="text-gray-600">
                                    {{ __('Used for client-side operations that don\'t require sensitive data access. This key can be safely exposed in your frontend code.') }}
                                </p>
                                
                                <div class="bg-green-50 border border-green-200 rounded-md p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3 {{ app()->getLocale() === 'ar' ? 'mr-3 ml-0' : '' }}">
                                            <h4 class="text-sm font-medium text-green-800">{{ __('Use Cases:') }}</h4>
                                            <ul class="mt-2 text-sm text-green-700 list-disc list-inside">
                                                <li>{{ __('Loading public components (comments, reviews)') }}</li>
                                                <li>{{ __('User authentication flows') }}</li>
                                                <li>{{ __('Public data retrieval') }}</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-900 rounded-lg p-4">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-medium text-gray-300">{{ __('Usage Example') }}</span>
                                        <button onclick="copyToClipboard('public-key-example')" class="text-xs text-gray-400 hover:text-white">
                                            {{ __('Copy') }}
                                        </button>
                                    </div>
                                    <pre id="public-key-example" class="text-green-400 text-sm overflow-x-auto"><code>// Include in HTTP headers
'X-API-Key': 'your-public-api-key'
'X-Website-UUID': 'your-website-uuid'</code></pre>
                                </div>
                            </div>
                        </div>

                        <!-- Secret Key -->
                        <div id="secret-key" class="border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <div class="w-6 h-6 bg-red-100 rounded-full flex items-center justify-center mr-3 {{ app()->getLocale() === 'ar' ? 'ml-3 mr-0' : '' }}">
                                    <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                {{ __('Secret API Key') }}
                            </h3>
                            
                            <div class="space-y-4">
                                <p class="text-gray-600">
                                    {{ __('Used for server-side operations that require elevated privileges. Never expose this key in client-side code.') }}
                                </p>
                                
                                <div class="bg-red-50 border border-red-200 rounded-md p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3 {{ app()->getLocale() === 'ar' ? 'mr-3 ml-0' : '' }}">
                                            <h4 class="text-sm font-medium text-red-800">{{ __('Use Cases:') }}</h4>
                                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                                <li>{{ __('Administrative operations') }}</li>
                                                <li>{{ __('Bulk data operations') }}</li>
                                                <li>{{ __('Server-side integrations') }}</li>
                                                <li>{{ __('Webhook validations') }}</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-900 rounded-lg p-4">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-medium text-gray-300">{{ __('Usage Example') }}</span>
                                        <button onclick="copyToClipboard('secret-key-example')" class="text-xs text-gray-400 hover:text-white">
                                            {{ __('Copy') }}
                                        </button>
                                    </div>
                                    <pre id="secret-key-example" class="text-green-400 text-sm overflow-x-auto"><code>// Server-side only
'Authorization': 'Bearer your-secret-api-key'
'X-Website-UUID': 'your-website-uuid'</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Authentication -->
                <div id="user-auth" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-6">{{ __('User Authentication') }}</h2>
                    
                    <p class="text-gray-600 mb-6">
                        {{ __('WebBloc supports user authentication through the Auth component. Users can register, login, and manage their sessions.') }}
                    </p>

                    <div class="space-y-6">
                        <!-- Authentication Flow -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Authentication Flow') }}</h3>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                                <ol class="list-decimal list-inside space-y-2 text-sm text-blue-800">
                                    <li>{{ __('User submits login/register form through Auth component') }}</li>
                                    <li>{{ __('WebBloc validates credentials and creates session') }}</li>
                                    <li>{{ __('Session token is stored in secure HTTP-only cookie') }}</li>
                                    <li>{{ __('Subsequent requests include session token automatically') }}</li>
                                    <li>{{ __('Components adapt UI based on authentication state') }}</li>
                                </ol>
                            </div>
                        </div>

                        <!-- Session Management -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Session Management') }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-900 mb-2">{{ __('Session Duration') }}</h4>
                                    <p class="text-sm text-gray-600">{{ __('Sessions are valid for 30 days by default, or until logout') }}</p>
                                </div>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-900 mb-2">{{ __('Remember Me') }}</h4>
                                    <p class="text-sm text-gray-600">{{ __('Extended sessions up to 1 year when "Remember Me" is checked') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Auth Endpoints -->
                <div id="endpoints" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-6">{{ __('Authentication Endpoints') }}</h2>

                    <div class="space-y-8">
                        <!-- Register -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <div class="flex items-center mb-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    POST
                                </span>
                                <code class="ml-3 {{ app()->getLocale() === 'ar' ? 'mr-3 ml-0' : '' }} text-sm font-mono text-gray-800">/api/auth/register</code>
                            </div>
                            
                            <p class="text-gray-600 mb-4">{{ __('Register a new user account') }}</p>
                            
                            <div class="space-y-4">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">{{ __('Request Body') }}</h4>
                                    <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                                        <pre class="text-green-400 text-sm"><code>{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}</code></pre>
                                    </div>
                                </div>
                                
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">{{ __('Response') }}</h4>
                                    <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                                        <pre class="text-green-400 text-sm"><code>{
    "success": true,
    "message": "Registration successful",
    "data": {
        "user": {
            "uuid": "550e8400-e29b-41d4-a716-446655440000",
            "name": "John Doe",
            "email": "john@example.com",
            "avatar": null,
            "created_at": "2024-01-01T00:00:00Z"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
    }
}</code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Login -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <div class="flex items-center mb-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    POST
                                </span>
                                <code class="ml-3 {{ app()->getLocale() === 'ar' ? 'mr-3 ml-0' : '' }} text-sm font-mono text-gray-800">/api/auth/login</code>
                            </div>
                            
                            <p class="text-gray-600 mb-4">{{ __('Login with existing credentials') }}</p>
                            
                            <div class="space-y-4">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">{{ __('Request Body') }}</h4>
                                    <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                                        <pre class="text-green-400 text-sm"><code>{
    "email": "john@example.com",
    "password": "password123",
    "remember": true
}</code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Logout -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <div class="flex items-center mb-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    POST
                                </span>
                                <code class="ml-3 {{ app()->getLocale() === 'ar' ? 'mr-3 ml-0' : '' }} text-sm font-mono text-gray-800">/api/auth/logout</code>
                            </div>
                            
                            <p class="text-gray-600 mb-4">{{ __('Logout current user session') }}</p>
                            
                            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    <div class="ml-3 {{ app()->getLocale() === 'ar' ? 'mr-3 ml-0' : '' }}">
                                        <p class="text-sm text-yellow-700">
                                            {{ __('Requires authentication. No request body needed.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Get User -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <div class="flex items-center mb-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    GET
                                </span>
                                <code class="ml-3 {{ app()->getLocale() === 'ar' ? 'mr-3 ml-0' : '' }} text-sm font-mono text-gray-800">/api/auth/user</code>
                            </div>
                            
                            <p class="text-gray-600 mb-4">{{ __('Get current authenticated user information') }}</p>
                            
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 mb-2">{{ __('Response') }}</h4>
                                <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                                    <pre class="text-green-400 text-sm"><code>{
    "success": true,
    "data": {
        "uuid": "550e8400-e29b-41d4-a716-446655440000",
        "name": "John Doe",
        "email": "john@example.com",
        "avatar": "https://example.com/avatar.jpg",
        "created_at": "2024-01-01T00:00:00Z",
        "updated_at": "2024-01-01T00:00:00Z"
    }
}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Examples -->
                <div id="examples" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-6">{{ __('Implementation Examples') }}</h2>

                    <div class="space-y-8">
                        <!-- JavaScript Example -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('JavaScript Implementation') }}</h3>
                            <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                                <pre class="text-green-400 text-sm"><code>// Initialize WebBloc with authentication
WebBloc.init({
    apiUrl: '{{ config('app.url') }}/api',
    publicKey: 'your-public-api-key',
    websiteUuid: 'your-website-uuid',
    locale: 'en'
});

// Listen for authentication events
WebBloc.on('auth:login', function(user) {
    console.log('User logged in:', user);
    // Update UI for authenticated user
});

WebBloc.on('auth:logout', function() {
    console.log('User logged out');
    // Update UI for guest user
});

// Check current authentication status
WebBloc.auth.check().then(function(user) {
    if (user) {
        console.log('User is authenticated:', user);
    } else {
        console.log('User is not authenticated');
    }
});</code></pre>
                            </div>
                        </div>

                        <!-- PHP/Server-side Example -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('PHP/Server-side Implementation') }}</h3>
                            <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                                <pre class="text-green-400 text-sm"><code>&lt;?php
// Using secret API key for server-side operations
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => '{{ config('app.url') }}/api/auth/user',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer your-secret-api-key',
        'X-Website-UUID: your-website-uuid',
        'Content-Type: application/json',
        'Accept: application/json'
    ]
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($httpCode === 200) {
    $userData = json_decode($response, true);
    if ($userData['success']) {
        $user = $userData['data'];
        echo "User: " . $user['name'] . " (" . $user['email'] . ")";
    }
}
?&gt;</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    const text = element.textContent;
    
    navigator.clipboard.writeText(text).then(function() {
        // Show success feedback
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = '{{ __("Copied!") }}';
        setTimeout(() => {
            button.textContent = originalText;
        }, 2000);
    });
}
</script>
@endsection
```
