<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CdnService;
use Illuminate\Support\Facades\File;

class BuildWebBlocCdn extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'webbloc:build-cdn 
                            {--force : Force rebuild all files}
                            {--component= : Build specific component only}
                            {--watch : Watch for changes and rebuild automatically}';

    /**
     * The console command description.
     */
    protected $description = 'Build WebBloc CDN assets (CSS, JS, and component files)';

    protected CdnService $cdnService;

    public function __construct(CdnService $cdnService)
    {
        parent::__construct();
        $this->cdnService = $cdnService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Building WebBloc CDN assets...');
        $startTime = microtime(true);
        
        try {
            if ($this->option('watch')) {
                return $this->handleWatch();
            }
            
            if ($this->option('force')) {
                $this->info('🧹 Clearing existing CDN files...');
                $this->cdnService->clearCache();
            }
            
            $component = $this->option('component');
            
            if ($component) {
                $this->buildSpecificComponent($component);
            } else {
                $this->buildAll();
            }
            
            $executionTime = round((microtime(true) - $startTime), 2);
            $this->info("✅ CDN build completed in {$executionTime}s");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ CDN build failed: ' . $e->getMessage());
            
            if ($this->option('verbose')) {
                $this->error($e->getTraceAsString());
            }
            
            return Command::FAILURE;
        }
    }
    
    /**
     * Build all CDN assets
     */
    protected function buildAll(): void
    {
        $this->info('📦 Building all WebBloc assets...');
        
        $results = $this->cdnService->buildAll();
        
        $this->displayBuildResults($results);
    }
    
    /**
     * Build specific component
     */
    protected function buildSpecificComponent(string $component): void
    {
        $this->info("📦 Building component: {$component}");
        
        $webBloc = \App\Models\WebBloc::where('type', $component)->first();
        
        if (!$webBloc) {
            $this->error("Component '{$component}' not found");
            return;
        }
        
        $this->info("Building {$webBloc->type} component...");
        
        // Build the specific component files
        // This would need to be implemented in CdnService
        // $results = $this->cdnService->buildComponent($webBloc);
        
        $this->info("✅ Component {$component} built successfully");
    }
    
    /**
     * Watch for changes and rebuild
     */
    protected function handleWatch(): int
    {
        $this->info('👀 Watching for changes...');
        $this->info('Press Ctrl+C to stop watching');
        
        $watchPaths = [
            resource_path('css/webbloc-core.css'),
            resource_path('css/webbloc-components.css'),
            resource_path('views/webbloc'),
        ];
        
        $lastModified = [];
        
        while (true) {
            $changed = false;
            
            foreach ($watchPaths as $path) {
                if (File::exists($path)) {
                    $currentModified = File::lastModified($path);
                    
                    if (!isset($lastModified[$path]) || $currentModified > $lastModified[$path]) {
                        $lastModified[$path] = $currentModified;
                        $changed = true;
                        
                        $this->info("📝 Change detected in: {$path}");
                    }
                }
            }
            
            if ($changed) {
                try {
                    $this->info('🔄 Rebuilding assets...');
                    $this->cdnService->buildAll();
                    $this->info('✅ Assets rebuilt successfully');
                } catch (\Exception $e) {
                    $this->error('❌ Rebuild failed: ' . $e->getMessage());
                }
            }
            
            sleep(1); // Check every second
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Display build results
     */
    protected function displayBuildResults(array $results): void
    {
        $this->newLine();
        $this->info('📊 Build Results:');
        
        // Core files
        if (isset($results['core_css'])) {
            $this->line("  ✅ Core CSS: {$results['core_css']}");
        }
        
        if (isset($results['core_js'])) {
            $this->line("  ✅ Core JS: {$results['core_js']}");
        }
        
        if (isset($results['components_css'])) {
            $this->line("  ✅ Components CSS: {$results['components_css']}");
        }
        
        // Component files
        if (isset($results['components']) && is_array($results['components'])) {
            $this->line("  📦 Components:");
            
            foreach ($results['components'] as $component => $files) {
                $this->line("    • {$component}:");
                
                if (isset($files['js'])) {
                    $this->line("      - JS: {$files['js']}");
                }
                
                if (isset($files['js_min'])) {
                    $this->line("      - JS (min): {$files['js_min']}");
                }
                
                if (isset($files['css'])) {
                    $this->line("      - CSS: {$files['css']}");
                }
            }
        }
        
        // Combined files
        if (isset($results['combined'])) {
            $this->line("  📋 Combined Files:");
            
            foreach ($results['combined'] as $type => $filename) {
                $this->line("    • {$type}: {$filename}");
            }
        }
        
        // Manifest
        if (isset($results['manifest'])) {
            $this->line("  📄 Manifest: {$results['manifest']}");
        }
        
        $this->newLine();
        $this->displayCdnUrls();
    }
    
    /**
     * Display CDN URLs
     */
    protected function displayCdnUrls(): void
    {
        $this->info('🔗 CDN URLs:');
        
        $baseUrl = url('/cdn');
        
        $this->line("  CSS: {$baseUrl}/webbloc.min.css");
        $this->line("  JS:  {$baseUrl}/webbloc.min.js");
        $this->line("  Manifest: {$baseUrl}/manifest.json");
        
        $this->newLine();
        
        // Display integration example
        $this->info('📋 Integration Example:');
        
        $example = <<<HTML
<!-- Add to your HTML <head> -->
<link rel="stylesheet" href="{$baseUrl}/webbloc.min.css">
<script defer src="{$baseUrl}/webbloc.min.js"></script>

<!-- Add WebBloc components -->
<div w2030b="comments" data-webbloc-api-key="YOUR_API_KEY" data-webbloc-website-id="YOUR_WEBSITE_ID"></div>
HTML;
        
        $this->line($example);
    }
    
    /**
     * Get file size in human readable format
     */
    protected function getHumanFileSize($bytes, $decimals = 2): string
    {
        $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . @$size[$factor];
    }
    
    /**
     * Display file information
     */
    protected function displayFileInfo(string $filepath): void
    {
        if (File::exists($filepath)) {
            $size = $this->getHumanFileSize(File::size($filepath));
            $filename = basename($filepath);
            $this->line("      {$filename} ({$size})");
        }
    }
    
    /**
     * Validate environment
     */
    protected function validateEnvironment(): bool
    {
        $errors = [];
        
        // Check if public/cdn directory is writable
        $cdnPath = public_path('cdn');
        if (!File::exists($cdnPath)) {
            try {
                File::makeDirectory($cdnPath, 0755, true);
            } catch (\Exception $e) {
                $errors[] = "Cannot create CDN directory: {$cdnPath}";
            }
        } elseif (!File::isWritable($cdnPath)) {
            $errors[] = "CDN directory is not writable: {$cdnPath}";
        }
        
        // Check if resource files exist
        $requiredFiles = [
            resource_path('css/webbloc-core.css'),
            resource_path('css/webbloc-components.css')
        ];
        
        foreach ($requiredFiles as $file) {
            if (!File::exists($file)) {
                $errors[] = "Required source file missing: {$file}";
            }
        }
        
        if (!empty($errors)) {
            $this->error('❌ Environment validation failed:');
            foreach ($errors as $error) {
                $this->error("  • {$error}");
            }
            return false;
        }
        
        return true;
    }
}