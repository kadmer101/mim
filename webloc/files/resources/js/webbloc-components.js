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