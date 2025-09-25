Now I have a complete understanding of the WebBloc project. This is a comprehensive Laravel-based multi-tenant system that provides dynamic web components as APIs for static websites. Based on the analysis, I'll provide all the requested blade view codes with the proper implementation details.

# Complete Blade Views Implementation for WebBloc Project

## 8. WebBloc Component Templates

### resources/views/webblocs/comment.blade.php
```php
@props([
    'websiteUuid' => '',
    'apiKey' => '',
    'pageUrl' => '',
    'limit' => 10,
    'sort' => 'newest',
    'locale' => 'en'
])

<div class="webbloc-comment" 
     x-data="commentComponent({
        websiteUuid: '{{ $websiteUuid }}',
        apiKey: '{{ $apiKey }}',
        pageUrl: '{{ $pageUrl }}',
        limit: {{ $limit }},
        sort: '{{ $sort }}',
        locale: '{{ $locale }}'
     })"
     x-init="loadComments()"
     {{ $attributes->merge(['class' => 'webbloc-comments-container']) }}>
    
    <!-- Loading State -->
    <div x-show="loading" class="flex justify-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Error State -->
    <div x-show="error" x-text="errorMessage" 
         class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"></div>

    <!-- Comment Form -->
    <div x-show="!loading && !error" class="mb-6">
        <div x-show="!isAuthenticated" class="mb-4">
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                <p x-text="translations.loginRequired"></p>
                <div class="mt-2 space-x-2">
                    <button @click="showLogin = true" 
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition-colors"
                            x-text="translations.login"></button>
                    <button @click="showRegister = true" 
                            class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition-colors"
                            x-text="translations.register"></button>
                </div>
            </div>
        </div>

        <div x-show="isAuthenticated" class="comment-form">
            <form @submit.prevent="submitComment()">
                <div class="mb-4">
                    <label :for="`comment-${componentId}`" 
                           class="block text-sm font-medium text-gray-700 mb-2"
                           x-text="translations.writeComment"></label>
                    <textarea :id="`comment-${componentId}`"
                              x-model="newComment.content"
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              :placeholder="translations.commentPlaceholder"
                              required></textarea>
                </div>
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500" x-show="user">
                        <span x-text="translations.postingAs"></span>
                        <span class="font-medium" x-text="user?.name"></span>
                    </div>
                    <button type="submit" 
                            :disabled="submitting"
                            class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <span x-show="!submitting" x-text="translations.postComment"></span>
                        <span x-show="submitting" x-text="translations.posting"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Comments List -->
    <div x-show="!loading && !error" class="comments-list">
        <!-- Sort & Filter Options -->
        <div class="flex justify-between items-center mb-4 pb-2 border-b">
            <h3 class="text-lg font-semibold" x-text="`${translations.comments} (${totalComments})`"></h3>
            <select x-model="currentSort" @change="loadComments()" 
                    class="px-3 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="newest" x-text="translations.newest"></option>
                <option value="oldest" x-text="translations.oldest"></option>
                <option value="popular" x-text="translations.mostPopular"></option>
            </select>
        </div>

        <!-- Comments -->
        <div class="space-y-4">
            <template x-for="comment in comments" :key="comment.uuid">
                <div class="comment-item border rounded-lg p-4 bg-white hover:bg-gray-50 transition-colors">
                    <div class="flex items-start space-x-3" :class="locale === 'ar' ? 'space-x-reverse' : ''">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                                <span x-text="comment.user_name?.charAt(0)?.toUpperCase()"></span>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center space-x-2" :class="locale === 'ar' ? 'space-x-reverse' : ''">
                                    <span class="font-medium text-gray-900" x-text="comment.user_name"></span>
                                    <span class="text-sm text-gray-500" x-text="formatDate(comment.created_at)"></span>
                                </div>
                                <div x-show="user && (user.id === comment.user_id || user.role === 'admin')" 
                                     class="flex space-x-1">
                                    <button @click="editComment(comment)" 
                                            class="text-blue-600 hover:text-blue-800 text-sm"
                                            x-text="translations.edit"></button>
                                    <button @click="deleteComment(comment.uuid)" 
                                            class="text-red-600 hover:text-red-800 text-sm"
                                            x-text="translations.delete"></button>
                                </div>
                            </div>
                            <div class="text-gray-700 whitespace-pre-wrap" x-text="comment.content"></div>
                            <div class="flex items-center space-x-4 mt-3" :class="locale === 'ar' ? 'space-x-reverse' : ''">
                                <button @click="toggleLike(comment)" 
                                        class="flex items-center space-x-1 text-sm hover:text-blue-600 transition-colors"
                                        :class="comment.user_liked ? 'text-blue-600' : 'text-gray-500'">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/>
                                    </svg>
                                    <span x-text="comment.likes_count || 0"></span>
                                </button>
                                <button @click="replyToComment(comment)" 
                                        class="text-sm text-gray-500 hover:text-blue-600 transition-colors"
                                        x-text="translations.reply"></button>
                            </div>

                            <!-- Replies -->
                            <div x-show="comment.replies && comment.replies.length > 0" 
                                 class="mt-4 pl-4 border-l-2 border-gray-200 space-y-3">
                                <template x-for="reply in comment.replies" :key="reply.uuid">
                                    <div class="flex items-start space-x-3" :class="locale === 'ar' ? 'space-x-reverse' : ''">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-gray-400 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                                                <span x-text="reply.user_name?.charAt(0)?.toUpperCase()"></span>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2 mb-1" :class="locale === 'ar' ? 'space-x-reverse' : ''">
                                                <span class="font-medium text-gray-900 text-sm" x-text="reply.user_name"></span>
                                                <span class="text-xs text-gray-500" x-text="formatDate(reply.created_at)"></span>
                                            </div>
                                            <div class="text-gray-700 text-sm" x-text="reply.content"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Load More -->
        <div x-show="hasMore" class="text-center mt-6">
            <button @click="loadMore()" 
                    :disabled="loadingMore"
                    class="bg-gray-200 text-gray-700 px-6 py-2 rounded hover:bg-gray-300 disabled:opacity-50 transition-colors">
                <span x-show="!loadingMore" x-text="translations.loadMore"></span>
                <span x-show="loadingMore" x-text="translations.loading"></span>
            </button>
        </div>

        <!-- Empty State -->
        <div x-show="comments.length === 0 && !loading" 
             class="text-center py-8 text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <p x-text="translations.noComments"></p>
        </div>
    </div>

    <!-- Authentication Modals -->
    <div x-show="showLogin" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
         @click.self="showLogin = false">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4"
             @click.stop>
            <h3 class="text-lg font-semibold mb-4" x-text="translations.login"></h3>
            <form @submit.prevent="login()">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2" x-text="translations.email"></label>
                    <input type="email" x-model="loginForm.email" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2" x-text="translations.password"></label>
                    <input type="password" x-model="loginForm.password" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" @click="showLogin = false" 
                            class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors"
                            x-text="translations.cancel"></button>
                    <button type="submit" :disabled="loggingIn"
                            class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 disabled:opacity-50 transition-colors">
                        <span x-show="!loggingIn" x-text="translations.login"></span>
                        <span x-show="loggingIn" x-text="translations.loggingIn"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function commentComponent(config) {
    return {
        // Configuration
        websiteUuid: config.websiteUuid,
        apiKey: config.apiKey,
        pageUrl: config.pageUrl,
        limit: config.limit,
        currentSort: config.sort,
        locale: config.locale,
        componentId: Math.random().toString(36).substr(2, 9),
        
        // State
        loading: false,
        loadingMore: false,
        error: false,
        errorMessage: '',
        submitting: false,
        loggingIn: false,
        
        // Data
        comments: [],
        totalComments: 0,
        currentPage: 1,
        hasMore: false,
        user: null,
        isAuthenticated: false,
        
        // Forms
        newComment: {
            content: ''
        },
        loginForm: {
            email: '',
            password: ''
        },
        
        // UI State
        showLogin: false,
        showRegister: false,
        
        // Translations
        translations: {
            en: {
                comments: 'Comments',
                writeComment: 'Write a comment',
                commentPlaceholder: 'Share your thoughts...',
                postComment: 'Post Comment',
                posting: 'Posting...',
                postingAs: 'Posting as',
                loginRequired: 'Please login to post comments',
                login: 'Login',
                register: 'Register',
                newest: 'Newest',
                oldest: 'Oldest',
                mostPopular: 'Most Popular',
                edit: 'Edit',
                delete: 'Delete',
                reply: 'Reply',
                loadMore: 'Load More',
                loading: 'Loading...',
                noComments: 'No comments yet. Be the first to comment!',
                email: 'Email',
                password: 'Password',
                cancel: 'Cancel',
                loggingIn: 'Logging in...'
            },
            ar: {
                comments: 'التعليقات',
                writeComment: 'اكتب تعليقاً',
                commentPlaceholder: 'شارك أفكارك...',
                postComment: 'نشر التعليق',
                posting: 'جاري النشر...',
                postingAs: 'النشر باسم',
                loginRequired: 'يرجى تسجيل الدخول لنشر التعليقات',
                login: 'تسجيل الدخول',
                register: 'إنشاء حساب',
                newest: 'الأحدث',
                oldest: 'الأقدم',
                mostPopular: 'الأكثر شعبية',
                edit: 'تعديل',
                delete: 'حذف',
                reply: 'رد',
                loadMore: 'تحميل المزيد',
                loading: 'جاري التحميل...',
                noComments: 'لا توجد تعليقات بعد. كن أول من يعلق!',
                email: 'البريد الإلكتروني',
                password: 'كلمة المرور',
                cancel: 'إلغاء',
                loggingIn: 'جاري تسجيل الدخول...'
            }
        },
        
        get translations() {
            return this.translations[this.locale] || this.translations.en;
        },
        
        async init() {
            await this.checkAuth();
            await this.loadComments();
        },
        
        async checkAuth() {
            try {
                const response = await fetch(`/api/webblocs/auth/check`, {
                    headers: {
                        'X-API-Key': this.apiKey,
                        'X-Website-UUID': this.websiteUuid
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.user = data.user;
                    this.isAuthenticated = !!data.user;
                }
            } catch (error) {
                console.error('Auth check failed:', error);
            }
        },
        
        async loadComments() {
            this.loading = true;
            this.error = false;
            
            try {
                const params = new URLSearchParams({
                    page_url: this.pageUrl,
                    sort: this.currentSort,
                    limit: this.limit,
                    page: this.currentPage
                });
                
                const response = await fetch(`/api/webblocs/comments?${params}`, {
                    headers: {
                        'X-API-Key': this.apiKey,
                        'X-Website-UUID': this.websiteUuid
                    }
                });
                
                if (!response.ok) {
                    throw new Error('Failed to load comments');
                }
                
                const data = await response.json();
                
                if (this.currentPage === 1) {
                    this.comments = data.data;
                } else {
                    this.comments = [...this.comments, ...data.data];
                }
                
                this.totalComments = data.total;
                this.hasMore = data.current_page < data.last_page;
                
            } catch (error) {
                this.error = true;
                this.errorMessage = error.message;
            } finally {
                this.loading = false;
                this.loadingMore = false;
            }
        },
        
        async loadMore() {
            this.loadingMore = true;
            this.currentPage++;
            await this.loadComments();
        },
        
        async submitComment() {
            if (!this.isAuthenticated) {
                this.showLogin = true;
                return;
            }
            
            this.submitting = true;
            
            try {
                const response = await fetch('/api/webblocs/comments', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-API-Key': this.apiKey,
                        'X-Website-UUID': this.websiteUuid,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify({
                        content: this.newComment.content,
                        page_url: this.pageUrl
                    })
                });
                
                if (!response.ok) {
                    throw new Error('Failed to post comment');
                }
                
                const comment = await response.json();
                this.comments.unshift(comment);
                this.totalComments++;
                this.newComment.content = '';
                
            } catch (error) {
                alert('Failed to post comment: ' + error.message);
            } finally {
                this.submitting = false;
            }
        },
        
        async login() {
            this.loggingIn = true;
            
            try {
                const response = await fetch('/api/webblocs/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-API-Key': this.apiKey,
                        'X-Website-UUID': this.websiteUuid,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify(this.loginForm)
                });
                
                if (!response.ok) {
                    throw new Error('Login failed');
                }
                
                const data = await response.json();
                this.user = data.user;
                this.isAuthenticated = true;
                this.showLogin = false;
                this.loginForm = { email: '', password: '' };
                
            } catch (error) {
                alert('Login failed: ' + error.message);
            } finally {
                this.loggingIn = false;
            }
        },
        
        async toggleLike(comment) {
            if (!this.isAuthenticated) {
                this.showLogin = true;
                return;
            }
            
            try {
                const response = await fetch(`/api/webblocs/comments/${comment.uuid}/like`, {
                    method: 'POST',
                    headers: {
                        'X-API-Key': this.apiKey,
                        'X-Website-UUID': this.websiteUuid,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    comment.likes_count = data.likes_count;
                    comment.user_liked = data.user_liked;
                }
            } catch (error) {
                console.error('Failed to toggle like:', error);
            }
        },
        
        async deleteComment(uuid) {
            if (!confirm(this.translations.deleteConfirm || 'Are you sure?')) {
                return;
            }
            
            try {
                const response = await fetch(`/api/webblocs/comments/${uuid}`, {
                    method: 'DELETE',
                    headers: {
                        'X-API-Key': this.apiKey,
                        'X-Website-UUID': this.websiteUuid,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    this.comments = this.comments.filter(c => c.uuid !== uuid);
                    this.totalComments--;
                }
            } catch (error) {
                alert('Failed to delete comment');
            }
        },
        
        formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);
            
            if (minutes < 1) return this.translations.justNow || 'Just now';
            if (minutes < 60) return `${minutes}m`;
            if (hours < 24) return `${hours}h`;
            if (days < 7) return `${days}d`;
            
            return date.toLocaleDateString(this.locale);
        }
    }
}
</script>

<style>
.webbloc-comments-container {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.webbloc-comments-container[dir="rtl"] {
    direction: rtl;
    text-align: right;
}

.webbloc-comments-container[dir="rtl"] .space-x-reverse > :not([hidden]) ~ :not([hidden]) {
    --tw-space-x-reverse: 1;
}

.comment-item {
    transition: all 0.2s ease-in-out;
}

.comment-item:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.webbloc-comments-container input,
.webbloc-comments-container textarea,
.webbloc-comments-container select {
    font-size: 14px;
}

.webbloc-comments-container button {
    font-size: 14px;
    font-weight: 500;
}

@media (max-width: 640px) {
    .webbloc-comments-container {
        font-size: 14px;
    }
    
    .comment-item {
        padding: 12px;
    }
}
</style>
```

