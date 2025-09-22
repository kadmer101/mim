# WebBloc Components & Frontend Integration Files 

## 1: `resources/views/components/webbloc/auth.blade.php`

```blade
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
```


## 2. Comments Component (`resources/views/components/webbloc/comments.blade.php`)

```php
@props([
    'website_id' => null,
    'api_key' => null,
    'page_url' => request()->fullUrl(),
    'theme' => 'default',
    'limit' => 10,
    'sort' => 'newest',
    'allow_replies' => true,
    'moderation' => false,
    'show_count' => true,
    'guest_commenting' => false,
    'custom_fields' => []
])

<div 
    w2030b="comments"
    data-website-id="{{ $website_id }}"
    data-api-key="{{ $api_key }}"
    data-page-url="{{ $page_url }}"
    data-theme="{{ $theme }}"
    data-limit="{{ $limit }}"
    data-sort="{{ $sort }}"
    data-allow-replies="{{ $allow_replies ? 'true' : 'false' }}"
    data-moderation="{{ $moderation ? 'true' : 'false' }}"
    data-show-count="{{ $show_count ? 'true' : 'false' }}"
    data-guest-commenting="{{ $guest_commenting ? 'true' : 'false' }}"
    data-custom-fields="{{ json_encode($custom_fields) }}"
    x-data="webBlocComments()"
    x-init="init()"
    {{ $attributes->merge(['class' => 'webbloc-comments-container']) }}
>
    <!-- Comments Header -->
    <div class="webbloc-comments-header" x-show="showCount">
        <h3 class="comments-title" x-text="`${totalComments} Comment${totalComments !== 1 ? 's' : ''}`"></h3>
        <div class="comments-sort">
            <label>Sort by:</label>
            <select x-model="sortBy" @change="loadComments()">
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
                <option value="popular">Most Popular</option>
            </select>
        </div>
    </div>

    <!-- Comment Form -->
    <div class="webbloc-comment-form" x-show="canComment">
        <template x-if="!user && !guestCommenting">
            <div class="auth-required">
                <p>Please <button @click="showLogin = true" class="link-btn">sign in</button> to leave a comment.</p>
            </div>
        </template>

        <template x-if="user || guestCommenting">
            <form @submit.prevent="submitComment()" class="comment-form">
                <!-- User Info for Guests -->
                <template x-if="!user && guestCommenting">
                    <div class="guest-info">
                        <input 
                            type="text" 
                            x-model="guestName" 
                            placeholder="Your Name" 
                            required
                            class="form-input"
                        >
                        <input 
                            type="email" 
                            x-model="guestEmail" 
                            placeholder="Your Email" 
                            required
                            class="form-input"
                        >
                    </div>
                </template>

                <!-- Comment Content -->
                <textarea 
                    x-model="newComment.content"
                    placeholder="Write your comment..."
                    rows="4"
                    required
                    class="form-textarea"
                    :disabled="submitting"
                ></textarea>

                <!-- Custom Fields -->
                <template x-for="field in customFields" :key="field.name">
                    <div class="custom-field">
                        <label x-text="field.label"></label>
                        <input 
                            :type="field.type || 'text'"
                            :placeholder="field.placeholder"
                            x-model="newComment.custom_data[field.name]"
                            :required="field.required"
                            class="form-input"
                        >
                    </div>
                </template>

                <div class="form-actions">
                    <button 
                        type="submit" 
                        :disabled="submitting || !newComment.content.trim()"
                        class="btn btn-primary"
                    >
                        <span x-show="!submitting">Post Comment</span>
                        <span x-show="submitting">Posting...</span>
                    </button>
                </div>
            </form>
        </template>
    </div>

    <!-- Comments List -->
    <div class="webbloc-comments-list">
        <template x-if="loading && comments.length === 0">
            <div class="loading-state">
                <div class="spinner"></div>
                <p>Loading comments...</p>
            </div>
        </template>

        <template x-if="!loading && comments.length === 0">
            <div class="empty-state">
                <p>No comments yet. Be the first to comment!</p>
            </div>
        </template>

        <template x-for="comment in comments" :key="comment.id">
            <div class="comment-item" :class="{ 'comment-reply': comment.parent_id }">
                <div class="comment-avatar">
                    <img 
                        :src="comment.user?.avatar || '/images/default-avatar.png'" 
                        :alt="comment.user?.name || comment.guest_name"
                        class="avatar"
                    >
                </div>
                
                <div class="comment-content">
                    <div class="comment-header">
                        <span class="comment-author" x-text="comment.user?.name || comment.guest_name"></span>
                        <span class="comment-date" x-text="formatDate(comment.created_at)"></span>
                        
                        <template x-if="canModerate(comment)">
                            <div class="comment-actions">
                                <button @click="editComment(comment)" class="btn-sm">Edit</button>
                                <button @click="deleteComment(comment)" class="btn-sm btn-danger">Delete</button>
                            </div>
                        </template>
                    </div>

                    <div class="comment-text" x-html="comment.content"></div>

                    <!-- Custom Fields Display -->
                    <template x-if="comment.custom_data">
                        <div class="comment-custom-data">
                            <template x-for="(value, key) in comment.custom_data" :key="key">
                                <span class="custom-tag" x-text="`${key}: ${value}`"></span>
                            </template>
                        </div>
                    </template>

                    <div class="comment-footer">
                        <button 
                            @click="toggleLike(comment)" 
                            class="like-btn"
                            :class="{ 'liked': comment.user_liked }"
                        >
                            ❤️ <span x-text="comment.likes_count || 0"></span>
                        </button>

                        <template x-if="allowReplies && !comment.parent_id">
                            <button 
                                @click="replyTo(comment)" 
                                class="reply-btn"
                            >
                                Reply
                            </button>
                        </template>
                    </div>

                    <!-- Reply Form -->
                    <template x-if="replyingTo === comment.id">
                        <div class="reply-form">
                            <textarea 
                                x-model="replyContent"
                                placeholder="Write your reply..."
                                rows="3"
                                class="form-textarea"
                            ></textarea>
                            <div class="form-actions">
                                <button @click="submitReply(comment.id)" class="btn btn-sm btn-primary">Reply</button>
                                <button @click="cancelReply()" class="btn btn-sm">Cancel</button>
                            </div>
                        </div>
                    </template>

                    <!-- Replies -->
                    <template x-if="comment.replies && comment.replies.length > 0">
                        <div class="comment-replies">
                            <template x-for="reply in comment.replies" :key="reply.id">
                                <div class="comment-item comment-reply">
                                    <div class="comment-avatar">
                                        <img 
                                            :src="reply.user?.avatar || '/images/default-avatar.png'" 
                                            :alt="reply.user?.name || reply.guest_name"
                                            class="avatar"
                                        >
                                    </div>
                                    <div class="comment-content">
                                        <div class="comment-header">
                                            <span class="comment-author" x-text="reply.user?.name || reply.guest_name"></span>
                                            <span class="comment-date" x-text="formatDate(reply.created_at)"></span>
                                        </div>
                                        <div class="comment-text" x-html="reply.content"></div>
                                        <div class="comment-footer">
                                            <button 
                                                @click="toggleLike(reply)" 
                                                class="like-btn"
                                                :class="{ 'liked': reply.user_liked }"
                                            >
                                                ❤️ <span x-text="reply.likes_count || 0"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <!-- Load More -->
        <template x-if="hasMore">
            <div class="load-more">
                <button @click="loadMore()" :disabled="loading" class="btn btn-outline">
                    <span x-show="!loading">Load More Comments</span>
                    <span x-show="loading">Loading...</span>
                </button>
            </div>
        </template>
    </div>

    <!-- Success/Error Messages -->
    <div x-show="message" :class="messageType" class="webbloc-message" x-transition>
        <span x-text="message"></span>
    </div>
</div>

<style>
.webbloc-comments-container {
    max-width: 800px;
    margin: 0 auto;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.webbloc-comments-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e5e7eb;
}

.comments-title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
    color: #111827;
}

.comments-sort select {
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: white;
}

.comment-form {
    background: #f9fafb;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
}

.guest-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 15px;
}

.form-input, .form-textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.form-input:focus, .form-textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.comment-item {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f3f4f6;
}

.comment-reply {
    margin-left: 40px;
    border-left: 3px solid #e5e7eb;
    padding-left: 15px;
}

.comment-avatar .avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.comment-content {
    flex: 1;
}

.comment-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 8px;
}

.comment-author {
    font-weight: 600;
    color: #111827;
}

.comment-date {
    font-size: 12px;
    color: #6b7280;
}

.comment-text {
    color: #374151;
    line-height: 1.6;
    margin-bottom: 12px;
}

.comment-footer {
    display: flex;
    gap: 15px;
    align-items: center;
}

.like-btn, .reply-btn {
    background: none;
    border: none;
    color: #6b7280;
    font-size: 14px;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 4px;
    transition: all 0.2s;
}

.like-btn:hover, .reply-btn:hover {
    background: #f3f4f6;
    color: #374151;
}

.like-btn.liked {
    color: #ef4444;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #2563eb;
}

.btn-primary:disabled {
    background: #9ca3af;
    cursor: not-allowed;
}

.loading-state, .empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #6b7280;
}

.spinner {
    width: 24px;
    height: 24px;
    border: 2px solid #e5e7eb;
    border-top: 2px solid #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 10px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.webbloc-message {
    padding: 12px 16px;
    border-radius: 6px;
    margin-top: 15px;
    font-size: 14px;
}

.webbloc-message.success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.webbloc-message.error {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

@media (max-width: 640px) {
    .webbloc-comments-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .guest-info {
        grid-template-columns: 1fr;
    }
    
    .comment-reply {
        margin-left: 20px;
    }
}
</style>
```

## 3. Reviews Component (`resources/views/components/webbloc/reviews.blade.php`)

