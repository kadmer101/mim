<?php
/**
 * WebBloc Migration Script
 * Handles database migrations without SSH access
 * 
 * Usage: https://yourdomain.com/deploy/migrate.php
 */

// Security check - require basic auth or IP whitelist
$allowedIps = ['127.0.0.1', '::1']; // Add your IPs here
$requireAuth = true; // Set to false to disable auth
$username = 'webbloc_admin'; // Change this
$password = 'secure_migration_password_2024'; // Change this

// IP Check
$clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'];
$isAllowedIp = in_array($clientIp, $allowedIps) || $clientIp === '127.0.0.1';

// Basic Auth Check
$isAuthenticated = false;
if ($requireAuth && !$isAllowedIp) {
    if (!isset($_SERVER['PHP_AUTH_USER']) || 
        $_SERVER['PHP_AUTH_USER'] !== $username || 
        $_SERVER['PHP_AUTH_PW'] !== $password) {
        header('WWW-Authenticate: Basic realm="WebBloc Migration"');
        header('HTTP/1.0 401 Unauthorized');
        die('Authentication required for migration access.');
    }
    $isAuthenticated = true;
}

// Bootstrap Laravel
require_once __DIR__ . '/../../vendor/autoload.php';

if (!file_exists(__DIR__ . '/../../bootstrap/app.php')) {
    die('Laravel application not found. Please ensure WebBloc is properly installed.');
}

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

