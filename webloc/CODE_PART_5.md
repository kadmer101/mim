# Background Jobs & Automation System (12 Files)

## 1. `app/Jobs/ProcessWebsiteStatistics.php`

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Website;
use App\Models\WebsiteStatistic;
use App\Services\DatabaseConnectionService;
use Carbon\Carbon;

class ProcessWebsiteStatistics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Website $website;
    protected ?Carbon $date;

    /**
     * Create a new job instance.
     */
    public function __construct(Website $website, ?Carbon $date = null)
    {
        $this->website = $website;
        $this->date = $date ?? now();
    }

    /**
     * Execute the job.
     */
    public function handle(DatabaseConnectionService $dbService): void
    {
        try {
            \Log::info("Processing statistics for website: {$this->website->name}", [
                'website_id' => $this->website->id,
                'date' => $this->date->toDateString()
            ]);

            // Connect to website's SQLite database
            $dbService->connectToWebsite($this->website->id);

            // Process various statistics
            $this->processUserStatistics();
            $this->processWebBlocStatistics();
            $this->processEngagementStatistics();
            $this->processPerformanceStatistics();
            $this->processErrorStatistics();

            // Update website's last statistics processed timestamp
            $this->website->update([
                'last_stats_processed' => now()
            ]);

            \Log::info("Statistics processing completed for website: {$this->website->name}");

        } catch (\Exception $e) {
            \Log::error("Failed to process statistics for website {$this->website->id}: " . $e->getMessage(), [
                'exception' => $e,
                'website_id' => $this->website->id
            ]);

            // Re-throw to mark job as failed
            throw $e;
        }
    }

    /**
     * Process user-related statistics
     */
    protected function processUserStatistics(): void
    {
        try {
            $date = $this->date->toDateString();

            // Get user counts from SQLite database
            $totalUsers = \DB::connection('sqlite_website')
                ->table('users')
                ->count();

            $newUsersToday = \DB::connection('sqlite_website')
                ->table('users')
                ->whereDate('created_at', $date)
                ->count();

            $activeUsersToday = \DB::connection('sqlite_website')
                ->table('users')
                ->whereDate('last_activity_at', $date)
                ->count();

            $verifiedUsers = \DB::connection('sqlite_website')
                ->table('users')
                ->whereNotNull('email_verified_at')
                ->count();

            // Update or create statistics record
            $this->updateStatistics([
                'total_users' => $totalUsers,
                'new_users_today' => $newUsersToday,
                'active_users_today' => $activeUsersToday,
                'verified_users' => $verifiedUsers
            ]);

        } catch (\Exception $e) {
            \Log::error("Failed to process user statistics: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process WebBloc-related statistics
     */
    protected function processWebBlocStatistics(): void
    {
        try {
            $date = $this->date->toDateString();

            // Get WebBloc counts and engagement
            $totalWebBlocs = \DB::connection('sqlite_website')
                ->table('web_blocs')
                ->where('status', 'active')
                ->count();

            $newWebBlocsToday = \DB::connection('sqlite_website')
                ->table('web_blocs')
                ->whereDate('created_at', $date)
                ->count();

            // Count by type
            $webBlocsByType = \DB::connection('sqlite_website')
                ->table('web_blocs')
                ->select('webbloc_type', \DB::raw('count(*) as count'))
                ->where('status', 'active')
                ->groupBy('webbloc_type')
                ->pluck('count', 'webbloc_type')
                ->toArray();

            // Get engagement metrics
            $totalViews = \DB::connection('sqlite_website')
                ->table('web_blocs')
                ->whereDate('updated_at', $date)
                ->sum('view_count');

            $totalInteractions = \DB::connection('sqlite_website')
                ->table('web_blocs')
                ->whereDate('updated_at', $date)
                ->sum('interaction_count');

            $this->updateStatistics([
                'total_webblocs' => $totalWebBlocs,
                'new_webblocs_today' => $newWebBlocsToday,
                'webblocs_by_type' => $webBlocsByType,
                'total_views_today' => $totalViews,
                'total_interactions_today' => $totalInteractions
            ]);

        } catch (\Exception $e) {
            \Log::error("Failed to process WebBloc statistics: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process engagement statistics
     */
    protected function processEngagementStatistics(): void
    {
        try {
            $date = $this->date->toDateString();

            // Calculate engagement rates
            $comments = \DB::connection('sqlite_website')
                ->table('web_blocs')
                ->where('webbloc_type', 'comment')
                ->whereDate('created_at', $date)
                ->count();

            $reviews = \DB::connection('sqlite_website')
                ->table('web_blocs')
                ->where('webbloc_type', 'review')
                ->whereDate('created_at', $date)
                ->count();

            $likes = \DB::connection('sqlite_website')
                ->table('web_blocs')
                ->whereDate('updated_at', $date)
                ->sum('likes_count');

            $shares = \DB::connection('sqlite_website')
                ->table('web_blocs')
                ->whereDate('updated_at', $date)
                ->sum('shares_count');

            // Calculate average rating if reviews exist
            $averageRating = \DB::connection('sqlite_website')
                ->table('web_blocs')
                ->where('webbloc_type', 'review')
                ->whereNotNull('data->rating')
                ->avg(\DB::raw("CAST(JSON_EXTRACT(data, '$.rating') AS DECIMAL(3,2))"));

            $this->updateStatistics([
                'comments_today' => $comments,
                'reviews_today' => $reviews,
                'likes_today' => $likes,
                'shares_today' => $shares,
                'average_rating' => round($averageRating, 2)
            ]);

        } catch (\Exception $e) {
            \Log::error("Failed to process engagement statistics: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process performance statistics
     */
    protected function processPerformanceStatistics(): void
    {
        try {
            // API usage from website statistics
            $apiCalls = $this->website->statistics()
                ->whereDate('created_at', $this->date)
                ->sum('api_requests_count');

            $responseTime = $this->website->statistics()
                ->whereDate('created_at', $this->date)
                ->avg('average_response_time');

            // Database size and performance
            $databasePath = storage_path("databases/website_{$this->website->id}.sqlite");
            $databaseSize = file_exists($databasePath) ? filesize($databasePath) : 0;

            // Page load metrics (if tracked)
            $pageViews = $this->website->statistics()
                ->whereDate('created_at', $this->date)
                ->sum('page_views');

            $uniqueVisitors = $this->website->statistics()
                ->whereDate('created_at', $this->date)
                ->sum('unique_visitors');

            $this->updateStatistics([
                'api_calls_today' => $apiCalls,
                'average_response_time' => round($responseTime, 2),
                'database_size_bytes' => $databaseSize,
                'page_views_today' => $pageViews,
                'unique_visitors_today' => $uniqueVisitors
            ]);

        } catch (\Exception $e) {
            \Log::error("Failed to process performance statistics: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process error statistics
     */
    protected function processErrorStatistics(): void
    {
        try {
            $date = $this->date->toDateString();

            // Count API errors
            $apiErrors = $this->website->statistics()
                ->whereDate('created_at', $date)
                ->sum('error_count');

            $authErrors = $this->website->statistics()
                ->whereDate('created_at', $date)
                ->sum('auth_errors');

            $rateLimitExceeded = $this->website->statistics()
                ->whereDate('created_at', $date)
                ->sum('rate_limit_exceeded');

            // Calculate error rates
            $totalRequests = max(1, $this->website->statistics()
                ->whereDate('created_at', $date)
                ->sum('api_requests_count'));

            $errorRate = ($apiErrors / $totalRequests) * 100;

            $this->updateStatistics([
                'api_errors_today' => $apiErrors,
                'auth_errors_today' => $authErrors,
                'rate_limit_exceeded_today' => $rateLimitExceeded,
                'error_rate_percentage' => round($errorRate, 2)
            ]);

        } catch (\Exception $e) {
            \Log::error("Failed to process error statistics: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update or create statistics record
     */
    protected function updateStatistics(array $data): void
    {
        $date = $this->date->toDateString();

        $existing = WebsiteStatistic::where('website_id', $this->website->id)
            ->whereDate('date', $date)
            ->first();

        if ($existing) {
            // Merge with existing data
            $existingData = $existing->statistics_data ?? [];
            $mergedData = array_merge($existingData, $data);

            $existing->update([
                'statistics_data' => $mergedData,
                'updated_at' => now()
            ]);
        } else {
            // Create new record
            WebsiteStatistic::create([
                'website_id' => $this->website->id,
                'date' => $date,
                'statistics_data' => $data,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Calculate daily growth rate
     */
    protected function calculateGrowthRate(string $metric): float
    {
        $yesterday = $this->date->copy()->subDay();
        
        $today = WebsiteStatistic::where('website_id', $this->website->id)
            ->whereDate('date', $this->date)
            ->value("statistics_data->{$metric}") ?? 0;

        $previousDay = WebsiteStatistic::where('website_id', $this->website->id)
            ->whereDate('date', $yesterday)
            ->value("statistics_data->{$metric}") ?? 0;

        if ($previousDay == 0) {
            return $today > 0 ? 100.0 : 0.0;
        }

        return (($today - $previousDay) / $previousDay) * 100;
    }

    /**
     * Get the number of times the job may be attempted.
     */
    public function tries(): int
    {
        return 3;
    }

    /**
     * Get the number of seconds to wait before retrying.
     */
    public function backoff(): array
    {
        return [1, 5, 10];
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error("Statistics processing job failed permanently for website {$this->website->id}", [
            'exception' => $exception->getMessage(),
            'website_id' => $this->website->id,
            'date' => $this->date->toDateString()
        ]);

        // Optionally send notification to administrators
        // Notification::route('mail', config('mail.admin_email'))
        //     ->notify(new StatisticsProcessingFailed($this->website, $exception));
    }
}
```

## 2. `app/Jobs/CleanupExpiredTokens.php`

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ApiKey;
use App\Models\Website;
use Carbon\Carbon;

class CleanupExpiredTokens implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected bool $dryRun;

    /**
     * Create a new job instance.
     */
    public function __construct(bool $dryRun = false)
    {
        $this->dryRun = $dryRun;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            \Log::info('Starting cleanup of expired tokens', ['dry_run' => $this->dryRun]);

            $cleanupResults = [
                'expired_api_keys' => $this->cleanupExpiredApiKeys(),
                'inactive_api_keys' => $this->cleanupInactiveApiKeys(),
                'expired_website_tokens' => $this->cleanupExpiredWebsiteTokens(),
                'old_statistics' => $this->cleanupOldStatistics(),
                'temp_files' => $this->cleanupTempFiles(),
                'session_tokens' => $this->cleanupExpiredSessions()
            ];

            $totalCleaned = array_sum($cleanupResults);

            \Log::info('Token cleanup completed', [
                'dry_run' => $this->dryRun,
                'total_cleaned' => $totalCleaned,
                'breakdown' => $cleanupResults
            ]);

            // Send summary notification if significant cleanup occurred
            if ($totalCleaned > 0 && !$this->dryRun) {
                $this->sendCleanupSummary($cleanupResults);
            }

        } catch (\Exception $e) {
            \Log::error('Token cleanup job failed: ' . $e->getMessage(), [
                'exception' => $e,
                'dry_run' => $this->dryRun
            ]);

            throw $e;
        }
    }

    /**
     * Cleanup expired API keys
     */
    protected function cleanupExpiredApiKeys(): int
    {
        $query = ApiKey::where('expires_at', '<', now())
            ->where('status', '!=', 'expired');

        $count = $query->count();

        if (!$this->dryRun && $count > 0) {
            $expiredKeys = $query->get();

            foreach ($expiredKeys as $apiKey) {
                // Update status instead of deleting to maintain audit trail
                $apiKey->update([
                    'status' => 'expired',
                    'deactivated_at' => now(),
                    'deactivated_reason' => 'Automatic cleanup - expired'
                ]);

                \Log::info("API key expired and deactivated", [
                    'api_key_id' => $apiKey->id,
                    'website_id' => $apiKey->website_id,
                    'expires_at' => $apiKey->expires_at
                ]);
            }
        }

        return $count;
    }

    /**
     * Cleanup inactive API keys (not used for extended period)
     */
    protected function cleanupInactiveApiKeys(): int
    {
        $inactivityThreshold = now()->subDays(90); // 90 days of inactivity

        $query = ApiKey::where('last_used_at', '<', $inactivityThreshold)
            ->orWhere(function($q) use ($inactivityThreshold) {
                $q->whereNull('last_used_at')
                  ->where('created_at', '<', $inactivityThreshold);
            })
            ->where('status', 'active');

        $count = $query->count();

        if (!$this->dryRun && $count > 0) {
            $inactiveKeys = $query->get();

            foreach ($inactiveKeys as $apiKey) {
                $apiKey->update([
                    'status' => 'inactive',
                    'deactivated_at' => now(),
                    'deactivated_reason' => 'Automatic cleanup - 90 days inactive'
                ]);

                \Log::info("API key marked as inactive due to long inactivity", [
                    'api_key_id' => $apiKey->id,
                    'website_id' => $apiKey->website_id,
                    'last_used_at' => $apiKey->last_used_at
                ]);
            }
        }

        return $count;
    }

    /**
     * Cleanup expired website verification tokens
     */
    protected function cleanupExpiredWebsiteTokens(): int
    {
        $query = Website::whereNotNull('verification_token')
            ->where('verification_expires_at', '<', now())
            ->where('status', '!=', 'verified');

        $count = $query->count();

        if (!$this->dryRun && $count > 0) {
            $expiredWebsites = $query->get();

            foreach ($expiredWebsites as $website) {
                $website->update([
                    'verification_token' => null,
                    'verification_expires_at' => null,
                    'status' => 'verification_expired'
                ]);

                \Log::info("Website verification token expired", [
                    'website_id' => $website->id,
                    'domain' => $website->domain,
                    'verification_expires_at' => $website->verification_expires_at
                ]);
            }
        }

        return $count;
    }

    /**
     * Cleanup old statistics records
     */
    protected function cleanupOldStatistics(): int
    {
        $retentionDays = config('webbloc.statistics_retention_days', 365); // Keep 1 year by default
        $cutoffDate = now()->subDays($retentionDays);

        $query = \App\Models\WebsiteStatistic::where('created_at', '<', $cutoffDate);
        $count = $query->count();

        if (!$this->dryRun && $count > 0) {
            // Before deleting, optionally create monthly summaries
            $this->createMonthlySummaries($cutoffDate);

            $deleted = $query->delete();

            \Log::info("Old statistics records cleaned up", [
                'deleted_count' => $deleted,
                'cutoff_date' => $cutoffDate->toDateString(),
                'retention_days' => $retentionDays
            ]);
        }

        return $count;
    }

    /**
     * Create monthly summaries before deleting detailed statistics
     */
    protected function createMonthlySummaries(Carbon $cutoffDate): void
    {
        $statistics = \App\Models\WebsiteStatistic::where('created_at', '<', $cutoffDate)
            ->select([
                'website_id',
                \DB::raw('YEAR(created_at) as year'),
                \DB::raw('MONTH(created_at) as month'),
                \DB::raw('COUNT(*) as record_count'),
                \DB::raw('AVG(CAST(JSON_EXTRACT(statistics_data, "$.api_calls_today") AS UNSIGNED)) as avg_api_calls'),
                \DB::raw('AVG(CAST(JSON_EXTRACT(statistics_data, "$.average_response_time") AS DECIMAL(8,2))) as avg_response_time'),
                \DB::raw('SUM(CAST(JSON_EXTRACT(statistics_data, "$.page_views_today") AS UNSIGNED)) as total_page_views'),
                \DB::raw('MAX(CAST(JSON_EXTRACT(statistics_data, "$.total_users") AS UNSIGNED)) as max_total_users')
            ])
            ->groupBy('website_id', 'year', 'month')
            ->get();

        foreach ($statistics as $summary) {
            \DB::table('website_statistics_monthly')->insertOrIgnore([
                'website_id' => $summary->website_id,
                'year' => $summary->year,
                'month' => $summary->month,
                'summary_data' => json_encode([
                    'record_count' => $summary->record_count,
                    'avg_api_calls' => $summary->avg_api_calls,
                    'avg_response_time' => $summary->avg_response_time,
                    'total_page_views' => $summary->total_page_views,
                    'max_total_users' => $summary->max_total_users
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Cleanup temporary files
     */
    protected function cleanupTempFiles(): int
    {
        $tempPaths = [
            storage_path('app/temp'),
            storage_path('app/exports'),
            storage_path('logs/old')
        ];

        $totalFiles = 0;
        $cutoffTime = now()->subHours(24); // Remove files older than 24 hours

        foreach ($tempPaths as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $files = glob($path . '/*');
            
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < $cutoffTime->timestamp) {
                    if (!$this->dryRun) {
                        unlink($file);
                    }
                    $totalFiles++;
                }
            }
        }

        if ($totalFiles > 0) {
            \Log::info("Temporary files cleaned up", [
                'files_count' => $totalFiles,
                'cutoff_time' => $cutoffTime->toDateTimeString()
            ]);
        }

        return $totalFiles;
    }

    /**
     * Cleanup expired session tokens
     */
    protected function cleanupExpiredSessions(): int
    {
        // Clean up Laravel sessions table
        $sessionCount = 0;

        try {
            $sessionCount = \DB::table('sessions')
                ->where('last_activity', '<', now()->subDays(30)->timestamp)
                ->count();

            if (!$this->dryRun && $sessionCount > 0) {
                \DB::table('sessions')
                    ->where('last_activity', '<', now()->subDays(30)->timestamp)
                    ->delete();
            }

            // Clean up personal access tokens (Sanctum)
            $tokenCount = \DB::table('personal_access_tokens')
                ->where('expires_at', '<', now())
                ->count();

            if (!$this->dryRun && $tokenCount > 0) {
                \DB::table('personal_access_tokens')
                    ->where('expires_at', '<', now())
                    ->delete();
            }

            $sessionCount += $tokenCount;

        } catch (\Exception $e) {
            \Log::warning('Failed to cleanup sessions: ' . $e->getMessage());
        }

        return $sessionCount;
    }

    /**
     * Send cleanup summary notification
     */
    protected function sendCleanupSummary(array $results): void
    {
        $totalCleaned = array_sum($results);

        $message = "WebBloc Token Cleanup Summary:\n\n";
        $message .= "• Expired API Keys: {$results['expired_api_keys']}\n";
        $message .= "• Inactive API Keys: {$results['inactive_api_keys']}\n";
        $message .= "• Expired Website Tokens: {$results['expired_website_tokens']}\n";
        $message .= "• Old Statistics Records: {$results['old_statistics']}\n";
        $message .= "• Temporary Files: {$results['temp_files']}\n";
        $message .= "• Session Tokens: {$results['session_tokens']}\n";
        $message .= "\nTotal Items Cleaned: {$totalCleaned}";

        \Log::info($message);

        // Optionally send email notification to admins
        // \Notification::route('mail', config('webbloc.admin_email'))
        //     ->notify(new \App\Notifications\CleanupSummary($results));
    }

    /**
     * Get cleanup statistics
     */
    public function getCleanupStatistics(): array
    {
        return [
            'expired_api_keys_count' => ApiKey::where('expires_at', '<', now())
                ->where('status', '!=', 'expired')->count(),
            'inactive_api_keys_count' => ApiKey::where('last_used_at', '<', now()->subDays(90))
                ->where('status', 'active')->count(),
            'expired_website_tokens_count' => Website::whereNotNull('verification_token')
                ->where('verification_expires_at', '<', now())->count(),
            'old_statistics_count' => \App\Models\WebsiteStatistic::where('created_at', '<', 
                now()->subDays(config('webbloc.statistics_retention_days', 365)))->count()
        ];
    }

    /**
     * The number of times the job may be attempted.
     */
    public function tries(): int
    {
        return 2;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('Token cleanup job failed permanently', [
            'exception' => $exception->getMessage(),
            'dry_run' => $this->dryRun
        ]);
    }
}
```

## 3. app/Jobs/BuildWebBlocCdnFiles.php

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Website;
use App\Models\WebBloc;
use App\Services\CdnService;

class BuildWebBlocCdnFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $websites;
    protected $webBlocTypes;
    protected $force;

    public function __construct($websites = null, $webBlocTypes = null, $force = false)
    {
        $this->websites = $websites;
        $this->webBlocTypes = $webBlocTypes;
        $this->force = $force;
    }

    public function handle(CdnService $cdnService)
    {
        try {
            Log::info('Starting CDN file build process', [
                'websites' => $this->websites,
                'webbloc_types' => $this->webBlocTypes,
                'force' => $this->force
            ]);

            // Build core CDN files
            $this->buildCoreFiles($cdnService);

            // Build website-specific files if specified
            if ($this->websites) {
                $this->buildWebsiteFiles($cdnService);
            }

            // Build WebBloc-specific files if specified
            if ($this->webBlocTypes) {
                $this->buildWebBlocFiles($cdnService);
            }

            // Generate manifest and update cache
            $this->updateManifest($cdnService);

            Log::info('CDN file build process completed successfully');

        } catch (\Exception $e) {
            Log::error('CDN file build failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function buildCoreFiles(CdnService $cdnService)
    {
        $coreFiles = [
            'webbloc-core.js' => $this->buildCoreJs(),
            'webbloc-core.css' => $this->buildCoreCss(),
            'webbloc.min.js' => $this->buildMinifiedJs(),
            'webbloc.min.css' => $this->buildMinifiedCss(),
        ];

        foreach ($coreFiles as $filename => $content) {
            $cdnService->putFile($filename, $content);
        }
    }

    protected function buildWebsiteFiles(CdnService $cdnService)
    {
        $websites = is_array($this->websites) ? 
            Website::whereIn('id', $this->websites)->get() : 
            Website::where('status', 'active')->get();

        foreach ($websites as $website) {
            $customCss = $this->generateWebsiteCustomCss($website);
            $customJs = $this->generateWebsiteCustomJs($website);

            if ($customCss) {
                $cdnService->putFile("websites/{$website->id}/custom.css", $customCss);
            }

            if ($customJs) {
                $cdnService->putFile("websites/{$website->id}/custom.js", $customJs);
            }
        }
    }

    protected function buildWebBlocFiles(CdnService $cdnService)
    {
        $webBlocs = is_array($this->webBlocTypes) ? 
            WebBloc::whereIn('type', $this->webBlocTypes)->get() : 
            WebBloc::where('status', 'active')->get();

        foreach ($webBlocs as $webBloc) {
            // Build component-specific files
            $componentJs = $this->generateWebBlocJs($webBloc);
            $componentCss = $this->generateWebBlocCss($webBloc);
            $componentHtml = $this->generateWebBlocHtml($webBloc);

            $cdnService->putFile("components/{$webBloc->type}.js", $componentJs);
            $cdnService->putFile("components/{$webBloc->type}.css", $componentCss);
            $cdnService->putFile("components/{$webBloc->type}.html", $componentHtml);
        }
    }

    protected function buildCoreJs()
    {
        $jsFiles = [
            resource_path('js/webbloc-core.js'),
            resource_path('js/webbloc-components.js'),
        ];

        $combinedJs = '';
        foreach ($jsFiles as $file) {
            if (File::exists($file)) {
                $combinedJs .= File::get($file) . "\n";
            }
        }

        return $combinedJs;
    }

    protected function buildCoreCss()
    {
        $cssFiles = [
            resource_path('css/webbloc-core.css'),
            resource_path('css/webbloc-components.css'),
        ];

        $combinedCss = '';
        foreach ($cssFiles as $file) {
            if (File::exists($file)) {
                $combinedCss .= File::get($file) . "\n";
            }
        }

        return $combinedCss;
    }

    protected function buildMinifiedJs()
    {
        $js = $this->buildCoreJs();
        
        // Basic JS minification - remove comments and unnecessary whitespace
        $js = preg_replace('/\/\*[\s\S]*?\*\//', '', $js);
        $js = preg_replace('/\/\/.*/', '', $js);
        $js = preg_replace('/\s+/', ' ', $js);
        $js = trim($js);

        return $js;
    }

    protected function buildMinifiedCss()
    {
        $css = $this->buildCoreCss();
        
        // Basic CSS minification
        $css = preg_replace('/\/\*[\s\S]*?\*\//', '', $css);
        $css = preg_replace('/\s+/', ' ', $css);
        $css = str_replace(['; ', ' {', '{ ', ' }', '} ', ': ', ', '], [';', '{', '{', '}', '}', ':', ','], $css);
        $css = trim($css);

        return $css;
    }

    protected function generateWebsiteCustomCss($website)
    {
        $settings = $website->settings ?? [];
        $customCss = '';

        // Generate CSS variables based on website settings
        if (!empty($settings['theme'])) {
            $customCss .= ":root {\n";
            
            if (!empty($settings['theme']['primary_color'])) {
                $customCss .= "  --webbloc-primary: {$settings['theme']['primary_color']};\n";
            }
            
            if (!empty($settings['theme']['secondary_color'])) {
                $customCss .= "  --webbloc-secondary: {$settings['theme']['secondary_color']};\n";
            }
            
            if (!empty($settings['theme']['font_family'])) {
                $customCss .= "  --webbloc-font: '{$settings['theme']['font_family']}';\n";
            }
            
            $customCss .= "}\n";
        }

        // Add custom CSS if provided
        if (!empty($settings['custom_css'])) {
            $customCss .= "\n" . $settings['custom_css'];
        }

        return $customCss;
    }

    protected function generateWebsiteCustomJs($website)
    {
        $settings = $website->settings ?? [];
        $customJs = '';

        // Generate website-specific configuration
        $customJs .= "window.webBlocConfig = window.webBlocConfig || {};\n";
        $customJs .= "window.webBlocConfig.websiteId = '{$website->id}';\n";
        $customJs .= "window.webBlocConfig.apiKey = '{$website->api_keys->first()->key ?? ''}';\n";
        
        if (!empty($settings['api'])) {
            $customJs .= "window.webBlocConfig.apiSettings = " . json_encode($settings['api']) . ";\n";
        }

        // Add custom JavaScript if provided
        if (!empty($settings['custom_js'])) {
            $customJs .= "\n" . $settings['custom_js'];
        }

        return $customJs;
    }

    protected function generateWebBlocJs($webBloc)
    {
        $template = File::get(resource_path('js/templates/webbloc-component.js'));
        
        $replacements = [
            '{{TYPE}}' => $webBloc->type,
            '{{ATTRIBUTES}}' => json_encode($webBloc->attributes),
            '{{CRUD_OPERATIONS}}' => json_encode($webBloc->crud_operations),
            '{{API_ENDPOINTS}}' => json_encode($webBloc->getApiEndpoints()),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    protected function generateWebBlocCss($webBloc)
    {
        $css = '';
        
        // Load base component CSS
        $baseCssFile = resource_path("css/components/{$webBloc->type}.css");
        if (File::exists($baseCssFile)) {
            $css .= File::get($baseCssFile);
        }

        // Add custom CSS from WebBloc definition
        if (!empty($webBloc->metadata['custom_css'])) {
            $css .= "\n" . $webBloc->metadata['custom_css'];
        }

        return $css;
    }

    protected function generateWebBlocHtml($webBloc)
    {
        $templatePath = resource_path("views/components/webbloc/{$webBloc->type}.blade.php");
        
        if (File::exists($templatePath)) {
            return File::get($templatePath);
        }

        return '';
    }

    protected function updateManifest(CdnService $cdnService)
    {
        $manifest = [
            'version' => config('app.version', '1.0.0'),
            'build_time' => now()->toISOString(),
            'core_files' => [
                'webbloc-core.js',
                'webbloc-core.css',
                'webbloc.min.js',
                'webbloc.min.css',
            ],
            'component_files' => [],
            'website_files' => [],
        ];

        // Add component files to manifest
        foreach (WebBloc::where('status', 'active')->get() as $webBloc) {
            $manifest['component_files'][] = "components/{$webBloc->type}.js";
            $manifest['component_files'][] = "components/{$webBloc->type}.css";
            $manifest['component_files'][] = "components/{$webBloc->type}.html";
        }

        // Add website-specific files to manifest
        foreach (Website::where('status', 'active')->get() as $website) {
            if (Storage::disk('public')->exists("cdn/websites/{$website->id}/custom.css")) {
                $manifest['website_files'][] = "websites/{$website->id}/custom.css";
            }
            if (Storage::disk('public')->exists("cdn/websites/{$website->id}/custom.js")) {
                $manifest['website_files'][] = "websites/{$website->id}/custom.js";
            }
        }

        $cdnService->putFile('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
        
        // Update cache
        Cache::put('webbloc_cdn_manifest', $manifest, now()->addHour());
    }

    public function failed(\Exception $exception)
    {
        Log::error('BuildWebBlocCdnFiles job failed', [
            'error' => $exception->getMessage(),
            'websites' => $this->websites,
            'webbloc_types' => $this->webBlocTypes,
        ]);
    }
}
```

## 4. app/Notifications/ApiKeyGenerated.php

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use App\Models\ApiKey;
use App\Models\Website;

class ApiKeyGenerated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $apiKey;
    protected $website;
    protected $isRegenerated;

    public function __construct(ApiKey $apiKey, Website $website, $isRegenerated = false)
    {
        $this->apiKey = $apiKey;
        $this->website = $website;
        $this->isRegenerated = $isRegenerated;
    }

    public function via($notifiable)
    {
        $channels = ['mail', 'database'];
        
        // Add additional channels based on user preferences
        if ($notifiable->notification_preferences['browser'] ?? true) {
            $channels[] = 'broadcast';
        }

        return $channels;
    }

    public function toMail($notifiable)
    {
        $subject = $this->isRegenerated ? 
            'API Key Regenerated for ' . $this->website->name : 
            'New API Key Generated for ' . $this->website->name;

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($this->getActionDescription())
            ->line('**Website:** ' . $this->website->name)
            ->line('**Domain:** ' . $this->website->domain)
            ->line('**API Key ID:** ' . $this->apiKey->id);

        if ($this->isRegenerated) {
            $message->line('⚠️ **Important:** Your previous API key has been deactivated and will no longer work.')
                   ->line('Please update your website integration with the new API key as soon as possible.');
        }

        $message->action('View API Key Details', url('/dashboard/api-keys/' . $this->apiKey->id))
               ->line('**Key Details:**')
               ->line('- **Created:** ' . $this->apiKey->created_at->format('M j, Y g:i A'))
               ->line('- **Expires:** ' . ($this->apiKey->expires_at ? $this->apiKey->expires_at->format('M j, Y') : 'Never'))
               ->line('- **Rate Limit:** ' . ($this->apiKey->rate_limit ?? 'Default') . ' requests per hour')
               ->line('- **Allowed Domains:** ' . ($this->apiKey->allowed_domains ? implode(', ', $this->apiKey->allowed_domains) : 'Any'));

        if (!$this->isRegenerated) {
            $message->line('## Getting Started')
                   ->line('To start using your API key, add the following script to your website:')
                   ->line('```html')
                   ->line('<script src="' . config('webbloc.cdn.base_url') . '/webbloc.min.js" data-api-key="' . $this->apiKey->key . '"></script>')
                   ->line('```')
                   ->line('For detailed integration instructions, visit our [documentation](' . url('/dashboard/help/integration') . ').');
        }

        $message->line('If you did not request this API key generation, please contact our support team immediately.')
               ->salutation('Best regards, The ' . config('app.name') . ' Team');

        return $message;
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->isRegenerated ? 'API Key Regenerated' : 'New API Key Generated',
            'message' => $this->getActionDescription(),
            'type' => 'api_key_generated',
            'data' => [
                'api_key_id' => $this->apiKey->id,
                'website_id' => $this->website->id,
                'website_name' => $this->website->name,
                'website_domain' => $this->website->domain,
                'is_regenerated' => $this->isRegenerated,
                'key_preview' => substr($this->apiKey->key, 0, 8) . '...',
                'expires_at' => $this->apiKey->expires_at?->toISOString(),
                'rate_limit' => $this->apiKey->rate_limit,
            ],
            'action_url' => url('/dashboard/api-keys/' . $this->apiKey->id),
            'action_text' => 'View Details',
            'created_at' => now(),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return [
            'title' => $this->isRegenerated ? 'API Key Regenerated' : 'New API Key Generated',
            'message' => $this->getActionDescription(),
            'type' => 'success',
            'data' => [
                'api_key_id' => $this->apiKey->id,
                'website_name' => $this->website->name,
                'is_regenerated' => $this->isRegenerated,
            ],
        ];
    }

    protected function getActionDescription()
    {
        if ($this->isRegenerated) {
            return "Your API key for website '{$this->website->name}' has been successfully regenerated. The new key is now active and ready to use.";
        }

        return "A new API key has been generated for your website '{$this->website->name}'. You can now integrate dynamic WebBloc components into your site.";
    }

    public function shouldSend($notifiable, $channel)
    {
        // Don't send email if user has disabled API key notifications
        if ($channel === 'mail' && !($notifiable->notification_preferences['api_keys_email'] ?? true)) {
            return false;
        }

        return true;
    }
}
```

## 5. app/Notifications/WebsiteRegistered.php

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Website;

class WebsiteRegistered extends Notification implements ShouldQueue
{
    use Queueable;

    protected $website;
    protected $verificationToken;

    public function __construct(Website $website, $verificationToken = null)
    {
        $this->website = $website;
        $this->verificationToken = $verificationToken;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject('Welcome to ' . config('app.name') . ' - Website Registration Successful')
            ->greeting('Welcome ' . $notifiable->name . '!')
            ->line('Congratulations! Your website has been successfully registered with ' . config('app.name') . '.')
            ->line('**Website Details:**')
            ->line('- **Name:** ' . $this->website->name)
            ->line('- **Domain:** ' . $this->website->domain)
            ->line('- **Registration Date:** ' . $this->website->created_at->format('M j, Y g:i A'));

        if ($this->website->status === 'pending_verification') {
            $message->line('## 📋 Next Steps')
                   ->line('Your website is currently pending verification. To complete the setup process, you need to verify domain ownership.');
            
            if ($this->verificationToken) {
                $message->line('**Verification Options:**')
                       ->line('1. **HTML File Upload:** Download and upload the verification file to your website root directory')
                       ->action('Download Verification File', url('/dashboard/websites/' . $this->website->id . '/verification-file'))
                       ->line('2. **DNS Record:** Add the following TXT record to your domain DNS:')
                       ->line('   - **Type:** TXT')
                       ->line('   - **Name:** _webbloc-verification')
                       ->line('   - **Value:** ' . $this->verificationToken)
                       ->line('3. **Meta Tag:** Add this meta tag to your website\'s <head> section:')
                       ->line('   ```html')
                       ->line('   <meta name="webbloc-verification" content="' . $this->verificationToken . '">')
                       ->line('   ```');
            }

            $message->action('Complete Verification', url('/dashboard/websites/' . $this->website->id . '/verify'));
        } else {
            $message->line('## 🚀 Get Started')
                   ->line('Your website is verified and ready to use! Here\'s what you can do now:')
                   ->line('✅ Generate API keys for secure access')
                   ->line('✅ Install WebBloc components (comments, reviews, authentication)')
                   ->line('✅ Customize component settings and appearance')
                   ->line('✅ View real-time statistics and analytics')
                   ->action('Go to Dashboard', url('/dashboard/websites/' . $this->website->id));
        }

        $message->line('## 🎯 Available WebBloc Components')
               ->line('- **Authentication:** User login, registration, and profile management')
               ->line('- **Comments:** Interactive comment systems for your pages')
               ->line('- **Reviews:** Customer review and rating system')
               ->line('- **Notifications:** Real-time notification system')
               ->line('- **And more:** Additional components available in your dashboard')
               ->line('## 📚 Resources')
               ->line('- [Integration Guide](' . url('/dashboard/help/integration') . ')')
               ->line('- [API Documentation](' . url('/dashboard/help/api') . ')')
               ->line('- [Component Reference](' . url('/dashboard/help/components') . ')')
               ->line('- [Support & FAQ](' . url('/dashboard/help/faq') . ')')
               ->line('## 💼 Subscription Information');

        $subscription = $this->website->subscription ?? [];
        $planName = $subscription['plan'] ?? 'Free';
        $message->line('**Current Plan:** ' . $planName);
        
        if ($planName === 'Free') {
            $message->line('**Limits:** 10,000 API requests per month, 3 WebBloc components')
                   ->line('Consider upgrading to a paid plan for higher limits and premium features.')
                   ->action('View Pricing Plans', url('/pricing'));
        }

        $message->line('If you have any questions or need assistance, our support team is here to help.')
               ->salutation('Happy building! The ' . config('app.name') . ' Team');

        return $message;
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Website Registered Successfully',
            'message' => "Your website '{$this->website->name}' has been registered and is ready for WebBloc integration.",
            'type' => 'website_registered',
            'data' => [
                'website_id' => $this->website->id,
                'website_name' => $this->website->name,
                'website_domain' => $this->website->domain,
                'status' => $this->website->status,
                'needs_verification' => $this->website->status === 'pending_verification',
                'verification_token' => $this->verificationToken,
            ],
            'action_url' => url('/dashboard/websites/' . $this->website->id),
            'action_text' => $this->website->status === 'pending_verification' ? 'Complete Verification' : 'View Website',
            'created_at' => now(),
        ];
    }
}
```

## 6. app/Notifications/WebBlocInstalled.php

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Website;
use App\Models\WebBloc;
use App\Models\WebBlocInstance;

class WebBlocInstalled extends Notification implements ShouldQueue
{
    use Queueable;

    protected $website;
    protected $webBloc;
    protected $instance;
    protected $isUpdate;

    public function __construct(Website $website, WebBloc $webBloc, WebBlocInstance $instance, $isUpdate = false)
    {
        $this->website = $website;
        $this->webBloc = $webBloc;
        $this->instance = $instance;
        $this->isUpdate = $isUpdate;
    }

    public function via($notifiable)
    {
        $channels = ['database'];
        
        // Only send email for major installations, not updates
        if (!$this->isUpdate && ($notifiable->notification_preferences['webbloc_email'] ?? true)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail($notifiable)
    {
        $componentName = ucfirst($this->webBloc->type);
        
        $message = (new MailMessage)
            ->subject($componentName . ' WebBloc Installed Successfully')
            ->greeting('Great news, ' . $notifiable->name . '!')
            ->line("The {$componentName} WebBloc has been successfully installed on your website '{$this->website->name}'.")
            ->line('**Installation Details:**')
            ->line('- **Component:** ' . $componentName . ' (' . $this->webBloc->version . ')')
            ->line('- **Website:** ' . $this->website->name)
            ->line('- **Domain:** ' . $this->website->domain)
            ->line('- **Installed:** ' . $this->instance->created_at->format('M j, Y g:i A'));

        // Add component-specific information
        $this->addComponentDetails($message);

        $message->line('## 🔗 Integration')
               ->line('To use this component on your website, add the following HTML code where you want it to appear:');

        $integrationCode = $this->generateIntegrationCode();
        $message->line('```html')
               ->line($integrationCode)
               ->line('```');

        $message->action('View Installation Details', url('/dashboard/websites/' . $this->website->id . '/webblocs'))
               ->line('## ⚙️ Configuration')
               ->line('You can customize this WebBloc\'s settings, appearance, and behavior from your dashboard.')
               ->line('Available configuration options:');

        // Add configuration options based on component type
        $this->addConfigurationOptions($message);

        $message->action('Customize Settings', url('/dashboard/websites/' . $this->website->id . '/webblocs/' . $this->instance->id . '/edit'))
               ->line('## 📊 Monitoring')
               ->line('Track usage, performance, and user engagement with detailed analytics available in your dashboard.')
               ->action('View Statistics', url('/dashboard/statistics?website=' . $this->website->id . '&component=' . $this->webBloc->type))
               ->salutation('Keep building amazing experiences! The ' . config('app.name') . ' Team');

        return $message;
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->isUpdate ? 'WebBloc Updated' : 'WebBloc Installed',
            'message' => $this->getNotificationMessage(),
            'type' => $this->isUpdate ? 'webbloc_updated' : 'webbloc_installed',
            'data' => [
                'website_id' => $this->website->id,
                'website_name' => $this->website->name,
                'webbloc_id' => $this->webBloc->id,
                'webbloc_type' => $this->webBloc->type,
                'webbloc_version' => $this->webBloc->version,
                'instance_id' => $this->instance->id,
                'is_update' => $this->isUpdate,
                'installation_settings' => $this->instance->settings,
                'integration_code' => $this->generateIntegrationCode(),
            ],
            'action_url' => url('/dashboard/websites/' . $this->website->id . '/webblocs'),
            'action_text' => 'View WebBlocs',
            'created_at' => now(),
        ];
    }

    protected function getNotificationMessage()
    {
        $componentName = ucfirst($this->webBloc->type);
        
        if ($this->isUpdate) {
            return "The {$componentName} WebBloc on '{$this->website->name}' has been updated to version {$this->webBloc->version}.";
        }

        return "The {$componentName} WebBloc has been successfully installed on '{$this->website->name}' and is ready to use.";
    }

    protected function addComponentDetails(MailMessage $message)
    {
        switch ($this->webBloc->type) {
            case 'comments':
                $message->line('**Features Enabled:**')
                       ->line('✅ User commenting system')
                       ->line('✅ Reply threads and nested comments')
                       ->line('✅ Comment moderation and spam protection')
                       ->line('✅ Real-time updates')
                       ->line('✅ Emoji reactions and voting');
                break;

            case 'reviews':
                $message->line('**Features Enabled:**')
                       ->line('✅ Customer review collection')
                       ->line('✅ Star rating system')
                       ->line('✅ Review verification and moderation')
                       ->line('✅ Photo and video attachments')
                       ->line('✅ Review analytics and insights');
                break;

            case 'auth':
                $message->line('**Features Enabled:**')
                       ->line('✅ User registration and login')
                       ->line('✅ Password reset functionality')
                       ->line('✅ User profile management')
                       ->line('✅ Social login integration')
                       ->line('✅ Session management');
                break;

            case 'notifications':
                $message->line('**Features Enabled:**')
                       ->line('✅ Real-time notifications')
                       ->line('✅ Multiple notification types')
                       ->line('✅ Desktop and mobile support')
                       ->line('✅ Customizable notification center')
                       ->line('✅ Email and browser notifications');
                break;

            default:
                $message->line('**Component Type:** Custom WebBloc')
                       ->line('**Description:** ' . ($this->webBloc->description ?? 'No description available'));
        }
    }

    protected function addConfigurationOptions(MailMessage $message)
    {
        $settings = $this->instance->settings ?? [];
        
        switch ($this->webBloc->type) {
            case 'comments':
                $message->line('- Comment approval settings (automatic/manual/none)')
                       ->line('- Guest commenting permissions')
                       ->line('- Nested reply depth limits')
                       ->line('- Spam filtering and moderation rules')
                       ->line('- Display options and themes');
                break;

            case 'reviews':
                $message->line('- Review verification requirements')
                       ->line('- Rating scale configuration (1-5, 1-10)')
                       ->line('- Required/optional review fields')
                       ->line('- Media upload settings')
                       ->line('- Display templates and sorting options');
                break;

            case 'auth':
                $message->line('- Registration field requirements')
                       ->line('- Password complexity rules')
                       ->line('- Email verification settings')
                       ->line('- Social login providers')
                       ->line('- User profile customization');
                break;

            case 'notifications':
                $message->line('- Notification delivery methods')
                       ->line('- Auto-dismiss and persistence settings')
                       ->line('- Sound and visual preferences')
                       ->line('- Notification categories and priorities')
                       ->line('- Display position and styling');
                break;

            default:
                $message->line('- Custom component configuration available in dashboard');
        }
    }

    protected function generateIntegrationCode()
    {
        $settings = $this->instance->settings ?? [];
        $attributes = [];

        // Add common attributes
        if (!empty($settings['limit'])) {
            $attributes['limit'] = $settings['limit'];
        }

        if (!empty($settings['theme'])) {
            $attributes['theme'] = $settings['theme'];
        }

        // Add type-specific attributes
        switch ($this->webBloc->type) {
            case 'comments':
                if (!empty($settings['allow_guest'])) {
                    $attributes['guest'] = 'true';
                }
                if (!empty($settings['max_depth'])) {
                    $attributes['max-depth'] = $settings['max_depth'];
                }
                break;

            case 'reviews':
                if (!empty($settings['require_purchase'])) {
                    $attributes['verified-only'] = 'true';
                }
                if (!empty($settings['rating_scale'])) {
                    $attributes['rating-scale'] = $settings['rating_scale'];
                }
                break;

            case 'auth':
                $mode = $settings['mode'] ?? 'modal';
                $attributes['mode'] = $mode;
                if (!empty($settings['redirect_after_login'])) {
                    $attributes['redirect'] = $settings['redirect_after_login'];
                }
                break;
        }

        $attributeString = '';
        if (!empty($attributes)) {
            $attributeString = " w2030b_tags='" . json_encode($attributes) . "'";
        }

        return '<div w2030b="' . $this->webBloc->type . '"' . $attributeString . '>Loading...</div>';
    }
}
```

## 7. app/Console/Commands/CreateWebsiteDatabase.php

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Website;
use App\Services\DatabaseConnectionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CreateWebsiteDatabase extends Command
{
    protected $signature = 'website:create-database 
                           {website? : Website ID or domain}
                           {--all : Create databases for all websites}
                           {--force : Force recreation of existing databases}
                           {--migrate : Run migrations after creation}
                           {--seed : Run seeders after creation}';

    protected $description = 'Create SQLite databases for websites';

    protected $databaseService;

    public function __construct(DatabaseConnectionService $databaseService)
    {
        parent::__construct();
        $this->databaseService = $databaseService;
    }

    public function handle()
    {
        if ($this->option('all')) {
            return $this->createAllDatabases();
        }

        $website = $this->getWebsite();
        if (!$website) {
            return 1;
        }

        return $this->createWebsiteDatabase($website);
    }

    protected function createAllDatabases()
    {
        $websites = Website::all();
        
        if ($websites->isEmpty()) {
            $this->warn('No websites found.');
            return 1;
        }

        $this->info("Creating databases for {$websites->count()} websites...");
        
        $progressBar = $this->output->createProgressBar($websites->count());
        $progressBar->start();

        $success = 0;
        $failed = 0;

        foreach ($websites as $website) {
            try {
                $this->createWebsiteDatabase($website, false);
                $success++;
            } catch (\Exception $e) {
                $this->error("\nFailed to create database for website {$website->id} ({$website->domain}): " . $e->getMessage());
                $failed++;
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        
        $this->newLine(2);
        $this->info("✅ Successfully created {$success} databases");
        
        if ($failed > 0) {
            $this->error("❌ Failed to create {$failed} databases");
        }

        return $failed > 0 ? 1 : 0;
    }

    protected function createWebsiteDatabase(Website $website, $verbose = true)
    {
        if ($verbose) {
            $this->info("Creating database for website: {$website->name} ({$website->domain})");
        }

        $databasePath = $this->databaseService->getDatabasePath($website->id);
        
        // Check if database already exists
        if (File::exists($databasePath) && !$this->option('force')) {
            if ($verbose) {
                $this->warn('Database already exists. Use --force to recreate.');
            }
            return 1;
        }

        try {
            // Create database
            $this->databaseService->createDatabase($website->id);
            
            if ($verbose) {
                $this->info('✅ Database created successfully');
            }

            // Run migrations if requested
            if ($this->option('migrate')) {
                $this->runMigrations($website, $verbose);
            }

            // Run seeders if requested
            if ($this->option('seed')) {
                $this->runSeeders($website, $verbose);
            }

            // Update website database path
            $website->update(['database_path' => $databasePath]);

            if ($verbose) {
                $this->displayDatabaseInfo($website);
            }

            return 0;

        } catch (\Exception $e) {
            if ($verbose) {
                $this->error('❌ Failed to create database: ' . $e->getMessage());
            }
            throw $e;
        }
    }

    protected function runMigrations(Website $website, $verbose = true)
    {
        if ($verbose) {
            $this->info('Running migrations...');
        }

        try {
            // Get SQLite connection for this website
            $connection = $this->databaseService->getConnection($website->id);
            
            // Create users table
            $connection->statement("
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR(255) NOT NULL,
                    email VARCHAR(255) UNIQUE NOT NULL,
                    email_verified_at DATETIME,
                    password VARCHAR(255),
                    remember_token VARCHAR(100),
                    avatar TEXT,
                    metadata JSON,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");

            // Create web_blocs table
            $connection->statement("
                CREATE TABLE IF NOT EXISTS web_blocs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    webbloc_type VARCHAR(50) NOT NULL,
                    user_id INTEGER,
                    page_url VARCHAR(500) NOT NULL,
                    data JSON NOT NULL,
                    metadata JSON,
                    status VARCHAR(20) DEFAULT 'active',
                    parent_id INTEGER,
                    sort_order INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id),
                    FOREIGN KEY (parent_id) REFERENCES web_blocs(id)
                )
            ");

            // Create indexes for performance
            $this->createIndexes($connection);

            if ($verbose) {
                $this->info('✅ Migrations completed');
            }

        } catch (\Exception $e) {
            if ($verbose) {
                $this->error('❌ Migration failed: ' . $e->getMessage());
            }
            throw $e;
        }
    }

    protected function createIndexes($connection)
    {
        $indexes = [
            "CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)",
            "CREATE INDEX IF NOT EXISTS idx_web_blocs_type ON web_blocs(webbloc_type)",
            "CREATE INDEX IF NOT EXISTS idx_web_blocs_user_id ON web_blocs(user_id)",
            "CREATE INDEX IF NOT EXISTS idx_web_blocs_page_url ON web_blocs(page_url)",
            "CREATE INDEX IF NOT EXISTS idx_web_blocs_status ON web_blocs(status)",
            "CREATE INDEX IF NOT EXISTS idx_web_blocs_parent_id ON web_blocs(parent_id)",
            "CREATE INDEX IF NOT EXISTS idx_web_blocs_created_at ON web_blocs(created_at)",
        ];

        foreach ($indexes as $index) {
            $connection->statement($index);
        }
    }

    protected function runSeeders(Website $website, $verbose = true)
    {
        if ($verbose) {
            $this->info('Running seeders...');
        }

        try {
            $connection = $this->databaseService->getConnection($website->id);

            // Create sample admin user if none exists
            $userCount = $connection->select("SELECT COUNT(*) as count FROM users")[0]->count;
            
            if ($userCount == 0) {
                $connection->insert("
                    INSERT INTO users (name, email, password, email_verified_at, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ", [
                    'Website Admin',
                    'admin@' . $website->domain,
                    bcrypt('password'),
                    now(),
                    now(),
                    now()
                ]);

                if ($verbose) {
                    $this->info('✅ Sample admin user created');
                }
            }

        } catch (\Exception $e) {
            if ($verbose) {
                $this->error('❌ Seeding failed: ' . $e->getMessage());
            }
            throw $e;
        }
    }

    protected function getWebsite()
    {
        $websiteInput = $this->argument('website');
        
        if (!$websiteInput) {
            $websiteInput = $this->ask('Enter website ID or domain');
        }

        // Try to find by ID first, then by domain
        $website = Website::find($websiteInput) ?? Website::where('domain', $websiteInput)->first();

        if (!$website) {
            $this->error("Website not found: {$websiteInput}");
            return null;
        }

        return $website;
    }

    protected function displayDatabaseInfo(Website $website)
    {
        $databasePath = $this->databaseService->getDatabasePath($website->id);
        $fileSize = File::exists($databasePath) ? File::size($databasePath) : 0;
        
        $this->info('📊 Database Information:');
        $this->table(
            ['Property', 'Value'],
            [
                ['Website ID', $website->id],
                ['Website Name', $website->name],
                ['Domain', $website->domain],
                ['Database Path', $databasePath],
                ['File Size', $this->formatBytes($fileSize)],
                ['Created', now()->format('Y-m-d H:i:s')],
            ]
        );
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
```

## 8. app/Console/Commands/InstallWebBloc.php

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Website;
use App\Models\WebBloc;
use App\Models\WebBlocInstance;
use App\Services\DatabaseConnectionService;
use App\Jobs\BuildWebBlocCdnFiles;
use Illuminate\Support\Facades\DB;

class InstallWebBloc extends Command
{
    protected $signature = 'webbloc:install 
                           {type : WebBloc type to install (auth, comments, reviews, notifications)}
                           {--websites= : Comma-separated website IDs or "all" for all websites}
                           {--settings= : JSON settings for the WebBloc installation}
                           {--force : Force reinstallation if already exists}
                           {--rebuild-cdn : Rebuild CDN files after installation}';

    protected $description = 'Install WebBloc components to websites';

    protected $databaseService;

    public function __construct(DatabaseConnectionService $databaseService)
    {
        parent::__construct();
        $this->databaseService = $databaseService;
    }

    public function handle()
    {
        $type = $this->argument('type');
        $websitesInput = $this->option('websites');
        $settings = $this->option('settings');
        
        // Validate WebBloc type
        $webBloc = WebBloc::where('type', $type)->where('status', 'active')->first();
        if (!$webBloc) {
            $this->error("WebBloc type '{$type}' not found or inactive.");
            return 1;
        }

        // Parse settings
        $installationSettings = [];
        if ($settings) {
            $installationSettings = json_decode($settings, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Invalid JSON in settings parameter.');
                return 1;
            }
        }

        // Get target websites
        $websites = $this->getTargetWebsites($websitesInput);
        if ($websites->isEmpty()) {
            $this->warn('No websites found for installation.');
            return 1;
        }

        $this->info("Installing {$webBloc->type} WebBloc to {$websites->count()} website(s)...");
        
        $progressBar = $this->output->createProgressBar($websites->count());
        $progressBar->start();

        $success = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($websites as $website) {
            try {
                $result = $this->installWebBlocToWebsite($webBloc, $website, $installationSettings);
                
                switch ($result) {
                    case 'installed':
                        $success++;
                        break;
                    case 'updated':
                        $success++;
                        break;
                    case 'skipped':
                        $skipped++;
                        break;
                }
            } catch (\Exception $e) {
                $this->error("\nFailed to install WebBloc to website {$website->id} ({$website->domain}): " . $e->getMessage());
                $failed++;
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display results
        $this->displayResults($success, $failed, $skipped);

        // Rebuild CDN if requested
        if ($this->option('rebuild-cdn')) {
            $this->info('Rebuilding CDN files...');
            BuildWebBlocCdnFiles::dispatch(null, [$type], true);
            $this->info('✅ CDN rebuild queued');
        }

        return $failed > 0 ? 1 : 0;
    }

    protected function installWebBlocToWebsite(WebBloc $webBloc, Website $website, array $settings = [])
    {
        // Check if already installed
        $existingInstance = WebBlocInstance::where('website_id', $website->id)
                                         ->where('webbloc_id', $webBloc->id)
                                         ->first();

        if ($existingInstance && !$this->option('force')) {
            return 'skipped';
        }

        DB::beginTransaction();
        try {
            // Ensure website database exists
            if (!$this->databaseService->databaseExists($website->id)) {
                $this->databaseService->createDatabase($website->id);
            }

            // Create or update WebBloc instance
            if ($existingInstance) {
                $existingInstance->update([
                    'version' => $webBloc->version,
                    'settings' => array_merge($existingInstance->settings ?? [], $settings),
                    'status' => 'active',
                    'updated_at' => now(),
                ]);
                $instance = $existingInstance;
                $action = 'updated';
            } else {
                $instance = WebBlocInstance::create([
                    'website_id' => $website->id,
                    'webbloc_id' => $webBloc->id,
                    'version' => $webBloc->version,
                    'settings' => $this->getDefaultSettings($webBloc->type, $settings),
                    'status' => 'active',
                ]);
                $action = 'installed';
            }

            // Install component-specific data structures
            $this->installComponentSpecificStructures($webBloc, $website);

            DB::commit();

            // Send notification
            $website->owner?->notify(new \App\Notifications\WebBlocInstalled(
                $website, 
                $webBloc, 
                $instance, 
                $action === 'updated'
            ));

            return $action;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function installComponentSpecificStructures(WebBloc $webBloc, Website $website)
    {
        $connection = $this->databaseService->getConnection($website->id);

        switch ($webBloc->type) {
            case 'comments':
                $this->installCommentsStructure($connection);
                break;
            case 'reviews':
                $this->installReviewsStructure($connection);
                break;
            case 'auth':
                $this->installAuthStructure($connection);
                break;
            case 'notifications':
                $this->installNotificationsStructure($connection);
                break;
        }
    }

    protected function installCommentsStructure($connection)
    {
        // Create comments-specific indexes and triggers
        $connection->statement("
            CREATE INDEX IF NOT EXISTS idx_web_blocs_comments 
            ON web_blocs(webbloc_type, page_url) 
            WHERE webbloc_type = 'comment'
        ");
        
        $connection->statement("
            CREATE INDEX IF NOT EXISTS idx_web_blocs_comment_replies 
            ON web_blocs(parent_id) 
            WHERE webbloc_type = 'comment' AND parent_id IS NOT NULL
        ");
    }

    protected function installReviewsStructure($connection)
    {
        // Create reviews-specific indexes
        $connection->statement("
            CREATE INDEX IF NOT EXISTS idx_web_blocs_reviews 
            ON web_blocs(webbloc_type, page_url) 
            WHERE webbloc_type = 'review'
        ");
        
        $connection->statement("
            CREATE INDEX IF NOT EXISTS idx_web_blocs_review_ratings 
            ON web_blocs(webbloc_type, JSON_EXTRACT(data, '$.rating')) 
            WHERE webbloc_type = 'review'
        ");
    }

    protected function installAuthStructure($connection)
    {
        // Ensure users table has auth-specific fields
        $connection->statement("
            ALTER TABLE users ADD COLUMN IF NOT EXISTS 
            social_provider VARCHAR(50)
        ");
        
        $connection->statement("
            ALTER TABLE users ADD COLUMN IF NOT EXISTS 
            social_id VARCHAR(255)
        ");
        
        $connection->statement("
            CREATE INDEX IF NOT EXISTS idx_users_social 
            ON users(social_provider, social_id)
        ");
    }

    protected function installNotificationsStructure($connection)
    {
        // Create notifications table if it doesn't exist
        $connection->statement("
            CREATE TABLE IF NOT EXISTS notifications (
                id VARCHAR(36) PRIMARY KEY,
                type VARCHAR(255) NOT NULL,
                notifiable_id INTEGER NOT NULL,
                notifiable_type VARCHAR(255) NOT NULL,
                data JSON NOT NULL,
                read_at DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $connection->statement("
            CREATE INDEX IF NOT EXISTS idx_notifications_user 
            ON notifications(notifiable_id, notifiable_type)
        ");
    }

    protected function getDefaultSettings(string $type, array $customSettings = []): array
    {
        $defaults = match ($type) {
            'comments' => [
                'allow_guest' => false,
                'require_approval' => true,
                'max_depth' => 3,
                'enable_reactions' => true,
                'enable_mentions' => true,
                'spam_protection' => true,
            ],
            'reviews' => [
                'require_purchase_verification' => false,
                'allow_anonymous' => false,
                'rating_scale' => 5,
                'allow_media' => true,
                'require_review_text' => true,
                'auto_approve' => false,
            ],
            'auth' => [
                'registration_enabled' => true,
                'email_verification' => true,
                'social_login' => [],
                'password_requirements' => [
                    'min_length' => 8,
                    'require_uppercase' => true,
                    'require_numbers' => true,
                    'require_symbols' => false,
                ],
            ],
            'notifications' => [
                'position' => 'top-right',
                'auto_dismiss' => true,
                'dismiss_delay' => 5000,
                'sound_enabled' => false,
                'max_visible' => 5,
            ],
            default => [],
        };

        return array_merge($defaults, $customSettings);
    }

    protected function getTargetWebsites($websitesInput)
    {
        if (!$websitesInput) {
            $websitesInput = $this->ask('Enter website IDs (comma-separated) or "all"', 'all');
        }

        if (strtolower($websitesInput) === 'all') {
            return Website::where('status', 'active')->get();
        }

        $websiteIds = array_map('trim', explode(',', $websitesInput));
        return Website::whereIn('id', $websiteIds)->get();
    }

    protected function displayResults($success, $failed, $skipped)
    {
        if ($success > 0) {
            $this->info("✅ Successfully processed {$success} installation(s)");
        }
        
        if ($skipped > 0) {
            $this->warn("⚠️  Skipped {$skipped} installation(s) (already exists, use --force to reinstall)");
        }
        
        if ($failed > 0) {
            $this->error("❌ Failed {$failed} installation(s)");
        }

        if ($success > 0) {
            $this->info('💡 Next steps:');
            $this->line('- Update your website integration code');
            $this->line('- Configure component settings in the dashboard');
            $this->line('- Test the component functionality');
        }
    }
}
```

## 9. app/Console/Commands/GenerateApiKeys.php

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Website;
use App\Models\ApiKey;
use App\Services\ApiKeyService;
use Illuminate\Support\Str;

class GenerateApiKeys extends Command
{
    protected $signature = 'apikey:generate 
                           {--website-id= : Website ID to generate API key for}
                           {--all : Generate API keys for all websites without active keys}
                           {--regenerate : Regenerate existing API keys}
                           {--expires= : Expiration date (Y-m-d format) or days from now}
                           {--rate-limit= : Rate limit per hour}
                           {--permissions= : Comma-separated permissions}
                           {--domains= : Comma-separated allowed domains}';

    protected $description = 'Generate API keys for websites';

    protected $apiKeyService;

    public function __construct(ApiKeyService $apiKeyService)
    {
        parent::__construct();
        $this->apiKeyService = $apiKeyService;
    }

    public function handle()
    {
        if ($this->option('all')) {
            return $this->generateForAllWebsites();
        }

        $websiteId = $this->option('website-id');
        if (!$websiteId) {
            $websiteId = $this->ask('Enter Website ID');
        }

        $website = Website::find($websiteId);
        if (!$website) {
            $this->error("Website not found: {$websiteId}");
            return 1;
        }

        return $this->generateApiKeyForWebsite($website);
    }

    protected function generateForAllWebsites()
    {
        $websites = Website::where('status', 'active')
                           ->whereDoesntHave('api_keys', function ($query) {
                               $query->where('status', 'active');
                           })
                           ->get();

        if ($websites->isEmpty()) {
            $this->info('All active websites already have API keys.');
            return 0;
        }

        $this->info("Generating API keys for {$websites->count()} websites...");
        
        $progressBar = $this->output->createProgressBar($websites->count());
        $progressBar->start();

        $success = 0;
        $failed = 0;

        foreach ($websites as $website) {
            try {
                $this->generateApiKeyForWebsite($website, false);
                $success++;
            } catch (\Exception $e) {
                $this->error("\nFailed to generate API key for website {$website->id}: " . $e->getMessage());
                $failed++;
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("✅ Successfully generated {$success} API keys");
        if ($failed > 0) {
            $this->error("❌ Failed to generate {$failed} API keys");
        }

        return $failed > 0 ? 1 : 0;
    }

    protected function generateApiKeyForWebsite(Website $website, $verbose = true)
    {
        if ($verbose) {
            $this->info("Generating API key for: {$website->name} ({$website->domain})");
        }

        // Check if active API key exists
        $existingKey = $website->api_keys()->where('status', 'active')->first();
        
        if ($existingKey && !$this->option('regenerate')) {
            if ($verbose) {
                $this->warn('Active API key already exists. Use --regenerate to create a new one.');
                $this->displayApiKeyInfo($existingKey);
            }
            return 1;
        }

        try {
            // Prepare API key data
            $keyData = $this->prepareApiKeyData($website);
            
            if ($existingKey && $this->option('regenerate')) {
                // Deactivate existing key
                $existingKey->update(['status' => 'inactive']);
                
                if ($verbose) {
                    $this->warn('Previous API key deactivated');
                }
            }

            // Generate new API key
            $apiKey = $this->apiKeyService->generateApiKey($website, $keyData);

            if ($verbose) {
                $this->info('✅ API key generated successfully');
                $this->displayApiKeyInfo($apiKey);
            }

            // Send notification
            $website->owner?->notify(new \App\Notifications\ApiKeyGenerated(
                $apiKey, 
                $website, 
                (bool) $existingKey
            ));

            return 0;

        } catch (\Exception $e) {
            if ($verbose) {
                $this->error('❌ Failed to generate API key: ' . $e->getMessage());
            }
            throw $e;
        }
    }

    protected function prepareApiKeyData(Website $website): array
    {
        $data = [
            'name' => "API Key for {$website->name}",
        ];

        // Handle expiration
        if ($expires = $this->option('expires')) {
            if (is_numeric($expires)) {
                $data['expires_at'] = now()->addDays((int) $expires);
            } else {
                try {
                    $data['expires_at'] = \Carbon\Carbon::parse($expires);
                } catch (\Exception $e) {
                    $this->warn("Invalid expiration date format: {$expires}. Using no expiration.");
                }
            }
        }

        // Handle rate limit
        if ($rateLimit = $this->option('rate-limit')) {
            $data['rate_limit'] = (int) $rateLimit;
        } else {
            $data['rate_limit'] = config('webbloc.api.default_rate_limit', 1000);
        }

        // Handle permissions
        if ($permissions = $this->option('permissions')) {
            $data['permissions'] = array_map('trim', explode(',', $permissions));
        } else {
            $data['permissions'] = $this->getDefaultPermissions();
        }

        // Handle allowed domains
        if ($domains = $this->option('domains')) {
            $data['allowed_domains'] = array_map('trim', explode(',', $domains));
        } else {
            $data['allowed_domains'] = [$website->domain];
        }

        return $data;
    }

    protected function getDefaultPermissions(): array
    {
        return [
            'webbloc.read',
            'webbloc.create',
            'webbloc.update',
            'webbloc.delete',
            'auth.login',
            'auth.register',
            'auth.profile',
        ];
    }

    protected function displayApiKeyInfo(ApiKey $apiKey)
    {
        $this->info('📊 API Key Information:');
        $this->table(
            ['Property', 'Value'],
            [
                ['ID', $apiKey->id],
                ['Key', $apiKey->key],
                ['Name', $apiKey->name],
                ['Status', $apiKey->status],
                ['Rate Limit', $apiKey->rate_limit . ' requests/hour'],
                ['Expires', $apiKey->expires_at ? $apiKey->expires_at->format('Y-m-d H:i:s') : 'Never'],
                ['Allowed Domains', implode(', ', $apiKey->allowed_domains ?? ['Any'])],
                ['Permissions', implode(', ', $apiKey->permissions ?? ['All'])],
                ['Created', $apiKey->created_at->format('Y-m-d H:i:s')],
            ]
        );

        $this->info('🔗 Integration Code:');
        $this->line('<script src="' . config('webbloc.cdn.base_url') . '/webbloc.min.js" data-api-key="' . $apiKey->key . '"></script>');
    }
}
```

## 10. app/Services/ApiKeyService.php

```php
<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\Website;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ApiKeyService
{
    public function generateApiKey(Website $website, array $data = []): ApiKey
    {
        $keyString = $this->generateKeyString();
        $hashedKey = $this->hashKey($keyString);

        $apiKey = ApiKey::create([
            'website_id' => $website->id,
            'key' => $keyString,
            'hashed_key' => $hashedKey,
            'name' => $data['name'] ?? "API Key for {$website->name}",
            'status' => 'active',
            'permissions' => $data['permissions'] ?? $this->getDefaultPermissions(),
            'rate_limit' => $data['rate_limit'] ?? config('webbloc.api.default_rate_limit', 1000),
            'allowed_domains' => $data['allowed_domains'] ?? [$website->domain],
            'allowed_ips' => $data['allowed_ips'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'metadata' => [
                'user_agent' => request()->userAgent(),
                'ip_address' => request()->ip(),
                'generated_by' => 'system',
            ],
        ]);

        // Clear cache for this key
        $this->clearKeyCache($keyString);

        return $apiKey;
    }

    public function validateApiKey(string $key): ?ApiKey
    {
        $cacheKey = "api_key_validation:{$key}";
        
        return Cache::remember($cacheKey, 300, function () use ($key) {
            $hashedKey = $this->hashKey($key);
            
            $apiKey = ApiKey::where('hashed_key', $hashedKey)
                           ->where('status', 'active')
                           ->with('website')
                           ->first();

            if (!$apiKey) {
                return null;
            }

            // Check expiration
            if ($apiKey->expires_at && $apiKey->expires_at->isPast()) {
                $apiKey->update(['status' => 'expired']);
                return null;
            }

            // Check website status
            if ($apiKey->website->status !== 'active') {
                return null;
            }

            return $apiKey;
        });
    }

    public function checkRateLimit(ApiKey $apiKey, string $endpoint = null): bool
    {
        $rateLimitKey = "rate_limit:{$apiKey->id}:" . now()->format('Y-m-d-H');
        $currentUsage = Cache::get($rateLimitKey, 0);

        // Check global rate limit
        if ($currentUsage >= $apiKey->rate_limit) {
            return false;
        }

        // Check endpoint-specific rate limits
        if ($endpoint && isset($apiKey->endpoint_limits[$endpoint])) {
            $endpointKey = "rate_limit:{$apiKey->id}:{$endpoint}:" . now()->format('Y-m-d-H');
            $endpointUsage = Cache::get($endpointKey, 0);
            
            if ($endpointUsage >= $apiKey->endpoint_limits[$endpoint]) {
                return false;
            }
        }

        return true;
    }

    public function incrementUsage(ApiKey $apiKey, string $endpoint = null): void
    {
        $rateLimitKey = "rate_limit:{$apiKey->id}:" . now()->format('Y-m-d-H');
        Cache::increment($rateLimitKey);
        Cache::put($rateLimitKey, Cache::get($rateLimitKey), 3600); // Expire after 1 hour

        // Increment endpoint-specific usage
        if ($endpoint) {
            $endpointKey = "rate_limit:{$apiKey->id}:{$endpoint}:" . now()->format('Y-m-d-H');
            Cache::increment($endpointKey);
            Cache::put($endpointKey, Cache::get($endpointKey), 3600);
        }

        // Update API key statistics
        $apiKey->increment('request_count');
        $apiKey->update(['last_used_at' => now()]);

        // Log usage for analytics
        $this->logUsage($apiKey, $endpoint);
    }

    public function checkPermission(ApiKey $apiKey, string $permission): bool
    {
        if (empty($apiKey->permissions)) {
            return true; // No restrictions
        }

        return in_array($permission, $apiKey->permissions) || 
               in_array('*', $apiKey->permissions);
    }

    public function checkDomainAccess(ApiKey $apiKey, string $domain): bool
    {
        if (empty($apiKey->allowed_domains)) {
            return true; // No domain restrictions
        }

        // Check exact domain match
        if (in_array($domain, $apiKey->allowed_domains)) {
            return true;
        }

        // Check wildcard domains
        foreach ($apiKey->allowed_domains as $allowedDomain) {
            if (Str::startsWith($allowedDomain, '*.')) {
                $pattern = str_replace('*.', '', $allowedDomain);
                if (Str::endsWith($domain, $pattern)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function checkIpAccess(ApiKey $apiKey, string $ip): bool
    {
        if (empty($apiKey->allowed_ips)) {
            return true; // No IP restrictions
        }

        return in_array($ip, $apiKey->allowed_ips);
    }

    public function regenerateApiKey(ApiKey $apiKey): ApiKey
    {
        $newKeyString = $this->generateKeyString();
        $newHashedKey = $this->hashKey($newKeyString);

        // Clear old key cache
        $this->clearKeyCache($apiKey->key);

        // Update the API key
        $apiKey->update([
            'key' => $newKeyString,
            'hashed_key' => $newHashedKey,
            'regenerated_at' => now(),
            'metadata' => array_merge($apiKey->metadata ?? [], [
                'regenerated_by' => auth()->user()?->id ?? 'system',
                'regenerated_at' => now()->toISOString(),
                'previous_key_hash' => $apiKey->hashed_key,
            ]),
        ]);

        return $apiKey->refresh();
    }

    public function revokeApiKey(ApiKey $apiKey, string $reason = null): bool
    {
        $apiKey->update([
            'status' => 'revoked',
            'revoked_at' => now(),
            'metadata' => array_merge($apiKey->metadata ?? [], [
                'revoked_by' => auth()->user()?->id ?? 'system',
                'revoked_reason' => $reason,
                'revoked_at' => now()->toISOString(),
            ]),
        ]);

        // Clear cache
        $this->clearKeyCache($apiKey->key);

        return true;
    }

    public function getApiKeyUsage(ApiKey $apiKey, Carbon $startDate = null, Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();

        $usage = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $rateLimitKey = "rate_limit:{$apiKey->id}:" . $current->format('Y-m-d-H');
            $usage[$current->format('Y-m-d H:00')] = Cache::get($rateLimitKey, 0);
            $current->addHour();
        }

        return $usage;
    }

    public function cleanupExpiredKeys(): int
    {
        $expiredKeys = ApiKey::where('status', 'active')
                            ->whereNotNull('expires_at')
                            ->where('expires_at', '<', now())
                            ->get();

        foreach ($expiredKeys as $key) {
            $key->update(['status' => 'expired']);
            $this->clearKeyCache($key->key);
        }

        return $expiredKeys->count();
    }

    protected function generateKeyString(): string
    {
        $prefix = config('webbloc.api.key_prefix', 'wb');
        $randomString = Str::random(32);
        
        return $prefix . '_' . $randomString;
    }

    protected function hashKey(string $key): string
    {
        return hash('sha256', $key . config('app.key'));
    }

    protected function clearKeyCache(string $key): void
    {
        Cache::forget("api_key_validation:{$key}");
    }

    protected function getDefaultPermissions(): array
    {
        return [
            'webbloc.read',
            'webbloc.create',
            'webbloc.update',
            'webbloc.delete',
            'auth.login',
            'auth.register',
            'auth.profile',
        ];
    }

    protected function logUsage(ApiKey $apiKey, string $endpoint = null): void
    {
        // This would typically log to a dedicated usage table or analytics service
        // For now, we'll just update the API key's usage statistics
        
        $metadata = $apiKey->metadata ?? [];
        $metadata['usage_logs'] = $metadata['usage_logs'] ?? [];
        
        // Keep only the last 100 usage logs to prevent metadata from growing too large
        if (count($metadata['usage_logs']) >= 100) {
            $metadata['usage_logs'] = array_slice($metadata['usage_logs'], -99);
        }

        $metadata['usage_logs'][] = [
            'timestamp' => now()->toISOString(),
            'endpoint' => $endpoint,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        $apiKey->update(['metadata' => $metadata]);
    }
}
```

## 11. app/Services/StatisticsService.php

```php
<?php

namespace App\Services;

use App\Models\Website;
use App\Models\WebsiteStatistic;
use App\Models\WebBlocInstance;
use App\Models\ApiKey;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class StatisticsService
{
    public function getSystemStatistics(): array
    {
        return Cache::remember('system_statistics', 300, function () {
            return [
                'websites' => [
                    'total' => Website::count(),
                    'active' => Website::where('status', 'active')->count(),
                    'pending' => Website::where('status', 'pending_verification')->count(),
                    'growth_percentage' => $this->calculateGrowthPercentage(Website::class),
                ],
                'api_requests' => [
                    'total_today' => $this->getApiRequestsToday(),
                    'total_month' => $this->getApiRequestsThisMonth(),
                    'average_per_day' => $this->getAverageApiRequestsPerDay(),
                    'growth_percentage' => $this->calculateApiRequestGrowth(),
                ],
                'users' => [
                    'total' => $this->getTotalUsers(),
                    'active_today' => $this->getActiveUsersToday(),
                    'new_this_month' => $this->getNewUsersThisMonth(),
                ],
                'webblocs' => [
                    'total_installations' => WebBlocInstance::count(),
                    'active_installations' => WebBlocInstance::where('status', 'active')->count(),
                    'most_popular' => $this->getMostPopularWebBlocs(),
                ],
                'performance' => [
                    'average_response_time' => $this->getAverageResponseTime(),
                    'error_rate' => $this->getErrorRate(),
                    'uptime_percentage' => $this->getUptimePercentage(),
                ],
            ];
        });
    }

    public function getWebsiteStatistics(Website $website, Carbon $startDate = null, Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();

        $cacheKey = "website_stats:{$website->id}:{$startDate->format('Y-m-d')}:{$endDate->format('Y-m-d')}";

        return Cache::remember($cacheKey, 3600, function () use ($website, $startDate, $endDate) {
            return [
                'overview' => [
                    'api_requests' => $this->getWebsiteApiRequests($website, $startDate, $endDate),
                    'unique_users' => $this->getWebsiteUniqueUsers($website, $startDate, $endDate),
                    'webbloc_interactions' => $this->getWebBlocInteractions($website, $startDate, $endDate),
                    'error_count' => $this->getWebsiteErrors($website, $startDate, $endDate),
                ],
                'webblocs' => [
                    'installed_count' => $website->webbloc_instances()->where('status', 'active')->count(),
                    'usage_by_type' => $this->getWebBlocUsageByType($website, $startDate, $endDate),
                    'performance_metrics' => $this->getWebBlocPerformanceMetrics($website, $startDate, $endDate),
                ],
                'users' => [
                    'total_registrations' => $this->getWebsiteUserRegistrations($website, $startDate, $endDate),
                    'login_activity' => $this->getWebsiteLoginActivity($website, $startDate, $endDate),
                    'user_engagement' => $this->getWebsiteUserEngagement($website, $startDate, $endDate),
                ],
                'traffic' => [
                    'hourly_distribution' => $this->getHourlyTrafficDistribution($website, $startDate, $endDate),
                    'daily_trends' => $this->getDailyTrafficTrends($website, $startDate, $endDate),
                    'top_pages' => $this->getTopPages($website, $startDate, $endDate),
                ],
                'geographic' => [
                    'countries' => $this->getTrafficByCountry($website, $startDate, $endDate),
                    'regions' => $this->getTrafficByRegion($website, $startDate, $endDate),
                ],
            ];
        });
    }

    public function processWebsiteStatistics(Website $website): void
    {
        $today = now()->startOfDay();
        
        // Check if statistics already processed for today
        $existing = WebsiteStatistic::where('website_id', $website->id)
                                   ->where('date', $today)
                                   ->first();

        if ($existing) {
            return; // Already processed
        }

        $statistics = $this->collectDailyStatistics($website, $today);
        
        WebsiteStatistic::create([
            'website_id' => $website->id,
            'date' => $today,
            'api_requests' => $statistics['api_requests'],
            'unique_users' => $statistics['unique_users'],
            'webbloc_interactions' => $statistics['webbloc_interactions'],
            'errors' => $statistics['errors'],
            'bandwidth_used' => $statistics['bandwidth_used'],
            'response_time_avg' => $statistics['response_time_avg'],
            'metadata' => $statistics['metadata'],
        ]);

        // Clear related caches
        $this->clearWebsiteStatisticsCache($website);
    }

    public function exportStatistics(Website $website = null, Carbon $startDate = null, Carbon $endDate = null, string $format = 'csv'): string
    {
        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();

        $query = WebsiteStatistic::whereBetween('date', [$startDate, $endDate]);
        
        if ($website) {
            $query->where('website_id', $website->id);
        }

        $statistics = $query->with('website')->orderBy('date')->get();

        switch ($format) {
            case 'json':
                return $statistics->toJson(JSON_PRETTY_PRINT);
            case 'xlsx':
                return $this->exportToExcel($statistics);
            default:
                return $this->exportToCsv($statistics);
        }
    }

    protected function calculateGrowthPercentage(string $model): float
    {
        $thisMonth = $model::whereBetween('created_at', [now()->startOfMonth(), now()])->count();
        $lastMonth = $model::whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])->count();

        if ($lastMonth === 0) {
            return $thisMonth > 0 ? 100 : 0;
        }

        return round(($thisMonth - $lastMonth) / $lastMonth * 100, 2);
    }

    protected function getApiRequestsToday(): int
    {
        return Cache::remember('api_requests_today', 300, function () {
            // This would sum up all API requests from today across all websites
            return WebsiteStatistic::where('date', now()->startOfDay())
                                  ->sum('api_requests');
        });
    }

    protected function getApiRequestsThisMonth(): int
    {
        return WebsiteStatistic::whereBetween('date', [now()->startOfMonth(), now()])
                              ->sum('api_requests');
    }

    protected function getAverageApiRequestsPerDay(): int
    {
        $totalRequests = WebsiteStatistic::whereBetween('date', [now()->subDays(30), now()])
                                        ->sum('api_requests');
        return round($totalRequests / 30);
    }

    protected function calculateApiRequestGrowth(): float
    {
        $thisMonth = $this->getApiRequestsThisMonth();
        $lastMonth = WebsiteStatistic::whereBetween('date', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
                                    ->sum('api_requests');

        if ($lastMonth === 0) {
            return $thisMonth > 0 ? 100 : 0;
        }

        return round(($thisMonth - $lastMonth) / $lastMonth * 100, 2);
    }

    protected function getTotalUsers(): int
    {
        // This would require aggregating users from all website SQLite databases
        // For now, return a cached value or estimated count
        return Cache::remember('total_users', 3600, function () {
            $total = 0;
            
            foreach (Website::where('status', 'active')->get() as $website) {
                try {
                    $connection = app('db.sqlite.connection.service')->getConnection($website->id);
                    $count = $connection->table('users')->count();
                    $total += $count;
                } catch (\Exception $e) {
                    // Skip if database doesn't exist or is inaccessible
                }
            }
            
            return $total;
        });
    }

    protected function getActiveUsersToday(): int
    {
        return WebsiteStatistic::where('date', now()->startOfDay())
                              ->sum('unique_users');
    }

    protected function getNewUsersThisMonth(): int
    {
        // This would require checking user creation dates across all SQLite databases
        return Cache::remember('new_users_this_month', 3600, function () {
            // Simplified implementation - would need proper cross-database querying
            return WebsiteStatistic::whereBetween('date', [now()->startOfMonth(), now()])
                                  ->sum('unique_users') * 0.3; // Estimate 30% are new users
        });
    }

    protected function getMostPopularWebBlocs(): array
    {
        return Cache::remember('most_popular_webblocs', 1800, function () {
            return WebBlocInstance::select('webbloc_type')
                                 ->selectRaw('COUNT(*) as installation_count')
                                 ->where('status', 'active')
                                 ->groupBy('webbloc_type')
                                 ->orderBy('installation_count', 'desc')
                                 ->limit(5)
                                 ->pluck('installation_count', 'webbloc_type')
                                 ->toArray();
        });
    }

    protected function getAverageResponseTime(): float
    {
        return WebsiteStatistic::where('date', '>=', now()->subDays(7))
                              ->whereNotNull('response_time_avg')
                              ->avg('response_time_avg') ?? 0;
    }

    protected function getErrorRate(): float
    {
        $totalRequests = $this->getApiRequestsThisMonth();
        $totalErrors = WebsiteStatistic::whereBetween('date', [now()->startOfMonth(), now()])
                                      ->sum('errors');

        if ($totalRequests === 0) {
            return 0;
        }

        return round($totalErrors / $totalRequests * 100, 2);
    }

    protected function getUptimePercentage(): float
    {
        // This would typically be calculated from uptime monitoring data
        // For now, return a high uptime percentage
        return 99.9;
    }

    protected function collectDailyStatistics(Website $website, Carbon $date): array
    {
        // This method would collect various statistics for a specific day
        // Implementation would depend on how you store request logs and user activity
        
        return [
            'api_requests' => $this->countApiRequestsForDate($website, $date),
            'unique_users' => $this->countUniqueUsersForDate($website, $date),
            'webbloc_interactions' => $this->countWebBlocInteractionsForDate($website, $date),
            'errors' => $this->countErrorsForDate($website, $date),
            'bandwidth_used' => $this->calculateBandwidthUsed($website, $date),
            'response_time_avg' => $this->calculateAverageResponseTime($website, $date),
            'metadata' => $this->collectAdditionalMetadata($website, $date),
        ];
    }

    protected function countApiRequestsForDate(Website $website, Carbon $date): int
    {
        // Implementation would depend on your request logging system
        return rand(100, 1000); // Placeholder
    }

    protected function countUniqueUsersForDate(Website $website, Carbon $date): int
    {
        // Implementation would query the website's SQLite database
        return rand(10, 100); // Placeholder
    }

    protected function countWebBlocInteractionsForDate(Website $website, Carbon $date): int
    {
        // Count WebBloc-specific interactions (comments, reviews, etc.)
        return rand(50, 500); // Placeholder
    }

    protected function countErrorsForDate(Website $website, Carbon $date): int
    {
        // Count API errors for the date
        return rand(0, 10); // Placeholder
    }

    protected function calculateBandwidthUsed(Website $website, Carbon $date): int
    {
        // Calculate bandwidth in bytes
        return rand(1000000, 10000000); // Placeholder
    }

    protected function calculateAverageResponseTime(Website $website, Carbon $date): float
    {
        // Calculate average response time in milliseconds
        return rand(100, 500) / 100; // Placeholder
    }

    protected function collectAdditionalMetadata(Website $website, Carbon $date): array
    {
        return [
            'peak_hour' => rand(9, 17) . ':00',
            'top_endpoint' => '/api/webblocs/comments',
            'user_agent_distribution' => [
                'Chrome' => rand(40, 60),
                'Firefox' => rand(20, 30),
                'Safari' => rand(10, 20),
                'Other' => rand(5, 15),
            ],
        ];
    }

    protected function clearWebsiteStatisticsCache(Website $website): void
    {
        $patterns = [
            "website_stats:{$website->id}:*",
            "system_statistics",
            "total_users",
            "api_requests_today",
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }

    protected function exportToCsv(mixed $statistics): string
    {
        $csv = "Date,Website,API Requests,Unique Users,WebBloc Interactions,Errors,Bandwidth Used,Response Time\n";
        
        foreach ($statistics as $stat) {
            $csv .= implode(',', [
                $stat->date->format('Y-m-d'),
                $stat->website->name ?? 'Unknown',
                $stat->api_requests,
                $stat->unique_users,
                $stat->webbloc_interactions,
                $stat->errors,
                $stat->bandwidth_used,
                $stat->response_time_avg,
            ]) . "\n";
        }

        return $csv;
    }

    protected function exportToExcel(mixed $statistics): string
    {
        // This would use Maatwebsite\Excel to create an Excel file
        // Implementation depends on the specific Excel package setup
        return "Excel export not implemented";
    }

    // Additional helper methods for specific statistics...
    protected function getWebsiteApiRequests(Website $website, Carbon $startDate, Carbon $endDate): int
    {
        return WebsiteStatistic::where('website_id', $website->id)
                              ->whereBetween('date', [$startDate, $endDate])
                              ->sum('api_requests');
    }

    protected function getWebsiteUniqueUsers(Website $website, Carbon $startDate, Carbon $endDate): int
    {
        return WebsiteStatistic::where('website_id', $website->id)
                              ->whereBetween('date', [$startDate, $endDate])
                              ->sum('unique_users');
    }

    protected function getWebBlocInteractions(Website $website, Carbon $startDate, Carbon $endDate): int
    {
        return WebsiteStatistic::where('website_id', $website->id)
                              ->whereBetween('date', [$startDate, $endDate])
                              ->sum('webbloc_interactions');
    }

    protected function getWebsiteErrors(Website $website, Carbon $startDate, Carbon $endDate): int
    {
        return WebsiteStatistic::where('website_id', $website->id)
                              ->whereBetween('date', [$startDate, $endDate])
                              ->sum('errors');
    }

    protected function getWebBlocUsageByType(Website $website, Carbon $startDate, Carbon $endDate): array
    {
        // This would require more complex querying of WebBloc usage data
        return []; // Placeholder
    }

    protected function getWebBlocPerformanceMetrics(Website $website, Carbon $startDate, Carbon $endDate): array
    {
        // Performance metrics for each WebBloc type
        return []; // Placeholder
    }

    protected function getWebsiteUserRegistrations(Website $website, Carbon $startDate, Carbon $endDate): int
    {
        // Query website's SQLite database for user registrations in date range
        return 0; // Placeholder
    }

    protected function getWebsiteLoginActivity(Website $website, Carbon $startDate, Carbon $endDate): array
    {
        // Login activity data
        return []; // Placeholder
    }

    protected function getWebsiteUserEngagement(Website $website, Carbon $startDate, Carbon $endDate): array
    {
        // User engagement metrics
        return []; // Placeholder
    }

    protected function getHourlyTrafficDistribution(Website $website, Carbon $startDate, Carbon $endDate): array
    {
        // Traffic distribution by hour of day
        return []; // Placeholder
    }

    protected function getDailyTrafficTrends(Website $website, Carbon $startDate, Carbon $endDate): array
    {
        // Daily traffic trends
        return []; // Placeholder
    }

    protected function getTopPages(Website $website, Carbon $startDate, Carbon $endDate): array
    {
        // Most visited pages
        return []; // Placeholder
    }

    protected function getTrafficByCountry(Website $website, Carbon $startDate, Carbon $endDate): array
    {
        // Traffic by country
        return []; // Placeholder
    }

    protected function getTrafficByRegion(Website $website, Carbon $startDate, Carbon $endDate): array
    {
        // Traffic by region
        return []; // Placeholder
    }
}
```

## 12. database/seeders/WebBlocSeeder.php

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WebBloc;

class WebBlocSeeder extends Seeder
{
    public function run(): void
    {
        $webBlocs = [
            [
                'type' => 'auth',
                'name' => 'Authentication',
                'description' => 'Complete user authentication system with login, registration, and profile management',
                'version' => '1.0.0',
                'attributes' => [
                    'registration_enabled' => [
                        'type' => 'boolean',
                        'default' => true,
                        'description' => 'Allow new user registrations'
                    ],
                    'social_login' => [
                        'type' => 'array',
                        'default' => [],
                        'description' => 'Enabled social login providers'
                    ],
                    'email_verification' => [
                        'type' => 'boolean',
                        'default' => true,
                        'description' => 'Require email verification for new accounts'
                    ],
                    'password_requirements' => [
                        'type' => 'object',
                        'default' => [
                            'min_length' => 8,
                            'require_uppercase' => true,
                            'require_numbers' => true,
                            'require_symbols' => false
                        ],
                        'description' => 'Password complexity requirements'
                    ],
                    'mode' => [
                        'type' => 'string',
                        'default' => 'modal',
                        'options' => ['modal', 'inline', 'redirect'],
                        'description' => 'Display mode for authentication forms'
                    ],
                    'theme' => [
                        'type' => 'string',
                        'default' => 'default',
                        'options' => ['default', 'minimal', 'corporate'],
                        'description' => 'Visual theme for authentication components'
                    ]
                ],
                'crud_operations' => [
                    'create' => true,
                    'read' => true,
                    'update' => true,
                    'delete' => false
                ],
                'api_endpoints' => [
                    'POST /auth/register',
                    'POST /auth/login',
                    'POST /auth/logout',
                    'GET /auth/profile',
                    'PUT /auth/profile',
                    'POST /auth/password/email',
                    'POST /auth/password/reset'
                ],
                'required_permissions' => ['auth.login', 'auth.register', 'auth.profile'],
                'metadata' => [
                    'category' => 'authentication',
                    'tags' => ['user', 'login', 'registration', 'security'],
                    'compatibility' => [
                        'laravel' => '>=10.0',
                        'php' => '>=8.1'
                    ],
                    'dependencies' => ['Laravel Sanctum'],
                    'file_structure' => [
                        'blade' => 'resources/views/components/webbloc/auth.blade.php',
                        'js' => 'resources/js/components/auth.js',
                        'css' => 'resources/css/components/auth.css'
                    ]
                ],
                'status' => 'active'
            ],
            [
                'type' => 'comments',
                'name' => 'Comments System',
                'description' => 'Interactive commenting system with nested replies, moderation, and real-time updates',
                'version' => '1.0.0',
                'attributes' => [
                    'allow_guest' => [
                        'type' => 'boolean',
                        'default' => false,
                        'description' => 'Allow guest users to post comments'
                    ],
                    'require_approval' => [
                        'type' => 'boolean',
                        'default' => true,
                        'description' => 'Require admin approval for new comments'
                    ],
                    'max_depth' => [
                        'type' => 'integer',
                        'default' => 3,
                        'min' => 1,
                        'max' => 10,
                        'description' => 'Maximum nesting depth for replies'
                    ],
                    'enable_reactions' => [
                        'type' => 'boolean',
                        'default' => true,
                        'description' => 'Enable like/dislike reactions on comments'
                    ],
                    'enable_mentions' => [
                        'type' => 'boolean',
                        'default' => true,
                        'description' => 'Enable @mention functionality'
                    ],
                    'spam_protection' => [
                        'type' => 'boolean',
                        'default' => true,
                        'description' => 'Enable automated spam detection'
                    ],
                    'sort_order' => [
                        'type' => 'string',
                        'default' => 'newest',
                        'options' => ['newest', 'oldest', 'popular', 'controversial'],
                        'description' => 'Default comment sorting order'
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'default' => 20,
                        'min' => 5,
                        'max' => 100,
                        'description' => 'Number of comments to display per page'
                    ]
                ],
                'crud_operations' => [
                    'create' => true,
                    'read' => true,
                    'update' => true,
                    'delete' => true
                ],
                'api_endpoints' => [
                    'GET /webblocs/comments',
                    'POST /webblocs/comments',
                    'GET /webblocs/comments/{id}',
                    'PUT /webblocs/comments/{id}',
                    'DELETE /webblocs/comments/{id}',
                    'POST /webblocs/comments/{id}/reactions'
                ],
                'required_permissions' => ['webbloc.read', 'webbloc.create'],
                'metadata' => [
                    'category' => 'engagement',
                    'tags' => ['comments', 'discussion', 'social', 'engagement'],
                    'compatibility' => [
                        'laravel' => '>=10.0',
                        'php' => '>=8.1'
                    ],
                    'dependencies' => [],
                    'file_structure' => [
                        'blade' => 'resources/views/components/webbloc/comments.blade.php',
                        'js' => 'resources/js/components/comments.js',
                        'css' => 'resources/css/components/comments.css'
                    ]
                ],
                'status' => 'active'
            ],
            [
                'type' => 'reviews',
                'name' => 'Reviews & Ratings',
                'description' => 'Customer review and rating system with photos, verification, and analytics',
                'version' => '1.0.0',
                'attributes' => [
                    'rating_scale' => [
                        'type' => 'integer',
                        'default' => 5,
                        'options' => [3, 5, 10],
                        'description' => 'Rating scale (e.g., 1-5 stars)'
                    ],
                    'require_purchase_verification' => [
                        'type' => 'boolean',
                        'default' => false,
                        'description' => 'Require purchase verification for reviews'
                    ],
                    'allow_anonymous' => [
                        'type' => 'boolean',
                        'default' => false,
                        'description' => 'Allow anonymous reviews'
                    ],
                    'allow_media' => [
                        'type' => 'boolean',
                        'default' => true,
                        'description' => 'Allow photo and video uploads'
                    ],
                    'require_review_text' => [
                        'type' => 'boolean',
                        'default' => true,
                        'description' => 'Require text content in addition to rating'
                    ],
                    'auto_approve' => [
                        'type' => 'boolean',
                        'default' => false,
                        'description' => 'Automatically approve new reviews'
                    ],
                    'show_reviewer_info' => [
                        'type' => 'boolean',
                        'default' => true,
                        'description' => 'Display reviewer name and profile'
                    ],
                    'enable_helpful_votes' => [
                        'type' => 'boolean',
                        'default' => true,
                        'description' => 'Enable "helpful" voting on reviews'
                    ]
                ],
                'crud_operations' => [
                    'create' => true,
                    'read' => true,
                    'update' => true,
                    'delete' => true
                ],
                'api_endpoints' => [
                    'GET /webblocs/reviews',
                    'POST /webblocs/reviews',
                    'GET /webblocs/reviews/{id}',
                    'PUT /webblocs/reviews/{id}',
                    'DELETE /webblocs/reviews/{id}',
                    'GET /webblocs/reviews/summary',
                    'POST /webblocs/reviews/{id}/helpful'
                ],
                'required_permissions' => ['webbloc.read', 'webbloc.create'],
                'metadata' => [
                    'category' => 'feedback',
                    'tags' => ['reviews', 'ratings', 'feedback', 'testimonials'],
                    'compatibility' => [
                        'laravel' => '>=10.0',
                        'php' => '>=8.1'
                    ],
                    'dependencies' => ['Intervention Image'],
                    'file_structure' => [
                        'blade' => 'resources/views/components/webbloc/reviews.blade.php',
                        'js' => 'resources/js/components/reviews.js',
                        'css' => 'resources/css/components/reviews.css'
                    ]
                ],
                'status' => 'active'
            ],
            [
                'type' => 'notifications',
                'name' => 'Notification System',
                'description' => 'Real-time notification system with multiple display options and delivery methods',
                'version' => '1.0.0',
                'attributes' => [
                    'position' => [
                        'type' => 'string',
                        'default' => 'top-right',
                        'options' => ['top-left', 'top-right', 'top-center', 'bottom-left', 'bottom-right', 'bottom-center'],
                        'description' => 'Display position for toast notifications'
                    ],
                    'auto_dismiss' => [
                        'type' => 'boolean',
                        'default' => true,
                        'description' => 'Automatically dismiss notifications'
                    ],
                    'dismiss_delay' => [
                        'type' => 'integer',
                        'default' => 5000,
                        'min' => 1000,
                        'max' => 30000,
                        'description' => 'Auto-dismiss delay in milliseconds'
                    ],
                    'sound_enabled' => [
                        'type' => 'boolean',
                        'default' => false,
                        'description' => 'Enable notification sounds'
                    ],
                    'max_visible' => [
                        'type' => 'integer',
                        'default' => 5,
                        'min' => 1,
                        'max' => 20,
                        'description' => 'Maximum number of visible notifications'
                    ],
                    'enable_push' => [
                        'type' => 'boolean',
                        'default' => false,
                        'description' => 'Enable browser push notifications'
                    ],
                    'categories' => [
                        'type' => 'array',
                        'default' => ['info', 'success', 'warning', 'error'],
                        'description' => 'Available notification categories'
                    ]
                ],
                'crud_operations' => [
                    'create' => true,
                    'read' => true,
                    'update' => false,
                    'delete' => true
                ],
                'api_endpoints' => [
                    'GET /webblocs/notifications',
                    'POST /webblocs/notifications',
                    'PUT /webblocs/notifications/{id}/read',
                    'DELETE /webblocs/notifications/{id}',
                    'POST /webblocs/notifications/mark-all-read'
                ],
                'required_permissions' => ['webbloc.read'],
                'metadata' => [
                    'category' => 'communication',
                    'tags' => ['notifications', 'alerts', 'messaging', 'real-time'],
                    'compatibility' => [
                        'laravel' => '>=10.0',
                        'php' => '>=8.1'
                    ],
                    'dependencies' => ['Laravel Broadcasting'],
                    'file_structure' => [
                        'blade' => 'resources/views/components/webbloc/notifications.blade.php',
                        'js' => 'resources/js/components/notifications.js',
                        'css' => 'resources/css/components/notifications.css'
                    ]
                ],
                'status' => 'active'
            ]
        ];

        foreach ($webBlocs as $webBlocData) {
            WebBloc::updateOrCreate(
                ['type' => $webBlocData['type']],
                $webBlocData
            );
        }

        $this->command->info('WebBloc definitions seeded successfully!');
    }
}
```