```php
@props([
    'website_id' => null,
    'api_key' => null,
    'page_url' => request()->fullUrl(),
    'theme' => 'default',
    'limit' => 10,
    'sort' => 'newest',
    'show_rating_summary' => true,
    'allow_images' => false,
    'require_purchase' => false,
    'custom_fields' => [],
    'rating_scale' => 5
])

<div 
    w2030b="reviews"
    data-website-id="{{ $website_id }}"
    data-api-key="{{ $api_key }}"
    data-page-url="{{ $page_url }}"
    data-theme="{{ $theme }}"
    data-limit="{{ $limit }}"
    data-sort="{{ $sort }}"
    data-show-rating-summary="{{ $show_rating_summary ? 'true' : 'false' }}"
    data-allow-images="{{ $allow_images ? 'true' : 'false' }}"
    data-require-purchase="{{ $require_purchase ? 'true' : 'false' }}"
    data-custom-fields="{{ json_encode($custom_fields) }}"
    data-rating-scale="{{ $rating_scale }}"
    x-data="webBlocReviews()"
    x-init="init()"
    {{ $attributes->merge(['class' => 'webbloc-reviews-container']) }}
>
    <!-- Reviews Summary -->
    <template x-if="showRatingSummary && (reviews.length > 0 || loading)">
        <div class="reviews-summary">
            <div class="overall-rating">
                <div class="rating-score">
                    <span class="score" x-text="averageRating.toFixed(1)"></span>
                    <div class="stars" x-html="renderStars(averageRating)"></div>
                    <span class="total-reviews" x-text="`${totalReviews} review${totalReviews !== 1 ? 's' : ''}`"></span>
                </div>
            </div>

            <div class="rating-breakdown">
                <template x-for="rating in [5,4,3,2,1]" :key="rating">
                    <div class="rating-bar">
                        <span class="rating-label" x-text="`${rating} star${rating !== 1 ? 's' : ''}`"></span>
                        <div class="bar-container">
                            <div 
                                class="bar-fill" 
                                :style="`width: ${getRatingPercentage(rating)}%`"
                            ></div>
                        </div>
                        <span class="rating-count" x-text="getRatingCount(rating)"></span>
                    </div>
                </template>
            </div>
        </div>
    </template>

    <!-- Reviews Header -->
    <div class="reviews-header">
        <h3 class="reviews-title" x-text="`Customer Reviews (${totalReviews})`"></h3>
        <div class="reviews-controls">
            <div class="sort-control">
                <label>Sort by:</label>
                <select x-model="sortBy" @change="loadReviews()">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="highest">Highest Rated</option>
                    <option value="lowest">Lowest Rated</option>
                    <option value="helpful">Most Helpful</option>
                </select>
            </div>
            
            <div class="filter-control">
                <label>Filter:</label>
                <select x-model="filterRating" @change="loadReviews()">
                    <option value="">All Ratings</option>
                    <option value="5">5 Stars</option>
                    <option value="4">4 Stars</option>
                    <option value="3">3 Stars</option>
                    <option value="2">2 Stars</option>
                    <option value="1">1 Star</option>
                </select>
            </div>

            <button @click="showReviewForm = !showReviewForm" class="btn btn-primary">
                Write Review
            </button>
        </div>
    </div>

    <!-- Review Form -->
    <div class="review-form-container" x-show="showReviewForm" x-transition>
        <template x-if="!user">
            <div class="auth-required">
                <p>Please <button @click="showLogin = true" class="link-btn">sign in</button> to write a review.</p>
            </div>
        </template>

        <template x-if="user">
            <form @submit.prevent="submitReview()" class="review-form">
                <h4>Write Your Review</h4>
                
                <!-- Rating Selection -->
                <div class="rating-input">
                    <label>Your Rating:</label>
                    <div class="star-rating-input">
                        <template x-for="star in ratingScale" :key="star">
                            <button
                                type="button"
                                @click="newReview.rating = star"
                                @mouseover="hoverRating = star"
                                @mouseleave="hoverRating = 0"
                                class="star-btn"
                                :class="{ 
                                    'active': star <= (hoverRating || newReview.rating),
                                    'hover': star <= hoverRating && hoverRating > newReview.rating
                                }"
                            >
                                ★
                            </button>
                        </template>
                        <span class="rating-text" x-text="getRatingText(newReview.rating)"></span>
                    </div>
                </div>

                <!-- Review Title -->
                <div class="field-group">
                    <label for="review-title">Review Title:</label>
                    <input 
                        type="text" 
                        id="review-title"
                        x-model="newReview.title"
                        placeholder="Summarize your experience"
                        required
                        class="form-input"
                    >
                </div>

                <!-- Review Content -->
                <div class="field-group">
                    <label for="review-content">Your Review:</label>
                    <textarea 
                        id="review-content"
                        x-model="newReview.content"
                        placeholder="Tell others about your experience..."
                        rows="5"
                        required
                        class="form-textarea"
                        :disabled="submitting"
                    ></textarea>
                </div>

                <!-- Custom Fields -->
                <template x-for="field in customFields" :key="field.name">
                    <div class="field-group">
                        <label x-text="field.label"></label>
                        <input 
                            :type="field.type || 'text'"
                            :placeholder="field.placeholder"
                            x-model="newReview.custom_data[field.name]"
                            :required="field.required"
                            class="form-input"
                        >
                    </div>
                </template>

                <!-- Image Upload -->
                <template x-if="allowImages">
                    <div class="field-group">
                        <label>Add Photos:</label>
                        <input 
                            type="file"
                            @change="handleImageUpload($event)"
                            multiple
                            accept="image/*"
                            class="file-input"
                        >
                        <div class="image-preview" x-show="newReview.images.length > 0">
                            <template x-for="(image, index) in newReview.images" :key="index">
                                <div class="preview-item">
                                    <img :src="image.preview" alt="Review image">
                                    <button type="button" @click="removeImage(index)" class="remove-btn">×</button>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Verification -->
                <template x-if="requirePurchase">
                    <div class="field-group">
                        <label class="checkbox-label">
                            <input 
                                type="checkbox" 
                                x-model="newReview.verified_purchase"
                                required
                            >
                            I confirm I purchased this product/service
                        </label>
                    </div>
                </template>

                <div class="form-actions">
                    <button 
                        type="submit" 
                        :disabled="submitting || !newReview.rating || !newReview.title || !newReview.content"
                        class="btn btn-primary"
                    >
                        <span x-show="!submitting">Submit Review</span>
                        <span x-show="submitting">Submitting...</span>
                    </button>
                    <button type="button" @click="cancelReview()" class="btn btn-outline">
                        Cancel
                    </button>
                </div>
            </form>
        </template>
    </div>

    <!-- Reviews List -->
    <div class="reviews-list">
        <template x-if="loading && reviews.length === 0">
            <div class="loading-state">
                <div class="spinner"></div>
                <p>Loading reviews...</p>
            </div>
        </template>

        <template x-if="!loading && reviews.length === 0">
            <div class="empty-state">
                <p>No reviews yet. Be the first to review!</p>
            </div>
        </template>

        <template x-for="review in reviews" :key="review.id">
            <div class="review-item">
                <div class="review-header">
                    <div class="reviewer-info">
                        <img 
                            :src="review.user?.avatar || '/images/default-avatar.png'" 
                            :alt="review.user?.name"
                            class="reviewer-avatar"
                        >
                        <div class="reviewer-details">
                            <span class="reviewer-name" x-text="review.user?.name"></span>
                            <span class="review-date" x-text="formatDate(review.created_at)"></span>
                            <template x-if="review.verified_purchase">
                                <span class="verified-badge">✓ Verified Purchase</span>
                            </template>
                        </div>
                    </div>

                    <div class="review-rating">
                        <div class="stars" x-html="renderStars(review.rating)"></div>
                        <span class="rating-value" x-text="review.rating"></span>
                    </div>
                </div>

                <div class="review-content">
                    <h4 class="review-title" x-text="review.title"></h4>
                    <p class="review-text" x-text="review.content"></p>

                    <!-- Custom Fields Display -->
                    <template x-if="review.custom_data && Object.keys(review.custom_data).length > 0">
                        <div class="review-custom-data">
                            <template x-for="(value, key) in review.custom_data" :key="key">
                                <span class="custom-tag">
                                    <strong x-text="key + ':'"></strong>
                                    <span x-text="value"></span>
                                </span>
                            </template>
                        </div>
                    </template>

                    <!-- Images -->
                    <template x-if="review.images && review.images.length > 0">
                        <div class="review-images">
                            <template x-for="image in review.images" :key="image.id">
                                <img 
                                    :src="image.url" 
                                    :alt="'Review image'"
                                    class="review-image"
                                    @click="openImageModal(image)"
                                >
                            </template>
                        </div>
                    </template>
                </div>

                <div class="review-footer">
                    <button 
                        @click="toggleHelpful(review)" 
                        class="helpful-btn"
                        :class="{ 'active': review.user_found_helpful }"
                    >
                        👍 Helpful (<span x-text="review.helpful_count || 0"></span>)
                    </button>

                    <template x-if="canModerate(review)">
                        <div class="review-actions">
                            <button @click="editReview(review)" class="btn-sm">Edit</button>
                            <button @click="deleteReview(review)" class="btn-sm btn-danger">Delete</button>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <!-- Load More -->
        <template x-if="hasMore">
            <div class="load-more">
                <button @click="loadMore()" :disabled="loading" class="btn btn-outline">
                    <span x-show="!loading">Load More Reviews</span>
                    <span x-show="loading">Loading...</span>
                </button>
            </div>
        </template>
    </div>

    <!-- Success/Error Messages -->
    <div x-show="message" :class="messageType" class="webbloc-message" x-transition>
        <span x-text="message"></span>
    </div>
</div>

<style>
.webbloc-reviews-container {
    max-width: 900px;
    margin: 0 auto;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.reviews-summary {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 30px;
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 30px;
}

.overall-rating {
    text-align: center;
}

.rating-score .score {
    font-size: 3rem;
    font-weight: 700;
    color: #1f2937;
    display: block;
}

.stars {
    margin: 8px 0;
    font-size: 1.2rem;
}

.star-filled {
    color: #fbbf24;
}

.star-empty {
    color: #d1d5db;
}

.total-reviews {
    color: #6b7280;
    font-size: 0.9rem;
}

.rating-breakdown {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.rating-bar {
    display: grid;
    grid-template-columns: 80px 1fr 40px;
    align-items: center;
    gap: 12px;
    font-size: 14px;
}

.rating-label {
    color: #6b7280;
}

.bar-container {
    height: 8px;
    background: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
}

.bar-fill {
    height: 100%;
    background: #fbbf24;
    transition: width 0.3s ease;
}

.rating-count {
    text-align: right;
    color: #6b7280;
    font-size: 12px;
}

.reviews-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}

.reviews-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.reviews-controls {
    display: flex;
    gap: 15px;
    align-items: center;
    flex-wrap: wrap;
}

.sort-control, .filter-control {
    display: flex;
    align-items: center;
    gap: 8px;
}

.sort-control select, .filter-control select {
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: white;
    font-size: 14px;
}

.review-form {
    background: #ffffff;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 30px;
}

.review-form h4 {
    margin: 0 0 20px 0;
    font-size: 1.25rem;
    color: #111827;
}

.rating-input {
    margin-bottom: 20px;
}

.star-rating-input {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 8px;
}

.star-btn {
    background: none;
    border: none;
    font-size: 2rem;
    color: #d1d5db;
    cursor: pointer;
    transition: color 0.2s;
    padding: 0;
}

.star-btn.active, .star-btn.hover {
    color: #fbbf24;
}

.rating-text {
    margin-left: 10px;
    color: #6b7280;
    font-weight: 500;
}

.field-group {
    margin-bottom: 20px;
}

.field-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #374151;
}

.form-input, .form-textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.form-input:focus, .form-textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.image-preview {
    display: flex;
    gap: 10px;
    margin-top: 10px;
    flex-wrap: wrap;
}

.preview-item {
    position: relative;
}

.preview-item img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 6px;
}

.remove-btn {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ef4444;
    color: white;
    border: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    font-size: 14px;
    cursor: pointer;
}

.review-item {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 20px;
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
}

.reviewer-info {
    display: flex;
    gap: 12px;
    align-items: center;
}

.reviewer-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
}

.reviewer-name {
    font-weight: 600;
    color: #111827;
    display: block;
}

.review-date {
    font-size: 12px;
    color: #6b7280;
    display: block;
}

.verified-badge {
    font-size: 12px;
    color: #059669;
    background: #d1fae5;
    padding: 2px 6px;
    border-radius: 4px;
    display: inline-block;
    margin-top: 4px;
}

.review-rating {
    display: flex;
    align-items: center;
    gap: 8px;
}

.review-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 8px 0;
}

.review-text {
    color: #374151;
    line-height: 1.6;
    margin-bottom: 16px;
}

.review-images {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}

.review-image {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 6px;
    cursor: pointer;
    transition: transform 0.2s;
}

.review-image:hover {
    transform: scale(1.05);
}

.review-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.helpful-btn {
    background: none;
    border: 1px solid #d1d5db;
    padding: 8px 12px;
    border-radius: 6px;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s;
}

.helpful-btn:hover, .helpful-btn.active {
    background: #f3f4f6;
    color: #059669;
    border-color: #059669;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #2563eb;
}

.btn-outline {
    background: transparent;
    border: 1px solid #d1d5db;
    color: #374151;
}

.btn-outline:hover {
    background: #f9fafb;
}

@media (max-width: 768px) {
    .reviews-summary {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .reviews-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .reviews-controls {
        width: 100%;
        justify-content: flex-start;
    }
    
    .review-header {
        flex-direction: column;
        gap: 12px;
    }
}
</style>
```

