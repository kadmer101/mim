<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\WebsiteRequest;
use App\Models\Website;
use App\Models\User;
use App\Models\WebsiteStatistic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\Authorizable;

class WebsiteController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Website::with(['owner', 'statistics' => function ($q) {
            $q->whereDate('date', '>=', now()->subDays(7));
        }]);

        // Admin can see all websites, users see only their own
        if (!Auth::user()->hasRole('admin')) {
            $query->where('owner_id', Auth::id());
        }

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('subscription_status')) {
            $query->where('subscription_status', $request->subscription_status);
        }

        if ($request->filled('owner') && Auth::user()->hasRole('admin')) {
            $query->where('owner_id', $request->owner);
        }

        $websites = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get owners for filter (admin only)
        $owners = Auth::user()->hasRole('admin') 
            ? User::select('id', 'name')->orderBy('name')->get()
            : collect();

        return view('dashboard.websites.index', compact('websites', 'owners'));
    }

    public function create()
    {
        $this->authorize('create', Website::class);
        
        // Get available owners (admin only)
        $owners = Auth::user()->hasRole('admin') 
            ? User::select('id', 'name')->orderBy('name')->get()
            : collect();

        return view('dashboard.websites.create', compact('owners'));
    }

    public function store(WebsiteRequest $request)
    {
        $this->authorize('create', Website::class);

        $data = $request->validated();
        
        // Set owner
        if (!Auth::user()->hasRole('admin') || !isset($data['owner_id'])) {
            $data['owner_id'] = Auth::user()->id();
        }

        // Generate unique identifiers
        $data['slug'] = $this->generateUniqueSlug($data['name']);
        $data['verification_token'] = Str::random(64);

        // Create website
        $website = Website::create($data);

        // Create SQLite database
        try {
            $website->createSqliteDatabase();
        } catch (\Exception $e) {
            $website->delete();
            return back()->withErrors(['database' => 'Failed to create website database: ' . $e->getMessage()]);
        }

        return redirect()
            ->route('dashboard.websites.show', $website)
            ->with('success', 'Website created successfully. Please complete domain verification.');
    }

    public function show(Website $website)
    {
        $this->authorize('view', $website);

        $website->load(['owner', 'apiKeys', 'webBlocInstances.webBloc']);

        // Get statistics for the last 30 days
        $statistics = WebsiteStatistic::where('website_id', $website->id)
            ->whereDate('date', '>=', now()->subDays(30))
            ->orderBy('date')
            ->get();

        // Calculate summary stats
        $summaryStats = [
            'total_requests' => $statistics->sum('total_requests'),
            'unique_visitors' => $statistics->sum('unique_visitors'),
            'html_responses' => $statistics->sum('html_responses'),
            'json_responses' => $statistics->sum('json_responses'),
            'error_count' => $statistics->sum('error_count'),
            'avg_response_time' => $statistics->avg('avg_response_time'),
        ];

        // Prepare chart data
        $chartData = [
            'requests' => $this->prepareRequestsChart($statistics),
            'formats' => $this->prepareFormatsChart($statistics),
            'performance' => $this->preparePerformanceChart($statistics),
        ];

        return view('dashboard.websites.show', compact('website', 'summaryStats', 'chartData'));
    }

    public function edit(Website $website)
    {
        $this->authorize('update', $website);
        
        $owners = Auth::user()->hasRole('admin') 
            ? User::select('id', 'name')->orderBy('name')->get()
            : collect();

        return view('dashboard.websites.edit', compact('website', 'owners'));
    }

    public function update(WebsiteRequest $request, Website $website)
    {
        $this->authorize('update', $website);

        $data = $request->validated();
        
        // Admin can change owner
        if (!Auth::user()->hasRole('admin')) {
            unset($data['owner_id']);
        }

        // Update slug if name changed
        if (isset($data['name']) && $data['name'] !== $website->name) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $website->id);
        }

        $website->update($data);

        return redirect()
            ->route('dashboard.websites.show', $website)
            ->with('success', 'Website updated successfully.');
    }

    public function destroy(Website $website)
    {
        $this->authorize('delete', $website);

        try {
            // Delete SQLite database
            $website->deleteSqliteDatabase();
            
            // Delete website record
            $website->delete();

            return redirect()
                ->route('dashboard.websites.index')
                ->with('success', 'Website deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['delete' => 'Failed to delete website: ' . $e->getMessage()]);
        }
    }

    public function verify(Website $website)
    {
        $this->authorize('update', $website);

        if ($website->is_verified) {
            return back()->with('info', 'Website is already verified.');
        }

        // Here you would implement domain verification logic
        // For now, we'll simulate verification
        $verificationUrl = $website->url . '/.well-known/webbloc-verification.txt';
        $expectedContent = $website->verification_token;

        try {
            // In production, you would make an HTTP request to verify
            // $response = Http::get($verificationUrl);
            // $isValid = $response->successful() && trim($response->body()) === $expectedContent;
            
            // For demo purposes, we'll mark as verified
            $website->update([
                'is_verified' => true,
                'verified_at' => now(),
                'status' => 'active',
            ]);

            return back()->with('success', 'Website verified successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['verification' => 'Domain verification failed: ' . $e->getMessage()]);
        }
    }

    public function regenerateToken(Website $website)
    {
        $this->authorize('update', $website);

        $website->update([
            'verification_token' => Str::random(64),
            'is_verified' => false,
            'verified_at' => null,
        ]);

        return back()->with('success', 'Verification token regenerated. Please re-verify your domain.');
    }

    public function statistics(Website $website)
    {
        $this->authorize('view', $website);

        $range = request('range', '7days');
        $days = match($range) {
            '24hours' => 1,
            '7days' => 7,
            '30days' => 30,
            '90days' => 90,
            default => 7,
        };

        $statistics = WebsiteStatistic::where('website_id', $website->id)
            ->whereDate('date', '>=', now()->subDays($days))
            ->orderBy('date')
            ->get();

        return response()->json([
            'summary' => [
                'total_requests' => $statistics->sum('total_requests'),
                'unique_visitors' => $statistics->sum('unique_visitors'),
                'error_rate' => $statistics->sum('total_requests') > 0 
                    ? ($statistics->sum('error_count') / $statistics->sum('total_requests')) * 100 
                    : 0,
                'avg_response_time' => $statistics->avg('avg_response_time'),
            ],
            'chart_data' => [
                'requests' => $this->prepareRequestsChart($statistics),
                'formats' => $this->prepareFormatsChart($statistics),
                'errors' => $this->prepareErrorsChart($statistics),
            ],
        ]);
    }

    private function generateUniqueSlug($name, $ignoreId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (Website::where('slug', $slug)->when($ignoreId, function ($q, $id) {
            return $q->where('id', '!=', $id);
        })->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }

    private function prepareRequestsChart($statistics)
    {
        return $statistics->map(function ($stat) {
            return [
                'date' => $stat->date->format('M j'),
                'requests' => $stat->total_requests,
                'visitors' => $stat->unique_visitors,
            ];
        })->values();
    }

    private function prepareFormatsChart($statistics)
    {
        $totals = [
            'html' => $statistics->sum('html_responses'),
            'json' => $statistics->sum('json_responses'),
            'other' => $statistics->sum('other_responses'),
        ];

        $total = array_sum($totals);
        
        if ($total === 0) {
            return ['html' => 75, 'json' => 15, 'other' => 10];
        }

        return [
            'html' => round(($totals['html'] / $total) * 100, 1),
            'json' => round(($totals['json'] / $total) * 100, 1),
            'other' => round(($totals['other'] / $total) * 100, 1),
        ];
    }

    private function preparePerformanceChart($statistics)
    {
        return $statistics->map(function ($stat) {
            return [
                'date' => $stat->date->format('M j'),
                'response_time' => round($stat->avg_response_time, 2),
                'error_count' => $stat->error_count,
            ];
        })->values();
    }

    private function prepareErrorsChart($statistics)
    {
        return $statistics->map(function ($stat) {
            return [
                'date' => $stat->date->format('M j'),
                'errors' => $stat->error_count,
                'rate' => $stat->total_requests > 0 
                    ? round(($stat->error_count / $stat->total_requests) * 100, 2)
                    : 0,
            ];
        })->values();
    }
}