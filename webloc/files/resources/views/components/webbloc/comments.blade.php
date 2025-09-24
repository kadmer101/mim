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