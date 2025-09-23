<?php

namespace App\Services;

use App\Models\Website;
use App\Models\WebBloc;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CdnService
{
    protected string $cdnPath;
    protected string $publicPath;
    
    public function __construct()
    {
        $this->cdnPath = storage_path('app/cdn');
        $this->publicPath = public_path('cdn');
        
        // Ensure CDN directories exist
        if (!File::exists($this->cdnPath)) {
            File::makeDirectory($this->cdnPath, 0755, true);
        }
        
        if (!File::exists($this->publicPath)) {
            File::makeDirectory($this->publicPath, 0755, true);
        }
    }
    
    /**
     * Put a file to the CDN storage
     */
    public function putFile(string $filename, $content, bool $isPath = false): string
    {
        try {
            $filepath = $this->publicPath . '/' . ltrim($filename, '/');
            
            // Ensure directory exists
            $directory = dirname($filepath);
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
            
            if ($isPath && File::exists($content)) {
                // $content is a file path - copy the file
                File::copy($content, $filepath);
            } else {
                // $content is string content - write directly
                File::put($filepath, $content);
            }
            
            Log::info('CDN file stored', ['filename' => $filename, 'path' => $filepath]);
            
            return $this->getCdnUrl($filename);
            
        } catch (\Exception $e) {
            Log::error('CDN putFile Error: ' . $e->getMessage(), [
                'filename' => $filename,
                'isPath' => $isPath
            ]);
            throw $e;
        }
    }
    
    /**
     * Check if a file exists in CDN
     */
    public function fileExists(string $filename): bool
    {
        return File::exists($this->publicPath . '/' . ltrim($filename, '/'));
    }
    
    /**
     * Get file content from CDN
     */
    public function getFile(string $filename): string
    {
        $filepath = $this->publicPath . '/' . ltrim($filename, '/');
        
        if (!File::exists($filepath)) {
            throw new \Exception("File not found: {$filename}");
        }
        
        return File::get($filepath);
    }
    
    /**
     * Delete a file from CDN
     */
    public function deleteFile(string $filename): bool
    {
        $filepath = $this->publicPath . '/' . ltrim($filename, '/');
        
        if (File::exists($filepath)) {
            return File::delete($filepath);
        }
        
        return false;
    }

    
    /**
     * Build all CDN assets for WebBloc components
     */
    public function buildAll(): array
    {
        $results = [];
        
        try {
            // Build core CSS and JS files
            $results['core_css'] = $this->buildCoreCSS();
            $results['core_js'] = $this->buildCoreJS();
            $results['components_css'] = $this->buildComponentsCSS();
            
            // Build individual component files
            $webBlocs = WebBloc::where('status', 'active')->get();
            $componentResults = [];
            
            foreach ($webBlocs as $webBloc) {
                $componentResults[$webBloc->type] = $this->buildComponentFiles($webBloc);
            }
            
            $results['components'] = $componentResults;
            
            // Build combined/minified versions
            $results['combined'] = $this->buildCombinedFiles();
            
            // Generate manifest file
            $results['manifest'] = $this->generateManifest();
            
            return $results;
            
        } catch (\Exception $e) {
            Log::error('CDN Build Error: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Build core WebBloc CSS
     */
    protected function buildCoreCSS(): string
    {
        $coreCSS = File::get(resource_path('css/webbloc-core.css'));
        
        // Process CSS variables and optimize
        $processedCSS = $this->processCSS($coreCSS);
        
        // Write to CDN path
        $filename = 'webbloc-core.css';
        $filepath = $this->publicPath . '/' . $filename;
        File::put($filepath, $processedCSS);
        
        // Create minified version
        $minifiedCSS = $this->minifyCSS($processedCSS);
        $minFilepath = $this->publicPath . '/webbloc-core.min.css';
        File::put($minFilepath, $minifiedCSS);
        
        return $filename;
    }
    
    /**
     * Build components CSS
     */
    protected function buildComponentsCSS(): string
    {
        $componentsCSS = File::get(resource_path('css/webbloc-components.css'));
        
        // Process and optimize
        $processedCSS = $this->processCSS($componentsCSS);
        
        $filename = 'webbloc-components.css';
        $filepath = $this->publicPath . '/' . $filename;
        File::put($filepath, $processedCSS);
        
        // Minified version
        $minifiedCSS = $this->minifyCSS($processedCSS);
        $minFilepath = $this->publicPath . '/webbloc-components.min.css';
        File::put($minFilepath, $minifiedCSS);
        
        return $filename;
    }
    
    /**
     * Build core WebBloc JavaScript
     */
    protected function buildCoreJS(): string
    {
        $coreJS = $this->generateCoreJS();
        
        $filename = 'webbloc-core.js';
        $filepath = $this->publicPath . '/' . $filename;
        File::put($filepath, $coreJS);
        
        // Minified version
        $minifiedJS = $this->minifyJS($coreJS);
        $minFilepath = $this->publicPath . '/webbloc-core.min.js';
        File::put($minFilepath, $minifiedJS);
        
        return $filename;
    }
    
    /**
     * Generate core JavaScript functionality
     */
    protected function generateCoreJS(): string
    {
        return <<<'JS'
// WebBloc Core JavaScript
(function() {
    'use strict';
    
    // WebBloc namespace
    window.WebBloc = window.WebBloc || {};
    
    // Configuration
    WebBloc.config = {
        apiBaseUrl: window.webBlocConfig?.apiBaseUrl || '/api',
        apiKey: null,
        websiteId: null,
        debug: window.webBlocConfig?.debug || false
    };
    
    // Utilities
    WebBloc.utils = {
        // Get API key from various sources
        getApiKey() {
            return WebBloc.config.apiKey || 
                   document.querySelector('[data-webbloc-api-key]')?.dataset.webBlocApiKey ||
                   document.querySelector('meta[name="webbloc-api-key"]')?.content;
        },
        
        // Get website ID
        getWebsiteId() {
            return WebBloc.config.websiteId ||
                   document.querySelector('[data-webbloc-website-id]')?.dataset.webBlocWebsiteId ||
                   document.querySelector('meta[name="webbloc-website-id"]')?.content;
        },
        
        // Make API request
        async apiRequest(endpoint, options = {}) {
            const apiKey = this.getApiKey();
            const websiteId = this.getWebsiteId();
            
            if (!apiKey) {
                throw new Error('WebBloc API key not found');
            }
            
            const url = `${WebBloc.config.apiBaseUrl}${endpoint}`;
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-API-Key': apiKey,
                    'X-Website-ID': websiteId,
                    'Accept': 'application/json'
                }
            };
            
            const mergedOptions = {
                ...defaultOptions,
                ...options,
                headers: {
                    ...defaultOptions.headers,
                    ...options.headers
                }
            };
            
            const response = await fetch(url, mergedOptions);
            
            if (!response.ok) {
                const error = await response.json().catch(() => ({ message: 'Network error' }));
                throw new Error(error.message || 'API request failed');
            }
            
            return response.json();
        },
        
        // Format date
        formatDate(date) {
            return new Intl.DateTimeFormat('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }).format(new Date(date));
        },
        
        // Escape HTML
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },
        
        // Generate unique ID
        generateId() {
            return 'wb_' + Math.random().toString(36).substr(2, 9);
        },
        
        // Debounce function
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    };
    
    // Notification system
    WebBloc.notify = {
        show(message, type = 'info', duration = 5000) {
            const toast = document.createElement('div');
            toast.className = `webbloc-toast webbloc-toast-${type}`;
            toast.innerHTML = `
                <div class="webbloc-toast-content">
                    <div class="webbloc-toast-icon">
                        ${this.getIcon(type)}
                    </div>
                    <div class="webbloc-toast-body">
                        <div class="webbloc-toast-message">${WebBloc.utils.escapeHtml(message)}</div>
                    </div>
                    <button class="webbloc-toast-close" onclick="this.parentElement.remove()">×</button>
                </div>
            `;
            
            const container = this.getContainer();
            container.appendChild(toast);
            
            // Animate in
            setTimeout(() => toast.classList.add('show'), 10);
            
            // Auto remove
            if (duration > 0) {
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 300);
                }, duration);
            }
        },
        
        success(message, duration) {
            this.show(message, 'success', duration);
        },
        
        error(message, duration) {
            this.show(message, 'error', duration);
        },
        
        warning(message, duration) {
            this.show(message, 'warning', duration);
        },
        
        info(message, duration) {
            this.show(message, 'info', duration);
        },
        
        getContainer() {
            let container = document.querySelector('.webbloc-toast-container');
            if (!container) {
                container = document.createElement('div');
                container.className = 'webbloc-toast-container top-right';
                document.body.appendChild(container);
            }
            return container;
        },
        
        getIcon(type) {
            const icons = {
                success: '✓',
                error: '✕',
                warning: '⚠',
                info: 'ⓘ'
            };
            return icons[type] || icons.info;
        }
    };
    
    // Component initialization
    WebBloc.init = {
        auto() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.initializeComponents());
            } else {
                this.initializeComponents();
            }
        },
        
        initializeComponents() {
            // Find all WebBloc components
            const components = document.querySelectorAll('[w2030b]');
            
            components.forEach(component => {
                const type = component.getAttribute('w2030b');
                this.initializeComponent(component, type);
            });
        },
        
        initializeComponent(element, type) {
            if (element.dataset.webBlocInitialized === 'true') {
                return; // Already initialized
            }
            
            element.dataset.webBlocInitialized = 'true';
            
            // Load component-specific functionality
            if (WebBloc.components && WebBloc.components[type]) {
                try {
                    WebBloc.components[type].init(element);
                } catch (error) {
                    console.error(`Error initializing WebBloc component ${type}:`, error);
                }
            }
        }
    };
    
    // Loading states
    WebBloc.loading = {
        show(element) {
            if (!element.querySelector('.webbloc-loading-overlay')) {
                const overlay = document.createElement('div');
                overlay.className = 'webbloc-loading-overlay';
                overlay.innerHTML = '<div class="webbloc-spinner"></div>';
                overlay.style.cssText = `
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(255, 255, 255, 0.8);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 1000;
                `;
                element.style.position = 'relative';
                element.appendChild(overlay);
            }
        },
        
        hide(element) {
            const overlay = element.querySelector('.webbloc-loading-overlay');
            if (overlay) {
                overlay.remove();
            }
        }
    };
    
    // Components namespace
    WebBloc.components = {};
    
    // Auto-initialize on DOM ready
    WebBloc.init.auto();
    
    // Global error handling
    window.addEventListener('error', (event) => {
        if (WebBloc.config.debug) {
            console.error('WebBloc Error:', event.error);
        }
    });
    
    // Expose for manual initialization
    window.webBlocInit = WebBloc.init.initializeComponents.bind(WebBloc.init);
    
})();
JS;
    }
    
    /**
     * Build component-specific files
     */
    protected function buildComponentFiles(WebBloc $webBloc): array
    {
        $results = [];
        
        try {
            // Generate component JavaScript
            $componentJS = $this->generateComponentJS($webBloc);
            $jsFilename = "webbloc-{$webBloc->type}.js";
            $jsFilepath = $this->publicPath . '/' . $jsFilename;
            File::put($jsFilepath, $componentJS);
            
            // Minified version
            $minifiedJS = $this->minifyJS($componentJS);
            $minJsFilepath = $this->publicPath . "/webbloc-{$webBloc->type}.min.js";
            File::put($minJsFilepath, $minifiedJS);
            
            $results['js'] = $jsFilename;
            $results['js_min'] = "webbloc-{$webBloc->type}.min.js";
            
            // Generate component CSS if custom styles exist
            if (!empty($webBloc->css)) {
                $componentCSS = $this->processCSS($webBloc->css);
                $cssFilename = "webbloc-{$webBloc->type}.css";
                $cssFilepath = $this->publicPath . '/' . $cssFilename;
                File::put($cssFilepath, $componentCSS);
                
                $results['css'] = $cssFilename;
            }
            
        } catch (\Exception $e) {
            Log::error("Error building component {$webBloc->type}: " . $e->getMessage());
            throw $e;
        }
        
        return $results;
    }
    
    /**
     * Generate JavaScript for a specific component
     */
    protected function generateComponentJS(WebBloc $webBloc): string
    {
        $componentName = Str::studly($webBloc->type);
        $config = json_encode($webBloc->attributes ?? []);
        
        return <<<JS
// WebBloc {$componentName} Component
(function() {
    'use strict';
    
    if (!window.WebBloc) {
        console.error('WebBloc core not loaded');
        return;
    }
    
    const {$componentName}Component = {
        config: {$config},
        
        init(element) {
            this.element = element;
            this.setupComponent();
            this.bindEvents();
            this.loadContent();
        },
        
        setupComponent() {
            // Add component-specific classes
            this.element.classList.add('webbloc-{$webBloc->type}');
            
            // Set up initial HTML structure
            if (!this.element.innerHTML.trim()) {
                this.element.innerHTML = this.getTemplate();
            }
            
            // Initialize Alpine.js data if available
            if (typeof Alpine !== 'undefined' && !this.element._x_dataStack) {
                this.setupAlpineData();
            }
        },
        
        getTemplate() {
            return `{$this->getComponentTemplate($webBloc)}`;
        },
        
        setupAlpineData() {
            const self = this;
            
            this.element._x_dataStack = [{
                loading: false,
                error: null,
                data: [],
                
                async loadData() {
                    this.loading = true;
                    this.error = null;
                    
                    try {
                        const response = await WebBloc.utils.apiRequest('/webblocs/{$webBloc->type}', {
                            method: 'GET'
                        });
                        
                        this.data = response.data || [];
                    } catch (error) {
                        this.error = error.message;
                        WebBloc.notify.error(error.message);
                    } finally {
                        this.loading = false;
                    }
                },
                
                async create(formData) {
                    this.loading = true;
                    
                    try {
                        const response = await WebBloc.utils.apiRequest('/webblocs/{$webBloc->type}', {
                            method: 'POST',
                            body: JSON.stringify(formData)
                        });
                        
                        this.data.unshift(response.data);
                        WebBloc.notify.success('{$componentName} created successfully');
                        
                        // Reset form if it exists
                        const form = self.element.querySelector('form');
                        if (form) form.reset();
                        
                    } catch (error) {
                        WebBloc.notify.error(error.message);
                    } finally {
                        this.loading = false;
                    }
                },
                
                async update(id, formData) {
                    try {
                        const response = await WebBloc.utils.apiRequest(`/webblocs/{$webBloc->type}/\${id}`, {
                            method: 'PUT',
                            body: JSON.stringify(formData)
                        });
                        
                        const index = this.data.findIndex(item => item.id === id);
                        if (index !== -1) {
                            this.data[index] = response.data;
                        }
                        
                        WebBloc.notify.success('{$componentName} updated successfully');
                    } catch (error) {
                        WebBloc.notify.error(error.message);
                    }
                },
                
                async delete(id) {
                    try {
                        await WebBloc.utils.apiRequest(`/webblocs/{$webBloc->type}/\${id}`, {
                            method: 'DELETE'
                        });
                        
                        this.data = this.data.filter(item => item.id !== id);
                        WebBloc.notify.success('{$componentName} deleted successfully');
                    } catch (error) {
                        WebBloc.notify.error(error.message);
                    }
                }
            }];
            
            // Initialize Alpine if not already done
            if (typeof Alpine !== 'undefined' && Alpine.version) {
                Alpine.initTree(this.element);
            }
        },
        
        bindEvents() {
            // Component-specific event binding
            this.element.addEventListener('webbloc:refresh', () => {
                this.loadContent();
            });
        },
        
        loadContent() {
            // Trigger initial data load if Alpine.js is available
            if (this.element._x_dataStack && this.element._x_dataStack[0].loadData) {
                this.element._x_dataStack[0].loadData();
            }
        }
    };
    
    // Register component
    WebBloc.components['{$webBloc->type}'] = {$componentName}Component;
    
})();
JS;
    }
    
    /**
     * Get component template
     */
    protected function getComponentTemplate(WebBloc $webBloc): string
    {
        // Try to get template from blade views
        $templatePath = resource_path("views/webbloc/{$webBloc->type}/default.blade.php");
        
        if (File::exists($templatePath)) {
            $content = File::get($templatePath);
            // Remove blade directives and convert to HTML
            $content = preg_replace('/@[a-zA-Z]+(\([^)]*\))?/', '', $content);
            return addslashes($content);
        }
        
        // Fallback template based on component type
        return $this->getDefaultTemplate($webBloc->type);
    }
    
    /**
     * Get default template for component type
     */
    protected function getDefaultTemplate(string $type): string
    {
        switch ($type) {
            case 'comments':
                return '<div class="webbloc-comments-container"><div x-data><div x-show="loading" class="webbloc-loading"><div class="webbloc-spinner"></div></div><div x-show="!loading"><div class="webbloc-comments-list"><template x-for="comment in data"><div class="webbloc-comment" x-text="comment.content"></div></template></div></div></div></div>';
                
            case 'reviews':
                return '<div class="webbloc-reviews-container"><div x-data><div x-show="loading" class="webbloc-loading"><div class="webbloc-spinner"></div></div><div x-show="!loading"><div class="webbloc-reviews-list"><template x-for="review in data"><div class="webbloc-review" x-text="review.content"></div></template></div></div></div></div>';
                
            case 'auth':
                return '<div class="webbloc-auth-container"><div class="webbloc-auth-form"><form><div class="webbloc-auth-field"><input type="email" placeholder="Email" class="webbloc-input"></div><div class="webbloc-auth-field"><input type="password" placeholder="Password" class="webbloc-input"></div><button type="submit" class="webbloc-btn webbloc-btn-primary">Sign In</button></form></div></div>';
                
            default:
                return '<div class="webbloc-component-container"><div x-data><div x-show="loading" class="webbloc-loading"><div class="webbloc-spinner"></div></div><div x-show="!loading && data.length === 0" class="webbloc-empty">No items found.</div><div x-show="!loading && data.length > 0"><template x-for="item in data"><div class="webbloc-item" x-text="item.title || item.name || item.content"></div></template></div></div></div>';
        }
    }
    
    /**
     * Build combined/minified files
     */
    protected function buildCombinedFiles(): array
    {
        $results = [];
        
        // Combine all CSS
        $combinedCSS = '';
        $combinedCSS .= File::get($this->publicPath . '/webbloc-core.css');
        $combinedCSS .= "\n\n";
        $combinedCSS .= File::get($this->publicPath . '/webbloc-components.css');
        
        // Add component-specific CSS
        $webBlocs = WebBloc::where('status', 'active')->get();
        foreach ($webBlocs as $webBloc) {
            $componentCSSPath = $this->publicPath . "/webbloc-{$webBloc->type}.css";
            if (File::exists($componentCSSPath)) {
                $combinedCSS .= "\n\n";
                $combinedCSS .= File::get($componentCSSPath);
            }
        }
        
        $combinedCSSFile = 'webbloc.css';
        File::put($this->publicPath . '/' . $combinedCSSFile, $combinedCSS);
        
        $minifiedCSS = $this->minifyCSS($combinedCSS);
        $minCSSFile = 'webbloc.min.css';
        File::put($this->publicPath . '/' . $minCSSFile, $minifiedCSS);
        
        // Combine all JS
        $combinedJS = File::get($this->publicPath . '/webbloc-core.js');
        
        foreach ($webBlocs as $webBloc) {
            $componentJSPath = $this->publicPath . "/webbloc-{$webBloc->type}.js";
            if (File::exists($componentJSPath)) {
                $combinedJS .= "\n\n";
                $combinedJS .= File::get($componentJSPath);
            }
        }
        
        $combinedJSFile = 'webbloc.js';
        File::put($this->publicPath . '/' . $combinedJSFile, $combinedJS);
        
        $minifiedJS = $this->minifyJS($combinedJS);
        $minJSFile = 'webbloc.min.js';
        File::put($this->publicPath . '/' . $minJSFile, $minifiedJS);
        
        $results['css'] = $combinedCSSFile;
        $results['css_min'] = $minCSSFile;
        $results['js'] = $combinedJSFile;
        $results['js_min'] = $minJSFile;
        
        return $results;
    }
    
    /**
     * Generate manifest file
     */
    protected function generateManifest(): string
    {
        $files = File::files($this->publicPath);
        $manifest = [
            'generated_at' => now()->toISOString(),
            'version' => config('app.version', '1.0.0'),
            'files' => []
        ];
        
        foreach ($files as $file) {
            $filename = $file->getFilename();
            $manifest['files'][$filename] = [
                'size' => $file->getSize(),
                'hash' => md5_file($file->getPathname()),
                'modified' => date('c', $file->getMTime())
            ];
        }
        
        $manifestContent = json_encode($manifest, JSON_PRETTY_PRINT);
        $manifestFile = 'manifest.json';
        File::put($this->publicPath . '/' . $manifestFile, $manifestContent);
        
        return $manifestFile;
    }
    
    /**
     * Process CSS (autoprefixer, variables, etc.)
     */
    protected function processCSS(string $css): string
    {
        // Basic CSS processing
        $css = preg_replace('/\/\*.*?\*\//s', '', $css); // Remove comments
        $css = preg_replace('/\s+/', ' ', $css); // Normalize whitespace
        
        return trim($css);
    }
    
    /**
     * Minify CSS
     */
    protected function minifyCSS(string $css): string
    {
        // Remove comments
        $css = preg_replace('/\/\*.*?\*\//s', '', $css);
        
        // Remove unnecessary whitespace
        $css = preg_replace('/\s+/', ' ', $css);
        $css = preg_replace('/;\s*}/', '}', $css);
        $css = preg_replace('/\s*{\s*/', '{', $css);
        $css = preg_replace('/;\s*/', ';', $css);
        $css = preg_replace('/:\s*/', ':', $css);
        
        return trim($css);
    }
    
    /**
     * Minify JavaScript
     */
    protected function minifyJS(string $js): string
    {
        // Basic JS minification
        // Remove single-line comments
        $js = preg_replace('/\/\/.*$/m', '', $js);
        
        // Remove multi-line comments
        $js = preg_replace('/\/\*.*?\*\//s', '', $js);
        
        // Remove unnecessary whitespace
        $js = preg_replace('/\s+/', ' ', $js);
        
        return trim($js);
    }
    
    /**
     * Get CDN URL for a file
     */
    public function getCdnUrl(string $filename): string
    {
        return url("cdn/{$filename}");
    }
    
    /**
     * Get integration code for a website
     */
    public function getIntegrationCode(Website $website, array $components = []): array
    {
        $apiKey = $website->apiKeys()->where('status', 'active')->first();
        
        if (!$apiKey) {
            throw new \Exception('No active API key found for website');
        }
        
        $baseUrl = url('/');
        
        return [
            'html' => $this->generateIntegrationHTML($website, $apiKey, $components),
            'css_urls' => [
                $this->getCdnUrl('webbloc.min.css')
            ],
            'js_urls' => [
                $this->getCdnUrl('webbloc.min.js')
            ],
            'api_key' => $apiKey->key,
            'website_id' => $website->id
        ];
    }
    
    /**
     * Generate integration HTML
     */
    protected function generateIntegrationHTML(Website $website, $apiKey, array $components): string
    {
        $html = "<!-- WebBloc Integration for {$website->name} -->\n";
        $html .= "<link rel=\"stylesheet\" href=\"{$this->getCdnUrl('webbloc.min.css')}\">\n";
        $html .= "<script defer src=\"{$this->getCdnUrl('webbloc.min.js')}\"></script>\n\n";
        
        $html .= "<!-- WebBloc Configuration -->\n";
        $html .= "<script>\n";
        $html .= "window.webBlocConfig = {\n";
        $html .= "    apiBaseUrl: '" . url('/api') . "',\n";
        $html .= "    debug: false\n";
        $html .= "};\n";
        $html .= "</script>\n\n";
        
        $html .= "<!-- WebBloc Meta Tags -->\n";
        $html .= "<meta name=\"webbloc-api-key\" content=\"{$apiKey->key}\">\n";
        $html .= "<meta name=\"webbloc-website-id\" content=\"{$website->id}\">\n\n";
        
        if (!empty($components)) {
            $html .= "<!-- WebBloc Components -->\n";
            foreach ($components as $component) {
                $html .= "<div w2030b=\"{$component}\" data-webbloc-api-key=\"{$apiKey->key}\" data-webbloc-website-id=\"{$website->id}\"></div>\n";
            }
        }
        
        return $html;
    }
    
    /**
     * Clear CDN cache
     */
    public function clearCache(): void
    {
        Cache::tags(['webbloc-cdn'])->flush();
        
        // Clear file cache
        if (File::exists($this->publicPath)) {
            File::cleanDirectory($this->publicPath);
        }
    }

    /**
     * Get the full filesystem path for a CDN file
     */
    public function getFilePath(string $filename): string
    {
        return $this->publicPath . '/' . ltrim($filename, '/');
    }

    /**
     * Get file size
     */
    public function getFileSize(string $filename): int
    {
        $filepath = $this->getFilePath($filename);
        return File::exists($filepath) ? File::size($filepath) : 0;
    }

    /**
     * Get file modification time
     */
    public function getFileModifiedTime(string $filename): ?string
    {
        $filepath = $this->getFilePath($filename);
        return File::exists($filepath) ? date('c', File::lastModified($filepath)) : null;
    }

}