ini_set('max_execution_time', 300);
ini_set('memory_limit', '512M');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebBloc Database Migration</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 20px; background: #f8fafc; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        h1 { color: #1e40af; text-align: center; margin-bottom: 30px; }
        .status { padding: 15px; border-radius: 6px; margin: 15px 0; }
        .success { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .error { background: #fef2f2; color: #b91c1c; border: 1px solid #ef4444; }
        .warning { background: #fef3c7; color: #92400e; border: 1px solid #f59e0b; }
        .info { background: #eff6ff; color: #1e40af; border: 1px solid #3b82f6; }
        .log { background: #1f2937; color: #f9fafb; padding: 20px; border-radius: 6px; font-family: 'Courier New', monospace; font-size: 14px; max-height: 500px; overflow-y: auto; white-space: pre-wrap; }
        .button-group { text-align: center; margin: 20px 0; }
        button { background: #2563eb; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; margin: 0 10px; }
        button:hover { background: #1d4ed8; }
        button.danger { background: #dc2626; }
        button.danger:hover { background: #b91c1c; }
        button.success { background: #16a34a; }
        button.success:hover { background: #15803d; }
        button:disabled { background: #9ca3af; cursor: not-allowed; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
        .card { padding: 20px; border: 1px solid #e5e7eb; border-radius: 6px; background: #f9fafb; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .badge.up-to-date { background: #d1fae5; color: #065f46; }
        .badge.pending { background: #fef3c7; color: #92400e; }
        .badge.error { background: #fef2f2; color: #b91c1c; }
    </style>
</head>
<body>

<div class="container">
    <h1>🛠️ WebBloc Database Migration</h1>
    
    <?php
    
    function executeArtisan($command, $description = '') {
        try {
            ob_start();
            \Illuminate\Support\Facades\Artisan::call($command);
            $output = \Illuminate\Support\Facades\Artisan::output();
            ob_end_clean();
            
            return [
                'success' => true,
                'output' => $output,
                'command' => $command,
                'description' => $description
            ];
        } catch (Exception $e) {
            ob_end_clean();
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'command' => $command,
                'description' => $description
            ];
        }
    }
    
    function getMigrationStatus() {
        try {
            // Get pending migrations
            ob_start();
            \Illuminate\Support\Facades\Artisan::call('migrate:status');
            $output = \Illuminate\Support\Facades\Artisan::output();
            ob_end_clean();
            
            $lines = explode("\n", trim($output));
            $migrations = [];
            $inTable = false;
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (strpos($line, '|') === 0 && strpos($line, 'Migration name') !== false) {
                    $inTable = true;
                    continue;
                }
                
                if ($inTable && strpos($line, '|') === 0 && !empty(trim($line, '|+-'))) {
                    $parts = array_map('trim', explode('|', trim($line, '|')));
                    if (count($parts) >= 2) {
                        $migrations[] = [
                            'name' => $parts[1] ?? 'Unknown',
                            'status' => isset($parts[0]) && $parts[0] === 'Y' ? 'ran' : 'pending'
                        ];
                    }
                }
            }
            
            return $migrations;
        } catch (Exception $e) {
            return [];
        }
    }
    
    function getDatabaseInfo() {
        try {
            $default = config('database.default');
            $config = config("database.connections.{$default}");
            
            return [
                'driver' => $config['driver'] ?? 'unknown',
                'database' => $config['database'] ?? 'unknown',
                'host' => $config['host'] ?? 'unknown',
                'connection' => $default
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    function getWebsiteDatabases() {
        try {
            $websites = \App\Models\Website::all();
            $databases = [];
            
            foreach ($websites as $website) {
                $dbPath = storage_path("databases/website_{$website->id}.sqlite");
                $databases[] = [
                    'website_id' => $website->id,
                    'name' => $website->name,
                    'domain' => $website->domain,
                    'database_path' => $dbPath,
                    'exists' => file_exists($dbPath),
                    'size' => file_exists($dbPath) ? filesize($dbPath) : 0
                ];
            }
            
            return $databases;
        } catch (Exception $e) {
            return [];
        }
    }
    
    // Handle Actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        echo '<div class="log">';
        
        switch ($action) {
            case 'migrate':
                echo "Starting database migration...\n\n";
                $result = executeArtisan('migrate --force', 'Running pending migrations');
                echo $result['output'] ?? $result['error'] ?? 'No output';
                
                if ($result['success']) {
                    echo "\n\n✅ Migration completed successfully!";
                } else {
                    echo "\n\n❌ Migration failed: " . ($result['error'] ?? 'Unknown error');
                }
                break;
                
            case 'rollback':
                $steps = intval($_POST['steps'] ?? 1);
                echo "Rolling back last {$steps} migration batch(es)...\n\n";
                $result = executeArtisan("migrate:rollback --step={$steps} --force", "Rolling back migrations");
                echo $result['output'] ?? $result['error'] ?? 'No output';
                break;
                
            case 'fresh':
                if (($_POST['confirm_fresh'] ?? '') === 'yes') {
                    echo "🚨 DROPPING ALL TABLES AND RE-MIGRATING...\n\n";
                    $result = executeArtisan('migrate:fresh --force', 'Fresh migration (drops all tables)');
                    echo $result['output'] ?? $result['error'] ?? 'No output';
                    
                    if ($result['success'] && isset($_POST['seed_fresh'])) {
                        echo "\n\nSeeding database...\n";
                        $seedResult = executeArtisan('db:seed --force', 'Seeding database');
                        echo $seedResult['output'] ?? $seedResult['error'] ?? 'No seeding output';
                    }
                } else {
                    echo "❌ Fresh migration cancelled - confirmation required.";
                }
                break;
                
            case 'create_website_dbs':
                echo "Creating SQLite databases for all websites...\n\n";
                $result = executeArtisan('website:create-database --all --migrate', 'Creating website databases');
                echo $result['output'] ?? $result['error'] ?? 'No output';
                break;
                
            case 'seed':
                echo "Seeding database with default data...\n\n";
                $result = executeArtisan('db:seed --force', 'Seeding database');
                echo $result['output'] ?? $result['error'] ?? 'No output';
                break;
                
            case 'optimize':
                echo "Optimizing database and clearing caches...\n\n";
                $commands = [
                    'config:clear' => 'Clearing config cache',
                    'config:cache' => 'Caching configuration',
                    'route:clear' => 'Clearing route cache', 
                    'route:cache' => 'Caching routes',
                    'view:clear' => 'Clearing view cache',
                    'view:cache' => 'Caching views'
                ];
                
                foreach ($commands as $cmd => $desc) {
                    echo "Running: {$desc}\n";
                    $result = executeArtisan($cmd, $desc);
                    echo ($result['success'] ? '✅' : '❌') . " {$desc}\n";
                }
                echo "\n✅ Optimization complete!";
                break;
        }
        
        echo '</div>';
        echo '<div class="button-group"><button onclick="location.reload()">🔄 Refresh Page</button></div>';
    } else {
        
        // Display current status
        $dbInfo = getDatabaseInfo();
        $migrations = getMigrationStatus();
        $websiteDbs = getWebsiteDatabases();
        $pendingCount = array_reduce($migrations, function($count, $m) { return $count + ($m['status'] === 'pending' ? 1 : 0); }, 0);
        
        ?>
        
        <!-- Database Status -->
        <div class="card">
            <h3>📊 Database Status</h3>
            <?php if (isset($dbInfo['error'])): ?>
                <div class="status error">❌ Database connection error: <?= htmlspecialchars($dbInfo['error']) ?></div>
            <?php else: ?>
                <p><strong>Driver:</strong> <?= htmlspecialchars($dbInfo['driver']) ?></p>
                <p><strong>Database:</strong> <?= htmlspecialchars($dbInfo['database']) ?></p>
                <p><strong>Host:</strong> <?= htmlspecialchars($dbInfo['host']) ?></p>
                <p><strong>Connection:</strong> <?= htmlspecialchars($dbInfo['connection']) ?></p>
            <?php endif; ?>
        </div>
        
        <!-- Migration Status -->
        <div class="card">
            <h3>🔄 Migration Status</h3>
            <?php if ($pendingCount > 0): ?>
                <div class="status warning">
                    ⚠️ <strong><?= $pendingCount ?> pending migrations</strong> found. Database update needed.
                </div>
            <?php else: ?>
                <div class="status success">✅ All migrations are up to date</div>
            <?php endif; ?>
            
            <?php if (!empty($migrations)): ?>
                <details style="margin-top: 15px;">
                    <summary>View Migration Details (<?= count($migrations) ?> total)</summary>
                    <div style="max-height: 200px; overflow-y: auto; margin-top: 10px;">
                        <?php foreach (array_slice($migrations, -10) as $migration): ?>
                            <div style="padding: 5px 0; border-bottom: 1px solid #eee;">
                                <span class="badge <?= $migration['status'] === 'ran' ? 'up-to-date' : 'pending' ?>">
                                    <?= strtoupper($migration['status']) ?>
                                </span>
                                <?= htmlspecialchars($migration['name']) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </details>
            <?php endif; ?>
        </div>
        
        <!-- Website Databases Status -->
        <div class="card">
            <h3>🗄️ Website Databases</h3>
            <?php if (empty($websiteDbs)): ?>
                <p>No websites found or error accessing website data.</p>
            <?php else: ?>
                <p><strong><?= count($websiteDbs) ?></strong> website(s) registered</p>
                <?php
                $existingDbs = array_filter($websiteDbs, fn($db) => $db['exists']);
                $totalSize = array_sum(array_column($existingDbs, 'size'));
                ?>
                <p><strong><?= count($existingDbs) ?></strong> SQLite databases exist (<?= number_format($totalSize / 1024) ?> KB total)</p>
                
                <details>
                    <summary>View Website Database Details</summary>
                    <div style="max-height: 200px; overflow-y: auto; margin-top: 10px;">
                        <?php foreach ($websiteDbs as $db): ?>
                            <div style="padding: 5px 0; border-bottom: 1px solid #eee; font-size: 14px;">
                                <span class="badge <?= $db['exists'] ? 'up-to-date' : 'error' ?>">
                                    <?= $db['exists'] ? 'EXISTS' : 'MISSING' ?>
                                </span>
                                <strong><?= htmlspecialchars($db['domain']) ?></strong>
                                <?php if ($db['exists']): ?>
                                    (<?= number_format($db['size'] / 1024, 1) ?> KB)
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </details>
            <?php endif; ?>
        </div>
        
        <!-- Migration Actions -->
        <div class="card">
            <h3>⚡ Migration Actions</h3>
            
            <form method="post" style="margin: 10px 0;">
                <input type="hidden" name="action" value="migrate">
                <button type="submit" class="success" <?= $pendingCount === 0 ? 'disabled title="No pending migrations"' : '' ?>>
                    🚀 Run Migrations <?= $pendingCount > 0 ? "($pendingCount pending)" : '' ?>
                </button>
            </form>
            
            <form method="post" style="margin: 10px 0;">
                <input type="hidden" name="action" value="rollback">
                <label>Steps to rollback: <input type="number" name="steps" value="1" min="1" max="10" style="width: 60px;"></label>
                <button type="submit" class="danger">↩️ Rollback Migration</button>
            </form>
            
            <form method="post" style="margin: 10px 0;">
                <input type="hidden" name="action" value="create_website_dbs">
                <button type="submit">🗄️ Create Website Databases</button>
            </form>
            
            <form method="post" style="margin: 10px 0;">
                <input type="hidden" name="action" value="seed">
                <button type="submit">🌱 Seed Database</button>
            </form>
            
            <form method="post" style="margin: 10px 0;">
                <input type="hidden" name="action" value="optimize">
                <button type="submit">⚡ Optimize & Cache</button>
            </form>
        </div>
        
        <!-- Dangerous Actions -->
        <div class="card" style="border-color: #ef4444;">
            <h3 style="color: #dc2626;">🚨 Dangerous Actions</h3>
            <div class="status error">
                <strong>Warning:</strong> The action below will DELETE ALL DATA and recreate the database.
            </div>
            
            <form method="post" style="margin: 10px 0;" onsubmit="return confirm('⚠️ This will DELETE ALL DATA! Are you absolutely sure?');">
                <input type="hidden" name="action" value="fresh">
                <label>
                    <input type="checkbox" name="confirm_fresh" value="yes" required>
                    I understand this will delete all data
                </label><br><br>
                <label>
                    <input type="checkbox" name="seed_fresh" value="yes">
                    Also seed with default data after fresh migration
                </label><br><br>
                <button type="submit" class="danger">💥 Fresh Migration (Delete All)</button>
            </form>
        </div>
        
        <?php } ?>
</div>

<script>
// Auto-refresh page every 30 seconds if migrations are running
if (document.querySelector('.log')) {
    let countdown = 10;
    const refreshBtn = document.querySelector('button[onclick="location.reload()"]');
    if (refreshBtn) {
        const originalText = refreshBtn.textContent;
        const interval = setInterval(() => {
            refreshBtn.textContent = `${originalText} (${countdown})`;
            countdown--;
            if (countdown <= 0) {
                location.reload();
            }
        }, 1000);
    }
}
</script>

</body>
</html>