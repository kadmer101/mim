<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\WebBlocRequest;
use App\Models\WebBloc;
use App\Models\Website;
use App\Models\WebBlocInstance;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller as BaseController;

class WebBlocController extends BaseController
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = WebBloc::with(['instances' => function ($q) {
            $q->with('website');
        }]);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->whereJsonContains('metadata->category', $request->category);
        }

        $webBlocs = $query->orderBy('created_at', 'desc')->paginate(12);

        // Get filter options
        $types = WebBloc::distinct()->pluck('type')->filter()->sort();
        $categories = WebBloc::select('metadata')
            ->whereNotNull('metadata')
            ->get()
            ->pluck('metadata')
            ->map(function ($metadata) {
                return json_decode($metadata, true)['category'] ?? null;
            })
            ->filter()
            ->unique()
            ->sort();

        return view('dashboard.webblocs.index', compact('webBlocs', 'types', 'categories'));
    }

    public function create()
    {
        $this->authorize('create', WebBloc::class);
        
        return view('dashboard.webblocs.create');
    }

    public function store(WebBlocRequest $request)
    {
        $this->authorize('create', WebBloc::class);

        $data = $request->validated();
        
        // Set defaults for CRUD operations
        $data['crud'] = array_merge([
            'create' => false,
            'read' => true,
            'update' => false,
            'delete' => false,
        ], $data['crud'] ?? []);

        // Set metadata with defaults
        $metadata = $data['metadata'] ?? [];
        $metadata = array_merge([
            'created_by' => Auth::id(),
            'created_by_name' => Auth::user()->name,
        ], $metadata);
        $data['metadata'] = $metadata;

        $webBloc = WebBloc::create($data);

        // Generate component files if requested
        if ($request->boolean('generate_files')) {
            try {
                $this->generateWebBlocFiles($webBloc);
                $message = 'WebBloc created successfully and component files generated.';
            } catch (\Exception $e) {
                $message = 'WebBloc created successfully, but failed to generate component files: ' . $e->getMessage();
            }
        } else {
            $message = 'WebBloc created successfully.';
        }

        return redirect()
            ->route('dashboard.webblocs.show', $webBloc)
            ->with('success', $message);
    }

    public function show(WebBloc $webBloc)
    {
        $webBloc->load(['instances.website']);

        // Get installation statistics
        $installStats = [
            'total_installations' => $webBloc->instances()->count(),
            'active_installations' => $webBloc->instances()->where('status', 'active')->count(),
            'websites_using' => $webBloc->instances()->distinct('website_id')->count(),
        ];

        // Get usage statistics over time
        $usageStats = $this->getWebBlocUsageStats($webBloc);

        // Get recent installations
        $recentInstallations = $webBloc->instances()
            ->with('website')
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.webblocs.show', compact('webBloc', 'installStats', 'usageStats', 'recentInstallations'));
    }

    public function edit(WebBloc $webBloc)
    {
        $this->authorize('update', $webBloc);
        
        return view('dashboard.webblocs.edit', compact('webBloc'));
    }

    public function update(WebBlocRequest $request, WebBloc $webBloc)
    {
        $this->authorize('update', $webBloc);

        $data = $request->validated();
        
        // Preserve creation metadata
        if (isset($data['metadata'])) {
            $metadata = $webBloc->metadata ?? [];
            $data['metadata'] = array_merge($metadata, $data['metadata']);
            $data['metadata']['updated_by'] = Auth::id();
            $data['metadata']['updated_by_name'] = Auth::user()->name;
            $data['metadata']['updated_at'] = now()->toISOString();
        }

        // Increment version if significant changes
        if ($this->hasSignificantChanges($webBloc, $data)) {
            $version = explode('.', $webBloc->version ?? '1.0.0');
            $version[1] = (int)$version[1] + 1;
            $data['version'] = implode('.', $version);
        }

        $webBloc->update($data);

        // Regenerate files if requested
        if ($request->boolean('regenerate_files')) {
            try {
                $this->generateWebBlocFiles($webBloc);
                $message = 'WebBloc updated successfully and component files regenerated.';
            } catch (\Exception $e) {
                $message = 'WebBloc updated successfully, but failed to regenerate component files: ' . $e->getMessage();
            }
        } else {
            $message = 'WebBloc updated successfully.';
        }

        return redirect()
            ->route('dashboard.webblocs.show', $webBloc)
            ->with('success', $message);
    }

    public function destroy(WebBloc $webBloc)
    {
        $this->authorize('delete', $webBloc);

        // Check if WebBloc is in use
        $installCount = $webBloc->instances()->count();
        
        if ($installCount > 0) {
            return back()->withErrors([
                'delete' => "Cannot delete WebBloc '{$webBloc->name}' as it is installed on {$installCount} website(s). Please uninstall it from all websites first."
            ]);
        }

        // Remove component files
        try {
            $this->removeWebBlocFiles($webBloc);
        } catch (\Exception $e) {
            // Log error but continue with deletion
        }

        $webBloc->delete();

        return redirect()
            ->route('dashboard.webblocs.index')
            ->with('success', 'WebBloc deleted successfully.');
    }

    public function install(Request $request, WebBloc $webBloc)
    {
        $request->validate([
            'website_ids' => 'required|array|min:1',
            'website_ids.*' => 'exists:websites,id',
            'configuration' => 'nullable|array',
        ]);

        $websites = Website::whereIn('id', $request->website_ids);

        // Filter websites user has access to
        if (!Auth::user()->hasRole('admin')) {
            $websites = $websites->where('owner_id', Auth::id());
        }

        $websites = $websites->get();
        $installed = 0;
        $skipped = 0;
        $errors = [];

        foreach ($websites as $website) {
            try {
                // Check if already installed
                $existing = WebBlocInstance::where('website_id', $website->id)
                    ->where('webbloc_id', $webBloc->id)
                    ->first();

                if ($existing) {
                    $skipped++;
                    continue;
                }

                // Install WebBloc
                WebBlocInstance::create([
                    'website_id' => $website->id,
                    'webbloc_id' => $webBloc->id,
                    'configuration' => $request->configuration ?? [],
                    'status' => 'active',
                    'installed_by' => Auth::id(),
                ]);

                // Run installation command for the specific website
                try {
                    Artisan::call('webbloc:install', [
                        'type' => $webBloc->type,
                        '--website-id' => $website->id,
                    ]);
                } catch (\Exception $e) {
                    // Log but don't fail the installation
                }

                $installed++;
            } catch (\Exception $e) {
                $errors[] = "Failed to install on {$website->name}: " . $e->getMessage();
            }
        }

        $message = "WebBloc installed on {$installed} website(s).";
        if ($skipped > 0) {
            $message .= " {$skipped} website(s) already had this WebBloc installed.";
        }

        if (!empty($errors)) {
            return back()->withErrors($errors)->with('warning', $message);
        }

        return back()->with('success', $message);
    }

    public function uninstall(Request $request, WebBloc $webBloc)
    {
        $request->validate([
            'website_ids' => 'required|array|min:1',
            'website_ids.*' => 'exists:websites,id',
        ]);

        $websites = Website::whereIn('id', $request->website_ids);

        // Filter websites user has access to
        if (!Auth::user()->hasRole('admin')) {
            $websites = $websites->where('owner_id', Auth::id());
        }

        $websites = $websites->get();
        $uninstalled = 0;
        $errors = [];

        foreach ($websites as $website) {
            try {
                $instance = WebBlocInstance::where('website_id', $website->id)
                    ->where('webbloc_id', $webBloc->id)
                    ->first();

                if ($instance) {
                    // Run uninstallation command
                    try {
                        Artisan::call('webbloc:uninstall', [
                            'type' => $webBloc->type,
                            '--website-id' => $website->id,
                        ]);
                    } catch (\Exception $e) {
                        // Log but continue
                    }

                    $instance->delete();
                    $uninstalled++;
                }
            } catch (\Exception $e) {
                $errors[] = "Failed to uninstall from {$website->name}: " . $e->getMessage();
            }
        }

        $message = "WebBloc uninstalled from {$uninstalled} website(s).";

        if (!empty($errors)) {
            return back()->withErrors($errors)->with('warning', $message);
        }

        return back()->with('success', $message);
    }

    public function export(WebBloc $webBloc)
    {
        $exportData = [
            'webbloc' => $webBloc->toArray(),
            'export_version' => '1.0',
            'exported_at' => now()->toISOString(),
            'exported_by' => Auth::user()->name,
        ];

        $filename = "webbloc-{$webBloc->type}-{$webBloc->version}.json";

        return response()->json($exportData)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->header('Content-Type', 'application/json');
    }

    public function import(Request $request)
    {
        $this->authorize('create', WebBloc::class);

        $request->validate([
            'import_file' => 'required|file|mimes:json',
            'overwrite_existing' => 'boolean',
        ]);

        try {
            $content = file_get_contents($request->file('import_file')->getRealPath());
            $data = json_decode($content, true);

            if (!$data || !isset($data['webbloc'])) {
                return back()->withErrors(['import_file' => 'Invalid WebBloc export file.']);
            }

            $webBlocData = $data['webbloc'];
            
            // Check if WebBloc already exists
            $existing = WebBloc::where('type', $webBlocData['type'])->first();
            
            if ($existing && !$request->boolean('overwrite_existing')) {
                return back()->withErrors([
                    'import_file' => "WebBloc type '{$webBlocData['type']}' already exists. Check 'Overwrite existing' to replace it."
                ]);
            }

            // Remove ID and timestamps for import
            unset($webBlocData['id'], $webBlocData['created_at'], $webBlocData['updated_at']);
            
            // Add import metadata
            $metadata = $webBlocData['metadata'] ?? [];
            $metadata['imported_at'] = now()->toISOString();
            $metadata['imported_by'] = Auth::user()->name;
            $webBlocData['metadata'] = $metadata;

            if ($existing && $request->boolean('overwrite_existing')) {
                $existing->update($webBlocData);
                $webBloc = $existing;
                $message = 'WebBloc updated from import successfully.';
            } else {
                $webBloc = WebBloc::create($webBlocData);
                $message = 'WebBloc imported successfully.';
            }

            return redirect()
                ->route('dashboard.webblocs.show', $webBloc)
                ->with('success', $message);

        } catch (\Exception $e) {
            return back()->withErrors(['import_file' => 'Failed to import WebBloc: ' . $e->getMessage()]);
        }
    }

    public function duplicate(WebBloc $webBloc)
    {
        $this->authorize('create', WebBloc::class);

        $data = $webBloc->toArray();
        
        // Remove unique fields
        unset($data['id'], $data['created_at'], $data['updated_at']);
        
        // Modify type to make it unique
        $baseType = $data['type'];
        $counter = 1;
        do {
            $newType = $baseType . '_copy' . ($counter > 1 ? $counter : '');
            $counter++;
        } while (WebBloc::where('type', $newType)->exists());
        
        $data['type'] = $newType;
        $data['name'] = $data['name'] . ' (Copy)';
        
        // Update metadata
        $metadata = $data['metadata'] ?? [];
        $metadata['duplicated_from'] = $webBloc->id;
        $metadata['duplicated_at'] = now()->toISOString();
        $metadata['duplicated_by'] = Auth::user()->name;
        $data['metadata'] = $metadata;

        $newWebBloc = WebBloc::create($data);

        return redirect()
            ->route('dashboard.webblocs.edit', $newWebBloc)
            ->with('success', 'WebBloc duplicated successfully. You can now customize it.');
    }

    private function generateWebBlocFiles(WebBloc $webBloc)
    {
        // This would integrate with your WebBloc file generation system
        // For now, we'll simulate the process
        
        $directories = [
            app_path("WebBlocs/{$webBloc->type}"),
            resource_path("views/webbloc/{$webBloc->type}"),
            resource_path("js/webbloc"),
            resource_path("css/webbloc"),
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        // Generate placeholder files (in production, these would be proper templates)
        $files = [
            app_path("WebBlocs/{$webBloc->type}/{$webBloc->type}WebBloc.php") => $this->getWebBlocClassTemplate($webBloc),
            resource_path("views/webbloc/{$webBloc->type}/default.blade.php") => $this->getBladeTemplate($webBloc),
            resource_path("js/webbloc/{$webBloc->type}.js") => $this->getJavaScriptTemplate($webBloc),
            resource_path("css/webbloc/{$webBloc->type}.css") => $this->getCssTemplate($webBloc),
        ];

        foreach ($files as $path => $content) {
            file_put_contents($path, $content);
        }
    }

    private function removeWebBlocFiles(WebBloc $webBloc)
    {
        $directories = [
            app_path("WebBlocs/{$webBloc->type}"),
            resource_path("views/webbloc/{$webBloc->type}"),
        ];

        foreach ($directories as $dir) {
            if (is_dir($dir)) {
                $this->deleteDirectory($dir);
            }
        }

        // Remove JS and CSS files
        $files = [
            resource_path("js/webbloc/{$webBloc->type}.js"),
            resource_path("css/webbloc/{$webBloc->type}.css"),
        ];

        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) return;
        
        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        
        rmdir($dir);
    }

    private function hasSignificantChanges(WebBloc $webBloc, array $data)
    {
        $significantFields = ['attributes', 'crud', 'component_code'];
        
        foreach ($significantFields as $field) {
            if (isset($data[$field]) && $data[$field] !== $webBloc->$field) {
                return true;
            }
        }
        
        return false;
    }

    private function getWebBlocUsageStats(WebBloc $webBloc)
    {
        // This would integrate with your analytics system
        // For now, return sample data
        return [
            'installations_over_time' => collect(range(0, 6))->map(function ($i) {
                return [
                    'date' => now()->subDays($i)->format('M j'),
                    'installations' => rand(0, 5),
                ];
            })->reverse()->values(),
            'usage_by_website' => $webBloc->instances()
                ->with('website')
                ->get()
                ->map(function ($instance) {
                    return [
                        'website' => $instance->website->name,
                        'usage_count' => rand(10, 100), // Replace with actual usage data
                    ];
                }),
        ];
    }

    private function getWebBlocClassTemplate(WebBloc $webBloc)
    {
        $className = ucfirst(Str::camel($webBloc->type)) . 'WebBloc';
        
        return "<?php\n\nnamespace App\\WebBlocs\\{$webBloc->type};\n\nclass {$className}\n{\n    // Generated WebBloc class for {$webBloc->name}\n}\n";
    }

    private function getBladeTemplate(WebBloc $webBloc)
    {
        return "{{-- Generated Blade template for {$webBloc->name} --}}\n<div class=\"webbloc-{$webBloc->type}\">\n    <h3>{$webBloc->name}</h3>\n    <p>{$webBloc->description}</p>\n</div>\n";
    }

    private function getJavaScriptTemplate(WebBloc $webBloc)
    {
        return "// Generated JavaScript for {$webBloc->name}\ndocument.addEventListener('alpine:init', () => {\n    Alpine.data('{$webBloc->type}WebBloc', () => ({\n        // Component data and methods\n    }));\n});\n";
    }

    private function getCssTemplate(WebBloc $webBloc)
    {
        return "/* Generated CSS for {$webBloc->name} */\n.webbloc-{$webBloc->type} {\n    /* Component styles */\n}\n";
    }
}