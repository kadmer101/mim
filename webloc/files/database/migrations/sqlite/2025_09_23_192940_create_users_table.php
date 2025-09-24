<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for SQLite per-website database.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // Basic User Information
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable(); // Nullable for social login users
            $table->rememberToken();
            
            // Profile Information
            $table->string('username', 50)->unique()->nullable();
            $table->string('first_name', 50)->nullable();
            $table->string('last_name', 50)->nullable();
            $table->text('bio')->nullable();
            $table->string('avatar')->nullable();
            $table->string('website')->nullable();
            
            // Contact Information
            $table->string('phone', 20)->nullable();
            $table->string('country', 2)->nullable(); // ISO country code
            $table->string('timezone', 50)->nullable();
            $table->string('locale', 5)->default('en');
            
            // Account Status and Settings
            $table->enum('status', ['active', 'inactive', 'banned', 'pending'])->default('active');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            
            // User Preferences
            $table->json('preferences')->nullable(); // User preferences and settings
            $table->json('notification_settings')->nullable();
            $table->boolean('email_notifications')->default(true);
            $table->boolean('marketing_emails')->default(false);
            
            // Social Authentication
            $table->string('provider')->nullable(); // google, github, facebook, etc.
            $table->string('provider_id')->nullable();
            $table->json('provider_data')->nullable();
            
            // Security and Privacy
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_secret')->nullable();
            $table->json('two_factor_recovery_codes')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->integer('login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            
            // User Roles and Permissions (Website-specific)
            $table->json('roles')->nullable(); // ["subscriber", "contributor", "moderator"]
            $table->json('permissions')->nullable(); // Specific permissions array
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_moderator')->default(false);
            
            // Activity and Engagement
            $table->integer('comment_count')->default(0);
            $table->integer('review_count')->default(0);
            $table->integer('post_count')->default(0);
            $table->decimal('reputation_score', 8, 2)->default(0);
            $table->integer('points')->default(0);
            
            // Content Moderation
            $table->integer('reported_count')->default(0);
            $table->integer('approved_count')->default(0);
            $table->boolean('auto_approve_content')->default(false);
            
            // WebBloc Specific Fields
            $table->json('webbloc_permissions')->nullable(); // Permissions per WebBloc type
            $table->json('webbloc_settings')->nullable(); // User settings per WebBloc
            
            // API and Integration
            $table->string('api_token')->nullable();
            $table->timestamp('api_token_expires_at')->nullable();
            
            // Subscription and Membership (if applicable)
            $table->enum('membership_type', ['guest', 'member', 'premium', 'vip'])->default('guest');
            $table->timestamp('membership_expires_at')->nullable();
            
            // Metadata and Custom Fields
            $table->json('metadata')->nullable(); // Flexible metadata storage
            $table->json('custom_fields')->nullable(); // Website-specific custom fields
            
            // GDPR and Privacy
            $table->boolean('gdpr_consent')->default(false);
            $table->timestamp('gdpr_consent_at')->nullable();
            $table->boolean('data_export_requested')->default(false);
            $table->timestamp('data_export_requested_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        // Create indexes for better performance in SQLite
        Schema::table('users', function (Blueprint $table) {
            $table->index(['email', 'status']);
            $table->index(['username']);
            $table->index(['status', 'is_verified']);
            $table->index(['last_login_at']);
            $table->index(['created_at']);
            $table->index(['provider', 'provider_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};