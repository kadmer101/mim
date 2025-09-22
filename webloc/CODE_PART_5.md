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
