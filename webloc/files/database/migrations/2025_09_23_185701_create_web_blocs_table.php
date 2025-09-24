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
        Schema::create('web_blocs', function (Blueprint $table) {
            $table->id();
            
            // WebBloc Standard Fields
            $table->string('type', 50)->index(); // comment, review, auth, testimonial, etc.
            $table->string('name', 100);
            $table->string('version', 20)->default('1.0.0');
            $table->text('description')->nullable();
            
            // WebBloc Standard: Attributes
            $table->json('attributes'); // Configuration attributes like limit, sort, etc.
            $table->json('default_attributes')->nullable(); // Fallback attributes
            
            // WebBloc Standard: CRUD Operations
            $table->json('crud'); // {"create": true, "read": true, "update": true, "delete": true}
            
            // WebBloc Standard: Metadata
            $table->json('metadata')->nullable(); // Additional metadata
            
            // Component Structure
            $table->text('blade_component')->nullable(); // Blade template content
            $table->text('alpine_component')->nullable(); // Alpine.js component code
            $table->text('css_styles')->nullable(); // Component CSS
            $table->json('dependencies')->nullable(); // Required JS/CSS dependencies
            
            // API and Integration
            $table->json('api_endpoints')->nullable(); // Available API endpoints for this WebBloc
            $table->json('permissions')->nullable(); // Required permissions
            $table->string('integration_syntax', 500)->nullable(); // HTML integration code
            
            // Configuration and Settings
            $table->json('configuration_schema')->nullable(); // JSON schema for configuration
            $table->json('validation_rules')->nullable(); // Laravel validation rules
            $table->json('display_options')->nullable(); // Theme, layout options
            
            // Developer Information
            $table->string('author', 100)->nullable();
            $table->string('author_email')->nullable();
            $table->string('license', 50)->default('MIT');
            $table->string('repository_url')->nullable();
            $table->text('documentation_url')->nullable();
            
            // Status and Lifecycle
            $table->enum('status', ['draft', 'active', 'deprecated', 'disabled'])->default('draft');
            $table->boolean('is_core')->default(false); // Core WebBloc vs community
            $table->boolean('is_public')->default(true);
            $table->timestamp('published_at')->nullable();
            
            // Compatibility and Requirements
            $table->string('min_php_version', 10)->default('8.1');
            $table->string('min_laravel_version', 10)->default('10.0');
            $table->json('required_packages')->nullable(); // Composer/npm dependencies
            
            // Usage and Analytics
            $table->bigInteger('installation_count')->default(0);
            $table->bigInteger('usage_count')->default(0); // Total instances across all websites
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('review_count')->default(0);
            
            // File and Asset Management
            $table->string('icon_path')->nullable();
            $table->json('screenshots')->nullable(); // Array of screenshot URLs
            $table->json('assets')->nullable(); // CSS, JS, image assets
            
            // Internationalization
            $table->json('supported_locales')->nullable(); // Supported languages
            $table->json('translations')->nullable(); // Translation keys and defaults
            
            // Security and Validation
            $table->json('security_features')->nullable(); // Security configurations
            $table->boolean('sanitize_input')->default(true);
            $table->boolean('validate_permissions')->default(true);
            
            // Performance Settings
            $table->boolean('cacheable')->default(true);
            $table->integer('cache_ttl')->default(300); // seconds
            $table->boolean('lazy_load')->default(false);
            
            // WebBloc Categories and Tags
            $table->string('category', 50)->nullable(); // UI, Data, Social, etc.
            $table->json('tags')->nullable(); // ["authentication", "user-management"]
            
            // Installation and Setup
            $table->text('installation_instructions')->nullable();
            $table->json('setup_commands')->nullable(); // Artisan commands to run
            $table->text('migration_code')->nullable(); // Custom migration code if needed
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->unique(['type', 'version']);
            $table->index(['type', 'status']);
            $table->index(['status', 'is_public']);
            $table->index(['category', 'status']);
            $table->index(['is_core', 'status']);
            $table->index(['installation_count']);
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
        Schema::dropIfExists('web_blocs');
    }
};