## 4. Notifications Component (`resources/views/components/webbloc/notifications.blade.php`)

```php
@props([
    'website_id' => null,
    'api_key' => null,
    'theme' => 'default',
    'position' => 'top-right',
    'auto_dismiss' => true,
    'dismiss_timeout' => 5000,
    'max_notifications' => 5,
    'show_timestamps' => true,
    'enable_sound' => false
])

<div 
    w2030b="notifications"
    data-website-id="{{ $website_id }}"
    data-api-key="{{ $api_key }}"
    data-theme="{{ $theme }}"
    data-position="{{ $position }}"
    data-auto-dismiss="{{ $auto_dismiss ? 'true' : 'false' }}"
    data-dismiss-timeout="{{ $dismiss_timeout }}"
    data-max-notifications="{{ $max_notifications }}"
    data-show-timestamps="{{ $show_timestamps ? 'true' : 'false' }}"
    data-enable-sound="{{ $enable_sound ? 'true' : 'false' }}"
    x-data="webBlocNotifications()"
    x-init="init()"
    {{ $attributes->merge(['class' => 'webbloc-notifications-container']) }}
>
    <!-- Notifications Container -->
    <div 
        class="notifications-wrapper"
        :class="`notifications-${position}`"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-90"
        x-transition:enter-end="opacity-100 transform scale-100"
    >
        <template x-for="notification in notifications" :key="notification.id">
            <div 
                class="notification-item"
                :class="[
                    `notification-${notification.type}`,
                    { 'notification-dismissible': notification.dismissible !== false }
                ]"
                x-show="notification.visible"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-2"
                @click="handleNotificationClick(notification)"
            >
                <!-- Notification Icon -->
                <div class="notification-icon">
                    <template x-if="notification.type === 'success'">
                        <svg class="icon icon-success" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </template>
                    
                    <template x-if="notification.type === 'error'">
                        <svg class="icon icon-error" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </template>
                    
                    <template x-if="notification.type === 'warning'">
                        <svg class="icon icon-warning" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </template>
                    
                    <template x-if="notification.type === 'info'">
                        <svg class="icon icon-info" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </template>

                    <template x-if="notification.custom_icon">
                        <div x-html="notification.custom_icon"></div>
                    </template>
                </div>

                <!-- Notification Content -->
                <div class="notification-content">
                    <template x-if="notification.title">
                        <h4 class="notification-title" x-text="notification.title"></h4>
                    </template>
                    
                    <div class="notification-message" x-text="notification.message"></div>
                    
                    <!-- Custom Actions -->
                    <template x-if="notification.actions && notification.actions.length > 0">
                        <div class="notification-actions">
                            <template x-for="action in notification.actions" :key="action.id">
                                <button 
                                    @click="handleAction(notification, action)"
                                    :class="action.class || 'btn-action-default'"
                                    class="notification-action-btn"
                                >
                                    <span x-text="action.label"></span>
                                </button>
                            </template>
                        </div>
                    </template>
                    
                    <!-- Timestamp -->
                    <template x-if="showTimestamps">
                        <div class="notification-timestamp">
                            <span x-text="formatTimestamp(notification.created_at)"></span>
                        </div>
                    </template>
                </div>

                <!-- Progress Bar for Auto-dismiss -->
                <template x-if="notification.auto_dismiss && notification.progress !== undefined">
                    <div class="notification-progress">
                        <div 
                            class="progress-bar" 
                            :style="`width: ${notification.progress}%`"
                        ></div>
                    </div>
                </template>

                <!-- Close Button -->
                <template x-if="notification.dismissible !== false">
                    <button 
                        @click.stop="dismissNotification(notification.id)"
                        class="notification-close"
                        aria-label="Close notification"
                    >
                        <svg class="close-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </template>
            </div>
        </template>
    </div>

    <!-- Notification Bell/Indicator (Optional) -->
    <div class="notification-indicator" x-show="showIndicator" @click="toggleNotificationCenter()">
        <svg class="bell-icon" viewBox="0 0 20 20" fill="currentColor">
            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
        </svg>
        <template x-if="unreadCount > 0">
            <span class="notification-badge" x-text="unreadCount"></span>
        </template>
    </div>

    <!-- Notification Center Modal -->
    <template x-if="showCenter">
        <div class="notification-center-overlay" @click="closeNotificationCenter()">
            <div class="notification-center" @click.stop>
                <div class="notification-center-header">
                    <h3>Notifications</h3>
                    <div class="center-actions">
                        <button @click="markAllAsRead()" class="btn-sm">Mark All Read</button>
                        <button @click="clearAll()" class="btn-sm">Clear All</button>
                        <button @click="closeNotificationCenter()" class="btn-close">×</button>
                    </div>
                </div>

                <div class="notification-center-body">
                    <template x-if="allNotifications.length === 0">
                        <div class="empty-state">
                            <p>No notifications yet</p>
                        </div>
                    </template>

                    <template x-for="notification in allNotifications" :key="notification.id">
                        <div 
                            class="center-notification-item"
                            :class="{ 'unread': !notification.read }"
                            @click="markAsRead(notification.id)"
                        >
                            <div class="center-notification-icon">
                                <div :class="`icon-${notification.type}`">
                                    <span x-text="getTypeIcon(notification.type)"></span>
                                </div>
                            </div>
                            <div class="center-notification-content">
                                <div class="center-notification-title" x-text="notification.title"></div>
                                <div class="center-notification-message" x-text="notification.message"></div>
                                <div class="center-notification-time" x-text="formatRelativeTime(notification.created_at)"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>

    <!-- Audio element for notification sounds -->
    <template x-if="enableSound">
        <audio x-ref="notificationSound" preload="auto">
            <source src="/sounds/notification.mp3" type="audio/mpeg">
            <source src="/sounds/notification.ogg" type="audio/ogg">
        </audio>
    </template>
</div>

<style>
.webbloc-notifications-container {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    z-index: 9999;
}

.notifications-wrapper {
    position: fixed;
    pointer-events: none;
    z-index: 10000;
}

.notifications-top-right {
    top: 20px;
    right: 20px;
    max-width: 400px;
}

.notifications-top-left {
    top: 20px;
    left: 20px;
    max-width: 400px;
}

.notifications-bottom-right {
    bottom: 20px;
    right: 20px;
    max-width: 400px;
}

.notifications-bottom-left {
    bottom: 20px;
    left: 20px;
    max-width: 400px;
}

.notifications-top-center {
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    max-width: 400px;
}

.notifications-bottom-center {
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    max-width: 400px;
}

.notification-item {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    margin-bottom: 12px;
    padding: 16px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    pointer-events: auto;
    border-left: 4px solid #e5e7eb;
    position: relative;
    cursor: pointer;
    transition: all 0.2s ease;
}

.notification-item:hover {
    transform: translateX(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

.notification-success {
    border-left-color: #10b981;
}

.notification-error {
    border-left-color: #ef4444;
}

.notification-warning {
    border-left-color: #f59e0b;
}

.notification-info {
    border-left-color: #3b82f6;
}

.notification-icon {
    flex-shrink: 0;
    width: 24px;
    height: 24px;
}

.icon {
    width: 100%;
    height: 100%;
}

.icon-success {
    color: #10b981;
}

.icon-error {
    color: #ef4444;
}

.icon-warning {
    color: #f59e0b;
}

.icon-info {
    color: #3b82f6;
}

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-title {
    font-weight: 600;
    font-size: 14px;
    color: #111827;
    margin: 0 0 4px 0;
    line-height: 1.4;
}

.notification-message {
    font-size: 14px;
    color: #374151;
    line-height: 1.4;
    margin-bottom: 8px;
}

.notification-actions {
    display: flex;
    gap: 8px;
    margin-top: 8px;
}

.notification-action-btn {
    padding: 4px 12px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    background: white;
    color: #374151;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.notification-action-btn:hover {
    background: #f9fafb;
    border-color: #9ca3af;
}

.btn-action-default {
    background: #f3f4f6;
    border-color: #d1d5db;
}

.notification-timestamp {
    font-size: 11px;
    color: #9ca3af;
    margin-top: 4px;
}

.notification-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: rgba(0, 0, 0, 0.1);
    border-radius: 0 0 8px 8px;
}

.progress-bar {
    height: 100%;
    background: #3b82f6;
    transition: width 0.1s linear;
    border-radius: 0 0 8px 8px;
}

.notification-close {
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    padding: 0;
    width: 16px;
    height: 16px;
    flex-shrink: 0;
    transition: color 0.2s;
}

.notification-close:hover {
    color: #374151;
}

.close-icon {
    width: 100%;
    height: 100%;
}

.notification-indicator {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #3b82f6;
    color: white;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.2s;
    z-index: 9998;
}

.notification-indicator:hover {
    background: #2563eb;
    transform: scale(1.05);
}

.bell-icon {
    width: 24px;
    height: 24px;
}

.notification-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    background: #ef4444;
    color: white;
    font-size: 10px;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 10px;
    min-width: 18px;
    text-align: center;
    line-height: 1.2;
}

.notification-center-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 10001;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.notification-center {
    background: white;
    border-radius: 12px;
    width: 100%;
    max-width: 500px;
    max-height: 600px;
    display: flex;
    flex-direction: column;
}

.notification-center-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.notification-center-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #111827;
}

.center-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    background: white;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-sm:hover {
    background: #f9fafb;
}

.btn-close {
    background: none;
    border: none;
    font-size: 20px;
    color: #9ca3af;
    cursor: pointer;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.notification-center-body {
    flex: 1;
    overflow-y: auto;
    padding: 0;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #9ca3af;
}

.center-notification-item {
    display: flex;
    gap: 12px;
    padding: 16px 20px;
    border-bottom: 1px solid #f3f4f6;
    cursor: pointer;
    transition: background-color 0.2s;
}

.center-notification-item:hover {
    background: #f9fafb;
}

.center-notification-item.unread {
    background: #eff6ff;
    border-left: 3px solid #3b82f6;
}

.center-notification-icon {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.icon-success {
    background: #dcfce7;
    color: #16a34a;
}

.icon-error {
    background: #fee2e2;
    color: #dc2626;
}

.icon-warning {
    background: #fef3c7;
    color: #d97706;
}

.icon-info {
    background: #dbeafe;
    color: #2563eb;
}

.center-notification-content {
    flex: 1;
    min-width: 0;
}

.center-notification-title {
    font-weight: 500;
    font-size: 14px;
    color: #111827;
    margin-bottom: 2px;
}

.center-notification-message {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.4;
    margin-bottom: 4px;
}

.center-notification-time {
    font-size: 11px;
    color: #9ca3af;
}

@media (max-width: 640px) {
    .notifications-top-right,
    .notifications-top-left,
    .notifications-bottom-right,
    .notifications-bottom-left,
    .notifications-top-center,
    .notifications-bottom-center {
        left: 10px;
        right: 10px;
        max-width: none;
        transform: none;
    }
    
    .notification-center {
        margin: 10px;
        max-height: calc(100vh - 20px);
    }
}
</style>
```

