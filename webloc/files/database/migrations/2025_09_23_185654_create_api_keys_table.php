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
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('website_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // API Key Information
            $table->string('name', 100); // Human-readable name
            $table->string('public_key', 64)->unique();
            $table->string('secret_key', 128)->unique()->nullable();
            $table->string('key_type', 20)->default('standard'); // standard, webhook, admin
            
            // Permissions and Access Control
            $table->json('permissions')->nullable(); // Specific permissions array
            $table->json('allowed_webbloc_types')->nullable(); // Restrict to specific WebBloc types
            $table->json('allowed_domains')->nullable(); // Domain restrictions
            $table->json('allowed_ips')->nullable(); // IP whitelist
            
            // Rate Limiting
            $table->integer('rate_limit_per_minute')->default(60);
            $table->integer('rate_limit_per_hour')->default(1000);
            $table->integer('rate_limit_per_day')->default(10000);
            
            // Status and Lifecycle
            $table->enum('status', ['active', 'inactive', 'suspended', 'revoked'])->default('active');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            
            // Usage Statistics
            $table->bigInteger('total_requests')->default(0);
            $table->bigInteger('successful_requests')->default(0);
            $table->bigInteger('failed_requests')->default(0);
            $table->bigInteger('current_month_requests')->default(0);
            $table->bigInteger('current_day_requests')->default(0);
            
            // Security Features
            $table->boolean('requires_signature')->default(false);
            $table->string('signature_algorithm', 20)->default('hmac-sha256');
            $table->integer('max_request_size')->default(1048576); // 1MB default
            $table->json('security_settings')->nullable();
            
            // Environment and Context
            $table->enum('environment', ['development', 'staging', 'production'])->default('production');
            $table->string('user_agent_pattern')->nullable();
            $table->string('referer_pattern')->nullable();
            
            // Webhook Configuration (if applicable)
            $table->string('webhook_url')->nullable();
            $table->json('webhook_events')->nullable();
            $table->string('webhook_secret')->nullable();
            
            // Monitoring and Alerts
            $table->boolean('monitoring_enabled')->default(true);
            $table->json('alert_thresholds')->nullable(); // Usage, error rate thresholds
            $table->string('alert_email')->nullable();
            
            // API Key Metadata
            $table->string('description', 500)->nullable();
            $table->json('tags')->nullable(); // Organizational tags
            $table->json('metadata')->nullable(); // Custom metadata
            
            // Audit Trail
            $table->string('created_by_ip')->nullable();
            $table->string('last_used_ip')->nullable();
            $table->json('usage_history')->nullable(); // Recent usage summary
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['website_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['status', 'expires_at']);
            $table->index(['key_type', 'status']);
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
        Schema::dropIfExists('api_keys');
    }
};