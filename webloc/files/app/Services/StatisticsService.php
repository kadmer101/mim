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