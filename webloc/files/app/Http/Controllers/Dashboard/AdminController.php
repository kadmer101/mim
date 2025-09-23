<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Website;
use App\Models\ApiKey;
use App\Models\WebBloc;
use App\Models\WebsiteStatistic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan; // Import the Artisan facades
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class AdminController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        // Dashboard statistics
        $stats = [
            'total_websites' => Website::count(),
            'active_websites' => Website::where('status', 'active')->count(),
            'total_api_keys' => ApiKey::count(),
            'active_api_keys' => ApiKey::where('status', 'active')->count(),
            'total_webblocs' => WebBloc::count(),
            'public_webblocs' => WebBloc::where('is_public', true)->count(),
            'total_requests_today' => $this->getTodayRequests(),
            'total_requests_month' => $this->getMonthRequests(),
        ];

        // Recent activity
        $recent_websites = Website::with('owner')
            ->latest()
            ->take(5)
            ->get();

        $recent_api_keys = ApiKey::with(['website', 'user'])
            ->latest()
            ->take(5)
            ->get();

        // System health metrics
        $health = [
            'avg_response_time' => $this->getAverageResponseTime(),
            'error_rate' => $this->getErrorRate(),
            'storage_usage' => $this->getStorageUsage(),
            'database_status' => $this->getDatabaseStatus(),
        ];

        // Chart data for dashboard
        $chartData = [
            'requests_last_7_days' => $this->getRequestsChart(),
            'response_format_distribution' => $this->getResponseFormatChart(),
            'webbloc_usage' => $this->getWebBlocUsageChart(),
            'geographical_distribution' => $this->getGeographicalChart(),
        ];

        return view('dashboard.admin.index', compact(
            'stats',
            'recent_websites',
            'recent_api_keys',
            'health',
            'chartData'
        ));
    }

    private function getTodayRequests()
    {
        return WebsiteStatistic::whereDate('date', today())
            ->sum('total_requests');
    }

    private function getMonthRequests()
    {
        return WebsiteStatistic::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('total_requests');
    }

    private function getAverageResponseTime()
    {
        return WebsiteStatistic::whereDate('date', '>=', now()->subDays(7))
            ->avg('avg_response_time') ?? 0;
    }

    private function getErrorRate()
    {
        $totalRequests = WebsiteStatistic::whereDate('date', '>=', now()->subDays(7))
            ->sum('total_requests');
        
        $errorRequests = WebsiteStatistic::whereDate('date', '>=', now()->subDays(7))
            ->sum('error_count');

        return $totalRequests > 0 ? ($errorRequests / $totalRequests) * 100 : 0;
    }

    private function getStorageUsage()
    {
        $databases = Website::pluck('sqlite_database_path')
            ->filter()
            ->map(function ($path) {
                $fullPath = storage_path('databases/' . basename($path));
                return file_exists($fullPath) ? filesize($fullPath) : 0;
            })
            ->sum();

        return [
            'databases_size' => $databases,
            'total_size' => $databases + $this->getLogSize(),
            'formatted_size' => $this->formatBytes($databases),
        ];
    }

    private function getLogSize()
    {
        $logPath = storage_path('logs');
        $size = 0;
        
        if (is_dir($logPath)) {
            foreach (glob($logPath . '/*.log') as $file) {
                $size += filesize($file);
            }
        }
        
        return $size;
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    private function getDatabaseStatus()
    {
        try {
            // Check MySQL connection
            DB::connection()->getPdo();
            $mysql_status = 'connected';
            
            // Check SQLite databases
            $websites = Website::where('status', 'active')->count();
            $working_databases = 0;
            
            foreach (Website::where('status', 'active')->take(10)->get() as $website) {
                try {
                    $website->getSqliteConnection()->getPdo();
                    $working_databases++;
                } catch (\Exception $e) {
                    // Database connection failed
                }
            }
            
            return [
                'mysql' => $mysql_status,
                'sqlite_health' => $websites > 0 ? ($working_databases / min($websites, 10)) * 100 : 100,
            ];
        } catch (\Exception $e) {
            return [
                'mysql' => 'disconnected',
                'sqlite_health' => 0,
            ];
        }
    }

    private function getRequestsChart()
    {
        $data = WebsiteStatistic::selectRaw('DATE(date) as day, SUM(total_requests) as requests')
            ->whereDate('date', '>=', now()->subDays(7))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $labels = [];
        $values = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M j');
            
            $dayData = $data->firstWhere('day', $date);
            $values[] = $dayData ? $dayData->requests : 0;
        }

        return [
            'labels' => $labels,
            'data' => $values,
        ];
    }

    private function getResponseFormatChart()
    {
        $stats = WebsiteStatistic::selectRaw('
            SUM(html_responses) as html,
            SUM(json_responses) as json,
            SUM(other_responses) as other
        ')
        ->whereDate('date', '>=', now()->subDays(30))
        ->first();

        $total = ($stats->html ?? 0) + ($stats->json ?? 0) + ($stats->other ?? 0);
        
        if ($total === 0) {
            return [
                'labels' => ['HTML', 'JSON', 'Other'],
                'data' => [75, 15, 10], // Default distribution
            ];
        }

        return [
            'labels' => ['HTML', 'JSON', 'Other'],
            'data' => [
                round(($stats->html / $total) * 100, 1),
                round(($stats->json / $total) * 100, 1),
                round(($stats->other / $total) * 100, 1),
            ],
        ];
    }

    private function getWebBlocUsageChart()
    {
        $usage = DB::table('web_bloc_instances')
            ->join('web_blocs', 'web_bloc_instances.webbloc_id', '=', 'web_blocs.id')
            ->selectRaw('web_blocs.type, COUNT(*) as count')
            ->groupBy('web_blocs.type')
            ->orderByDesc('count')
            ->take(10)
            ->get();

        return [
            'labels' => $usage->pluck('type')->toArray(),
            'data' => $usage->pluck('count')->toArray(),
        ];
    }

    private function getGeographicalChart()
    {
        // This would require IP geolocation data
        // For now, return sample data
        return [
            'labels' => ['United States', 'Canada', 'United Kingdom', 'Germany', 'Australia'],
            'data' => [45, 20, 15, 12, 8],
        ];
    }

    public function systemInfo()
    {
        $info = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_version' => DB::selectOne('SELECT VERSION() as version')->version ?? 'Unknown',
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'disk_space' => [
                'total' => disk_total_space(storage_path()),
                'free' => disk_free_space(storage_path()),
                'used_percentage' => (1 - (disk_free_space(storage_path()) / disk_total_space(storage_path()))) * 100,
            ],
        ];

        return response()->json($info);
    }

    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');

            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache: ' . $e->getMessage(),
            ], 500);
        }
    }
}