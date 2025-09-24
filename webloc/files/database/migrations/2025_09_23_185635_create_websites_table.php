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
        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('url')->unique();
            $table->string('description', 500)->nullable();
            $table->string('domain')->index();
            
            // SQLite Database Configuration
            $table->string('sqlite_database_path');
            $table->string('sqlite_database_name')->unique();
            $table->boolean('sqlite_database_exists')->default(false);
            
            // Owner Information
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('owner_email');
            
            // Website Status and Configuration
            $table->enum('status', ['pending', 'active', 'suspended', 'inactive'])->default('pending');
            $table->boolean('verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            
            // Subscription and Limits
            $table->enum('subscription_type', ['free', 'basic', 'pro', 'enterprise'])->default('free');
            $table->json('subscription_limits')->nullable(); // API calls, storage, etc.
            $table->timestamp('subscription_expires_at')->nullable();
            
            // WebBloc Configuration
            $table->json('enabled_webblocs')->nullable(); // List of enabled WebBloc types
            $table->json('webbloc_settings')->nullable(); // Custom settings per WebBloc type
            
            // API Configuration
            $table->string('public_api_key', 64)->unique();
            $table->string('secret_api_key', 128)->unique();
            $table->json('api_permissions')->nullable();
            $table->integer('api_rate_limit')->default(1000); // requests per hour
            
            // Usage Statistics
            $table->bigInteger('total_api_calls')->default(0);
            $table->bigInteger('monthly_api_calls')->default(0);
            $table->timestamp('last_api_call_at')->nullable();
            
            // CDN and Assets
            $table->string('cdn_url')->nullable();
            $table->json('custom_css')->nullable();
            $table->json('custom_js')->nullable();
            
            // Security Settings
            $table->json('allowed_domains')->nullable();
            $table->json('cors_settings')->nullable();
            $table->boolean('ssl_required')->default(true);
            
            // Metadata and Settings
            $table->json('metadata')->nullable();
            $table->json('settings')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['status', 'verified']);
            $table->index(['owner_id', 'status']);
            $table->index(['domain', 'status']);
            $table->index(['subscription_type', 'status']);
            $table->index(['created_at']);
            $table->index(['last_api_call_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('websites');
    }
};