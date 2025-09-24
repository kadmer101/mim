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