## 5. Core Alpine.js WebBloc Functionality (`resources/js/webbloc-core.js`)

```javascript
// WebBloc Core - Alpine.js Integration
window.webBlocCore = {
    // Configuration
    config: {
        apiBaseUrl: '/api/webblocs',
        defaultHeaders: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        retryAttempts: 3,
        retryDelay: 1000,
        cacheTimeout: 300000 // 5 minutes
    },

    // Cache management
    cache: new Map(),

    // Global state
    state: {
        user: null,
        notifications: [],
        isInitialized: false
    },

    // Initialize WebBloc system
    async init() {
        if (this.state.isInitialized) return;
        
        try {
            // Initialize SweetAlert2 if available
            if (typeof Swal !== 'undefined') {
                this.initSweetAlert();
            }

            // Load user session if available
            await this.loadUserSession();
            
            // Initialize notification system
            this.initNotifications();
            
            this.state.isInitialized = true;
            this.log('WebBloc Core initialized successfully');
        } catch (error) {
            this.log('Failed to initialize WebBloc Core:', error);
        }
    },

    // API Request handler with retry logic
    async apiRequest(endpoint, options = {}) {
        const { retryAttempts, retryDelay } = this.config;
        let lastError;

        for (let attempt = 0; attempt < retryAttempts; attempt++) {
            try {
                const response = await fetch(endpoint, {
                    ...options,
                    headers: {
                        ...this.config.defaultHeaders,
                        ...options.headers
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const contentType = response.headers.get('content-type');
                
                // Handle different response formats (75% HTML, 15% JSON, 10% other)
                if (contentType?.includes('application/json')) {
                    return await response.json();
                } else if (contentType?.includes('text/html')) {
                    return await response.text();
                } else {
                    return await response.blob();
                }
            } catch (error) {
                lastError = error;
                if (attempt < retryAttempts - 1) {
                    await this.delay(retryDelay * (attempt + 1));
                }
            }
        }

        throw lastError;
    },

    // Get API endpoint with proper authentication
    getApiEndpoint(websiteId, apiKey, type, action = '', id = '') {
        let endpoint = `${this.config.apiBaseUrl}/${type}`;
        
        if (id) {
            endpoint += `/${id}`;
        }
        if (action) {
            endpoint += `/${action}`;
        }

        const params = new URLSearchParams({
            website_id: websiteId,
            api_key: apiKey
        });

        return `${endpoint}?${params.toString()}`;
    },

    // Cache management methods
    getCacheKey(key, params = {}) {
        return `webbloc_${key}_${JSON.stringify(params)}`;
    },

    setCache(key, data, timeout = null) {
        const expiry = timeout || this.config.cacheTimeout;
        this.cache.set(key, {
            data,
            timestamp: Date.now(),
            expiry: Date.now() + expiry
        });
    },

    getCache(key) {
        const cached = this.cache.get(key);
        if (!cached) return null;
        
        if (Date.now() > cached.expiry) {
            this.cache.delete(key);
            return null;
        }
        
        return cached.data;
    },

    clearCache(pattern = null) {
        if (pattern) {
            for (const [key] of this.cache) {
                if (key.includes(pattern)) {
                    this.cache.delete(key);
                }
            }
        } else {
            this.cache.clear();
        }
    },

    // User session management
    async loadUserSession() {
        try {
            const token = localStorage.getItem('webbloc_token');
            if (!token) return null;

            // Validate token and get user info
            const response = await this.apiRequest('/api/auth/me', {
                headers: { 'Authorization': `Bearer ${token}` }
            });

            this.state.user = response.user || response;
            return this.state.user;
        } catch (error) {
            // Clear invalid token
            localStorage.removeItem('webbloc_token');
            this.state.user = null;
            return null;
        }
    },

    setUser(user, token = null) {
        this.state.user = user;
        if (token) {
            localStorage.setItem('webbloc_token', token);
        }
    },

    clearUser() {
        this.state.user = null;
        localStorage.removeItem('webbloc_token');
    },

    // Notification system
    initNotifications() {
        // Initialize global notification handler
        window.webBlocNotify = this.notify.bind(this);
    },

    notify(message, type = 'info', options = {}) {
        const notification = {
            id: Date.now() + Math.random(),
            message,
            type,
            timestamp: Date.now(),
            ...options
        };

        this.state.notifications.unshift(notification);

        // Use SweetAlert2 if available
        if (typeof Swal !== 'undefined') {
            this.showSweetAlert(notification);
        }

        // Dispatch custom event for component integration
        window.dispatchEvent(new CustomEvent('webbloc-notification', {
            detail: notification
        }));

        return notification;
    },

    initSweetAlert() {
        // Configure SweetAlert2 default settings
        if (typeof Swal !== 'undefined') {
            Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
        }
    },

    showSweetAlert(notification) {
        if (typeof Swal === 'undefined') return;

        const iconMap = {
            success: 'success',
            error: 'error',
            warning: 'warning',
            info: 'info'
        };

        Swal.fire({
            icon: iconMap[notification.type] || 'info',
            title: notification.title || notification.message,
            text: notification.title ? notification.message : '',
            timer: notification.timer || 3000
        });
    },

    // Utility methods
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    },

    formatDate(date) {
        if (!date) return '';
        
        const d = new Date(date);
        const now = new Date();
        const diff = now - d;
        
        // Less than 1 minute
        if (diff < 60000) {
            return 'Just now';
        }
        
        // Less than 1 hour
        if (diff < 3600000) {
            const minutes = Math.floor(diff / 60000);
            return `${minutes} minute${minutes !== 1 ? 's' : ''} ago`;
        }
        
        // Less than 1 day
        if (diff < 86400000) {
            const hours = Math.floor(diff / 3600000);
            return `${hours} hour${hours !== 1 ? 's' : ''} ago`;
        }
        
        // More than 1 day
        const options = { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        return d.toLocaleDateString(undefined, options);
    },

    sanitizeHtml(html) {
        const div = document.createElement('div');
        div.textContent = html;
        return div.innerHTML;
    },

    validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },

    generateId() {
        return Date.now().toString(36) + Math.random().toString(36).substr(2);
    },

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        }
    },

    log(...args) {
        if (window.webBlocConfig?.debug) {
            console.log('[WebBloc]', ...args);
        }
    },

    error(...args) {
        console.error('[WebBloc Error]', ...args);
    }
};

// Initialize WebBloc Core when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    webBlocCore.init();
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = webBlocCore;
}
```

