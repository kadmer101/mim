# INSTALLATION_GUIDE.md

# Mim Platform - Streamlined Installation Guide

*Optimized setup for Laravel SaaS application with 85 PHP files*

---

## 🔧 Prerequisites

### System Requirements

- **XAMPP** with PHP 8.2+ (Apache, MySQL, phpMyAdmin)
- **Composer** 2.0+
- **Node.js** 18+ with npm
- **Git** for version control
- **Web browser** (Chrome, Firefox, Safari)

### XAMPP Configuration

# Start XAMPP services
sudo /opt/lampp/lampp start

# Or on Windows via XAMPP Control Panel:
# - Start Apache
# - Start MySQL
PHP Configuration (php.ini):

memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 32M
post_max_size = 32M
extension=curl
extension=fileinfo
extension=gd
extension=mbstring
extension=openssl
extension=pdo_mysql
extension=tokenizer
extension=xml

---

📦 Installation Steps

Step 1: Create Laravel Project with Breeze
# Create new Laravel project
composer create-project laravel/laravel mim-platform

# Navigate to project
cd mim-platform

# Install Laravel Breeze
composer require laravel/breeze --dev

# Install Breeze with Blade stack
php artisan breeze:install blade

# Install and compile frontend assets
npm install && npm run build

Step 2: Install Required Packages
# Essential packages for Mim platform
composer require spatie/laravel-permission \
    laravel/cashier \
    guzzlehttp/guzzle \
    spatie/laravel-activitylog \
    laravel/sanctum

# Development packages
composer require --dev laravel/telescope \
    barryvdh/laravel-debugbar

Step 3: Environment Configuration
#  environment file
cp .env.example .env

# Generate application key
php artisan key:generate
Configure .env file:

# Application
APP_NAME="Mim Platform"
APP_ENV=local
APP_KEY=base64:generated_key_here
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mim_platform
DB_USERNAME=root
DB_PASSWORD=

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls

# Binance API
BINANCE_API_KEY=your_binance_api_key
BINANCE_SECRET_KEY=your_binance_secret_key
BINANCE_TESTNET=true

# Platform Settings
SUBSCRIPTION_PRICE=99
REWARD_AMOUNT=999
PLATFORM_CURRENCY=USDT

# Queue
QUEUE_CONNECTION=database

Step 4: Database Setup
# Create database in phpMyAdmin or MySQL
mysql -u root -p -e "CREATE DATABASE mim_platform;"

# Run core migrations
php artisan migrate

# Install Telescope (optional for debugging)
php artisan telescope:install

Step 5: Create Custom Configuration Files
Create config/binance.php:
php artisan make:config binance

Create config/reputation.php:
php artisan make:config reputation

Create config/mim.php:
php artisan make:config mim

Step 6: Generate Application Structure
# Create core migrations (20 tables)
php artisan make:migration create_categories_table
php artisan make:migration create_challenges_table
php artisan make:migration create_challenge_responses_table
php artisan make:migration create_challenge_votes_table
php artisan make:migration create_user_subscriptions_table
php artisan make:migration create_wallet_addresses_table
php artisan make:migration create_payment_transactions_table
php artisan make:migration create_reward_claims_table
php artisan make:migration create_reputation_points_table
php artisan make:migration create_achievement_badges_table
php artisan make:migration create_user_achievement_badges_table
php artisan make:migration create_pages_table
php artisan make:migration create_settings_table

# Create models with relationships
php artisan make:model Challenge -cr
php artisan make:model ChallengeResponse -cr
php artisan make:model Category -cr
php artisan make:model UserSubscription -c
php artisan make:model WalletAddress -c
php artisan make:model PaymentTransaction -c
php artisan make:model RewardClaim -c
php artisan make:model ReputationPoint -c
php artisan make:model AchievementBadge -c
php artisan make:model Page -c
php artisan make:model Setting -c

# Create controllers
php artisan make:controller Admin/AdminController
php artisan make:controller SubscriptionController
php artisan make:controller PaymentController
php artisan make:controller LeaderboardController

# Create services
php artisan make:class Services/PaymentService
php artisan make:class Services/ReputationService
php artisan make:class Services/ChallengeService
php artisan make:class Services/AnalyticsService

# Create jobs for background processing
php artisan make:job ProcessPaymentJob
php artisan make:job SendRewardJob
php artisan make:job UpdateReputationJob
php artisan make:job SyncBinanceJob

# Create middleware
php artisan make:middleware CheckSubscription
php artisan make:middleware EnsureUserRole
php artisan make:middleware LocalizationMiddleware

# Create form requests
php artisan make:request StoreChallengeRequest
php artisan make:request ProcessPaymentRequest
php artisan make:request UpdateProfileRequest

# Create notifications
php artisan make:notification ChallengeNotification
php artisan make:notification PaymentNotification
php artisan make:notification SystemNotification

