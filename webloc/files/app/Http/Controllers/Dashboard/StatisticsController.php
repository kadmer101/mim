<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Models\WebsiteStatistic;
use App\Models\ApiKey;
use App\Models\WebBloc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller as BaseController;

class StatisticsController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $range = $request->get('range', '30days');
        $websiteId = $request->get('website_id');
        
        // Determine date range
        $days = match($range) {
            '24hours' => 1,
            '7days' => 7,
            '30days' => 30,
            '90days' => 90,
            'year' => 365,
            default => 30,
        };

        $startDate = now()->subDays($days);

        // Filter websites based on user role
        $websitesQuery = Website::query();
        if (!Auth::user()->hasRole('admin')) {
            $websitesQuery->where('owner_id', Auth::id());
        }
        
        if ($websiteId) {
            $websitesQuery->where('id', $websiteId);
        }

        $websites = $websitesQuery->get();
        $websiteIds = $websites->pluck('id');

        // Get statistics
        $stats = $this->getOverviewStats($websiteIds, $startDate);
        $chartData = $this->getChartData($websiteIds, $startDate, $range);
        $topMetrics = $this->getTopMetrics($websiteIds, $startDate);
        $performanceMetrics = $this->getPerformanceMetrics($websiteIds, $startDate);

        // Get websites for filter (if admin)
        $availableWebsites = Auth::user()->hasRole('admin')
            ? Website::select('id', 'name')->orderBy('name')->get()
            : $websites;

        return view('dashboard.statistics.index', compact(
            'stats',
            'chartData',
            'topMetrics',
            'performanceMetrics',
            'availableWebsites',
            'websiteId',
            'range'
        ));
    }

    public function website(Website $website, Request $request)
    {
        // Check authorization
        if (!Auth::user()->hasRole('admin') && $website->owner_id !== Auth::id()) {
            abort(403, 'You can only view statistics for your own websites.');
        }

        $range = $request->get('range', '30days');
        $days = match($range) {
            '24hours' => 1,
            '7days' => 7,
            '30days' => 30,
            '90days' => 90,
            'year' => 365,
            default => 30,
        };

        $startDate = now()->subDays($days);

        // Get website-specific statistics
        $stats = $this->getWebsiteStats($website->id, $startDate);
        $chartData = $this->getWebsiteChartData($website->id, $startDate, $range);
        $webBlocStats = $this->getWebsiteWebBlocStats($website->id, $startDate);
        $apiKeyStats = $this->getWebsiteApiKeyStats($website->id, $startDate);
        $geographicData = $this->getGeographicData($website->id, $startDate);

        return view('dashboard.statistics.website', compact(
            'website',
            'stats',
            'chartData',
            'webBlocStats',
            'apiKeyStats',
            'geographicData',
            'range'
        ));
    }

    public function realtime(Request $request)
    {
        $websiteId = $request->get('website_id');
        
        // Filter websites based on user role
        if ($websiteId) {
            $website = Website::findOrFail($websiteId);
            if (!Auth::user()->hasRole('admin') && $website->owner_id !== Auth::id()) {
                abort(403);
            }
            $websiteIds = [$websiteId];
        } else {
            $websitesQuery = Website::query();
            if (!Auth::user()->hasRole('admin')) {
                $websitesQuery->where('owner_id', Auth::id());
            }
            $websiteIds = $websitesQuery->pluck('id');
        }

        // Get real-time data (last 24 hours with hourly breakdown)
        $realtimeData = $this->getRealtimeData($websiteIds);
        
        return response()->json($realtimeData);
    }

    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,xlsx,json',
            'range' => 'required|in:24hours,7days,30days,90days,year',
            'website_id' => 'nullable|exists:websites,id',
            'metrics' => 'required|array|min:1',
            'metrics.*' => 'string|in:requests,visitors,formats,errors,performance,webblocs',
        ]);

        $websiteId = $request->website_id;
        $range = $request->range;
        $format = $request->format;
        $metrics = $request->metrics;

        // Check authorization for specific website
        if ($websiteId) {
            $website = Website::findOrFail($websiteId);
            if (!Auth::user()->hasRole('admin') && $website->owner_id !== Auth::id()) {
                abort(403);
            }
        }

        $days = match($range) {
            '24hours' => 1,
            '7days' => 7,
            '30days' => 30,
            '90days' => 90,
            'year' => 365,
        };

        $startDate = now()->subDays($days);
        $exportData = $this->generateExportData($websiteId, $startDate, $metrics);

        return $this->downloadExport($exportData, $format, $range);
    }

    private function getOverviewStats($websiteIds, $startDate)
    {
        $current = WebsiteStatistic::whereIn('website_id', $websiteIds)
            ->where('date', '>=', $startDate)
            ->selectRaw('
                SUM(total_requests) as total_requests,
                SUM(unique_visitors) as unique_visitors,
                SUM(html_responses) as html_responses,
                SUM(json_responses) as json_responses,
                SUM(other_responses) as other_responses,
                SUM(error_count) as error_count,
                AVG(avg_response_time) as avg_response_time,
                SUM(data_transferred) as data_transferred
            ')
            ->first();

        // Get previous period for comparison
        $previousStartDate = $startDate->copy()->subDays($startDate->diffInDays(now()));
        $previous = WebsiteStatistic::whereIn('website_id', $websiteIds)
            ->whereBetween('date', [$previousStartDate, $startDate])
            ->selectRaw('
                SUM(total_requests) as total_requests,
                SUM(unique_visitors) as unique_visitors,
                SUM(error_count) as error_count,
                AVG(avg_response_time) as avg_response_time
            ')
            ->first();

        return [
            'current' => [
                'total_requests' => $current->total_requests ?? 0,
                'unique_visitors' => $current->unique_visitors ?? 0,
                'html_responses' => $current->html_responses ?? 0,
                'json_responses' => $current->json_responses ?? 0,
                'other_responses' => $current->other_responses ?? 0,
                'error_count' => $current->error_count ?? 0,
                'avg_response_time' => round($current->avg_response_time ?? 0, 2),
                'data_transferred' => $current->data_transferred ?? 0,
                'error_rate' => $current->total_requests > 0 
                    ? round(($current->error_count / $current->total_requests) * 100, 2)
                    : 0,
            ],
            'previous' => [
                'total_requests' => $previous->total_requests ?? 0,
                'unique_visitors' => $previous->unique_visitors ?? 0,
                'error_count' => $previous->error_count ?? 0,
                'avg_response_time' => round($previous->avg_response_time ?? 0, 2),
            ],
            'changes' => [
                'requests' => $this->calculatePercentageChange(
                    $previous->total_requests ?? 0, 
                    $current->total_requests ?? 0
                ),
                'visitors' => $this->calculatePercentageChange(
                    $previous->unique_visitors ?? 0, 
                    $current->unique_visitors ?? 0
                ),
                'errors' => $this->calculatePercentageChange(
                    $previous->error_count ?? 0, 
                    $current->error_count ?? 0
                ),
                'response_time' => $this->calculatePercentageChange(
                    $previous->avg_response_time ?? 0, 
                    $current->avg_response_time ?? 0
                ),
            ],
        ];
    }

    private function getChartData($websiteIds, $startDate, $range)
    {
        $groupBy = match($range) {
            '24hours' => 'HOUR(date)',
            '7days', '30days' => 'DATE(date)',
            '90days', 'year' => 'WEEK(date)',
        };

        $dateFormat = match($range) {
            '24hours' => 'H:i',
            '7days', '30days' => 'M j',
            '90days', 'year' => 'M j',
        };

        $statistics = WebsiteStatistic::whereIn('website_id', $websiteIds)
            ->where('date', '>=', $startDate)
            ->selectRaw("
                {$groupBy} as period,
                DATE(date) as date,
                SUM(total_requests) as requests,
                SUM(unique_visitors) as visitors,
                SUM(html_responses) as html,
                SUM(json_responses) as json,
                SUM(other_responses) as other,
                SUM(error_count) as errors,
                AVG(avg_response_time) as response_time
            ")
            ->groupBy('period', 'date')
            ->orderBy('date')
            ->get();

        return [
            'requests_and_visitors' => $statistics->map(function ($stat) use ($dateFormat) {
                return [
                    'date' => Carbon::parse($stat->date)->format($dateFormat),
                    'requests' => $stat->requests,
                    'visitors' => $stat->visitors,
                ];
            })->values(),
            'response_formats' => $statistics->map(function ($stat) use ($dateFormat) {
                $total = $stat->html + $stat->json + $stat->other;
                return [
                    'date' => Carbon::parse($stat->date)->format($dateFormat),
                    'html' => $total > 0 ? round(($stat->html / $total) * 100, 1) : 0,
                    'json' => $total > 0 ? round(($stat->json / $total) * 100, 1) : 0,
                    'other' => $total > 0 ? round(($stat->other / $total) * 100, 1) : 0,
                ];
            })->values(),
            'errors_and_performance' => $statistics->map(function ($stat) use ($dateFormat) {
                return [
                    'date' => Carbon::parse($stat->date)->format($dateFormat),
                    'errors' => $stat->errors,
                    'response_time' => round($stat->response_time, 2),
                    'error_rate' => $stat->requests > 0 
                        ? round(($stat->errors / $stat->requests) * 100, 2)
                        : 0,
                ];
            })->values(),
        ];
    }

    private function getTopMetrics($websiteIds, $startDate)
    {
        // Top websites by requests
        $topWebsites = WebsiteStatistic::whereIn('website_id', $websiteIds)
            ->where('date', '>=', $startDate)
            ->join('websites', 'website_statistics.website_id', '=', 'websites.id')
            ->selectRaw('
                websites.id,
                websites.name,
                websites.url,
                SUM(total_requests) as total_requests,
                SUM(unique_visitors) as unique_visitors,
                AVG(avg_response_time) as avg_response_time
            ')
            ->groupBy('websites.id', 'websites.name', 'websites.url')
            ->orderByDesc('total_requests')
            ->take(10)
            ->get();

        // Top WebBlocs by usage
        $topWebBlocs = DB::table('web_bloc_instances')
            ->join('web_blocs', 'web_bloc_instances.webbloc_id', '=', 'web_blocs.id')
            ->whereIn('web_bloc_instances.website_id', $websiteIds)
            ->selectRaw('
                web_blocs.id,
                web_blocs.name,
                web_blocs.type,
                COUNT(web_bloc_instances.id) as installation_count
            ')
            ->groupBy('web_blocs.id', 'web_blocs.name', 'web_blocs.type')
            ->orderByDesc('installation_count')
            ->take(10)
            ->get();

        // Most active API keys
        $topApiKeys = ApiKey::whereIn('website_id', $websiteIds)
            ->with(['website', 'user'])
            ->orderByDesc('total_requests')
            ->take(10)
            ->get();

        return [
            'websites' => $topWebsites,
            'webblocs' => $topWebBlocs,
            'api_keys' => $topApiKeys,
        ];
    }

    private function getPerformanceMetrics($websiteIds, $startDate)
    {
        $hourlyData = WebsiteStatistic::whereIn('website_id', $websiteIds)
            ->where('date', '>=', now()->subHours(24))
            ->selectRaw('
                HOUR(created_at) as hour,
                AVG(avg_response_time) as avg_response_time,
                SUM(total_requests) as total_requests,
                SUM(error_count) as error_count
            ')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $performanceInsights = [
            'peak_hour' => $hourlyData->sortByDesc('total_requests')->first(),
            'fastest_hour' => $hourlyData->sortBy('avg_response_time')->first(),
            'slowest_hour' => $hourlyData->sortByDesc('avg_response_time')->first(),
            'most_errors_hour' => $hourlyData->sortByDesc('error_count')->first(),
        ];

        return [
            'hourly_data' => $hourlyData->map(function ($data) {
                return [
                    'hour' => str_pad($data->hour, 2, '0', STR_PAD_LEFT) . ':00',
                    'response_time' => round($data->avg_response_time, 2),
                    'requests' => $data->total_requests,
                    'errors' => $data->error_count,
                    'error_rate' => $data->total_requests > 0 
                        ? round(($data->error_count / $data->total_requests) * 100, 2)
                        : 0,
                ];
            })->values(),
            'insights' => $performanceInsights,
        ];
    }

    private function getWebsiteStats($websiteId, $startDate)
    {
        return WebsiteStatistic::where('website_id', $websiteId)
            ->where('date', '>=', $startDate)
            ->selectRaw('
                SUM(total_requests) as total_requests,
                SUM(unique_visitors) as unique_visitors,
                SUM(html_responses) as html_responses,
                SUM(json_responses) as json_responses,
                SUM(other_responses) as other_responses,
                SUM(error_count) as error_count,
                AVG(avg_response_time) as avg_response_time,
                SUM(data_transferred) as data_transferred
            ')
            ->first();
    }

    private function getWebsiteChartData($websiteId, $startDate, $range)
    {
        // Similar to getChartData but for single website
        return $this->getChartData([$websiteId], $startDate, $range);
    }

    private function getWebsiteWebBlocStats($websiteId, $startDate)
    {
        return DB::table('web_bloc_instances')
            ->join('web_blocs', 'web_bloc_instances.webbloc_id', '=', 'web_blocs.id')
            ->where('web_bloc_instances.website_id', $websiteId)
            ->selectRaw('
                web_blocs.id,
                web_blocs.name,
                web_blocs.type,
                web_bloc_instances.status,
                web_bloc_instances.created_at as installed_at
            ')
            ->orderBy('web_bloc_instances.created_at', 'desc')
            ->get();
    }

    private function getWebsiteApiKeyStats($websiteId, $startDate)
    {
        return ApiKey::where('website_id', $websiteId)
            ->with('user')
            ->selectRaw('
                *,
                CASE 
                    WHEN last_used_at >= ? THEN "active"
                    WHEN last_used_at IS NULL THEN "unused"
                    ELSE "inactive"
                END as usage_status
            ', [now()->subDays(7)])
            ->orderBy('total_requests', 'desc')
            ->get();
    }

    private function getGeographicData($websiteId, $startDate)
    {
        // This would require IP geolocation data
        // For now, return sample data
        return [
            ['country' => 'United States', 'requests' => rand(100, 1000)],
            ['country' => 'Canada', 'requests' => rand(50, 500)],
            ['country' => 'United Kingdom', 'requests' => rand(30, 300)],
            ['country' => 'Germany', 'requests' => rand(20, 200)],
            ['country' => 'Australia', 'requests' => rand(10, 100)],
        ];
    }

    private function getRealtimeData($websiteIds)
    {
        // Get data from the last 24 hours, grouped by hour
        $hourlyStats = WebsiteStatistic::whereIn('website_id', $websiteIds)
            ->where('created_at', '>=', now()->subHours(24))
            ->selectRaw('
                HOUR(created_at) as hour,
                SUM(total_requests) as requests,
                SUM(unique_visitors) as visitors,
                SUM(error_count) as errors,
                AVG(avg_response_time) as response_time
            ')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Current metrics (last hour)
        $currentHour = now()->hour;
        $currentStats = $hourlyStats->firstWhere('hour', $currentHour);

        return [
            'current' => [
                'requests_per_hour' => $currentStats->requests ?? 0,
                'visitors_per_hour' => $currentStats->visitors ?? 0,
                'errors_per_hour' => $currentStats->errors ?? 0,
                'avg_response_time' => round($currentStats->response_time ?? 0, 2),
                'timestamp' => now()->toISOString(),
            ],
            'hourly_trend' => $hourlyStats->map(function ($stat) {
                return [
                    'hour' => str_pad($stat->hour, 2, '0', STR_PAD_LEFT) . ':00',
                    'requests' => $stat->requests,
                    'visitors' => $stat->visitors,
                    'errors' => $stat->errors,
                    'response_time' => round($stat->response_time, 2),
                ];
            })->values(),
        ];
    }

    private function calculatePercentageChange($previous, $current)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function generateExportData($websiteId, $startDate, $metrics)
    {
        $data = [];
        
        if ($websiteId) {
            $websiteIds = [$websiteId];
            $website = Website::find($websiteId);
            $data['website'] = $website->name;
        } else {
            $websitesQuery = Website::query();
            if (!Auth::user()->hasRole('admin')) {
                $websitesQuery->where('owner_id', Auth::id());
            }
            $websiteIds = $websitesQuery->pluck('id');
            $data['scope'] = 'All accessible websites';
        }

        $data['period'] = $startDate->format('Y-m-d') . ' to ' . now()->format('Y-m-d');
        $data['exported_at'] = now()->toISOString();
        $data['exported_by'] = Auth::user()->name;

        // Get requested metrics
        if (in_array('requests', $metrics)) {
            $data['requests'] = WebsiteStatistic::whereIn('website_id', $websiteIds)
                ->where('date', '>=', $startDate)
                ->select('date', 'total_requests', 'unique_visitors')
                ->orderBy('date')
                ->get()
                ->toArray();
        }

        // Add other metrics based on request...

        return $data;
    }

    private function downloadExport($data, $format, $range)
    {
        $filename = "webbloc_statistics_{$range}_" . now()->format('Y-m-d_H-i-s');

        switch ($format) {
            case 'json':
                return response()->json($data)
                    ->header('Content-Disposition', "attachment; filename=\"{$filename}.json\"");
            
            case 'csv':
                // Convert to CSV format
                $csv = $this->arrayToCsv($data);
                return response($csv)
                    ->header('Content-Type', 'text/csv')
                    ->header('Content-Disposition', "attachment; filename=\"{$filename}.csv\"");
            
            case 'xlsx':
                // This would require additional Excel processing
                // For now, return JSON
                return response()->json($data)
                    ->header('Content-Disposition', "attachment; filename=\"{$filename}.json\"");
            
            default:
                return response()->json(['error' => 'Unsupported format'], 400);
        }
    }

    private function arrayToCsv($data)
    {
        $output = fopen('php://temp', 'r+');
        
        // Write headers
        if (!empty($data) && is_array($data)) {
            $firstRow = reset($data);
            if (is_array($firstRow)) {
                fputcsv($output, array_keys($firstRow));
                foreach ($data as $row) {
                    fputcsv($output, $row);
                }
            }
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
}