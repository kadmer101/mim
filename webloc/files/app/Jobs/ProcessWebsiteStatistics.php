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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
            Log::info("Processing statistics for website: {$this->website->name}", [
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

            Log::info("Statistics processing completed for website: {$this->website->name}");

        } catch (\Exception $e) {
            Log::error("Failed to process statistics for website {$this->website->id}: " . $e->getMessage(), [
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
            $totalUsers = DB::connection('sqlite_website')
                ->table('users')
                ->count();

            $newUsersToday = DB::connection('sqlite_website')
                ->table('users')
                ->whereDate('created_at', $date)
                ->count();

            $activeUsersToday = DB::connection('sqlite_website')
                ->table('users')
                ->whereDate('last_activity_at', $date)
                ->count();

            $verifiedUsers = DB::connection('sqlite_website')
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
            Log::error("Failed to process user statistics: " . $e->getMessage());
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
            $totalWebBlocs = DB::connection('sqlite_website')
                ->table('web_blocs')
                ->where('status', 'active')
                ->count();

            $newWebBlocsToday = DB::connection('sqlite_website')
                ->table('web_blocs')
                ->whereDate('created_at', $date)
                ->count();

            // Count by type
            $webBlocsByType = DB::connection('sqlite_website')
                ->table('web_blocs')
                ->select('webbloc_type', DB::raw('count(*) as count'))
                ->where('status', 'active')
                ->groupBy('webbloc_type')
                ->pluck('count', 'webbloc_type')
                ->toArray();

            // Get engagement metrics
            $totalViews = DB::connection('sqlite_website')
                ->table('web_blocs')
                ->whereDate('updated_at', $date)
                ->sum('view_count');

            $totalInteractions = DB::connection('sqlite_website')
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
            Log::error("Failed to process WebBloc statistics: " . $e->getMessage());
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
            $comments = DB::connection('sqlite_website')
                ->table('web_blocs')
                ->where('webbloc_type', 'comment')
                ->whereDate('created_at', $date)
                ->count();

            $reviews = DB::connection('sqlite_website')
                ->table('web_blocs')
                ->where('webbloc_type', 'review')
                ->whereDate('created_at', $date)
                ->count();

            $likes = DB::connection('sqlite_website')
                ->table('web_blocs')
                ->whereDate('updated_at', $date)
                ->sum('likes_count');

            $shares = DB::connection('sqlite_website')
                ->table('web_blocs')
                ->whereDate('updated_at', $date)
                ->sum('shares_count');

            // Calculate average rating if reviews exist
            $averageRating = DB::connection('sqlite_website')
                ->table('web_blocs')
                ->where('webbloc_type', 'review')
                ->whereNotNull('data->rating')
                ->avg(DB::raw("CAST(JSON_EXTRACT(data, '$.rating') AS DECIMAL(3,2))"));

            $this->updateStatistics([
                'comments_today' => $comments,
                'reviews_today' => $reviews,
                'likes_today' => $likes,
                'shares_today' => $shares,
                'average_rating' => round($averageRating, 2)
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to process engagement statistics: " . $e->getMessage());
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
            Log::error("Failed to process performance statistics: " . $e->getMessage());
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
            Log::error("Failed to process error statistics: " . $e->getMessage());
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
        Log::error("Statistics processing job failed permanently for website {$this->website->id}", [
            'exception' => $exception->getMessage(),
            'website_id' => $this->website->id,
            'date' => $this->date->toDateString()
        ]);

        // Optionally send notification to administrators
        // Notification::route('mail', config('mail.admin_email'))
        //     ->notify(new StatisticsProcessingFailed($this->website, $exception));
    }
}