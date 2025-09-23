<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Routing\Controller as BaseController;
use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ApiKeyController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = ApiKey::with(['website', 'user']);

        // Filter by user's websites if not admin
        if (!Auth::user()->hasRole('admin')) {
            $websiteIds = Auth::user()->websites()->pluck('id');
            $query->whereIn('website_id', $websiteIds);
        }

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('key', 'like', "%{$search}%")
                  ->orWhereHas('website', function ($wq) use ($search) {
                      $wq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('website') && Auth::user()->hasRole('admin')) {
            $query->where('website_id', $request->website);
        }

        $apiKeys = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get websites for filter
        $websites = Auth::user()->hasRole('admin')
            ? Website::select('id', 'name')->orderBy('name')->get()
            : Auth::user()->websites()->select('id', 'name')->orderBy('name')->get();

        return view('dashboard.api-keys.index', compact('apiKeys', 'websites'));
    }

    public function create(Request $request)
    {
        $websiteId = $request->get('website_id');
        
        // Get available websites
        $websites = Auth::user()->hasRole('admin')
            ? Website::where('status', 'active')->select('id', 'name')->orderBy('name')->get()
            : Auth::user()->websites()->where('status', 'active')->select('id', 'name')->orderBy('name')->get();

        if ($websites->isEmpty()) {
            return redirect()
                ->route('dashboard.websites.create')
                ->with('info', 'You need to create a website first before generating API keys.');
        }

        $selectedWebsite = $websiteId ? $websites->find($websiteId) : null;

        return view('dashboard.api-keys.create', compact('websites', 'selectedWebsite'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'website_id' => 'required|exists:websites,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:public,private',
            'environment' => 'required|in:development,staging,production',
            'allowed_domains' => 'nullable|string',
            'allowed_ips' => 'nullable|string',
            'rate_limit_per_minute' => 'nullable|integer|min:1|max:10000',
            'rate_limit_per_hour' => 'nullable|integer|min:1|max:100000',
            'rate_limit_per_day' => 'nullable|integer|min:1|max:1000000',
            'expires_at' => 'nullable|date|after:today',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'string|in:read,write,delete',
            'webbloc_types' => 'nullable|array',
            'webbloc_types.*' => 'string',
        ]);

        $website = Website::findOrFail($request->website_id);
        
        // Check authorization
        if (!Auth::user()->hasRole('admin') && $website->owner_id !== Auth::id()) {
            abort(403, 'You can only create API keys for your own websites.');
        }

        // Generate unique keys
        do {
            $key = 'wb_' . Str::random(32);
        } while (ApiKey::where('key', $key)->exists());

        do {
            $secretKey = Str::random(64);
        } while (ApiKey::where('secret_key', Hash::make($secretKey))->exists());

        // Process domains and IPs
        $allowedDomains = $request->allowed_domains 
            ? array_filter(array_map('trim', explode("\n", $request->allowed_domains)))
            : [];
            
        $allowedIps = $request->allowed_ips
            ? array_filter(array_map('trim', explode("\n", $request->allowed_ips)))
            : [];

        // Create API key
        $apiKey = ApiKey::create([
            'website_id' => $website->id,
            'user_id' => Auth::id(),
            'name' => $request->name,
            'key' => $key,
            'secret_key' => Hash::make($secretKey),
            'type' => $request->type,
            'environment' => $request->environment,
            'permissions' => $request->permissions,
            'webbloc_types' => $request->webbloc_types ?? [],
            'allowed_domains' => $allowedDomains,
            'allowed_ips' => $allowedIps,
            'rate_limit_per_minute' => $request->rate_limit_per_minute ?? 60,
            'rate_limit_per_hour' => $request->rate_limit_per_hour ?? 1000,
            'rate_limit_per_day' => $request->rate_limit_per_day ?? 10000,
            'expires_at' => $request->expires_at,
            'status' => 'active',
        ]);

        // Store the plain secret key in session to show once
        session(['new_api_secret' => $secretKey]);

        return redirect()
            ->route('dashboard.api-keys.show', $apiKey)
            ->with('success', 'API key created successfully. Make sure to copy the secret key as it will not be shown again.');
    }

    public function show(ApiKey $apiKey)
    {
        // Check authorization
        if (!Auth::user()->hasRole('admin') && $apiKey->website->owner_id !== Auth::id()) {
            abort(403, 'You can only view API keys for your own websites.');
        }

        $apiKey->load(['website', 'user']);

        // Get usage statistics for the last 30 days
        $usageStats = $this->getUsageStatistics($apiKey);

        // Get recent activity
        $recentActivity = $this->getRecentActivity($apiKey);

        // Check for secret key in session (only shown once after creation)
        $secretKey = session()->pull('new_api_secret');

        return view('dashboard.api-keys.show', compact('apiKey', 'usageStats', 'recentActivity', 'secretKey'));
    }

    public function edit(ApiKey $apiKey)
    {
        // Check authorization
        if (!Auth::user()->hasRole('admin') && $apiKey->website->owner_id !== Auth::id()) {
            abort(403, 'You can only edit API keys for your own websites.');
        }

        return view('dashboard.api-keys.edit', compact('apiKey'));
    }

    public function update(Request $request, ApiKey $apiKey)
    {
        // Check authorization
        if (!Auth::user()->hasRole('admin') && $apiKey->website->owner_id !== Auth::id()) {
            abort(403, 'You can only update API keys for your own websites.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive,suspended',
            'allowed_domains' => 'nullable|string',
            'allowed_ips' => 'nullable|string',
            'rate_limit_per_minute' => 'nullable|integer|min:1|max:10000',
            'rate_limit_per_hour' => 'nullable|integer|min:1|max:100000',
            'rate_limit_per_day' => 'nullable|integer|min:1|max:1000000',
            'expires_at' => 'nullable|date',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'string|in:read,write,delete',
            'webbloc_types' => 'nullable|array',
            'webbloc_types.*' => 'string',
        ]);

        // Process domains and IPs
        $allowedDomains = $request->allowed_domains 
            ? array_filter(array_map('trim', explode("\n", $request->allowed_domains)))
            : [];
            
        $allowedIps = $request->allowed_ips
            ? array_filter(array_map('trim', explode("\n", $request->allowed_ips)))
            : [];

        $apiKey->update([
            'name' => $request->name,
            'status' => $request->status,
            'permissions' => $request->permissions,
            'webbloc_types' => $request->webbloc_types ?? [],
            'allowed_domains' => $allowedDomains,
            'allowed_ips' => $allowedIps,
            'rate_limit_per_minute' => $request->rate_limit_per_minute ?? 60,
            'rate_limit_per_hour' => $request->rate_limit_per_hour ?? 1000,
            'rate_limit_per_day' => $request->rate_limit_per_day ?? 10000,
            'expires_at' => $request->expires_at,
        ]);

        return redirect()
            ->route('dashboard.api-keys.show', $apiKey)
            ->with('success', 'API key updated successfully.');
    }

    public function destroy(ApiKey $apiKey)
    {
        // Check authorization
        if (!Auth::user()->hasRole('admin') && $apiKey->website->owner_id !== Auth::id()) {
            abort(403, 'You can only delete API keys for your own websites.');
        }

        $apiKey->delete();

        return redirect()
            ->route('dashboard.api-keys.index')
            ->with('success', 'API key deleted successfully.');
    }

    public function regenerate(ApiKey $apiKey)
    {
        // Check authorization
        if (!Auth::user()->hasRole('admin') && $apiKey->website->owner_id !== Auth::id()) {
            abort(403, 'You can only regenerate API keys for your own websites.');
        }

        // Generate new secret key
        do {
            $secretKey = Str::random(64);
        } while (ApiKey::where('secret_key', Hash::make($secretKey))->exists());

        $apiKey->update([
            'secret_key' => Hash::make($secretKey),
            'last_used_at' => null, // Reset usage
        ]);

        // Store the plain secret key in session to show once
        session(['new_api_secret' => $secretKey]);

        return redirect()
            ->route('dashboard.api-keys.show', $apiKey)
            ->with('success', 'API key regenerated successfully. Make sure to update your applications with the new secret key.');
    }

    public function suspend(ApiKey $apiKey)
    {
        // Check authorization
        if (!Auth::user()->hasRole('admin') && $apiKey->website->owner_id !== Auth::id()) {
            abort(403, 'You can only suspend API keys for your own websites.');
        }

        $apiKey->update(['status' => 'suspended']);

        return back()->with('success', 'API key suspended successfully.');
    }

    public function activate(ApiKey $apiKey)
    {
        // Check authorization
        if (!Auth::user()->hasRole('admin') && $apiKey->website->owner_id !== Auth::id()) {
            abort(403, 'You can only activate API keys for your own websites.');
        }

        $apiKey->update(['status' => 'active']);

        return back()->with('success', 'API key activated successfully.');
    }

    public function usage(ApiKey $apiKey)
    {
        // Check authorization
        if (!Auth::user()->hasRole('admin') && $apiKey->website->owner_id !== Auth::id()) {
            abort(403, 'You can only view usage for your own API keys.');
        }

        $range = request('range', '7days');
        $days = match($range) {
            '24hours' => 1,
            '7days' => 7,
            '30days' => 30,
            '90days' => 90,
            default => 7,
        };

        $usageData = $this->getUsageStatistics($apiKey, $days);

        return response()->json($usageData);
    }

    private function getUsageStatistics(ApiKey $apiKey, $days = 30)
    {
        // This would integrate with your actual usage tracking system
        // For now, return sample data based on the API key's usage fields
        
        $currentUsage = [
            'requests_today' => $apiKey->requests_today ?? 0,
            'requests_this_month' => $apiKey->requests_this_month ?? 0,
            'total_requests' => $apiKey->total_requests ?? 0,
            'last_used' => $apiKey->last_used_at,
        ];

        // Calculate limits and percentages
        $limits = [
            'daily_limit' => $apiKey->rate_limit_per_day,
            'hourly_limit' => $apiKey->rate_limit_per_hour,
            'minute_limit' => $apiKey->rate_limit_per_minute,
        ];

        $usage_percentages = [
            'daily' => $limits['daily_limit'] > 0 
                ? ($currentUsage['requests_today'] / $limits['daily_limit']) * 100 
                : 0,
        ];

        // Generate sample chart data for the specified period
        $chartData = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartData[] = [
                'date' => $date->format('M j'),
                'requests' => rand(10, 100), // Replace with actual data
                'errors' => rand(0, 5),
            ];
        }

        return [
            'current' => $currentUsage,
            'limits' => $limits,
            'percentages' => $usage_percentages,
            'chart_data' => $chartData,
        ];
    }

    private function getRecentActivity(ApiKey $apiKey, $limit = 10)
    {
        // This would query your actual activity log
        // For now, return sample data
        return collect([
            [
                'timestamp' => now()->subMinutes(5),
                'action' => 'GET /api/webblocs/comments',
                'status' => 200,
                'response_time' => 45,
                'ip_address' => '192.168.1.100',
            ],
            [
                'timestamp' => now()->subMinutes(15),
                'action' => 'POST /api/webblocs/reviews',
                'status' => 201,
                'response_time' => 120,
                'ip_address' => '192.168.1.100',
            ],
            // Add more sample activities...
        ])->take($limit);
    }
}
