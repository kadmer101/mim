<?php
/**
 * WebBloc Asset Builder - No SSH Required
 * Builds and minifies CSS/JS assets using PHP only
 * 
 * Usage: https://yourdomain.com/deploy/build-assets.php
 */

// Security: Basic authentication required
$auth_user = 'webbloc_deploy';
$auth_pass = 'build_assets_secure_2024';

if (!isset($_SERVER['PHP_AUTH_USER']) || 
    $_SERVER['PHP_AUTH_USER'] !== $auth_user || 
    $_SERVER['PHP_AUTH_PW'] !== $auth_pass) {
    header('WWW-Authenticate: Basic realm="WebBloc Asset Builder"');
    header('HTTP/1.0 401 Unauthorized');
    die('Authentication required');
}

ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');

// Bootstrap Laravel if available
$laravelBootstrapped = false;
if (file_exists(__DIR__ . '/../../vendor/autoload.php') && file_exists(__DIR__ . '/../../bootstrap/app.php')) {
    try {
        require_once __DIR__ . '/../../vendor/autoload.php';
        $app = require_once __DIR__ . '/../../bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        $laravelBootstrapped = true;
    } catch (Exception $e) {
        // Fall back to non-Laravel mode
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebBloc Asset Builder</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 20px; background: #f1f5f9; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        h1 { color: #0f172a; text-align: center; margin-bottom: 30px; }
        .status { padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid; }
        .success { background: #dcfce7; color: #166534; border-color: #22c55e; }
        .error { background: #fef2f2; color: #dc2626; border-color: #ef4444; }
        .warning { background: #fefce8; color: #ca8a04; border-color: #eab308; }
        .info { background: #eff6ff; color: #2563eb; border-color: #3b82f6; }
        .log { background: #0f172a; color: #e2e8f0; padding: 20px; border-radius: 8px; font-family: 'Courier New', monospace; font-size: 14px; max-height: 600px; overflow-y: auto; white-space: pre-wrap; line-height: 1.4; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }
        .card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; }
        .card h3 { margin-top: 0; color: #334155; }
        button { background: #2563eb; color: white; padding: 12px 20px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; margin: 5px; transition: background 0.2s; }
        button:hover { background: #1d4ed8; }
        button.danger { background: #dc2626; }
        button.danger:hover { background: #b91c1c; }
        button.success { background: #059669; }
        button.success:hover { background: #047857; }
        .file-info { font-size: 12px; color: #64748b; margin-top: 5px; }
        .progress { width: 100%; height: 8px; background: #e2e8f0; border-radius: 4px; margin: 10px 0; overflow: hidden; }
        .progress-bar { height: 100%; background: linear-gradient(90deg, #3b82f6, #1d4ed8); transition: width 0.3s ease; }
        .file-tree { font-family: monospace; font-size: 12px; background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f1f5f9; font-weight: 600; }
    </style>
</head>
<body>

<div class="container">
    <h1>🎨 WebBloc Asset Builder</h1>
    
    <?php
    
    class AssetBuilder {
        private $basePath;
        private $outputPath;
        private $log = [];
        
        public function __construct($basePath) {
            $this->basePath = rtrim($basePath, '/');
            $this->outputPath = $this->basePath . '/public/cdn';
        }
        
        // Add this public getter method
        public function getBasePath() {
            return $this->basePath;
        }
        
        public function log($message, $type = 'info') {
            $timestamp = date('H:i:s');
            $this->log[] = ['time' => $timestamp, 'message' => $message, 'type' => $type];
            echo "[$timestamp] $message\n";
            flush();
        }
        
        public function ensureDirectory($path) {
            if (!is_dir($path)) {
                if (mkdir($path, 0755, true)) {
                    $this->log("✓ Created directory: " . basename($path));
                } else {
                    $this->log("✗ Failed to create directory: $path", 'error');
                    return false;
                }
            }
            return true;
        }
        
        public function minifyCSS($css) {
            // Remove comments
            $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
            // Remove whitespace
            $css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);
            // Remove extra spaces
            $css = preg_replace('/\s+/', ' ', $css);
            // Remove spaces around specific characters
            $css = str_replace(['; ', ' {', '{ ', ' }', '} ', ': ', ' :'], [';', '{', '{', '}', '}', ':', ':'], $css);
            return trim($css);
        }
        
        public function minifyJS($js) {
            // Basic JS minification - remove comments and excess whitespace
            $js = preg_replace('/\/\*[\s\S]*?\*\//', '', $js); // Remove /* */ comments
            $js = preg_replace('/\/\/.*$/m', '', $js); // Remove // comments
            $js = preg_replace('/\s+/', ' ', $js); // Collapse whitespace
            $js = str_replace(['; ', ' {', '{ ', ' }', '} '], [';', '{', '{', '}', '}'], $js);
            return trim($js);
        }
        
        public function buildCoreCSS() {
            $this->log("Building core CSS...");
            
            $cssFiles = [
                $this->basePath . '/resources/css/webbloc-core.css',
                $this->basePath . '/resources/css/webbloc-components.css'
            ];
            
            $combinedCSS = "/* WebBloc Core CSS - Generated " . date('Y-m-d H:i:s') . " */\n";
            $totalSize = 0;
            
            foreach ($cssFiles as $file) {
                if (file_exists($file)) {
                    $content = file_get_contents($file);
                    $combinedCSS .= "\n/* " . basename($file) . " */\n" . $content . "\n";
                    $totalSize += strlen($content);
                    $this->log("  ✓ Added " . basename($file) . " (" . number_format(strlen($content)) . " bytes)");
                } else {
                    $this->log("  ⚠ File not found: " . basename($file), 'warning');
                }
            }
            
            // Minify
            $minifiedCSS = $this->minifyCSS($combinedCSS);
            $compressionRatio = round((1 - strlen($minifiedCSS) / strlen($combinedCSS)) * 100, 1);
            
            // Write files
            $this->ensureDirectory($this->outputPath);
            
            // Full version
            file_put_contents($this->outputPath . '/webbloc.css', $combinedCSS);
            // Minified version
            file_put_contents($this->outputPath . '/webbloc.min.css', $minifiedCSS);
            
            $this->log("✓ Core CSS built: " . number_format(strlen($minifiedCSS)) . " bytes (compressed {$compressionRatio}%)");
            
            return [
                'original_size' => strlen($combinedCSS),
                'minified_size' => strlen($minifiedCSS),
                'compression' => $compressionRatio,
                'files_processed' => count(array_filter($cssFiles, 'file_exists'))
            ];
        }
        
        public function buildCoreJS() {
            $this->log("Building core JavaScript...");
            
            $jsFiles = [
                $this->basePath . '/resources/js/webbloc-core.js',
                $this->basePath . '/resources/js/webbloc-components.js'
            ];
            
            $combinedJS = "/* WebBloc Core JS - Generated " . date('Y-m-d H:i:s') . " */\n";
            
            foreach ($jsFiles as $file) {
                if (file_exists($file)) {
                    $content = file_get_contents($file);
                    $combinedJS .= "\n/* " . basename($file) . " */\n" . $content . "\n";
                    $this->log("  ✓ Added " . basename($file) . " (" . number_format(strlen($content)) . " bytes)");
                } else {
                    $this->log("  ⚠ File not found: " . basename($file), 'warning');
                }
            }
            
            // Minify
            $minifiedJS = $this->minifyJS($combinedJS);
            $compressionRatio = round((1 - strlen($minifiedJS) / strlen($combinedJS)) * 100, 1);
            
            // Write files
            file_put_contents($this->outputPath . '/webbloc.js', $combinedJS);
            file_put_contents($this->outputPath . '/webbloc.min.js', $minifiedJS);
            
            $this->log("✓ Core JS built: " . number_format(strlen($minifiedJS)) . " bytes (compressed {$compressionRatio}%)");
            
            return [
                'original_size' => strlen($combinedJS),
                'minified_size' => strlen($minifiedJS),
                'compression' => $compressionRatio,
                'files_processed' => count(array_filter($jsFiles, 'file_exists'))
            ];
        }
        
        public function buildComponentAssets() {
            $this->log("Building individual component assets...");
            
            $components = ['auth', 'comments', 'reviews', 'notifications'];
            $results = [];
            
            foreach ($components as $component) {
                $this->log("  Building {$component} component...");
                
                // Component CSS
                $cssPath = $this->basePath . "/resources/css/components/{$component}.css";
                if (file_exists($cssPath)) {
                    $css = file_get_contents($cssPath);
                    $minifiedCSS = $this->minifyCSS($css);
                    file_put_contents($this->outputPath . "/{$component}.css", $css);
                    file_put_contents($this->outputPath . "/{$component}.min.css", $minifiedCSS);
                    $this->log("    ✓ CSS: " . number_format(strlen($minifiedCSS)) . " bytes");
                }
                
                // Component JS
                $jsPath = $this->basePath . "/resources/js/components/{$component}.js";
                if (file_exists($jsPath)) {
                    $js = file_get_contents($jsPath);
                    $minifiedJS = $this->minifyJS($js);
                    file_put_contents($this->outputPath . "/{$component}.js", $js);
                    file_put_contents($this->outputPath . "/{$component}.min.js", $minifiedJS);
                    $this->log("    ✓ JS: " . number_format(strlen($minifiedJS)) . " bytes");
                }
                
                $results[$component] = 'processed';
            }
            
            return $results;
        }
        
        public function buildManifest() {
            $this->log("Building asset manifest...");
            
            $manifest = [
                'built_at' => date('c'),
                'version' => '1.0.0',
                'assets' => []
            ];
            
            $files = glob($this->outputPath . '/*.{css,js}', GLOB_BRACE);
            foreach ($files as $file) {
                $filename = basename($file);
                $manifest['assets'][$filename] = [
                    'size' => filesize($file),
                    'hash' => md5_file($file),
                    'modified' => date('c', filemtime($file))
                ];
            }
            
            file_put_contents($this->outputPath . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
            $this->log("✓ Manifest created with " . count($manifest['assets']) . " assets");
            
            return $manifest;
        }
        
        public function getAssetInfo() {
            $info = [];
            $totalSize = 0;
            
            if (is_dir($this->outputPath)) {
                $files = glob($this->outputPath . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $size = filesize($file);
                        $info[basename($file)] = [
                            'size' => $size,
                            'size_formatted' => $this->formatBytes($size),
                            'modified' => date('Y-m-d H:i:s', filemtime($file))
                        ];
                        $totalSize += $size;
                    }
                }
            }
            
            return ['files' => $info, 'total_size' => $totalSize];
        }
        
        public function formatBytes($bytes) {
            $units = ['B', 'KB', 'MB'];
            $bytes = max($bytes, 0);
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);
            $bytes /= pow(1024, $pow);
            return round($bytes, 1) . ' ' . $units[$pow];
        }
        
        public function cleanup() {
            $this->log("Cleaning up old assets...");
            $cleaned = 0;
            
            if (is_dir($this->outputPath)) {
                $files = glob($this->outputPath . '/*.{css,js,map}', GLOB_BRACE);
                foreach ($files as $file) {
                    // Keep files modified in last hour
                    if (filemtime($file) < (time() - 3600)) {
                        if (unlink($file)) {
                            $cleaned++;
                        }
                    }
                }
            }
            
            $this->log("✓ Cleaned up {$cleaned} old files");
            return $cleaned;
        }
    }
    
    $builder = new AssetBuilder(__DIR__ . '/../..');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        echo '<div class="log">';
        
        switch ($action) {
            case 'build_all':
                echo "🚀 Starting full asset build...\n\n";
                
                $cssResult = $builder->buildCoreCSS();
                echo "\n";
                
                $jsResult = $builder->buildCoreJS();
                echo "\n";
                
                $componentResults = $builder->buildComponentAssets();
                echo "\n";
                
                $manifest = $builder->buildManifest();
                echo "\n";
                
                echo "🎉 Build complete!\n";
                echo "CSS: {$builder->formatBytes($cssResult['minified_size'])} (compressed {$cssResult['compression']}%)\n";
                echo "JS: {$builder->formatBytes($jsResult['minified_size'])} (compressed {$jsResult['compression']}%)\n";
                break;
                
            case 'build_css':
                $result = $builder->buildCoreCSS();
                echo "\n✅ CSS build complete: {$builder->formatBytes($result['minified_size'])}";
                break;
                
            case 'build_js':
                $result = $builder->buildCoreJS();
                echo "\n✅ JS build complete: {$builder->formatBytes($result['minified_size'])}";
                break;
                
            case 'build_components':
                $builder->buildComponentAssets();
                echo "\n✅ Component assets built";
                break;
                
            case 'cleanup':
                $cleaned = $builder->cleanup();
                echo "✅ Cleanup complete: {$cleaned} files removed";
                break;
                
            case 'laravel_build':
                if ($laravelBootstrapped) {
                    echo "Running Laravel WebBloc CDN build command...\n\n";
                    try {
                        \Illuminate\Support\Facades\Artisan::call('webbloc:build-cdn');
                        echo \Illuminate\Support\Facades\Artisan::output();
                        echo "\n✅ Laravel build complete";
                    } catch (Exception $e) {
                        echo "❌ Laravel build failed: " . $e->getMessage();
                    }
                } else {
                    echo "❌ Laravel not available for this operation";
                }
                break;
        }
        
        echo '</div>';
        
        echo '<div style="text-align: center; margin: 20px 0;">
                <button onclick="location.reload()">🔄 Refresh Status</button>
              </div>';
        
    } else {
        
        // Display current status and controls
        $assetInfo = $builder->getAssetInfo();
        
        ?>
        
        <!-- Asset Status -->
        <div class="card">
            <h3>📦 Current Assets</h3>
            <?php if (empty($assetInfo['files'])): ?>
                <div class="status warning">No assets found. Run a build to generate assets.</div>
            <?php else: ?>
                <div class="status success">
                    <?= count($assetInfo['files']) ?> assets found 
                    (<?= $builder->formatBytes($assetInfo['total_size']) ?> total)
                </div>
                
                <table>
                    <thead>
                        <tr><th>File</th><th>Size</th><th>Modified</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assetInfo['files'] as $filename => $info): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($filename) ?></code></td>
                                <td><?= $info['size_formatted'] ?></td>
                                <td><?= $info['modified'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Build Actions -->
        <div class="grid">
            <div class="card">
                <h3>🏗️ Build Actions</h3>
                
                <form method="post" style="margin: 10px 0;">
                    <input type="hidden" name="action" value="build_all">
                    <button type="submit" class="success">🚀 Full Build (CSS + JS + Components)</button>
                </form>
                
                <form method="post" style="margin: 10px 0;">
                    <input type="hidden" name="action" value="build_css">
                    <button type="submit">🎨 Build CSS Only</button>
                </form>
                
                <form method="post" style="margin: 10px 0;">
                    <input type="hidden" name="action" value="build_js">
                    <button type="submit">⚡ Build JavaScript Only</button>
                </form>
                
                <form method="post" style="margin: 10px 0;">
                    <input type="hidden" name="action" value="build_components">
                    <button type="submit">🧩 Build Components Only</button>
                </form>
            </div>
            
            <div class="card">
                <h3>🧹 Maintenance</h3>
                
                <form method="post" style="margin: 10px 0;">
                    <input type="hidden" name="action" value="cleanup">
                    <button type="submit">🗑️ Cleanup Old Assets</button>
                </form>
                
                <?php if ($laravelBootstrapped): ?>
                    <form method="post" style="margin: 10px 0;">
                        <input type="hidden" name="action" value="laravel_build">
                        <button type="submit">🔥 Laravel CDN Build</button>
                    </form>
                    <div class="status success">✅ Laravel integration available</div>
                <?php else: ?>
                    <div class="status warning">⚠️ Laravel integration unavailable</div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Source Files -->
        <div class="card">
            <h3>📁 Source Files</h3>
            <div class="file-tree">
                <?php
                $sourceFiles = [
                    'CSS Files' => [
                        'resources/css/webbloc-core.css',
                        'resources/css/webbloc-components.css'
                    ],
                    'JavaScript Files' => [
                        'resources/js/webbloc-core.js',
                        'resources/js/webbloc-components.js'
                    ],
                    'Component Files' => [
                        'resources/css/components/',
                        'resources/js/components/'
                    ]
                ];
                
                foreach ($sourceFiles as $category => $files):
                ?>
                    <strong><?= $category ?>:</strong><br>
                    <?php foreach ($files as $file): ?>
                        <?php 
                        $fullPath = $builder->getBasePath() . '/' . $file;
                        $exists = file_exists($fullPath);
                        $size = $exists ? filesize($fullPath) : 0;
                        ?>
                        <?= $exists ? '✓' : '✗' ?> <?= $file ?>
                        <?php if ($exists): ?>
                            (<?= $builder->formatBytes($size) ?>)
                        <?php endif; ?>
                        <br>
                    <?php endforeach; ?>
                    <br>
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php } ?>
</div>

</body>
</html>