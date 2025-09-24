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