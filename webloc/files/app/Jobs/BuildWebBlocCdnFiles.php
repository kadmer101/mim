<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Website;
use App\Models\WebBloc;
use App\Services\CdnService;

class BuildWebBlocCdnFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $websites;
    protected $webBlocTypes;
    protected $force;

    public function __construct($websites = null, $webBlocTypes = null, $force = false)
    {
        $this->websites = $websites;
        $this->webBlocTypes = $webBlocTypes;
        $this->force = $force;
    }

    public function handle(CdnService $cdnService)
    {
        try {
            Log::info('Starting CDN file build process', [
                'websites' => $this->websites,
                'webbloc_types' => $this->webBlocTypes,
                'force' => $this->force
            ]);

            // Build core CDN files
            $this->buildCoreFiles($cdnService);

            // Build website-specific files if specified
            if ($this->websites) {
                $this->buildWebsiteFiles($cdnService);
            }

            // Build WebBloc-specific files if specified
            if ($this->webBlocTypes) {
                $this->buildWebBlocFiles($cdnService);
            }

            // Generate manifest and update cache
            $this->updateManifest($cdnService);

            Log::info('CDN file build process completed successfully');

        } catch (\Exception $e) {
            Log::error('CDN file build failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function buildCoreFiles(CdnService $cdnService)
    {
        $coreFiles = [
            'webbloc-core.js' => $this->buildCoreJs(),
            'webbloc-core.css' => $this->buildCoreCss(),
            'webbloc.min.js' => $this->buildMinifiedJs(),
            'webbloc.min.css' => $this->buildMinifiedCss(),
        ];

        foreach ($coreFiles as $filename => $content) {
            $cdnService->putFile($filename, $content);
        }
    }

    protected function buildWebsiteFiles(CdnService $cdnService)
    {
        $websites = is_array($this->websites) ? 
            Website::whereIn('id', $this->websites)->get() : 
            Website::where('status', 'active')->get();

        foreach ($websites as $website) {
            $customCss = $this->generateWebsiteCustomCss($website);
            $customJs = $this->generateWebsiteCustomJs($website);

            if ($customCss) {
                $cdnService->putFile("websites/{$website->id}/custom.css", $customCss);
            }

            if ($customJs) {
                $cdnService->putFile("websites/{$website->id}/custom.js", $customJs);
            }
        }
    }

    protected function buildWebBlocFiles(CdnService $cdnService)
    {
        $webBlocs = is_array($this->webBlocTypes) ? 
            WebBloc::whereIn('type', $this->webBlocTypes)->get() : 
            WebBloc::where('status', 'active')->get();

        foreach ($webBlocs as $webBloc) {
            // Build component-specific files
            $componentJs = $this->generateWebBlocJs($webBloc);
            $componentCss = $this->generateWebBlocCss($webBloc);
            $componentHtml = $this->generateWebBlocHtml($webBloc);

            $cdnService->putFile("components/{$webBloc->type}.js", $componentJs);
            $cdnService->putFile("components/{$webBloc->type}.css", $componentCss);
            $cdnService->putFile("components/{$webBloc->type}.html", $componentHtml);
        }
    }

    protected function buildCoreJs()
    {
        $jsFiles = [
            resource_path('js/webbloc-core.js'),
            resource_path('js/webbloc-components.js'),
        ];

        $combinedJs = '';
        foreach ($jsFiles as $file) {
            if (File::exists($file)) {
                $combinedJs .= File::get($file) . "\n";
            }
        }

        return $combinedJs;
    }

    protected function buildCoreCss()
    {
        $cssFiles = [
            resource_path('css/webbloc-core.css'),
            resource_path('css/webbloc-components.css'),
        ];

        $combinedCss = '';
        foreach ($cssFiles as $file) {
            if (File::exists($file)) {
                $combinedCss .= File::get($file) . "\n";
            }
        }

        return $combinedCss;
    }

    protected function buildMinifiedJs()
    {
        $js = $this->buildCoreJs();
        
        // Basic JS minification - remove comments and unnecessary whitespace
        $js = preg_replace('/\/\*[\s\S]*?\*\//', '', $js);
        $js = preg_replace('/\/\/.*/', '', $js);
        $js = preg_replace('/\s+/', ' ', $js);
        $js = trim($js);

        return $js;
    }

    protected function buildMinifiedCss()
    {
        $css = $this->buildCoreCss();
        
        // Basic CSS minification
        $css = preg_replace('/\/\*[\s\S]*?\*\//', '', $css);
        $css = preg_replace('/\s+/', ' ', $css);
        $css = str_replace(['; ', ' {', '{ ', ' }', '} ', ': ', ', '], [';', '{', '{', '}', '}', ':', ','], $css);
        $css = trim($css);

        return $css;
    }

    protected function generateWebsiteCustomCss($website)
    {
        $settings = $website->settings ?? [];
        $customCss = '';

        // Generate CSS variables based on website settings
        if (!empty($settings['theme'])) {
            $customCss .= ":root {\n";
            
            if (!empty($settings['theme']['primary_color'])) {
                $customCss .= "  --webbloc-primary: {$settings['theme']['primary_color']};\n";
            }
            
            if (!empty($settings['theme']['secondary_color'])) {
                $customCss .= "  --webbloc-secondary: {$settings['theme']['secondary_color']};\n";
            }
            
            if (!empty($settings['theme']['font_family'])) {
                $customCss .= "  --webbloc-font: '{$settings['theme']['font_family']}';\n";
            }
            
            $customCss .= "}\n";
        }

        // Add custom CSS if provided
        if (!empty($settings['custom_css'])) {
            $customCss .= "\n" . $settings['custom_css'];
        }

        return $customCss;
    }

    protected function generateWebsiteCustomJs($website)
    {
        $settings = $website->settings ?? [];
        $customJs = '';

        $website_api_keys_first = $website->api_keys->first()->key ?? '';

        // Generate website-specific configuration
        $customJs .= "window.webBlocConfig = window.webBlocConfig || {};\n";
        $customJs .= "window.webBlocConfig.websiteId = '{$website->id}';\n";
        $customJs .= "window.webBlocConfig.apiKey = '{$website_api_keys_first}';\n";
        
        if (!empty($settings['api'])) {
            $customJs .= "window.webBlocConfig.apiSettings = " . json_encode($settings['api']) . ";\n";
        }

        // Add custom JavaScript if provided
        if (!empty($settings['custom_js'])) {
            $customJs .= "\n" . $settings['custom_js'];
        }

        return $customJs;
    }

    protected function generateWebBlocJs($webBloc)
    {
        $template = File::get(resource_path('js/templates/webbloc-component.js'));
        
        $replacements = [
            '{{TYPE}}' => $webBloc->type,
            '{{ATTRIBUTES}}' => json_encode($webBloc->attributes),
            '{{CRUD_OPERATIONS}}' => json_encode($webBloc->crud_operations),
            '{{API_ENDPOINTS}}' => json_encode($webBloc->getApiEndpoints()),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    protected function generateWebBlocCss($webBloc)
    {
        $css = '';
        
        // Load base component CSS
        $baseCssFile = resource_path("css/components/{$webBloc->type}.css");
        if (File::exists($baseCssFile)) {
            $css .= File::get($baseCssFile);
        }

        // Add custom CSS from WebBloc definition
        if (!empty($webBloc->metadata['custom_css'])) {
            $css .= "\n" . $webBloc->metadata['custom_css'];
        }

        return $css;
    }

    protected function generateWebBlocHtml($webBloc)
    {
        $templatePath = resource_path("views/components/webbloc/{$webBloc->type}.blade.php");
        
        if (File::exists($templatePath)) {
            return File::get($templatePath);
        }

        return '';
    }

    protected function updateManifest(CdnService $cdnService)
    {
        $manifest = [
            'version' => config('app.version', '1.0.0'),
            'build_time' => now()->toISOString(),
            'core_files' => [
                'webbloc-core.js',
                'webbloc-core.css',
                'webbloc.min.js',
                'webbloc.min.css',
            ],
            'component_files' => [],
            'website_files' => [],
        ];

        // Add component files to manifest
        foreach (WebBloc::where('status', 'active')->get() as $webBloc) {
            $manifest['component_files'][] = "components/{$webBloc->type}.js";
            $manifest['component_files'][] = "components/{$webBloc->type}.css";
            $manifest['component_files'][] = "components/{$webBloc->type}.html";
        }

        // Add website-specific files to manifest
        foreach (Website::where('status', 'active')->get() as $website) {
            if (Storage::disk('public')->exists("cdn/websites/{$website->id}/custom.css")) {
                $manifest['website_files'][] = "websites/{$website->id}/custom.css";
            }
            if (Storage::disk('public')->exists("cdn/websites/{$website->id}/custom.js")) {
                $manifest['website_files'][] = "websites/{$website->id}/custom.js";
            }
        }

        $cdnService->putFile('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
        
        // Update cache
        Cache::put('webbloc_cdn_manifest', $manifest, now()->addHour());
    }

    public function failed(\Exception $exception)
    {
        Log::error('BuildWebBlocCdnFiles job failed', [
            'error' => $exception->getMessage(),
            'websites' => $this->websites,
            'webbloc_types' => $this->webBlocTypes,
        ]);
    }
}