## 6. Individual Component Implementations (`resources/js/webbloc-components.js`)

```javascript
// WebBloc Components - Alpine.js Component Implementations

// Base WebBloc Component
function webBlocBase() {
    return {
        // Common properties
        websiteId: null,
        apiKey: null,
        loading: false,
        error: null,
        message: '',
        messageType: 'info',

        // Initialize component
        async init() {
            await this.loadConfig();
            await this.loadData();
        },

        // Load configuration from data attributes
        loadConfig() {
            const el = this.$el;
            this.websiteId = el.dataset.websiteId;
            this.apiKey = el.dataset.apiKey;
            
            if (!this.websiteId || !this.apiKey) {
                this.showError('Missing required configuration: website_id and api_key are required');
                return false;
            }
            return true;
        },

        // Show success message
        showSuccess(message, duration = 3000) {
            this.message = message;
            this.messageType = 'success';
            
            webBlocCore.notify(message, 'success');
            
            if (duration > 0) {
                setTimeout(() => {
                    this.message = '';
                }, duration);
            }
        },

        // Show error message
        showError(message, duration = 5000) {
            this.message = message;
            this.messageType = 'error';
            this.error = message;
            
            webBlocCore.notify(message, 'error');
            
            if (duration > 0) {
                setTimeout(() => {
                    this.message = '';
                    this.error = null;
                }, duration);
            }
        },

        // API request wrapper
        async apiRequest(endpoint, options = {}) {
            this.loading = true;
            this.error = null;

            try {
                return await webBlocCore.apiRequest(endpoint, options);
            } catch (error) {
                this.showError(error.message || 'An error occurred');
                throw error;
            } finally {
                this.loading = false;
            }
        }
    };
}

// Comments Component
function webBlocComments() {
    return {
        ...webBlocBase(),
        
        // Component state
        comments: [],
        totalComments: 0,
        currentPage: 1,
        hasMore: false,
        sortBy: 'newest',
        
        // Form data
        newComment: {
            content: '',
            parent_id: null,
            custom_data: {}
        },
        guestName: '',
        guestEmail: '',
        
        // UI state
        replyingTo: null,
        replyContent: '',
        submitting: false,
        user: null,
        
        // Configuration
        pageUrl: '',
        theme: 'default',
        limit: 10,
        allowReplies: true,
        moderation: false,
        showCount: true,
        guestCommenting: false,
        customFields: [],

        async init() {
            await this.loadConfig();
            this.user = webBlocCore.state.user;
            await this.loadComments();
        },

        loadConfig() {
            if (!webBlocBase().loadConfig.call(this)) return false;
            
            const el = this.$el;
            this.pageUrl = el.dataset.pageUrl || window.location.href;
            this.theme = el.dataset.theme || 'default';
            this.limit = parseInt(el.dataset.limit) || 10;
            this.sortBy = el.dataset.sort || 'newest';
            this.allowReplies = el.dataset.allowReplies === 'true';
            this.moderation = el.dataset.moderation === 'true';
            this.showCount = el.dataset.showCount === 'true';
            this.guestCommenting = el.dataset.guestCommenting === 'true';
            
            try {
                this.customFields = JSON.parse(el.dataset.customFields || '[]');
            } catch (e) {
                this.customFields = [];
            }

            return true;
        },

        get canComment() {
            return this.user || this.guestCommenting;
        },

        async loadComments(append = false) {
            try {
                const endpoint = webBlocCore.getApiEndpoint(
                    this.websiteId,
                    this.apiKey,
                    'comments'
                );

                const params = new URLSearchParams({
                    page_url: this.pageUrl,
                    sort: this.sortBy,
                    page: append ? this.currentPage + 1 : 1,
                    limit: this.limit
                });

                const response = await this.apiRequest(`${endpoint}&${params.toString()}`);
                
                if (append) {
                    this.comments.push(...response.data);
                    this.currentPage++;
                } else {
                    this.comments = response.data;
                    this.currentPage = 1;
                }
                
                this.totalComments = response.total || response.data.length;
                this.hasMore = response.has_more || false;
                
            } catch (error) {
                this.showError('Failed to load comments');
            }
        },

        async submitComment() {
            if (!this.newComment.content.trim()) return;
            
            this.submitting = true;

            try {
                const endpoint = webBlocCore.getApiEndpoint(
                    this.websiteId,
                    this.apiKey,
                    'comments'
                );

                const payload = {
                    content: this.newComment.content,
                    page_url: this.pageUrl,
                    parent_id: this.newComment.parent_id,
                    custom_data: this.newComment.custom_data
                };

                if (!this.user && this.guestCommenting) {
                    payload.guest_name = this.guestName;
                    payload.guest_email = this.guestEmail;
                }

                const response = await this.apiRequest(endpoint, {
                    method: 'POST',
                    body: JSON.stringify(payload)
                });

                // Add new comment to the list
                if (this.newComment.parent_id) {
                    // Find parent and add reply
                    const parent = this.comments.find(c => c.id === this.newComment.parent_id);
                    if (parent) {
                        if (!parent.replies) parent.replies = [];
                        parent.replies.push(response.data);
                    }
                } else {
                    this.comments.unshift(response.data);
                }

                // Reset form
                this.newComment = {
                    content: '',
                    parent_id: null,
                    custom_data: {}
                };
                this.guestName = '';
                this.guestEmail = '';
                
                this.totalComments++;
                this.showSuccess('Comment posted successfully!');
                
            } catch (error) {
                this.showError('Failed to post comment');
            } finally {
                this.submitting = false;
            }
        },

        async toggleLike(comment) {
            if (!this.user) {
                this.showError('Please sign in to like comments');
                return;
            }

            try {
                const endpoint = webBlocCore.getApiEndpoint(
                    this.websiteId,
                    this.apiKey,
                    'comments',
                    'like',
                    comment.id
                );

                const response = await this.apiRequest(endpoint, {
                    method: 'POST'
                });

                comment.user_liked = !comment.user_liked;
                comment.likes_count = response.likes_count || 
                    (comment.likes_count || 0) + (comment.user_liked ? 1 : -1);
                
            } catch (error) {
                this.showError('Failed to update like');
            }
        },

        replyTo(comment) {
            this.replyingTo = comment.id;
            this.replyContent = '';
            this.newComment.parent_id = comment.id;
        },

        cancelReply() {
            this.replyingTo = null;
            this.replyContent = '';
            this.newComment.parent_id = null;
        },

        async submitReply(parentId) {
            if (!this.replyContent.trim()) return;
            
            this.newComment.content = this.replyContent;
            this.newComment.parent_id = parentId;
            
            await this.submitComment();
            this.cancelReply();
        },

        canModerate(comment) {
            return this.user && (
                this.user.id === comment.user_id || 
                this.user.role === 'admin' || 
                this.user.role === 'moderator'
            );
        },

        async deleteComment(comment) {
            if (!confirm('Are you sure you want to delete this comment?')) return;

            try {
                const endpoint = webBlocCore.getApiEndpoint(
                    this.websiteId,
                    this.apiKey,
                    'comments',
                    '',
                    comment.id
                );

                await this.apiRequest(endpoint, {
                    method: 'DELETE'
                });

                // Remove comment from list
                if (comment.parent_id) {
                    const parent = this.comments.find(c => c.id === comment.parent_id);
                    if (parent && parent.replies) {
                        parent.replies = parent.replies.filter(r => r.id !== comment.id);
                    }
                } else {
                    this.comments = this.comments.filter(c => c.id !== comment.id);
                }
                
                this.totalComments--;
                this.showSuccess('Comment deleted successfully');
                
            } catch (error) {
                this.showError('Failed to delete comment');
            }
        },

        async loadMore() {
            await this.loadComments(true);
        },

        formatDate(date) {
            return webBlocCore.formatDate(date);
        }
    };
}

// Reviews Component
function webBlocReviews() {
    return {
        ...webBlocBase(),
        
        // Component state
        reviews: [],
        totalReviews: 0,
        averageRating: 0,
        ratingBreakdown: {},
        currentPage: 1,
        hasMore: false,
        sortBy: 'newest',
        filterRating: '',
        
        // Form data
        newReview: {
            rating: 0,
            title: '',
            content: '',
            images: [],
            custom_data: {},
            verified_purchase: false
        },
        hoverRating: 0,
        
        // UI state
        showReviewForm: false,
        submitting: false,
        user: null,
        
        // Configuration
        pageUrl: '',
        theme: 'default',
        limit: 10,
        showRatingSummary: true,
        allowImages: false,
        requirePurchase: false,
        customFields: [],
        ratingScale: 5,

        async init() {
            await this.loadConfig();
            this.user = webBlocCore.state.user;
            await this.loadReviews();
        },

        loadConfig() {
            if (!webBlocBase().loadConfig.call(this)) return false;
            
            const el = this.$el;
            this.pageUrl = el.dataset.pageUrl || window.location.href;
            this.theme = el.dataset.theme || 'default';
            this.limit = parseInt(el.dataset.limit) || 10;
            this.sortBy = el.dataset.sort || 'newest';
            this.showRatingSummary = el.dataset.showRatingSummary === 'true';
            this.allowImages = el.dataset.allowImages === 'true';
            this.requirePurchase = el.dataset.requirePurchase === 'true';
            this.ratingScale = parseInt(el.dataset.ratingScale) || 5;
            
            try {
                this.customFields = JSON.parse(el.dataset.customFields || '[]');
            } catch (e) {
                this.customFields = [];
            }

            return true;
        },

        async loadReviews(append = false) {
            try {
                const endpoint = webBlocCore.getApiEndpoint(
                    this.websiteId,
                    this.apiKey,
                    'reviews'
                );

                const params = new URLSearchParams({
                    page_url: this.pageUrl,
                    sort: this.sortBy,
                    page: append ? this.currentPage + 1 : 1,
                    limit: this.limit
                });

                if (this.filterRating) {
                    params.append('rating', this.filterRating);
                }

                const response = await this.apiRequest(`${endpoint}&${params.toString()}`);
                
                if (append) {
                    this.reviews.push(...response.data);
                    this.currentPage++;
                } else {
                    this.reviews = response.data;
                    this.currentPage = 1;
                }
                
                this.totalReviews = response.total || response.data.length;
                this.averageRating = response.average_rating || 0;
                this.ratingBreakdown = response.rating_breakdown || {};
                this.hasMore = response.has_more || false;
                
            } catch (error) {
                this.showError('Failed to load reviews');
            }
        },

        async submitReview() {
            if (!this.newReview.rating || !this.newReview.title || !this.newReview.content) {
                this.showError('Please fill in all required fields');
                return;
            }
            
            this.submitting = true;

            try {
                const endpoint = webBlocCore.getApiEndpoint(
                    this.websiteId,
                    this.apiKey,
                    'reviews'
                );

                const response = await this.apiRequest(endpoint, {
                    method: 'POST',
                    body: JSON.stringify({
                        ...this.newReview,
                        page_url: this.pageUrl
                    })
                });

                // Add new review to the list
                this.reviews.unshift(response.data);
                
                // Reset form
                this.newReview = {
                    rating: 0,
                    title: '',
                    content: '',
                    images: [],
                    custom_data: {},
                    verified_purchase: false
                };
                this.showReviewForm = false;
                
                this.totalReviews++;
                this.showSuccess('Review submitted successfully!');
                
                // Refresh to update averages
                await this.loadReviews();
                
            } catch (error) {
                this.showError('Failed to submit review');
            } finally {
                this.submitting = false;
            }
        },

        async toggleHelpful(review) {
            if (!this.user) {
                this.showError('Please sign in to mark reviews as helpful');
                return;
            }

            try {
                const endpoint = webBlocCore.getApiEndpoint(
                    this.websiteId,
                    this.apiKey,
                    'reviews',
                    'helpful',
                    review.id
                );

                const response = await this.apiRequest(endpoint, {
                    method: 'POST'
                });

                review.user_found_helpful = !review.user_found_helpful;
                review.helpful_count = response.helpful_count || 
                    (review.helpful_count || 0) + (review.user_found_helpful ? 1 : -1);
                
            } catch (error) {
                this.showError('Failed to update helpful status');
            }
        },

        renderStars(rating) {
            let stars = '';
            for (let i = 1; i <= this.ratingScale; i++) {
                if (i <= rating) {
                    stars += '<span class="star-filled">★</span>';
                } else {
                    stars += '<span class="star-empty">☆</span>';
                }
            }
            return stars;
        },

        getRatingText(rating) {
            const texts = {
                1: 'Poor',
                2: 'Fair', 
                3: 'Good',
                4: 'Very Good',
                5: 'Excellent'
            };
            return texts[rating] || '';
        },

        getRatingPercentage(rating) {
            if (!this.totalReviews) return 0;
            const count = this.ratingBreakdown[rating] || 0;
            return (count / this.totalReviews) * 100;
        },

        getRatingCount(rating) {
            return this.ratingBreakdown[rating] || 0;
        },

        handleImageUpload(event) {
            const files = Array.from(event.target.files);
            files.forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.newReview.images.push({
                            file,
                            preview: e.target.result
                        });
                    };
                    reader.readAsDataURL(file);
                }
            });
        },

        removeImage(index) {
            this.newReview.images.splice(index, 1);
        },

        cancelReview() {
            this.showReviewForm = false;
            this.newReview = {
                rating: 0,
                title: '',
                content: '',
                images: [],
                custom_data: {},
                verified_purchase: false
            };
        },

        canModerate(review) {
            return this.user && (
                this.user.id === review.user_id || 
                this.user.role === 'admin' || 
                this.user.role === 'moderator'
            );
        },

        async loadMore() {
            await this.loadReviews(true);
        },

        formatDate(date) {
            return webBlocCore.formatDate(date);
        }
    };
}

// Notifications Component
function webBlocNotifications() {
    return {
        ...webBlocBase(),
        
        // Component state
        notifications: [],
        allNotifications: [],
        unreadCount: 0,
        
        // UI state
        showIndicator: true,
        showCenter: false,
        
        // Configuration
        theme: 'default',
        position: 'top-right',
        autoDismiss: true,
        dismissTimeout: 5000,
        maxNotifications: 5,
        showTimestamps: true,
        enableSound: false,

        async init() {
            await this.loadConfig();
            this.initEventListeners();
            await this.loadNotifications();
        },

        loadConfig() {
            if (!webBlocBase().loadConfig.call(this)) return false;
            
            const el = this.$el;
            this.theme = el.dataset.theme || 'default';
            this.position = el.dataset.position || 'top-right';
            this.autoDismiss = el.dataset.autoDismiss === 'true';
            this.dismissTimeout = parseInt(el.dataset.dismissTimeout) || 5000;
            this.maxNotifications = parseInt(el.dataset.maxNotifications) || 5;
            this.showTimestamps = el.dataset.showTimestamps === 'true';
            this.enableSound = el.dataset.enableSound === 'true';

            return true;
        },

        initEventListeners() {
            // Listen for global WebBloc notifications
            window.addEventListener('webbloc-notification', (event) => {
                this.addNotification(event.detail);
            });
        },

        async loadNotifications() {
            if (!this.user) return;

            try {
                const endpoint = webBlocCore.getApiEndpoint(
                    this.websiteId,
                    this.apiKey,
                    'notifications'
                );

                const response = await this.apiRequest(endpoint);
                this.allNotifications = response.data || [];
                this.updateUnreadCount();
                
            } catch (error) {
                console.error('Failed to load notifications:', error);
            }
        },

        addNotification(notification) {
            const newNotification = {
                id: notification.id || webBlocCore.generateId(),
                type: notification.type || 'info',
                title: notification.title,
                message: notification.message,
                actions: notification.actions || [],
                dismissible: notification.dismissible !== false,
                auto_dismiss: notification.auto_dismiss !== false && this.autoDismiss,
                visible: true,
                progress: 100,
                created_at: new Date().toISOString(),
                ...notification
            };

            // Add to active notifications
            this.notifications.unshift(newNotification);
            
            // Limit active notifications
            if (this.notifications.length > this.maxNotifications) {
                this.notifications = this.notifications.slice(0, this.maxNotifications);
            }

            // Add to all notifications for center
            this.allNotifications.unshift(newNotification);
            this.updateUnreadCount();

            // Auto-dismiss if enabled
            if (newNotification.auto_dismiss) {
                this.startAutoDismiss(newNotification);
            }

            // Play sound if enabled
            if (this.enableSound) {
                this.playNotificationSound();
            }
        },

        startAutoDismiss(notification) {
            if (!notification.auto_dismiss) return;

            const interval = 50; // Update progress every 50ms
            const steps = this.dismissTimeout / interval;
            const decrement = 100 / steps;

            const progressInterval = setInterval(() => {
                notification.progress -= decrement;
                
                if (notification.progress <= 0) {
                    clearInterval(progressInterval);
                    this.dismissNotification(notification.id);
                }
            }, interval);

            notification.progressInterval = progressInterval;
        },

        dismissNotification(id) {
            const notification = this.notifications.find(n => n.id === id);
            if (notification) {
                if (notification.progressInterval) {
                    clearInterval(notification.progressInterval);
                }
                notification.visible = false;
                
                setTimeout(() => {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }, 200); // Allow transition to complete
            }
        },

        handleNotificationClick(notification) {
            if (notification.onClick) {
                notification.onClick(notification);
            }
            
            if (notification.url) {
                window.open(notification.url, notification.target || '_self');
            }
        },

        handleAction(notification, action) {
            if (action.onClick) {
                action.onClick(notification, action);
            }
            
            if (action.dismiss !== false) {
                this.dismissNotification(notification.id);
            }
        },

        toggleNotificationCenter() {
            this.showCenter = !this.showCenter;
            if (this.showCenter) {
                this.markAllAsRead();
            }
        },

        closeNotificationCenter() {
            this.showCenter = false;
        },

        markAsRead(id) {
            const notification = this.allNotifications.find(n => n.id === id);
            if (notification) {
                notification.read = true;
                this.updateUnreadCount();
            }
        },

        markAllAsRead() {
            this.allNotifications.forEach(n => n.read = true);
            this.updateUnreadCount();
        },

        clearAll() {
            this.allNotifications = [];
            this.notifications = [];
            this.unreadCount = 0;
        },

        updateUnreadCount() {
            this.unreadCount = this.allNotifications.filter(n => !n.read).length;
        },

        playNotificationSound() {
            if (this.$refs.notificationSound) {
                this.$refs.notificationSound.currentTime = 0;
                this.$refs.notificationSound.play().catch(() => {
                    // Ignore audio play errors (user interaction required)
                });
            }
        },

        formatTimestamp(timestamp) {
            return webBlocCore.formatDate(timestamp);
        },

        formatRelativeTime(timestamp) {
            return webBlocCore.formatDate(timestamp);
        },

        getTypeIcon(type) {
            const icons = {
                success: '✅',
                error: '❌',
                warning: '⚠️',
                info: 'ℹ️'
            };
            return icons[type] || 'ℹ️';
        }
    };
}

// Auth Component
function webBlocAuth() {
    return {
        ...webBlocBase(),
        
        // Component state
        user: null,
        currentView: 'login', // login, register, forgot, profile
        
        // Form data
        loginForm: {
            email: '',
            password: '',
            remember: false
        },
        registerForm: {
            first_name: '',
            last_name: '',
            email: '',
            password: '',
            password_confirmation: '',
            terms: false,
            custom_data: {}
        },
        forgotForm: {
            email: ''
        },
        profileForm: {
            name: '',
            email: '',
            current_password: '',
            new_password: '',
            new_password_confirmation: ''
        },
        
        // UI state
        submitting: false,
        showLogin: false,
        showProfile: false,
        
        // Configuration
        theme: 'default',
        mode: 'modal',
        redirectAfterLogin: null,
        redirectAfterRegister: null,
        showRegister: true,
        showForgotPassword: true,
        customFields: [],
        socialLogin: false,

        async init() {
            await this.loadConfig();
            this.user = webBlocCore.state.user;
            
            // Watch for user changes
            this.$watch('user', (newUser) => {
                webBlocCore.state.user = newUser;
            });
        },

        loadConfig() {
            if (!webBlocBase().loadConfig.call(this)) return false;
            
            const el = this.$el;
            this.theme = el.dataset.theme || 'default';
            this.mode = el.dataset.mode || 'modal';
            this.redirectAfterLogin = el.dataset.redirectAfterLogin;
            this.redirectAfterRegister = el.dataset.redirectAfterRegister;
            this.showRegister = el.dataset.showRegister === 'true';
            this.showForgotPassword = el.dataset.showForgotPassword === 'true';
            this.socialLogin = el.dataset.socialLogin === 'true';
            
            try {
                this.customFields = JSON.parse(el.dataset.customFields || '[]');
            } catch (e) {
                this.customFields = [];
            }

            return true;
        },

        async login() {
            if (!this.loginForm.email || !this.loginForm.password) {
                this.showError('Please fill in all fields');
                return;
            }

            this.submitting = true;

            try {
                const response = await this.apiRequest('/api/auth/login', {
                    method: 'POST',
                    body: JSON.stringify(this.loginForm)
                });

                this.user = response.user;
                webBlocCore.setUser(this.user, response.token);
                
                this.showSuccess('Logged in successfully!');
                this.showLogin = false;
                
                // Reset form
                this.loginForm = { email: '', password: '', remember: false };
                
                // Redirect if specified
                if (this.redirectAfterLogin) {
                    setTimeout(() => {
                        window.location.href = this.redirectAfterLogin;
                    }, 1000);
                }
                
            } catch (error) {
                this.showError(error.message || 'Login failed');
            } finally {
                this.submitting = false;
            }
        },

        async register() {
            if (!this.validateRegisterForm()) return;

            this.submitting = true;

            try {
                const response = await this.apiRequest('/api/auth/register', {
                    method: 'POST',
                    body: JSON.stringify(this.registerForm)
                });

                this.user = response.user;
                webBlocCore.setUser(this.user, response.token);
                
                this.showSuccess('Registration successful!');
                this.showLogin = false;
                
                // Reset form
                this.registerForm = {
                    first_name: '', last_name: '', email: '', password: '', 
                    password_confirmation: '', terms: false, custom_data: {}
                };
                
                // Redirect if specified
                if (this.redirectAfterRegister) {
                    setTimeout(() => {
                        window.location.href = this.redirectAfterRegister;
                    }, 1000);
                }
                
            } catch (error) {
                this.showError(error.message || 'Registration failed');
            } finally {
                this.submitting = false;
            }
        },

        validateRegisterForm() {
            if (!this.registerForm.first_name || !this.registerForm.last_name) {
                this.showError('Please enter your first and last name');
                return false;
            }

            if (!webBlocCore.validateEmail(this.registerForm.email)) {
                this.showError('Please enter a valid email address');
                return false;
            }

            if (this.registerForm.password.length < 8) {
                this.showError('Password must be at least 8 characters');
                return false;
            }

            if (this.registerForm.password !== this.registerForm.password_confirmation) {
                this.showError('Passwords do not match');
                return false;
            }

            if (!this.registerForm.terms) {
                this.showError('Please accept the terms of service');
                return false;
            }

            return true;
        },

        async logout() {
            try {
                await this.apiRequest('/api/auth/logout', {
                    method: 'POST'
                });
            } catch (error) {
                // Continue with logout even if API call fails
            }

            this.user = null;
            webBlocCore.clearUser();
            this.showSuccess('Logged out successfully!');
        },

        switchView(view) {
            this.currentView = view;
            this.error = null;
            this.message = '';
        },

        formatDate(date) {
            return webBlocCore.formatDate(date);
        }
    };
}

// Make components globally available
window.webBlocComments = webBlocComments;
window.webBlocReviews = webBlocReviews; 
window.webBlocNotifications = webBlocNotifications;
window.webBlocAuth = webBlocAuth;
```

