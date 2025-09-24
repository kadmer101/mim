<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_bloc_instances', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('website_id')->constrained()->onDelete('cascade');
            $table->foreignId('web_bloc_id')->constrained()->onDelete('cascade');
            $table->foreignId('installed_by')->constrained('users')->onDelete('cascade');
            
            // Instance Configuration
            $table->string('instance_name', 100);
            $table->string('instance_identifier', 50); // Unique per website
            $table->json('configuration')->nullable(); // Custom configuration for this instance
            $table->json('attributes_override')->nullable(); // Override default attributes
            
            // Page and Location Information
            $table->string('page_url', 500)->nullable(); // Where it's used (if specific)
            $table->json('page_urls')->nullable(); // Multiple pages if applicable
            $table->string('container_selector')->nullable(); // CSS selector for placement
            $table->integer('display_order')->default(0);
            
            // Status and Lifecycle
            $table->enum('status', ['active', 'inactive', 'configured', 'error'])->default('configured');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            
            // Usage Statistics for this Instance
            $table->bigInteger('total_loads')->default(0);
            $table->bigInteger('total_interactions')->default(0);
            $table->decimal('avg_load_time', 8, 3)->default(0);
            $table->timestamp('last_interaction_at')->nullable();
            
            // Customization and Theming
            $table->json('custom_css')->nullable(); // Instance-specific CSS
            $table->json('custom_settings')->nullable(); // Custom display settings
            $table->string('theme', 50)->default('default');
            $table->json('theme_options')->nullable();
            
            // Integration Details
            $table->text('integration_code')->nullable(); // Generated HTML/JS for integration
            $table->json('cdn_assets')->nullable(); // Required CDN assets for this instance
            $table->boolean('auto_update')->default(true); // Auto-update when WebBloc updates
            
            // Permissions and Access Control
            $table->json('permissions')->nullable(); // Instance-specific permissions
            $table->json('user_roles')->nullable(); // Allowed user roles
            $table->boolean('public_access')->default(true);
            
            // Error Handling and Monitoring
            $table->json('error_log')->nullable(); // Recent errors for this instance
            $table->integer('error_count')->default(0);
            $table->timestamp('last_error_at')->nullable();
            $table->boolean('monitoring_enabled')->default(true);
            
            // Performance and Caching
            $table->boolean('cache_enabled')->default(true);
            $table->integer('cache_ttl')->nullable(); // Override default cache TTL
            $table->json('performance_metrics')->nullable(); // Performance data
            
            // Backup and Versioning
            $table->string('webbloc_version_installed', 20); // Version when installed
            $table->timestamp('last_updated_at')->nullable();
            $table->json('update_history')->nullable(); // Update log
            
            // Environment and Context
            $table->enum('environment', ['development', 'staging', 'production'])->default('production');
            $table->json('environment_variables')->nullable(); // Environment-specific settings
            
            // Analytics and Tracking
            $table->boolean('analytics_enabled')->default(true);
            $table->json('tracking_settings')->nullable();
            $table->string('google_analytics_id')->nullable();
            
            // Content and Data
            $table->json('initial_data')->nullable(); // Pre-populated data
            $table->boolean('data_export_enabled')->default(false);
            $table->timestamp('last_backup_at')->nullable();
            
            // Notifications and Alerts
            $table->json('notification_settings')->nullable();
            $table->boolean('email_notifications')->default(false);
            $table->string('notification_email')->nullable();
            
            // API and Webhook Configuration
            $table->json('api_settings')->nullable(); // Instance-specific API settings
            $table->string('webhook_url')->nullable();
            $table->json('webhook_events')->nullable();
            
            // Metadata and Notes
            $table->text('description')->nullable();
            $table->json('notes')->nullable(); // Admin/user notes
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->unique(['website_id', 'instance_identifier']);
            $table->index(['website_id', 'web_bloc_id']);
            $table->index(['website_id', 'status']);
            $table->index(['web_bloc_id', 'status']);
            $table->index(['status', 'activated_at']);
            $table->index(['last_used_at']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('web_bloc_instances');
    }
};