### resources/views/webblocs/review.blade.php
```php
@props([
    'websiteUuid' => '',
    'apiKey' => '',
    'pageUrl' => '',
    'limit' => 10,
    'sort' => 'newest',
    'locale' => 'en',
    'showRatingFilter' => true,
    'allowImages' => false
])

<div class="webbloc-review" 
     x-data="reviewComponent({
        websiteUuid: '{{ $websiteUuid }}',
        apiKey: '{{ $apiKey }}',
        pageUrl: '{{ $pageUrl }}',
        limit: {{ $limit }},
        sort: '{{ $sort }}',
        locale: '{{ $locale }}',
        showRatingFilter: {{ $showRatingFilter ? 'true' : 'false' }},
        allowImages: {{ $allowImages ? 'true' : 'false' }}
     })"
     x-init="init()"
     {{ $attributes->merge(['class' => 'webbloc-reviews-container']) }}>
    
    <!-- Loading State -->
    <div x-show="loading" class="flex justify-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Error State -->
    <div x-show="error" x-text="errorMessage" 
         class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"></div>

    <!-- Reviews Summary -->
    <div x-show="!loading && !error && totalReviews > 0" class="reviews-summary mb-6 p-4 bg-gray-50 rounded-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold" x-text="translations.reviewsSummary"></h3>
            <div class="flex items-center space-x-2" :class="locale === 'ar' ? 'space-x-reverse' : ''">
                <div class="flex items-center">
                    <template x-for="i in 5" :key="i">
                        <svg class="w-5 h-5" 
                             :class="i <= Math.round(averageRating) ? 'text-yellow-400' : 'text-gray-300'"
                             fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </template>
                </div>
                <span class="text-lg font-bold" x-text="averageRating.toFixed(1)"></span>
                <span class="text-sm text-gray-500">(<span x-text="totalReviews"></span> <span x-text="translations.reviews"></span>)</span>
            </div>
        </div>
        
        <!-- Rating Breakdown -->
        <div class="space-y-2">
            <template x-for="rating in [5,4,3,2,1]" :key="rating">
                <div class="flex items-center space-x-2" :class="locale === 'ar' ? 'space-x-reverse' : ''">
                    <span class="text-sm w-2" x-text="rating"></span>
                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-yellow-400 rounded-full" 
                             :style="`width: ${(ratingBreakdown[rating] || 0) / totalReviews * 100}%`"></div>
                    </div>
                    <span class="text-sm text-gray-500 w-8" x-text="ratingBreakdown[rating] || 0"></span>
                </div>
            </template>
        </div>
    </div>

    <!-- Review Form -->
    <div x-show="!loading && !error" class="mb-6">
        <div x-show="!isAuthenticated" class="mb-4">
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                <p x-text="translations.loginRequired"></p>
                <div class="mt-2 space-x-2">
                    <button @click="showLogin = true" 
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition-colors"
                            x-text="translations.login"></button>
                    <button @click="showRegister = true" 
                            class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition-colors"
                            x-text="translations.register"></button>
                </div>
            </div>
        </div>

        <div x-show="isAuthenticated" class="review-form">
            <h4 class="text-lg font-semibold mb-4" x-text="translations.writeReview"></h4>
            <form @submit.prevent="submitReview()">
                <!-- Rating Input -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2" x-text="translations.rating"></label>
                    <div class="flex items-center space-x-1">
                        <template x-for="i in 5" :key="i">
                            <button type="button" 
                                    @click="newReview.rating = i"
                                    class="p-1 hover:scale-110 transition-transform">
                                <svg class="w-8 h-8" 
                                     :class="i <= newReview.rating ? 'text-yellow-400' : 'text-gray-300'"
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </button>
                        </template>
                        <span class="ml-2 text-sm text-gray-600" x-text="getRatingText(newReview.rating)"></span>
                    </div>
                </div>

                <!-- Title Input -->
                <div class="mb-4">
                    <label for="review-title" class="block text-sm font-medium text-gray-700 mb-2" x-text="translations.reviewTitle"></label>
                    <input id="review-title" type="text" x-model="newReview.title" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           :placeholder="translations.reviewTitlePlaceholder" required>
                </div>

                <!-- Content Input -->
                <div class="mb-4">
                    <label for="review-content" class="block text-sm font-medium text-gray-700 mb-2" x-text="translations.reviewContent"></label>
                    <textarea id="review-content" x-model="newReview.content" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              :placeholder="translations.reviewContentPlaceholder" required></textarea>
                </div>

                <!-- Image Upload (if enabled) -->
                <div x-show="allowImages" class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2" x-text="translations.images"></label>
                    <input type="file" @change="handleImageUpload" multiple accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1" x-text="translations.imagesHelp"></p>
                    
                    <!-- Image Previews -->
                    <div x-show="newReview.images.length > 0" class="mt-3 flex flex-wrap gap-2">
                        <template x-for="(image, index) in newReview.images" :key="index">
                            <div class="relative">
                                <img :src="image.preview" class="w-20 h-20 object-cover rounded border">
                                <button type="button" @click="removeImage(index)"
                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600">
                                    ×
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Pros/Cons (Optional) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" x-text="translations.pros"></label>
                        <textarea x-model="newReview.pros" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                  :placeholder="translations.prosPlaceholder"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" x-text="translations.cons"></label>
                        <textarea x-model="newReview.cons" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                  :placeholder="translations.consPlaceholder"></textarea>
                    </div>
                </div>

                <!-- Recommendation -->
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" x-model="newReview.recommend" 
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700" x-text="translations.wouldRecommend"></span>
                    </label>
                </div>

                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500" x-show="user">
                        <span x-text="translations.reviewingAs"></span>
                        <span class="font-medium" x-text="user?.name"></span>
                    </div>
                    <button type="submit" 
                            :disabled="submitting || newReview.rating === 0"
                            class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <span x-show="!submitting" x-text="translations.submitReview"></span>
                        <span x-show="submitting" x-text="translations.submitting"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Filters -->
    <div x-show="!loading && !error && reviews.length > 0" class="filters mb-6 p-4 bg-gray-50 rounded-lg">
        <div class="flex flex-wrap items-center gap-4">
            <!-- Sort Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" x-text="translations.sortBy"></label>
                <select x-model="currentSort" @change="loadReviews()" 
                        class="px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="newest" x-text="translations.newest"></option>
                    <option value="oldest" x-text="translations.oldest"></option>
                    <option value="highest" x-text="translations.highestRated"></option>
                    <option value="lowest" x-text="translations.lowestRated"></option>
                    <option value="helpful" x-text="translations.mostHelpful"></option>
                </select>
            </div>

            <!-- Rating Filter -->
            <div x-show="showRatingFilter">
                <label class="block text-sm font-medium text-gray-700 mb-1" x-text="translations.filterByRating"></label>
                <select x-model="ratingFilter" @change="loadReviews()" 
                        class="px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="" x-text="translations.allRatings"></option>
                    <option value="5" x-text="'5 ' + translations.stars"></option>
                    <option value="4" x-text="'4+ ' + translations.stars"></option>
                    <option value="3" x-text="'3+ ' + translations.stars"></option>
                    <option value="2" x-text="'2+ ' + translations.stars"></option>
                    <option value="1" x-text="'1+ ' + translations.stars"></option>
                </select>
            </div>

            <!-- Search -->
            <div class="flex-1 min-w-64">
                <label class="block text-sm font-medium text-gray-700 mb-1" x-text="translations.searchReviews"></label>
                <input type="text" x-model="searchQuery" @input.debounce.500ms="loadReviews()"
                       class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       :placeholder="translations.searchPlaceholder">
            </div>
        </div>
    </div>

    <!-- Reviews List -->
    <div x-show="!loading && !error" class="reviews-list">
        <div x-show="reviews.length > 0" class="space-y-6">
            <template x-for="review in reviews" :key="review.uuid">
                <div class="review-item border rounded-lg p-6 bg-white hover:shadow-lg transition-shadow">
                    <!-- Review Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-start space-x-4" :class="locale === 'ar' ? 'space-x-reverse' : ''">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold text-lg">
                                    <span x-text="review.user_name?.charAt(0)?.toUpperCase()"></span>
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center space-x-2 mb-1" :class="locale === 'ar' ? 'space-x-reverse' : ''">
                                    <span class="font-semibold text-gray-900" x-text="review.user_name"></span>
                                    <span x-show="review.is_verified" 
                                          class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full"
                                          x-text="translations.verified"></span>
                                </div>
                                <div class="flex items-center space-x-2 mb-2" :class="locale === 'ar' ? 'space-x-reverse' : ''">
                                    <div class="flex items-center">
                                        <template x-for="i in 5" :key="i">
                                            <svg class="w-4 h-4" 
                                                 :class="i <= review.rating ? 'text-yellow-400' : 'text-gray-300'"
                                                 fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        </template>
                                    </div>
                                    <span class="text-sm text-gray-500" x-text="formatDate(review.created_at)"></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Review Actions -->
                        <div x-show="user && (user.id === review.user_id || user.role === 'admin')" 
                             class="flex space-x-2">
                            <button @click="editReview(review)" 
                                    class="text-blue-600 hover:text-blue-800 text-sm"
                                    x-text="translations.edit"></button>
                            <button @click="deleteReview(review.uuid)" 
                                    class="text-red-600 hover:text-red-800 text-sm"
                                    x-text="translations.delete"></button>
                        </div>
                    </div>

                    <!-- Review Content -->
                    <div class="mb-4">
                        <h5 class="font-semibold text-lg mb-2" x-text="review.title"></h5>
                        <p class="text-gray-700 whitespace-pre-wrap leading-relaxed" x-text="review.content"></p>
                    </div>

                    <!-- Pros/Cons -->
                    <div x-show="review.pros || review.cons" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div x-show="review.pros" class="bg-green-50 p-3 rounded">
                            <h6 class="font-medium text-green-800 mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span x-text="translations.pros"></span>
                            </h6>
                            <p class="text-green-700 text-sm whitespace-pre-wrap" x-text="review.pros"></p>
                        </div>
                        <div x-show="review.cons" class="bg-red-50 p-3 rounded">
                            <h6 class="font-medium text-red-800 mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <span x-text="translations.cons"></span>
                            </h6>
                            <p class="text-red-700 text-sm whitespace-pre-wrap" x-text="review.cons"></p>
                        </div>
                    </div>

                    <!-- Images -->
                    <div x-show="review.images && review.images.length > 0" class="mb-4">
                        <div class="flex flex-wrap gap-2">
                            <template x-for="image in review.images" :key="image.id">
                                <img :src="image.url" :alt="review.title" 
                                     class="w-20 h-20 object-cover rounded border cursor-pointer hover:opacity-80 transition-opacity"
                                     @click="openImageModal(image.url)">
                            </template>
                        </div>
                    </div>

                    <!-- Recommendation -->
                    <div x-show="review.recommend !== null" class="mb-4">
                        <div class="flex items-center space-x-2" :class="locale === 'ar' ? 'space-x-reverse' : ''">
                            <svg class="w-5 h-5" :class="review.recommend ? 'text-green-500' : 'text-red-500'" 
                                 fill="currentColor" viewBox="0 0 20 20">
                                <path x-show="review.recommend" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                <path x-show="!review.recommend" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium" 
                                  :class="review.recommend ? 'text-green-700' : 'text-red-700'"
                                  x-text="review.recommend ? translations.recommendsThis : translations.doesNotRecommend"></span>
                        </div>
                    </div>

                    <!-- Review Footer -->
                    <div class="flex items-center justify-between pt-4 border-t">
                        <div class="flex items-center space-x-4" :class="locale === 'ar' ? 'space-x-reverse' : ''">
                            <!-- Helpful Vote -->
                            <div class="flex items-center space-x-1">
                                <button @click="toggleHelpful(review)" 
                                        :disabled="!isAuthenticated"
                                        class="flex items-center space-x-1 text-sm hover:text-blue-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                        :class="review.user_voted_helpful ? 'text-blue-600' : 'text-gray-500'">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/>
                                    </svg>
                                    <span x-text="translations.helpful"></span>
                                    <span x-text="`(${review.helpful_count || 0})`"></span>
                                </button>
                            </div>

                            <!-- Share -->
                            <button @click="shareReview(review)" 
                                    class="text-sm text-gray-500 hover:text-blue-600 transition-colors"
                                    x-text="translations.share"></button>
                        </div>

                        <!-- Report -->
                        <button @click="reportReview(review)" 
                                class="text-sm text-gray-400 hover:text-red-600 transition-colors"
                                x-text="translations.report"></button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Load More -->
        <div x-show="hasMore" class="text-center mt-8">
            <button @click="loadMore()" 
                    :disabled="loadingMore"
                    class="bg-gray-200 text-gray-700 px-8 py-3 rounded-lg hover:bg-gray-300 disabled:opacity-50 transition-colors">
                <span x-show="!loadingMore" x-text="translations.loadMoreReviews"></span>
                <span x-show="loadingMore" x-text="translations.loading"></span>
            </button>
        </div>

        <!-- Empty State -->
        <div x-show="reviews.length === 0 && !loading" 
             class="text-center py-12 text-gray-500">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
            <h3 class="text-lg font-semibold mb-2" x-text="translations.noReviewsTitle"></h3>
            <p x-text="translations.noReviewsMessage"></p>
        </div>
    </div>

    <!-- Authentication Modal -->
    <div x-show="showLogin" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
         @click.self="showLogin = false">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4 max-h-screen overflow-y-auto"
             @click.stop>
            <h3 class="text-lg font-semibold mb-4" x-text="translations.login"></h3>
            <form @submit.prevent="login()">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2" x-text="translations.email"></label>
                    <input type="email" x-model="loginForm.email" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2" x-text="translations.password"></label>
                    <input type="password" x-model="loginForm.password" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" @click="showLogin = false" 
                            class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors"
                            x-text="translations.cancel"></button>
                    <button type="submit" :disabled="loggingIn"
                            class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 disabled:opacity-50 transition-colors">
                        <span x-show="!loggingIn" x-text="translations.login"></span>
                        <span x-show="loggingIn" x-text="translations.loggingIn"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Image Modal -->
    <div x-show="showImageModal" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50"
         @click.self="showImageModal = false">
        <img :src="selectedImage" class="max-w-full max-h-full object-contain" @click.stop>
        <button @click="showImageModal = false" 
                class="absolute top-4 right-4 text-white text-xl hover:text-gray-300">
            ×
        </button>
    </div>
</div>

<script>
function reviewComponent(config) {
    return {
        // Configuration
        websiteUuid: config.websiteUuid,
        apiKey: config.apiKey,
        pageUrl: config.pageUrl,
        limit: config.limit,
        currentSort: config.sort,
        locale: config.locale,
        showRatingFilter: config.showRatingFilter,
        allowImages: config.allowImages,
        componentId: Math.random().toString(36).substr(2, 9),
        
        // State
        loading: false,
        loadingMore: false,
        error: false,
        errorMessage: '',
        submitting: false,
        loggingIn: false,
        
        // Data
        reviews: [],
        totalReviews: 0,
        averageRating: 0,
        ratingBreakdown: {},
        currentPage: 1,
        hasMore: false,
        user: null,
        isAuthenticated: false,
        
        // Filters
        ratingFilter: '',
        searchQuery: '',
        
        // Forms
        newReview: {
            rating: 0,
            title: '',
            content: '',
            pros: '',
            cons: '',
            recommend: null,
            images: []
        },
        loginForm: {
            email: '',
            password: ''
        },
        
        // UI State
        showLogin: false,
        showImageModal: false,
        selectedImage: '',
        
        // Translations
        translations: {
            en: {
                reviews: 'Reviews',
                reviewsSummary: 'Reviews Summary',
                writeReview: 'Write a Review',
                rating: 'Rating',
                reviewTitle: 'Review Title',
                reviewTitlePlaceholder: 'Summarize your experience',
                reviewContent: 'Review Content',
                reviewContentPlaceholder: 'Tell others about your experience...',
                images: 'Images (Optional)',
                imagesHelp: 'Upload up to 5 images (max 2MB each)',
                pros: 'Pros',
                prosPlaceholder: 'What did you like?',
                cons: 'Cons',
                consPlaceholder: 'What could be improved?',
                wouldRecommend: 'I would recommend this',
                reviewingAs: 'Reviewing as',
                submitReview: 'Submit Review',
                submitting: 'Submitting...',
                loginRequired: 'Please login to write a review',
                login: 'Login',
                register: 'Register',
                sortBy: 'Sort by',
                filterByRating: 'Filter by Rating',
                searchReviews: 'Search Reviews',
                allRatings: 'All Ratings',
                stars: 'Stars',
                newest: 'Newest',
                oldest: 'Oldest',
                highestRated: 'Highest Rated',
                lowestRated: 'Lowest Rated',
                mostHelpful: 'Most Helpful',
                searchPlaceholder: 'Search reviews...',
                verified: 'Verified',
                edit: 'Edit',
                delete: 'Delete',
                recommendsThis: 'Recommends this',
                doesNotRecommend: 'Does not recommend',
                helpful: 'Helpful',
                share: 'Share',
                report: 'Report',
                loadMoreReviews: 'Load More Reviews',
                loading: 'Loading...',
                noReviewsTitle: 'No Reviews Yet',
                noReviewsMessage: 'Be the first to write a review!',
                email: 'Email',
                password: 'Password',
                cancel: 'Cancel',
                loggingIn: 'Logging in...',
                ratingLabels: ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent']
            },
            ar: {
                reviews: 'المراجعات',
                reviewsSummary: 'ملخص المراجعات',
                writeReview: 'اكتب مراجعة',
                rating: 'التقييم',
                reviewTitle: 'عنوان المراجعة',
                reviewTitlePlaceholder: 'لخص تجربتك',
                reviewContent: 'محتوى المراجعة',
                reviewContentPlaceholder: 'أخبر الآخرين عن تجربتك...',
                images: 'الصور (اختياري)',
                imagesHelp: 'ارفع حتى 5 صور (حد أقصى 2 ميجابايت لكل صورة)',
                pros: 'الإيجابيات',
                prosPlaceholder: 'ما الذي أعجبك؟',
                cons: 'السلبيات',
                consPlaceholder: 'ما الذي يمكن تحسينه؟',
                wouldRecommend: 'سأوصي بهذا',
                reviewingAs: 'مراجعة باسم',
                submitReview: 'إرسال المراجعة',
                submitting: 'جاري الإرسال...',
                loginRequired: 'يرجى تسجيل الدخول لكتابة مراجعة',
                login: 'تسجيل الدخول',
                register: 'إنشاء حساب',
                sortBy: 'ترتيب حسب',
                filterByRating: 'تصفية حسب التقييم',
                searchReviews: 'البحث في المراجعات',
                allRatings: 'جميع التقييمات',
                stars: 'نجوم',
                newest: 'الأحدث',
                oldest: 'الأقدم',
                highestRated: 'الأعلى تقييماً',
                lowestRated: 'الأقل تقييماً',
                mostHelpful: 'الأكثر فائدة',
                searchPlaceholder: 'البحث في المراجعات...',
                verified: 'مُتحقق',
                edit: 'تعديل',
                delete: 'حذف',
                recommendsThis: 'يوصي بهذا',
                doesNotRecommend: 'لا يوصي',
                helpful: 'مفيد',
                share: 'مشاركة',
                report: 'إبلاغ',
                loadMoreReviews: 'تحميل المزيد من المراجعات',
                loading: 'جاري التحميل...',
                noReviewsTitle: 'لا توجد مراجعات بعد',
                noReviewsMessage: 'كن أول من يكتب مراجعة!',
                email: 'البريد الإلكتروني',
                password: 'كلمة المرور',
                cancel: 'إلغاء',
                loggingIn: 'جاري تسجيل الدخول...',
                ratingLabels: ['', 'ضعيف', 'مقبول', 'جيد', 'جيد جداً', 'ممتاز']
            }
        },
        
        get translations() {
            return this.translations[this.locale] || this.translations.en;
        },
        
        async init() {
            await this.checkAuth();
            await this.loadReviews();
        },
        
        async checkAuth() {
            try {
                const response = await fetch(`/api/webblocs/auth/check`, {
                    headers: {
                        'X-API-Key': this.apiKey,
                        'X-Website-UUID': this.websiteUuid
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.user = data.user;
                    this.isAuthenticated = !!data.user;
                }
            } catch (error) {
                console.error('Auth check failed:', error);
            }
        },
        
        async loadReviews() {
            this.loading = this.currentPage === 1;
            this.error = false;
            
            try {
                const params = new URLSearchParams({
                    page_url: this.pageUrl,
                    sort: this.currentSort,
                    limit: this.limit,
                    page: this.currentPage
                });
                
                if (this.ratingFilter) params.append('rating', this.ratingFilter);
                if (this.searchQuery) params.append('search', this.searchQuery);
                
                const response = await fetch(`/api/webblocs/reviews?${params}`, {
                    headers: {
                        'X-API-Key': this.apiKey,
                        'X-Website-UUID': this.websiteUuid
                    }
                });
                
                if (!response.ok) {
                    throw new Error('Failed to load reviews');
                }
                
                const data = await response.json();
                
                if (this.currentPage === 1) {
                    this.reviews = data.data;
                } else {
                    this.reviews = [...this.reviews, ...data.data];
                }
                
                this.totalReviews = data.total;
                this.averageRating = data.average_rating || 0;
                this.ratingBreakdown = data.rating_breakdown || {};
                this.hasMore = data.current_page < data.last_page;
                
            } catch (error) {
                this.error = true;
                this.errorMessage = error.message;
            } finally {
                this.loading = false;
                this.loadingMore = false;
            }
        },
        
        async loadMore() {
            this.loadingMore = true;
            this.currentPage++;
            await this.loadReviews();
        },
        
        async submitReview() {
            if (!this.isAuthenticated) {
                this.showLogin = true;
                return;
            }
            
            if (this.newReview.rating === 0) {
                alert('Please select a rating');
                return;
            }
            
            this.submitting = true;
            
            try {
                const formData = new FormData();
                formData.append('rating', this.newReview.rating);
                formData.append('title', this.newReview.title);
                formData.append('content', this.newReview.content);
                formData.append('page_url', this.pageUrl);
                
                if (this.newReview.pros) formData.append('pros', this.newReview.pros);
                if (this.newReview.cons) formData.append('cons', this.newReview.cons);
                if (this.newReview.recommend !== null) formData.append('recommend', this.newReview.recommend);
                
                // Add images
                this.newReview.images.forEach((image, index) => {
                    formData.append(`images[${index}]`, image.file);
                });
                
                const response = await fetch('/api/webblocs/reviews', {
                    method: 'POST',
                    headers: {
                        'X-API-Key': this.apiKey,
                        'X-Website-UUID': this.websiteUuid,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error('Failed to submit review');
                }
                
                const review = await response.json();
                this.reviews.unshift(review);
                this.totalReviews++;
                
                // Reset form
                this.newReview = {
                    rating: 0,
                    title: '',
                    content: '',
                    pros: '',
                    cons: '',
                    recommend: null,
                    images: []
                };
                
                // Reload to update statistics
                this.currentPage = 1;
                await this.loadReviews();
                
            } catch (error) {
                alert('Failed to submit review: ' + error.message);
            } finally {
                this.submitting = false;
            }
        },
        
        async login() {
            this.loggingIn = true;
            
            try {
                const response = await fetch('/api/webblocs/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-API-Key': this.apiKey,
                        'X-Website-UUID': this.websiteUuid,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify(this.loginForm)
                });
                
                if (!response.ok) {
                    throw new Error('Login failed');
                }
                
                const data = await response.json();
                this.user = data.user;
                this.isAuthenticated = true;
                this.showLogin = false;
                this.loginForm = { email: '', password: '' };
                
            } catch (error) {
                alert('Login failed: ' + error.message);
            } finally {
                this.loggingIn = false;
            }
        },
        
        handleImageUpload(event) {
            const files = Array.from(event.target.files);
            const maxFiles = 5;
            const maxSize = 2 * 1024 * 1024; // 2MB
            
            if (this.newReview.images.length + files.length > maxFiles) {
                alert(`Maximum ${maxFiles} images allowed`);
                return;
            }
            
            files.forEach(file => {
                if (file.size > maxSize) {
                    alert(`File ${file.name} is too large. Maximum size is 2MB.`);
                    return;
                }
                
                if (!file.type.startsWith('image/')) {
                    alert(`File ${file.name} is not an image.`);
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.newReview.images.push({
                        file: file,
                        preview: e.target.result
                    });
                };
                reader.readAsDataURL(file);
            });
            
            // Clear the input
            event.target.value = '';
        },
        
        removeImage(index) {
            this.newReview.images.splice(index, 1);
        },
        
        getRatingText(rating) {
            const labels = this.translations.ratingLabels || ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
            return labels[rating] || '';
        },
        
        async toggleHelpful(review) {
            if (!this.isAuthenticated) {
                this.showLogin = true;
                return;
            }
            
            try {
                const response = await fetch(`/api/webblocs/reviews/${review.uuid}/helpful`, {
                    method: 'POST',
                    headers: {
                        'X-API-Key': this.apiKey,
                        'X-Website-UUID': this.websiteUuid,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    review.helpful_count = data.helpful_count;
                    review.user_voted_helpful = data.user_voted_helpful;
                }
            } catch (error) {
                console.error('Failed to toggle helpful vote:', error);
            }
        },
        
        shareReview(review) {
            if (navigator.share) {
                navigator.share({
                    title: review.title,
                    text: review.content,
                    url: window.location.href
                });
            } else {
                // Fallback: copy URL to clipboard
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Review URL copied to clipboard');
                });
            }
        },
        
        reportReview(review) {
            // Implement review reporting functionality
            const reason = prompt('Please specify the reason for reporting this review:');
            if (reason) {
                fetch(`/api/webblocs/reviews/${review.uuid}/report`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-API-Key': this.apiKey,
                        'X-Website-UUID': this.websiteUuid,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify({ reason })
                }).then(() => {
                    alert('Review reported successfully');
                }).catch(() => {
                    alert('Failed to report review');
                });
            }
        },
        
        openImageModal(imageUrl) {
            this.selectedImage = imageUrl;
            this.showImageModal = true;
        },
        
        async deleteReview(uuid) {
            if (!confirm('Are you sure you want to delete this review?')) {
                return;
            }
            
            try {
                const response = await fetch(`/api/webblocs/reviews/${uuid}`, {
                    method: 'DELETE',
                    headers: {
                        'X-API-Key': this.apiKey,
                        'X-Website-UUID': this.websiteUuid,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    this.reviews = this.reviews.filter(r => r.uuid !== uuid);
                    this.totalReviews--;
                    
                    // Reload to update statistics
                    this.currentPage = 1;
                    await this.loadReviews();
                }
            } catch (error) {
                alert('Failed to delete review');
            }
        },
        
        formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);
            
            if (minutes < 1) return this.translations.justNow || 'Just now';
            if (minutes < 60) return `${minutes}m`;
            if (hours < 24) return `${hours}h`;
            if (days < 7) return `${days}d`;
            
            return date.toLocaleDateString(this.locale);
        }
    }
}
</script>

<style>
.webbloc-reviews-container {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.webbloc-reviews-container[dir="rtl"] {
    direction: rtl;
    text-align: right;
}

.webbloc-reviews-container[dir="rtl"] .space-x-reverse > :not([hidden]) ~ :not([hidden]) {
    --tw-space-x-reverse: 1;
}

.review-item {
    transition: all 0.3s ease-in-out;
}

.review-item:hover {
    transform: translateY(-2px);
}

.reviews-summary {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.webbloc-reviews-container input,
.webbloc-reviews-container textarea,
.webbloc-reviews-container select {
    font-size: 14px;
}

.webbloc-reviews-container button {
    font-size: 14px;
    font-weight: 500;
}

/* Star rating hover effect */
.webbloc-reviews-container .rating-input button:hover svg {
    transform: scale(1.1);
}

/* Image preview styles */
.webbloc-reviews-container .image-preview {
    border: 2px dashed #e2e8f0;
    transition: border-color 0.2s ease;
}

.webbloc-reviews-container .image-preview:hover {
    border-color: #3b82f6;
}

@media (max-width: 640px) {
    .webbloc-reviews-container {
        font-size: 14px;
    }
    
    .review-item {
        padding: 16px;
    }
    
    .reviews-summary {
        padding: 16px;
    }
    
    .webbloc-reviews-container .filters {
        flex-direction: column;
        gap: 12px;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .webbloc-reviews-container .review-item {
        background-color: #1f2937;
        border-color: #374151;
        color: #f9fafb;
    }
    
    .webbloc-reviews-container .reviews-summary {
        background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
        color: #f9fafb;
    }
}
</style>
```

