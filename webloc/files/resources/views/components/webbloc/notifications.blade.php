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