## 7. Core WebBloc Styling (`resources/css/webbloc-core.css`)

```css
/* WebBloc Core Styles */
:root {
    /* Color Palette */
    --wb-primary: #3b82f6;
    --wb-primary-dark: #2563eb;
    --wb-primary-light: #dbeafe;
    
    --wb-success: #10b981;
    --wb-success-light: #dcfce7;
    
    --wb-error: #ef4444;
    --wb-error-light: #fef2f2;
    
    --wb-warning: #f59e0b;
    --wb-warning-light: #fef3c7;
    
    --wb-info: #3b82f6;
    --wb-info-light: #dbeafe;
    
    /* Neutral Colors */
    --wb-gray-50: #f9fafb;
    --wb-gray-100: #f3f4f6;
    --wb-gray-200: #e5e7eb;
    --wb-gray-300: #d1d5db;
    --wb-gray-400: #9ca3af;
    --wb-gray-500: #6b7280;
    --wb-gray-600: #4b5563;
    --wb-gray-700: #374151;
    --wb-gray-800: #1f2937;
    --wb-gray-900: #111827;
    
    /* Typography */
    --wb-font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    --wb-font-size-xs: 0.75rem;
    --wb-font-size-sm: 0.875rem;
    --wb-font-size-base: 1rem;
    --wb-font-size-lg: 1.125rem;
    --wb-font-size-xl: 1.25rem;
    --wb-font-size-2xl: 1.5rem;
    
    /* Spacing */
    --wb-space-1: 0.25rem;
    --wb-space-2: 0.5rem;
    --wb-space-3: 0.75rem;
    --wb-space-4: 1rem;
    --wb-space-5: 1.25rem;
    --wb-space-6: 1.5rem;
    --wb-space-8: 2rem;
    --wb-space-10: 2.5rem;
    --wb-space-12: 3rem;
    
    /* Border Radius */
    --wb-radius-sm: 0.25rem;
    --wb-radius: 0.375rem;
    --wb-radius-md: 0.5rem;
    --wb-radius-lg: 0.75rem;
    --wb-radius-xl: 1rem;
    --wb-radius-full: 9999px;
    
    /* Shadows */
    --wb-shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --wb-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    --wb-shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --wb-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --wb-shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    
    /* Transitions */
    --wb-transition: all 0.2s ease-in-out;
    --wb-transition-fast: all 0.15s ease-in-out;
    --wb-transition-slow: all 0.3s ease-in-out;
}

/* Reset and Base Styles */
[w2030b] {
    box-sizing: border-box;
    font-family: var(--wb-font-family);
    line-height: 1.5;
}

[w2030b] *,
[w2030b] *::before,
[w2030b] *::after {
    box-sizing: inherit;
}

/* WebBloc Container Base */
[w2030b] {
    --webkit-font-smoothing: antialiased;
    --moz-osx-font-smoothing: grayscale;
    color: var(--wb-gray-900);
    font-size: var(--wb-font-size-base);
}

/* Button Styles */
.webbloc-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--wb-space-2);
    padding: var(--wb-space-3) var(--wb-space-4);
    font-size: var(--wb-font-size-sm);
    font-weight: 500;
    border-radius: var(--wb-radius);
    border: 1px solid transparent;
    cursor: pointer;
    transition: var(--wb-transition);
    text-decoration: none;
    white-space: nowrap;
    outline: none;
    position: relative;
    overflow: hidden;
}

.webbloc-btn:focus {
    outline: 2px solid var(--wb-primary);
    outline-offset: 2px;
}

.webbloc-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    pointer-events: none;
}

/* Button Variants */
.webbloc-btn-primary {
    background: var(--wb-primary);
    color: white;
    border-color: var(--wb-primary);
}

.webbloc-btn-primary:hover:not(:disabled) {
    background: var(--wb-primary-dark);
    border-color: var(--wb-primary-dark);
}

.webbloc-btn-secondary {
    background: var(--wb-gray-100);
    color: var(--wb-gray-700);
    border-color: var(--wb-gray-300);
}

.webbloc-btn-secondary:hover:not(:disabled) {
    background: var(--wb-gray-200);
    border-color: var(--wb-gray-400);
}

.webbloc-btn-outline {
    background: transparent;
    color: var(--wb-primary);
    border-color: var(--wb-primary);
}

.webbloc-btn-outline:hover:not(:disabled) {
    background: var(--wb-primary-light);
}

.webbloc-btn-danger {
    background: var(--wb-error);
    color: white;
    border-color: var(--wb-error);
}

.webbloc-btn-danger:hover:not(:disabled) {
    background: #dc2626;
    border-color: #dc2626;
}

.webbloc-btn-success {
    background: var(--wb-success);
    color: white;
    border-color: var(--wb-success);
}

.webbloc-btn-success:hover:not(:disabled) {
    background: #059669;
    border-color: #059669;
}

/* Button Sizes */
.webbloc-btn-xs {
    padding: var(--wb-space-1) var(--wb-space-2);
    font-size: var(--wb-font-size-xs);
}

.webbloc-btn-sm {
    padding: var(--wb-space-2) var(--wb-space-3);
    font-size: var(--wb-font-size-xs);
}

.webbloc-btn-lg {
    padding: var(--wb-space-4) var(--wb-space-6);
    font-size: var(--wb-font-size-lg);
}

.webbloc-btn-xl {
    padding: var(--wb-space-5) var(--wb-space-8);
    font-size: var(--wb-font-size-xl);
}

/* Form Elements */
.webbloc-input {
    width: 100%;
    padding: var(--wb-space-3) var(--wb-space-4);
    border: 1px solid var(--wb-gray-300);
    border-radius: var(--wb-radius);
    font-size: var(--wb-font-size-sm);
    background: white;
    color: var(--wb-gray-900);
    transition: var(--wb-transition);
    outline: none;
}

.webbloc-input:focus {
    border-color: var(--wb-primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.webbloc-input:invalid {
    border-color: var(--wb-error);
}

.webbloc-input::placeholder {
    color: var(--wb-gray-400);
}

.webbloc-textarea {
    min-height: 100px;
    resize: vertical;
    font-family: inherit;
}

.webbloc-select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right var(--wb-space-3) center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: var(--wb-space-10);
}

.webbloc-checkbox,
.webbloc-radio {
    width: var(--wb-space-4);
    height: var(--wb-space-4);
    border: 1px solid var(--wb-gray-300);
    margin-right: var(--wb-space-2);
    cursor: pointer;
}

.webbloc-checkbox {
    border-radius: var(--wb-radius-sm);
}

.webbloc-radio {
    border-radius: var(--wb-radius-full);
}

/* Label Styles */
.webbloc-label {
    display: block;
    font-size: var(--wb-font-size-sm);
    font-weight: 500;
    color: var(--wb-gray-700);
    margin-bottom: var(--wb-space-2);
}

.webbloc-label-required::after {
    content: ' *';
    color: var(--wb-error);
}

/* Form Groups */
.webbloc-form-group {
    margin-bottom: var(--wb-space-5);
}

.webbloc-form-group-inline {
    display: flex;
    align-items: center;
    gap: var(--wb-space-3);
}

/* Card Styles */
.webbloc-card {
    background: white;
    border: 1px solid var(--wb-gray-200);
    border-radius: var(--wb-radius-lg);
    box-shadow: var(--wb-shadow-sm);
    overflow: hidden;
}

.webbloc-card-header {
    padding: var(--wb-space-5) var(--wb-space-6);
    border-bottom: 1px solid var(--wb-gray-200);
    background: var(--wb-gray-50);
}

.webbloc-card-body {
    padding: var(--wb-space-6);
}

.webbloc-card-footer {
    padding: var(--wb-space-4) var(--wb-space-6);
    border-top: 1px solid var(--wb-gray-200);
    background: var(--wb-gray-50);
}

/* Modal Styles */
.webbloc-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: var(--wb-space-4);
}

.webbloc-modal {
    background: white;
    border-radius: var(--wb-radius-xl);
    box-shadow: var(--wb-shadow-xl);
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
}

.webbloc-modal-header {
    display: flex;
    justify-content: between;
    align-items: center;
    padding: var(--wb-space-6) var(--wb-space-6) var(--wb-space-4);
    border-bottom: 1px solid var(--wb-gray-200);
}

.webbloc-modal-title {
    font-size: var(--wb-font-size-xl);
    font-weight: 600;
    margin: 0;
    color: var(--wb-gray-900);
}

.webbloc-modal-close {
    background: none;
    border: none;
    font-size: var(--wb-font-size-xl);
    color: var(--wb-gray-400);
    cursor: pointer;
    padding: var(--wb-space-2);
    border-radius: var(--wb-radius);
    transition: var(--wb-transition);
}

.webbloc-modal-close:hover {
    color: var(--wb-gray-600);
    background: var(--wb-gray-100);
}

.webbloc-modal-body {
    padding: var(--wb-space-6);
}

.webbloc-modal-footer {
    padding: var(--wb-space-4) var(--wb-space-6) var(--wb-space-6);
    display: flex;
    justify-content: flex-end;
    gap: var(--wb-space-3);
}

/* Message/Alert Styles */
.webbloc-alert {
    padding: var(--wb-space-4);
    border-radius: var(--wb-radius);
    border: 1px solid transparent;
    margin-bottom: var(--wb-space-4);
    font-size: var(--wb-font-size-sm);
}

.webbloc-alert-success {
    background: var(--wb-success-light);
    color: #065f46;
    border-color: #10b981;
}

.webbloc-alert-error {
    background: var(--wb-error-light);
    color: #991b1b;
    border-color: var(--wb-error);
}

.webbloc-alert-warning {
    background: var(--wb-warning-light);
    color: #92400e;
    border-color: var(--wb-warning);
}

.webbloc-alert-info {
    background: var(--wb-info-light);
    color: #1e40af;
    border-color: var(--wb-info);
}

/* Loading States */
.webbloc-loading {
    display: inline-flex;
    align-items: center;
    gap: var(--wb-space-2);
    color: var(--wb-gray-500);
}

.webbloc-spinner {
    width: 1em;
    height: 1em;
    border: 2px solid var(--wb-gray-200);
    border-top: 2px solid var(--wb-primary);
    border-radius: var(--wb-radius-full);
    animation: webbloc-spin 1s linear infinite;
}

@keyframes webbloc-spin {
    to { transform: rotate(360deg); }
}

/* Badge Styles */
.webbloc-badge {
    display: inline-flex;
    align-items: center;
    padding: var(--wb-space-1) var(--wb-space-2);
    font-size: var(--wb-font-size-xs);
    font-weight: 500;
    border-radius: var(--wb-radius-full);
    white-space: nowrap;
}

.webbloc-badge-primary {
    background: var(--wb-primary-light);
    color: var(--wb-primary);
}

.webbloc-badge-success {
    background: var(--wb-success-light);
    color: #065f46;
}

.webbloc-badge-error {
    background: var(--wb-error-light);
    color: #991b1b;
}

.webbloc-badge-warning {
    background: var(--wb-warning-light);
    color: #92400e;
}

.webbloc-badge-gray {
    background: var(--wb-gray-100);
    color: var(--wb-gray-700);
}

/* Avatar Styles */
.webbloc-avatar {
    display: inline-block;
    position: relative;
    overflow: hidden;
    border-radius: var(--wb-radius-full);
    background: var(--wb-gray-100);
}

.webbloc-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.webbloc-avatar-xs {
    width: 24px;
    height: 24px;
}

.webbloc-avatar-sm {
    width: 32px;
    height: 32px;
}

.webbloc-avatar-md {
    width: 40px;
    height: 40px;
}

.webbloc-avatar-lg {
    width: 48px;
    height: 48px;
}

.webbloc-avatar-xl {
    width: 64px;
    height: 64px;
}

/* Utility Classes */
.webbloc-sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

.webbloc-text-center {
    text-align: center;
}

.webbloc-text-left {
    text-align: left;
}

.webbloc-text-right {
    text-align: right;
}

.webbloc-hidden {
    display: none !important;
}

.webbloc-flex {
    display: flex;
}

.webbloc-flex-col {
    flex-direction: column;
}

.webbloc-items-center {
    align-items: center;
}

.webbloc-justify-center {
    justify-content: center;
}

.webbloc-justify-between {
    justify-content: space-between;
}

.webbloc-gap-2 {
    gap: var(--wb-space-2);
}

.webbloc-gap-3 {
    gap: var(--wb-space-3);
}

.webbloc-gap-4 {
    gap: var(--wb-space-4);
}

/* Responsive Design */
@media (max-width: 640px) {
    .webbloc-modal {
        margin: var(--wb-space-4);
        max-width: none;
    }
    
    .webbloc-modal-header,
    .webbloc-modal-body,
    .webbloc-modal-footer {
        padding-left: var(--wb-space-4);
        padding-right: var(--wb-space-4);
    }
    
    .webbloc-btn {
        width: 100%;
        justify-content: center;
    }
    
    .webbloc-modal-footer {
        flex-direction: column;
    }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    :root {
        --wb-gray-50: #1f2937;
        --wb-gray-100: #374151;
        --wb-gray-200: #4b5563;
        --wb-gray-300: #6b7280;
        --wb-gray-400: #9ca3af;
        --wb-gray-500: #d1d5db;
        --wb-gray-600: #e5e7eb;
        --wb-gray-700: #f3f4f6;
        --wb-gray-800: #f9fafb;
        --wb-gray-900: #ffffff;
    }
    
    .webbloc-card,
    .webbloc-modal,
    .webbloc-input {
        background: var(--wb-gray-100);
        border-color: var(--wb-gray-200);
    }
}

/* High Contrast Mode */
@media (prefers-contrast: high) {
    :root {
        --wb-primary: #0000ff;
```