# Create mail templates
php artisan make:mail ChallengeNotification --markdown=emails.challenge.notification
php artisan make:mail PaymentNotification --markdown=emails.payment.notification

Step 7: Database Seeding
# Create seeders
php artisan make:seeder AdminUserSeeder
php artisan make:seeder CategoriesSeeder
php artisan make:seeder AchievementBadgesSeeder
php artisan make:seeder SettingsSeeder
php artisan make:seeder PagesSeeder

# Run seeders
php artisan db:seed

Step 8: Asset Compilation and Optimization
# Install frontend dependencies
npm install

# Add Tailwind CSS and Alpine.js
npm install -D tailwindcss @tailwindcss/forms @tailwindcss/typography
npm install alpinejs

# Initialize Tailwind
npx tailwindcss init -p

# Build assets for development
npm run dev

# Build assets for production
npm run build

Step 9: Permissions and Storage
# Create storage link
php artisan storage:link

# Set permissions (Linux/Mac)
chmod -R 755 storage bootstrap/cache

# Install Spatie permissions
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate

Step 10: Final Setup and Testing
# Clear and cache configurations
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Optimize for production (optional)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create application key if not done
php artisan key:generate

# Test the installation
php artisan serve

---

🌐 Local Development
Start Development Server
# Method 1: Laravel built-in server
php artisan serve
# Access: http://localhost:8000

# Method 2: XAMPP virtual host
# Add to /opt/lampp/etc/httpd.conf or httpd-vhosts.conf
<VirtualHost *:80>
    DocumentRoot "/path/to/mim-platform/public"
    ServerName mim.local
    <Directory "/path/to/mim-platform/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>

# Add to /etc/hosts (Linux/Mac) or C:\Windows\System32\drivers\etc\hosts (Windows)
127.0.0.1 mim.local
Queue Processing
# Start queue worker
php artisan queue:work

# Or use supervisor in production
php artisan queue:restart
Scheduled Tasks
# Add to crontab for production
* * * * * cd /path/to/mim-platform && php artisan schedule:run >> /dev/null 2>&1

---

🚀 Production Deployment
Pre-deployment Checklist
Environment Configuration:
# Production .env settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database credentials
DB_HOST=your_production_host
DB_DATABASE=your_production_db
DB_USERNAME=your_production_user
DB_PASSWORD=secure_password

# Mail service (use production SMTP)
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host

# Binance API (production keys)
BINANCE_TESTNET=false
Security Setup:
# Generate secure app key
php artisan key:generate

# Set proper permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 777 storage bootstrap/cache
Deployment via FTP/cPanel (Non-SSH Servers)
Build Locally:
# Optimize for production
composer install --no-dev --optimize-autoloader
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
Upload Files:
Upload entire project to server
Point domain to /public directory
Create .htaccess in public folder:
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
Database Setup:

Create database via cPanel
Run migrations via web-based tool or import SQL
Final Steps:

# Via web-based terminal or create a setup script
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
Alternative: Deployment Script
Create deploy.php in project root:

<?php
// Simple deployment script for non-SSH servers
if ($_GET['deploy'] === 'secret_key_here') {
    echo "Starting deployment...\n";
    
    // Run essential commands
    exec('php artisan migrate --force');
    exec('php artisan config:cache');
    exec('php artisan route:cache');
    exec('php artisan view:cache');
    exec('php artisan storage:link');
    
    echo "Deployment completed!\n";
}
?>

---

🔧 Configuration Files Overview
Essential Custom Configs
config/binance.php:

API endpoints and credentials
Transaction limits and fees
config/reputation.php:

Point calculation rules
Badge requirements and rewards
config/mim.php:

Platform-specific settings
Feature toggles and limits

---

🧪 Testing Installation
Verify Core Features
Authentication:

Register new user: /register
Login: /login
Password reset: /forgot-password
Core Functionality:

Challenge submission: /challenges/create
Payment processing: /subscription
Admin panel: /admin
API Endpoints:

Test API authentication: /api/user
Challenge API: /api/challenges
Common Issues and Solutions
Issue: 500 Internal Server Error

# Check logs
tail -f storage/logs/laravel.log

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
Issue: Database Connection Failed

Verify database credentials in .env
Ensure MySQL service is running
Check database exists
Issue: Assets Not Loading

# Rebuild assets
npm run build

# Check public/build directory exists
# Verify APP_URL in .env matches your domain

---

📚 Development Workflow
Daily Development
# Start services
php artisan serve
npm run dev

# Watch for changes
npm run dev -- --watch

# Queue worker
php artisan queue:work
Code Quality
# Run tests
php artisan test

# Code formatting (if installed)
./vendor/bin/pint

# Static analysis (if installed)
./vendor/bin/phpstan analyse
This streamlined installation guide provides all essential steps to get the Mim platform running efficiently on both localhost XAMPP and production servers without SSH access.