### resources/views/webblocs/auth.blade.php
```php
@props([
    'websiteUuid' => '',
    'apiKey' => '',
    'locale' => 'en',
    'redirectUrl' => '',
    'showRegister' => true,
    'showForgotPassword' => true
])

<div class="webbloc-auth" 
     x-data="authComponent({
        websiteUuid: '{{ $websiteUuid }}',
        apiKey: '{{ $apiKey }}',
        locale: '{{ $locale }}',
        redirectUrl: '{{ $redirectUrl }}',
        showRegister: {{ $showRegister ? 'true' : 'false' }},
        showForgotPassword: {{ $showForgotPassword ? 'true' : 'false' }}
     })"
     x-init="init()"
     {{ $attributes->merge(['class' => 'webbloc-auth-container']) }}>
    
    <!-- Authentication State Display -->
    <div x-show="isAuthenticated" class="authenticated-state">
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3" :class="locale === 'ar' ? 'space-x-reverse' : ''">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-semibold">
                            <span x-text="user?.name?.charAt(0)?.toUpperCase()"></span>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-green-800" x-text="translations.welcomeBack"></p>
                        <p class="text-sm text-green-600" x-text="user?.name"></p>
                    </div>
                </div>
                <button @click="logout()" 
                        class="text-sm text-green-600 hover:text-green-800 underline transition-colors"
                        x-text="translations.logout"></button>
            </div>
        </div>
    </div>

    <!-- Authentication Forms -->
    <div x-show="!isAuthenticated" class="auth-forms">
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="flex space-x-8" :class="locale === 'ar' ? 'space-x-reverse' : ''">
                <button @click="currentTab = 'login'" 
                        :class="currentTab === 'login' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors"
                        x-text="translations.login"></button>
                <button x-show="showRegister" @click="currentTab = 'register'" 
                        :class="currentTab === 'register' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors"
                        x-text="translations.register"></button>
                <button x-show="showForgotPassword" @click="currentTab = 'forgot'" 
                        :class="currentTab === 'forgot' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors"
                        x-text="translations.forgotPassword"></button>
            </nav>
        </div>

        <!-- Login Form -->
        <div x-show="currentTab === 'login'" x-transition class="login-form">
            <form @submit.prevent="login()">
                <div class="space-y-4">
                    <div>
                        <label for="login-email" class="block text-sm font-medium text-gray-700 mb-1" x-text="translations.email"></label>
                        <input id="login-email" type="email" x-model="loginForm.email" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               :placeholder="translations.emailPlaceholder">
                        <div x-show="loginErrors.email" class="mt-1 text-sm text-red-600" x-text="loginErrors.email"></div>
                    </div>
                    
                    <div>
                        <label for="login-password" class="block text-sm font-medium text-gray-700 mb-1" x-text="translations.password"></label>
                        <div class="relative">
                            <input :id="'login-password-' + componentId" 
                                   :type="showLoginPassword ? 'text' : 'password'" 
                                   x-model="loginForm.password" required
                                   class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   :placeholder="translations.passwordPlaceholder">
                            <button type="button" @click="showLoginPassword = !showLoginPassword" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg x-show="!showLoginPassword" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showLoginPassword" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <div x-show="loginErrors.password" class="mt-1 text-sm text-red-600" x-text="loginErrors.password"></div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" x-model="loginForm.remember" 
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-600" x-text="translations.rememberMe"></span>
                        </label>
                        <button type="button" @click="currentTab = 'forgot'" 
                                class="text-sm text-blue-600 hover:text-blue-800 underline transition-colors"
                                x-text="translations.forgotPassword"></button>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" :disabled="loggingIn"
                            class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <span x-show="!loggingIn" x-text="translations.signIn"></span>
                        <span x-show="loggingIn" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="translations.signingIn"></span>
                        </span>
                    </button>
                </div>

                <div x-show="loginError" class="mt-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-md">
                    <div class="flex">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span x-text="loginError"></span>
                    </div>
                </div>
            </form>
        </div>

        <!-- Register Form -->
        <div x-show="currentTab === 'register'" x-transition class="register-form">
            <form @submit.prevent="register()">
                <div class="space-y-4">
                    <div>
                        <label for="register-name" class="block text-sm font-medium text-gray-700 mb-1" x-text="translations.fullName"></label>
                        <input id="register-name" type="text" x-model="registerForm.name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               :placeholder="translations.fullNamePlaceholder">
                        <div x-show="registerErrors.name" class="mt-1 text-sm text-red-600" x-text="registerErrors.name"></div>
                    </div>

                    <div>
                        <label for="register-email" class="block text-sm font-medium text-gray-700 mb-1" x-text="translations.email"></label>
                        <input id="register-email" type="email" x-model="registerForm.email" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               :placeholder="translations.emailPlaceholder">
                        <div x-show="registerErrors.email" class="mt-1 text-sm text-red-600" x-text="registerErrors.email"></div>
                    </div>

                    <div>
                        <label for="register-password" class="block text-sm font-medium text-gray-700 mb-1" x-text="translations.password"></label>
                        <div class="relative">
                            <input :id="'register-password-' + componentId" 
                                   :type="showRegisterPassword ? 'text' : 'password'" 
                                   x-model="registerForm.password" required
                                   class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   :placeholder="translations.passwordPlaceholder"
                                   @input="checkPasswordStrength()">
                            <button type="button" @click="showRegisterPassword = !showRegisterPassword" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg x-show="!showRegisterPassword" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showRegisterPassword" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Password Strength Indicator -->
                        <div x-show="registerForm.password.length > 0" class="mt-2">
                            <div class="flex items-center space-x-2 text-xs">
                                <span x-text="translations.passwordStrength + ':'"></span>
                                <span :class="getPasswordStrengthColor()" x-text="getPasswordStrengthText()"></span>
                            </div>
                            <div class="mt-1 h-1 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full transition-all duration-300" 
                                     :class="getPasswordStrengthColor()" 
                                     :style="`width: ${passwordStrength.score * 25}%`"></div>
                            </div>
                            <div x-show="passwordStrength.suggestions.length > 0" class="mt-1 text-xs text-gray-600">
                                <ul class="list-disc list-inside space-y-1">
                                    <template x-for="suggestion in passwordStrength.suggestions" :key="suggestion">
                                        <li x-text="suggestion"></li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                        
                        <div x-show="registerErrors.password" class="mt-1 text-sm text-red-600" x-text="registerErrors.password"></div>
                    </div>

                    <div>
                        <label for="register-password-confirmation" class="block text-sm font-medium text-gray-700 mb-1" x-text="translations.confirmPassword"></label>
                        <input id="register-password-confirmation" type="password" x-model="registerForm.password_confirmation" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               :placeholder="translations.confirmPasswordPlaceholder">
                        <div x-show="registerErrors.password_confirmation" class="mt-1 text-sm text-red-600" x-text="registerErrors.password_confirmation"></div>
                    </div>

                    <div>
                        <label class="flex items-start">
                            <input type="checkbox" x-model="registerForm.agree_terms" required
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 mt-1">
                            <span class="ml-2 text-sm text-gray-600">
                                <span x-text="translations.agreeToTerms"></span>
                                <a href="#" class="text-blue-600 hover:text-blue-800 underline" x-text="translations.termsOfService"></a>
                                <span x-text="translations.and"></span>
                                <a href="#" class="text-blue-600 hover:text-blue-800 underline" x-text="translations.privacyPolicy"></a>
                            </span>
                        </label>
                        <div x-show="registerErrors.agree_terms" class="mt-1 text-sm text-red-600" x-text="registerErrors.agree_terms"></div>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" :disabled="registering || !registerForm.agree_terms"
                            class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <span x-show="!registering" x-text="translations.createAccount"></span>
                        <span x-show="registering" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="translations.creatingAccount"></span>
                        </span>
                    </button>
                </div>

                <div x-show="registerError" class="mt-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-md">
                    <div class="flex">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span x-text="registerError"></span>
                    </div>
                </div>

                <div x-show="registerSuccess" class="mt-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-md">
                    <div class="flex">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span x-text="translations.registrationSuccess"></span>
                    </div>
                </div>
            </form>
        </div>

        <!-- Forgot Password Form -->
        <div x-show="currentTab === 'forgot'" x-transition class="forgot-form">
            <div class="mb-4 text-sm text-gray-600" x-text="translations.forgotPasswordInstructions"></div>
            
            <form @submit.prevent="forgotPassword()">
                <div class="space-y-4">
                    <div>
                        <label for="forgot-email" class="block text-sm font-medium text-gray-700 mb-1" x-text="translations.email"></label>
                        <input id="forgot-email" type="email" x-model="forgotForm.email" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               :placeholder="translations.emailPlaceholder">
                        <div x-show="forgotErrors.email" class="mt-1 text-sm text-red-600" x-text="forgotErrors.email"></div>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" :disabled="sendingReset"
                            class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <span x-show="!sendingReset" x-text="translations.sendResetLink"></span>
                        <span x-show="sendingReset" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="translations.sendingLink"></span>
                        </span>
                    </button>
                </div>

                <div class="mt-4 text-center">
                    <button type="button" @click="currentTab = 'login'" 
                            class="text-sm text-blue-600 hover:text-blue-800 underline transition-colors"
                            x-text="translations.backToLogin"></button>
                </div>

                <div x-show="forgotError" class="mt-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-md">
                    <div class="flex">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span x-text="forgotError"></span>
                    </div>
                </div>

                <div x-show="forgotSuccess" class="mt-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-md">
                    <div class="flex">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span x-text="translations.resetLinkSent"></span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function authComponent(config) {
    return {
        // Configuration
        websiteUuid: config.websiteUuid,
        apiKey: config.apiKey,
        locale: config.locale,
        redirectUrl: config.redirectUrl,
        showRegister: config.showRegister,
        showForgotPassword: config.showForgotPassword,
        componentId: Math.random().toString(36).substr(2, 9),
        
        // State
        isAuthenticated: false,
        user: null,
        currentTab: 'login',
        
        // Loading states
        loggingIn: false,
        registering: false,
        sendingReset: false,
        
        // Password visibility
        showLoginPassword: false,
        showRegisterPassword: false,
        
        // Password strength
        passwordStrength: {
            score: 0,
            suggestions: []
        },
        
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
            password_confirmation: '',
            agree_terms: false
        },
        forgotForm: {
            email: ''
        },
        
        // Errors
        loginError: '',
        loginErrors: {},
        registerError: '',
        registerErrors: {},
        registerSuccess: false,
        forgotError: '',
        forgotErrors: {},
        forgotSuccess: false,
        
        // Translations
        translations: {
            en: {
                welcomeBack: 'Welcome back!',
                logout: 'Logout',
                login: 'Login',
                register: 'Register',
                forgotPassword: 'Forgot Password',
                email: 'Email',
                emailPlaceholder: 'Enter your email',
                password: 'Password',
                passwordPlaceholder: 'Enter your password',
                fullName: 'Full Name',
                fullNamePlaceholder: 'Enter your full name',
                confirmPassword: 'Confirm Password',
                confirmPasswordPlaceholder: 'Confirm your password',
                rememberMe: 'Remember me',
                signIn: 'Sign In',
                signingIn: 'Signing in...',
                createAccount: 'Create Account',
                creatingAccount: 'Creating account...',
                sendResetLink: 'Send Reset Link',
                sendingLink: 'Sending...',
                backToLogin: 'Back to Login',
                agreeToTerms: 'I agree to the',
                termsOfService: 'Terms of Service',
                and: 'and',
                privacyPolicy: 'Privacy Policy',
                passwordStrength: 'Password strength',
                weak: 'Weak',
                fair: 'Fair',
                good: 'Good',
                strong: 'Strong',
                forgotPasswordInstructions: 'Enter your email address and we\'ll send you a link to reset your password.',
                registrationSuccess: 'Registration successful! Please check your email to verify your account.',
                resetLinkSent: 'Password reset link has been sent to your email.',
                loginSuccess: 'Login successful!',
                logoutSuccess: 'Logged out successfully!'
            },
            ar: {
                welcomeBack: 'مرحباً بعودتك!',
                logout: 'تسجيل الخروج',
                login: 'تسجيل الدخول',
                register: 'إنشاء حساب',
                forgotPassword: 'نسيت كلمة المرور',
                email: 'البريد الإلكتروني',
                emailPlaceholder: 'أدخل بريدك الإلكتروني',
                password: 'كلمة المرور',
                passwordPlaceholder: 'أدخل كلمة المرور',
                fullName: 'الاسم الكامل',
                fullNamePlaceholder: 'أدخل اسمك الكامل',
                confirmPassword: 'تأكيد كلمة المرور',
                confirmPasswordPlaceholder: 'أكد كلمة المرور',
                rememberMe: 'تذكرني',
                signIn: 'تسجيل الدخول',
                signingIn: 'جاري تسجيل الدخول...',
                createAccount: 'إنشاء حساب',
                creatingAccount: 'جاري إنشاء الحساب...',
                sendResetLink: 'إرسال رابط إعادة التعيين',
                sendingLink: 'جاري الإرسال...',
                backToLogin: 'العودة لتسجيل الدخول',
                agreeToTerms: 'أوافق على',
                termsOfService: 'شروط الخدمة',
                and: 'و',
                privacyPolicy: 'سياسة الخصوصية',
                passwordStrength: 'قوة كلمة المرور',
                weak: 'ضعيفة',
                fair: 'مقبولة',
                good: 'جيدة',
                strong: 'قوية',
                forgotPasswordInstructions: 'أدخل عنوان بريدك الإلكتروني وسنرسل لك رابطاً لإعادة تعيين كلمة المرور.',
                registrationSuccess: 'تم التسجيل بنجاح! يرجى التحقق من بريدك الإلكتروني لتأكيد حسابك.',
                resetLinkSent: 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني.',
                loginSuccess: 'تم تسجيل الدخول بنجاح!',
                logoutSuccess: 'تم تسجيل الخروج بنجاح!'
            }
        },
        
        get translations() {
            return this.translations[this.locale] || this.translations.en;
        },
        
        async init() {
            await this.checkAuth();
        },
        
        async checkAuth() {
            try {
                const response = await fetch(`/api/webblocs/auth/check`, {
                    headers: {
                        'X-API-Key': this.apiKey,
                        'X-Website-UUID': this.websiteUuid
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.user = data.user;
                    this.isAuthenticated = !!data.user;
                }
            } catch (error) {
                console.error('Auth check failed:', error);
                this.isAuthenticated = false;
            }
        },
        
        async login() {
            this.loggingIn = true;
            this.loginError = '';
            this.loginErrors = {};
            
            try {
                const response = await fetch('/api/webblocs/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-API-Key': this.apiKey,
                        'X-Website-UUID': this.websiteUuid,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify(this.loginForm)
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    if (data.errors) {
                        this.loginErrors = data.errors;
                    } else {
                        this.loginError = data.message || 'Login failed';
                    }
                    return;
                }
                
                this.user = data.user;
                this.isAuthenticated = true;
                this.loginForm = { email: '', password: '', remember: false };
                
                // Show success message
                this.showMessage(this.translations.loginSuccess, 'success');
                
                // Redirect if specified
                if (this.redirectUrl) {
                    setTimeout(() => {
                        window.location.href = this.redirectUrl;
                    }, 1000);
                }
                
            } catch (error) {
                this.loginError = 'Network error occurred';
            } finally {
                this.loggingIn = false;
            }
        },
        
        async register() {
            this.registering = true;
            this.registerError = '';
            this.registerErrors = {};
            this.registerSuccess = false;
            
            try {
                const response = await fetch('/api/webblocs/auth/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-API-Key': this.apiKey,
                        'X-Website-UUID': this.websiteUuid,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify(this.registerForm)
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    if (data.errors) {
                        this.registerErrors = data.errors;
                    } else {
                        this.registerError = data.message || 'Registration failed';
                    }
                    return;
                }
                
                this.registerSuccess = true;
                this.registerForm = {
                    name: '',
                    email: '',
                    password: '',
                    password_confirmation: '',
                    agree_terms: false
                };
                
                // Switch to login tab after a delay
                setTimeout(() => {
                    this.currentTab = 'login';
                    this.registerSuccess = false;
                }, 3000);
                
            } catch (error) {
                this.registerError = 'Network error occurred';
            } finally {
                this.registering = false;
            }
        },
        
        async forgotPassword() {
            this.sendingReset = true;
            this.forgotError = '';
            this.forgotErrors = {};
            this.forgotSuccess = false;
            
            try {
                const response = await fetch('/api/webblocs/auth/forgot-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-API-Key': this.apiKey,
                        'X-Website-UUID': this.websiteUuid,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify(this.forgotForm)
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    if (data.errors) {
                        this.forgotErrors = data.errors;
                    } else {
                        this.forgotError = data.message || 'Failed to send reset link';
                    }
                    return;
                }
                
                this.forgotSuccess = true;
                this.forgotForm = { email: '' };
                
            } catch (error) {
                this.forgotError = 'Network error occurred';
            } finally {
                this.sendingReset = false;
            }
        },
        
        async logout() {
            try {
                const response = await fetch('/api/webblocs/auth/logout', {
                    method: 'POST',
                    headers: {
                        'X-API-Key': this.apiKey,
                        'X-Website-UUID': this.websiteUuid,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });
                
                this.user = null;
                this.isAuthenticated = false;
                
                // Show success message
                this.showMessage(this.translations.logoutSuccess, 'success');
                
                // Redirect if specified
                if (this.redirectUrl) {
                    setTimeout(() => {
                        window.location.href = this.redirectUrl;
                    }, 1000);
                }
                
            } catch (error) {
                console.error('Logout failed:', error);
            }
        },
        
        checkPasswordStrength() {
            const password = this.registerForm.password;
            let score = 0;
            const suggestions = [];
            
            if (password.length === 0) {
                this.passwordStrength = { score: 0, suggestions: [] };
                return;
            }
            
            // Length check
            if (password.length >= 8) score++;
            else suggestions.push('Use at least 8 characters');
            
            // Lowercase check
            if (/[a-z]/.test(password)) score++;
            else suggestions.push('Include lowercase letters');
            
            // Uppercase check
            if (/[A-Z]/.test(password)) score++;
            else suggestions.push('Include uppercase letters');
            
            // Number check
            if (/\d/.test(password)) score++;
            else suggestions.push('Include numbers');
            
            // Special character check
