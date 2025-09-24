<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for SQLite per-website database.
     * This table stores WebBloc instances and their data.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_blocs', function (Blueprint $table) {
            $table->id();
            
            // WebBloc Type and Reference
            $table->string('webbloc_type', 50); // comment, review, auth, testimonial, etc.
            $table->integer('webbloc_definition_id')->nullable(); // References central MySQL web_blocs table
            
            // User and Content Association
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('page_url', 500); // URL where this WebBloc instance appears
            $table->string('page_title')->nullable();
            $table->string('section_identifier')->nullable(); // Optional section/container ID
            
            // WebBloc Data (JSON format for flexibility)
            $table->json('data'); // Main content data specific to WebBloc type
            $table->json('attributes')->nullable(); // Runtime attributes (limit, sort, etc.)
            $table->json('metadata')->nullable(); // Additional metadata
            
            // Status and Moderation
            $table->enum('status', ['active', 'pending', 'approved', 'rejected', 'spam', 'deleted'])->default('active');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('moderation_notes')->nullable();
            
            // Hierarchy and Relationships
            $table->foreignId('parent_id')->nullable()->constrained('web_blocs')->onDelete('cascade');
            $table->integer('sort_order')->default(0);
            $table->integer('depth')->default(0); // For nested structures like comment threads
            
            // Engagement and Interaction
            $table->integer('likes_count')->default(0);
            $table->integer('dislikes_count')->default(0);
            $table->integer('views_count')->default(0);
            $table->integer('replies_count')->default(0);
            $table->integer('shares_count')->default(0);
            $table->decimal('rating', 3, 2)->nullable(); // For reviews (1.00 to 5.00)
            
            // User Interactions Tracking
            $table->json('user_interactions')->nullable(); // Track who liked, shared, etc.
            
            // Content and Media
            $table->text('content')->nullable(); // Main text content
            $table->text('excerpt')->nullable(); // Short summary
            $table->json('attachments')->nullable(); // Files, images, etc.
            $table->json('media')->nullable(); // Embedded media (videos, images)
            
            // SEO and Searchability
            $table->string('slug')->nullable();
            $table->json('tags')->nullable(); // Content tags
            $table->text('search_content')->nullable(); // Searchable text content
            
            // Geolocation (if applicable)
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_name')->nullable();
            
            // Versioning and History
            $table->integer('version')->default(1);
            $table->foreignId('original_id')->nullable()->constrained('web_blocs')->onDelete('set null');
            $table->timestamp('last_modified_at')->nullable();
            $table->foreignId('last_modified_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Spam and Abuse Prevention
            $table->integer('report_count')->default(0);
            $table->json('reports')->nullable(); // Abuse reports
            $table->boolean('is_flagged')->default(false);
            $table->string('spam_score', 10)->nullable();
            
            // Scheduling and Publication
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_pinned')->default(false);
            
            // IP and User Agent (for security)
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            
            // Notification Settings
            $table->boolean('notify_on_reply')->default(true);
            $table->boolean('notify_on_like')->default(false);
            $table->json('notification_settings')->nullable();
            
            // Custom Fields for Different WebBloc Types
            $table->json('custom_fields')->nullable(); // Type-specific additional fields
            
            // API and External Integration
            $table->string('external_id')->nullable(); // For external system integration
            $table->json('external_data')->nullable(); // External system data
            
            // Performance and Caching
            $table->timestamp('cached_at')->nullable();
            $table->json('cache_tags')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        // Create comprehensive indexes for SQLite performance
        Schema::table('web_blocs', function (Blueprint $table) {
            // Primary lookup indexes
            $table->index(['webbloc_type', 'status']);
            $table->index(['page_url', 'webbloc_type']);
            $table->index(['user_id', 'webbloc_type']);
            
            // Status and moderation indexes
            $table->index(['status', 'created_at']);
            $table->index(['status', 'approved_at']);
            
            // Hierarchy and sorting indexes
            $table->index(['parent_id', 'sort_order']);
            $table->index(['parent_id', 'created_at']);
            
            // Engagement indexes
            $table->index(['likes_count']);
            $table->index(['views_count']);
            $table->index(['rating']);
            
            // Time-based indexes
            $table->index(['created_at']);
            $table->index(['updated_at']);
            $table->index(['published_at']);
            
            // Search and filtering indexes
            $table->index(['webbloc_type', 'is_featured']);
            $table->index(['webbloc_type', 'is_pinned']);
            $table->index(['is_flagged', 'status']);
            
            // Geolocation index (if using spatial queries)
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('web_blocs');
    }
};