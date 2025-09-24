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
        Schema::create('website_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->onDelete('cascade');
            
            // Date and Period Information
            $table->date('date');
            $table->enum('period_type', ['daily', 'weekly', 'monthly', 'yearly'])->default('daily');
            $table->integer('year');
            $table->integer('month')->nullable();
            $table->integer('week')->nullable();
            $table->integer('day')->nullable();
            
            // API Usage Statistics
            $table->bigInteger('api_calls')->default(0);
            $table->bigInteger('successful_calls')->default(0);
            $table->bigInteger('failed_calls')->default(0);
            $table->bigInteger('cached_calls')->default(0);
            
            // Response Format Statistics (75% HTML, 15% JSON, 10% other)
            $table->bigInteger('html_responses')->default(0);
            $table->bigInteger('json_responses')->default(0);
            $table->bigInteger('other_responses')->default(0);
            
            // WebBloc Usage by Type
            $table->json('webbloc_usage')->nullable(); // {"auth": 150, "comment": 89, "review": 45}
            
            // Performance Metrics
            $table->decimal('avg_response_time', 8, 3)->default(0); // milliseconds
            $table->decimal('max_response_time', 8, 3)->default(0);
            $table->decimal('min_response_time', 8, 3)->default(0);
            
            // Data Transfer
            $table->bigInteger('bytes_sent')->default(0);
            $table->bigInteger('bytes_received')->default(0);
            
            // Error Statistics
            $table->integer('http_200')->default(0);
            $table->integer('http_400')->default(0);
            $table->integer('http_401')->default(0);
            $table->integer('http_403')->default(0);
            $table->integer('http_404')->default(0);
            $table->integer('http_422')->default(0);
            $table->integer('http_429')->default(0);
            $table->integer('http_500')->default(0);
            $table->integer('other_errors')->default(0);
            
            // User Activity
            $table->integer('unique_users')->default(0);
            $table->integer('new_registrations')->default(0);
            $table->integer('active_sessions')->default(0);
            
            // Content Statistics
            $table->integer('new_comments')->default(0);
            $table->integer('new_reviews')->default(0);
            $table->integer('total_webbloc_instances')->default(0);
            
            // Geographical Statistics
            $table->json('countries')->nullable(); // {"US": 45, "CA": 12, "UK": 8}
            $table->json('referrers')->nullable(); // Top referring domains
            
            // Database and Storage
            $table->bigInteger('sqlite_database_size')->default(0); // bytes
            $table->integer('sqlite_operations')->default(0);
            $table->decimal('sqlite_avg_query_time', 8, 3)->default(0);
            
            // Cache Statistics
            $table->integer('cache_hits')->default(0);
            $table->integer('cache_misses')->default(0);
            $table->decimal('cache_hit_ratio', 5, 2)->default(0);
            
            // Bandwidth and CDN
            $table->bigInteger('cdn_requests')->default(0);
            $table->bigInteger('cdn_bandwidth')->default(0);
            
            // Metadata
            $table->json('raw_data')->nullable(); // Store raw analytics data
            $table->json('custom_metrics')->nullable(); // Custom tracking metrics
            
            $table->timestamps();
            
            // Indexes for performance
            $table->unique(['website_id', 'date', 'period_type']);
            $table->index(['website_id', 'period_type', 'date']);
            $table->index(['date', 'period_type']);
            $table->index(['year', 'month']);
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
        Schema::dropIfExists('website_statistics');
    }
};