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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
            Log::info('Starting cleanup of expired tokens', ['dry_run' => $this->dryRun]);

            $cleanupResults = [
                'expired_api_keys' => $this->cleanupExpiredApiKeys(),
                'inactive_api_keys' => $this->cleanupInactiveApiKeys(),
                'expired_website_tokens' => $this->cleanupExpiredWebsiteTokens(),
                'old_statistics' => $this->cleanupOldStatistics(),
                'temp_files' => $this->cleanupTempFiles(),
                'session_tokens' => $this->cleanupExpiredSessions()
            ];

            $totalCleaned = array_sum($cleanupResults);

            Log::info('Token cleanup completed', [
                'dry_run' => $this->dryRun,
                'total_cleaned' => $totalCleaned,
                'breakdown' => $cleanupResults
            ]);

            // Send summary notification if significant cleanup occurred
            if ($totalCleaned > 0 && !$this->dryRun) {
                $this->sendCleanupSummary($cleanupResults);
            }

        } catch (\Exception $e) {
            Log::error('Token cleanup job failed: ' . $e->getMessage(), [
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

                Log::info("API key expired and deactivated", [
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

                Log::info("API key marked as inactive due to long inactivity", [
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

                Log::info("Website verification token expired", [
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

            Log::info("Old statistics records cleaned up", [
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
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as record_count'),
                DB::raw('AVG(CAST(JSON_EXTRACT(statistics_data, "$.api_calls_today") AS UNSIGNED)) as avg_api_calls'),
                DB::raw('AVG(CAST(JSON_EXTRACT(statistics_data, "$.average_response_time") AS DECIMAL(8,2))) as avg_response_time'),
                DB::raw('SUM(CAST(JSON_EXTRACT(statistics_data, "$.page_views_today") AS UNSIGNED)) as total_page_views'),
                DB::raw('MAX(CAST(JSON_EXTRACT(statistics_data, "$.total_users") AS UNSIGNED)) as max_total_users')
            ])
            ->groupBy('website_id', 'year', 'month')
            ->get();

        foreach ($statistics as $summary) {
            DB::table('website_statistics_monthly')->insertOrIgnore([
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
            Log::info("Temporary files cleaned up", [
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
            $sessionCount = DB::table('sessions')
                ->where('last_activity', '<', now()->subDays(30)->timestamp)
                ->count();

            if (!$this->dryRun && $sessionCount > 0) {
                DB::table('sessions')
                    ->where('last_activity', '<', now()->subDays(30)->timestamp)
                    ->delete();
            }

            // Clean up personal access tokens (Sanctum)
            $tokenCount = DB::table('personal_access_tokens')
                ->where('expires_at', '<', now())
                ->count();

            if (!$this->dryRun && $tokenCount > 0) {
                DB::table('personal_access_tokens')
                    ->where('expires_at', '<', now())
                    ->delete();
            }

            $sessionCount += $tokenCount;

        } catch (\Exception $e) {
            Log::warning('Failed to cleanup sessions: ' . $e->getMessage());
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

        Log::info($message);

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
        Log::error('Token cleanup job failed permanently', [
            'exception' => $exception->getMessage(),
            'dry_run' => $this->dryRun
        ]);
    }
}