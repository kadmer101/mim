<?php
/**
 * WebBloc Auto-Installation Script
 * No SSH access required - executes via HTTP
 * 
 * Usage: https://yourdomain.com/deploy/install-webbloc.php
 * Security: Auto-deletes after successful installation
 */

// Security: Only run if not already installed
if (file_exists(__DIR__ . '/../../vendor/laravel/framework/src/Illuminate/Foundation/Application.php') && 
    file_exists(__DIR__ . '/../../.env')) {
    die('WebBloc already installed. For security, this installer is disabled.');
}

ini_set('max_execution_time', 300); // 5 minutes
ini_set('memory_limit', '512M');

$output = [];
$errors = [];

function logOutput($message, $isError = false) {
    global $output, $errors;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message";
    
    if ($isError) {
        $errors[] = $logMessage;
    } else {
        $output[] = $logMessage;
    }
    
    echo $logMessage . "\n";
    flush();
}

function executeCommand($command, $description = null) {
    if ($description) {
        logOutput("Starting: $description");
    }
    
    logOutput("Executing: $command");
    
    $output = [];
    $return_var = 0;
    exec($command . ' 2>&1', $output, $return_var);
    
    foreach ($output as $line) {
        logOutput("  $line");
    }
    
    if ($return_var !== 0) {
        logOutput("ERROR: Command failed with return code $return_var", true);
        return false;
    }
    
    logOutput("SUCCESS: Command completed");
    return true;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebBloc Installation</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2563eb; text-align: center; margin-bottom: 30px; }
        .status { padding: 15px; border-radius: 5px; margin: 10px 0; }
        .success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
        .error { background: #fef2f2; color: #b91c1c; border-left: 4px solid #ef4444; }
        .info { background: #eff6ff; color: #1e40af; border-left: 4px solid #3b82f6; }
        .log { background: #f8fafc; padding: 20px; border-radius: 5px; font-family: monospace; font-size: 14px; max-height: 400px; overflow-y: auto; white-space: pre-wrap; }
        .progress { width: 100%; height: 6px; background: #e5e7eb; border-radius: 3px; margin: 10px 0; }
        .progress-bar { height: 100%; background: #3b82f6; border-radius: 3px; transition: width 0.3s; }
        button { background: #2563eb; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #1d4ed8; }
        button:disabled { background: #9ca3af; cursor: not-allowed; }
    </style>
</head>
<body>

<div class="container">
    <h1>🚀 WebBloc Installation</h1>
    
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install'])): ?>
        
        <div class="info">
            <strong>Installation Started:</strong> This process may take 3-5 minutes. Please do not close this page.
        </div>
        
        <div class="progress">
            <div class="progress-bar" id="progressBar" style="width: 0%"></div>
        </div>
        
        <div class="log" id="logOutput">
        <?php
        
        // Step 1: Environment Setup
        logOutput("=== WebBloc Installation Process Started ===");
        logOutput("Checking system requirements...");
        
        $phpVersion = phpversion();
        if (version_compare($phpVersion, '8.1', '<')) {
            logOutput("ERROR: PHP 8.1+ required. Current version: $phpVersion", true);
        } else {
            logOutput("✓ PHP version check passed: $phpVersion");
        }
        
        // Check if Composer is available
        $composerPath = trim(shell_exec('which composer') ?: shell_exec('where composer'));
        if (empty($composerPath)) {
            $composerPath = 'php composer.phar';
        } else {
            $composerPath = 'composer';
        }
        
        logOutput("✓ Composer detected: $composerPath");
        
        echo "<script>document.getElementById('progressBar').style.width = '10%';</script>";
        flush();
        
        // Step 2: Install Dependencies
        logOutput("\n=== Installing Dependencies ===");
        if (!executeCommand("$composerPath install --no-dev --optimize-autoloader", "Installing Composer dependencies")) {
            logOutput("Installation failed during dependency installation", true);
            goto installation_complete;
        }
        
        echo "<script>document.getElementById('progressBar').style.width = '30%';</script>";
        flush();
        
        // Step 3: Environment Configuration
        logOutput("\n=== Environment Configuration ===");
        if (!file_exists('.env') && file_exists('.env.example')) {
            if (copy('.env.example', '.env')) {
                logOutput("✓ Environment file created from example");
            } else {
                logOutput("ERROR: Could not create .env file", true);
            }
        }
        
        // Generate application key
        if (!executeCommand('php artisan key:generate --force', "Generating application key")) {
            logOutput("Warning: Could not generate application key", true);
        }
        
        echo "<script>document.getElementById('progressBar').style.width = '40%';</script>";
        flush();
        
        // Step 4: Database Setup
        logOutput("\n=== Database Setup ===");
        if (!executeCommand('php artisan migrate:fresh --force', "Running database migrations")) {
            logOutput("ERROR: Database migration failed", true);
        } else {
            logOutput("✓ Database migrations completed");
        }
        
        echo "<script>document.getElementById('progressBar').style.width = '60%';</script>";
        flush();
        
        // Step 5: Seed Default Data
        logOutput("\n=== Seeding Default Data ===");
        if (!executeCommand('php artisan db:seed --force', "Seeding database with default data")) {
            logOutput("Warning: Database seeding had issues", true);
        } else {
            logOutput("✓ Default data seeded successfully");
        }
        
        echo "<script>document.getElementById('progressBar').style.width = '70%';</script>";
        flush();
        
        // Step 6: Storage & Permissions
        logOutput("\n=== Setting up Storage and Permissions ===");
        
        // Create storage link
        if (!executeCommand('php artisan storage:link', "Creating storage symbolic link")) {
            logOutput("Warning: Could not create storage link", true);
        }
        
        // Create necessary directories
        $directories = [
            'storage/databases',
            'public/cdn',
            'storage/app/public/uploads',
            'storage/app/backups',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views'
        ];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                if (mkdir($dir, 0755, true)) {
                    logOutput("✓ Created directory: $dir");
                } else {
                    logOutput("Warning: Could not create directory: $dir", true);
                }
            }
        }
        
        // Set permissions (if on Unix-like system)
        if (DIRECTORY_SEPARATOR === '/') {
            executeCommand('chmod -R 755 storage bootstrap/cache public/cdn', "Setting directory permissions");
            executeCommand('chmod -R 775 storage/databases', "Setting database directory permissions");
        }
        
        echo "<script>document.getElementById('progressBar').style.width = '80%';</script>";
        flush();
        
        // Step 7: Install Default WebBlocs
        logOutput("\n=== Installing Default WebBlocs ===");
        
        $webblocs = ['auth', 'comments', 'reviews', 'notifications'];
        foreach ($webblocs as $webbloc) {
            if (!executeCommand("php artisan webbloc:install $webbloc --force", "Installing $webbloc WebBloc")) {
                logOutput("Warning: Could not install $webbloc WebBloc", true);
            }
        }
        
        echo "<script>document.getElementById('progressBar').style.width = '90%';</script>";
        flush();
        
        // Step 8: Build CDN Assets
        logOutput("\n=== Building CDN Assets ===");
        if (!executeCommand('php artisan webbloc:build-cdn', "Building WebBloc CDN files")) {
            logOutput("Warning: CDN build had issues", true);
        }
        
        // Step 9: Cache Optimization
        logOutput("\n=== Optimizing Application ===");
        executeCommand('php artisan config:cache', "Caching configuration");
        executeCommand('php artisan route:cache', "Caching routes");
        executeCommand('php artisan view:cache', "Caching views");
        
        echo "<script>document.getElementById('progressBar').style.width = '100%';</script>";
        flush();
        
        logOutput("\n=== Installation Complete ===");
        logOutput("✓ WebBloc has been successfully installed!");
        logOutput("✓ You can now access your WebBloc dashboard");
        logOutput("⚠️  Don't forget to:");
        logOutput("   1. Update your .env file with proper database credentials");
        logOutput("   2. Configure your web server to serve the application");
        logOutput("   3. Set up SSL certificate for production");
        logOutput("   4. Create your first admin user via: php artisan make:admin");
        logOutput("   5. Delete this installer file for security");
        
        installation_complete:
        
        ?>
        </div>
        
        <?php if (empty($errors)): ?>
            <div class="success">
                <strong>🎉 Installation Successful!</strong><br>
                WebBloc has been installed successfully. You can now access your dashboard.<br>
                <strong>Important:</strong> Delete this installer file for security.
            </div>
            
            <script>
                setTimeout(() => {
                    if (confirm('Installation complete! Delete this installer file for security?')) {
                        fetch('<?= $_SERVER['PHP_SELF'] ?>?delete=1')
                            .then(() => alert('Installer deleted successfully!'))
                            .catch(() => alert('Please manually delete this file: <?= __FILE__ ?>'));
                    }
                }, 2000);
            </script>
        <?php else: ?>
            <div class="error">
                <strong>❌ Installation had errors:</strong><br>
                Please check the log above and resolve any issues before proceeding.
            </div>
        <?php endif; ?>
        
    <?php elseif (isset($_GET['delete'])): ?>
        <?php
        if (unlink(__FILE__)) {
            echo json_encode(['status' => 'success', 'message' => 'Installer deleted']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Could not delete installer']);
        }
        exit;
        ?>
        
    <?php else: ?>
        
        <div class="info">
            <strong>Welcome to WebBloc Installation!</strong><br>
            This will install and configure WebBloc on your server. The process includes:
        </div>
        
        <ul>
            <li>✅ Install PHP dependencies via Composer</li>
            <li>✅ Configure environment and generate security keys</li>
            <li>✅ Set up database and run migrations</li>
            <li>✅ Seed default data and WebBloc components</li>
            <li>✅ Configure storage and set permissions</li>
            <li>✅ Install default WebBlocs (auth, comments, reviews, notifications)</li>
            <li>✅ Build and optimize CDN assets</li>
            <li>✅ Cache configuration for production</li>
        </ul>
        
        <div class="info">
            <strong>Requirements:</strong><br>
            • PHP 8.1+ with required extensions<br>
            • Composer installed<br>
            • Write permissions on storage and cache directories<br>
            • Database connection configured in .env file
        </div>
        
        <form method="post">
            <button type="submit" name="install">🚀 Start Installation</button>
        </form>
        
    <?php endif; ?>
    
</div>

<script>
// Auto-scroll log output
const logOutput = document.getElementById('logOutput');
if (logOutput) {
    const observer = new MutationObserver(() => {
        logOutput.scrollTop = logOutput.scrollHeight;
    });
    observer.observe(logOutput, { childList: true, subtree: true });
}
</script>

</body>
</html>