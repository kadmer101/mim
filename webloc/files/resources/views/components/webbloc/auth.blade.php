@props([
    'website_id' => null,
    'api_key' => null,
    'theme' => 'default',
    'mode' => 'modal', // modal, inline, redirect
    'redirect_after_login' => null,
    'redirect_after_register' => null,
    'show_register' => true,
    'show_forgot_password' => true,
    'custom_fields' => [],
    'social_login' => false
])

<div 
    w2030b="auth"
    data-website-id="{{ $website_id }}"
    data-api-key="{{ $api_key }}"
    data-theme="{{ $theme }}"
    data-mode="{{ $mode }}"
    data-redirect-after-login="{{ $redirect_after_login }}"
    data-redirect-after-register="{{ $redirect_after_register }}"
    data-show-register="{{ $show_register ? 'true' : 'false' }}"
    data-show-forgot-password="{{ $show_forgot_password ? 'true' : 'false' }}"
    data-custom-fields="{{ json_encode($custom_fields) }}"
    data-social-login="{{ $social_login ? 'true' : 'false' }}"
    x-data="webBlocAuth()"
    x-init="init()"
    {{ $attributes->merge(['class' => 'webbloc-auth-container']) }}
>
    <!-- Authentication Status Display -->
    <div x-show="!loading && user" class="webbloc-auth-user-info">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="webbloc-avatar me-2">
                    <img :src="user?.avatar || '/default-avatar.png'" 
                         :alt="user?.name" 
                         class="rounded-circle" 
                         width="32" 
                         height="32"
                         onerror="this.src='/default-avatar.png'">
                </div>
                <div>
                    <div class="fw-bold" x-text="user?.name"></div>
                    <small class="text-muted" x-text="user?.email"></small>
                </div>
            </div>
            <div class="btn-group btn-group-sm">
                <button @click="showProfile = true" class="btn btn-outline-primary">
                    <i class="bi bi-person"></i> Profile
                </button>
                <button @click="logout()" class="btn btn-outline-secondary">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </div>
        </div>
    </div>

    <!-- Authentication Forms (when not logged in) -->
    <div x-show="!loading && !user" class="webbloc-auth-forms">
        
        <!-- Login/Register Toggle Buttons -->
        <div class="webbloc-auth-toggle mb-3">
            <div class="btn-group w-100" role="group">
                <button type="button" 
                        class="btn" 
                        :class="currentForm === 'login' ? 'btn-primary' : 'btn-outline-primary'"
                        @click="currentForm = 'login'">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Login
                </button>
                <button type="button" 
                        class="btn" 
                        :class="currentForm === 'register' ? 'btn-primary' : 'btn-outline-primary'"
                        @click="currentForm = 'register'"
                        x-show="showRegister">
                    <i class="bi bi-person-plus me-1"></i> Register
                </button>
            </div>
        </div>

        <!-- Login Form -->
        <form x-show="currentForm === 'login'" @submit.prevent="login()" class="webbloc-auth-login-form">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" 
                           class="form-control" 
                           x-model="loginForm.email" 
                           required 
                           placeholder="Enter your email">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input :type="showPassword ? 'text' : 'password'" 
                           class="form-control" 
                           x-model="loginForm.password" 
                           required 
                           placeholder="Enter your password">
                    <button type="button" class="btn btn-outline-secondary" @click="showPassword = !showPassword">
                        <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                    </button>
                </div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" x-model="loginForm.remember" id="rememberMe">
                <label class="form-check-label" for="rememberMe">Remember me</label>
            </div>

            <button type="submit" 
                    class="btn btn-primary w-100 mb-3" 
                    :disabled="loginLoading">
                <span x-show="loginLoading" class="spinner-border spinner-border-sm me-2"></span>
                <i x-show="!loginLoading" class="bi bi-box-arrow-in-right me-1"></i>
                <span x-text="loginLoading ? 'Signing In...' : 'Sign In'"></span>
            </button>

            <div class="text-center" x-show="showForgotPassword">
                <a href="#" @click.prevent="currentForm = 'forgot'" class="text-decoration-none">
                    Forgot your password?
                </a>
            </div>
        </form>

        <!-- Register Form -->
        <form x-show="currentForm === 'register'" @submit.prevent="register()" class="webbloc-auth-register-form">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" 
                           class="form-control" 
                           x-model="registerForm.first_name" 
                           required 
                           placeholder="First name">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" 
                           class="form-control" 
                           x-model="registerForm.last_name" 
                           required 
                           placeholder="Last name">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" 
                           class="form-control" 
                           x-model="registerForm.email" 
                           required 
                           placeholder="Enter your email">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input :type="showPassword ? 'text' : 'password'" 
                           class="form-control" 
                           x-model="registerForm.password" 
                           required 
                           placeholder="Create a password">
                    <button type="button" class="btn btn-outline-secondary" @click="showPassword = !showPassword">
                        <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                    </button>
                </div>
                <div class="form-text">Password must be at least 8 characters long</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" 
                           class="form-control" 
                           x-model="registerForm.password_confirmation" 
                           required 
                           placeholder="Confirm your password">
                </div>
            </div>

            <!-- Custom Fields -->
            <template x-for="field in customFields" :key="field.name">
                <div class="mb-3">
                    <label class="form-label" x-text="field.label"></label>
                    <input :type="field.type || 'text'" 
                           class="form-control" 
                           :placeholder="field.placeholder || field.label"
                           :required="field.required || false"
                           x-model="registerForm[field.name]">
                    <div x-show="field.help" class="form-text" x-text="field.help"></div>
                </div>
            </template>

            <div class="mb-3 form-check">
                <input type="checkbox" 
                       class="form-check-input" 
                       x-model="registerForm.terms" 
                       id="acceptTerms" 
                       required>
                <label class="form-check-label" for="acceptTerms">
                    I agree to the <a href="#" target="_blank">Terms of Service</a> and 
                    <a href="#" target="_blank">Privacy Policy</a>
                </label>
            </div>

            <button type="submit" 
                    class="btn btn-success w-100" 
                    :disabled="registerLoading">
                <span x-show="registerLoading" class="spinner-border spinner-border-sm me-2"></span>
                <i x-show="!registerLoading" class="bi bi-person-plus me-1"></i>
                <span x-text="registerLoading ? 'Creating Account...' : 'Create Account'"></span>
            </button>
        </form>

        <!-- Forgot Password Form -->
        <form x-show="currentForm === 'forgot'" @submit.prevent="forgotPassword()" class="webbloc-auth-forgot-form">
            <div class="text-center mb-3">
                <i class="bi bi-key fs-1 text-muted"></i>
                <h4>Forgot Password?</h4>
                <p class="text-muted">Enter your email to receive a password reset link</p>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" 
                           class="form-control" 
                           x-model="forgotForm.email" 
                           required 
                           placeholder="Enter your email">
                </div>
            </div>

            <button type="submit" 
                    class="btn btn-warning w-100 mb-3" 
                    :disabled="forgotLoading">
                <span x-show="forgotLoading" class="spinner-border spinner-border-sm me-2"></span>
                <i x-show="!forgotLoading" class="bi bi-send me-1"></i>
                <span x-text="forgotLoading ? 'Sending...' : 'Send Reset Link'"></span>
            </button>

            <div class="text-center">
                <a href="#" @click.prevent="currentForm = 'login'" class="text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i> Back to Login
                </a>
            </div>
        </form>

        <!-- Social Login (if enabled) -->
        <div x-show="socialLogin" class="webbloc-auth-social mt-3">
            <div class="text-center mb-3">
                <span class="text-muted">Or continue with</span>
            </div>
            <div class="d-grid gap-2">
                <button @click="socialAuth('google')" class="btn btn-outline-danger">
                    <i class="bi bi-google me-2"></i> Continue with Google
                </button>
                <button @click="socialAuth('facebook')" class="btn btn-outline-primary">
                    <i class="bi bi-facebook me-2"></i> Continue with Facebook
                </button>
                <button @click="socialAuth('github')" class="btn btn-outline-dark">
                    <i class="bi bi-github me-2"></i> Continue with GitHub
                </button>
            </div>
        </div>
    </div>

    <!-- Profile Modal -->
    <div x-show="showProfile" 
         class="modal fade show" 
         style="display: block;" 
         @click.self="showProfile = false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Profile</h5>
                    <button type="button" class="btn-close" @click="showProfile = false"></button>
                </div>
                <form @submit.prevent="updateProfile()">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" x-model="profileForm.name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" x-model="profileForm.email">
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" x-model="profileForm.password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" x-model="profileForm.password_confirmation">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="showProfile = false">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" :disabled="profileLoading">
                            <span x-show="profileLoading" class="spinner-border spinner-border-sm me-2"></span>
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div x-show="showProfile" class="modal-backdrop fade show"></div>

    <!-- Loading State -->
    <div x-show="loading" class="text-center py-4">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="mt-2">Loading authentication...</div>
    </div>

    <!-- Error Display -->
    <div x-show="error" class="alert alert-danger mt-3" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <span x-text="error"></span>
    </div>

    <!-- Success Display -->
    <div x-show="success" class="alert alert-success mt-3" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <span x-text="success"></span>
    </div>
</div>

<style>
.webbloc-auth-container {
    max-width: 400px;
}

.webbloc-avatar img {
    object-fit: cover;
}

.webbloc-auth-user-info {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
    border: 1px solid #dee2e6;
}

.webbloc-auth-forms {
    padding: 1rem;
    background: white;
    border-radius: 0.5rem;
    border: 1px solid #dee2e6;
}

.webbloc-auth-toggle .btn-group {
    box-shadow: none;
}

.webbloc-auth-social .btn {
    text-align: left;
}

@media (max-width: 768px) {
    .webbloc-auth-container {
        max-width: 100%;
    }
}
</style>