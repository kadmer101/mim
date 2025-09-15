# INSTALLATION_GUIDE.md

# Mim Platform - Installation Guide
*Step-by-step setup instructions for localhost XAMPP and production deployment*

---

## 🔧 Prerequisites

### System Requirements
- **XAMPP** with PHP 8.2 or higher
- **Composer** 2.0 or higher
- **Node.js** 18+ and **npm** for asset compilation
- **Git** for version control
- **Binance API credentials** for payment integration
- **Pusher account** for real-time features (optional)

### XAMPP Configuration
1. Download and install XAMPP with PHP 8.2
2. Start Apache and MySQL services
3. Verify PHP version in terminal:

php -v
Enable required PHP extensions in php.ini:
Copyextension=curl
extension=fileinfo
extension=gd
extension=mbstring
extension=openssl
extension=pdo_mysql
extension=tokenizer
extension=xml
extension=zip
extension=intl
extension=bcmath
📦 Laravel Installation
Step 1: Install Laravel with Breeze
Copy# Navigate to your development directory (e.g., htdocs)
cd /c/xampp/htdocs

# Create new Laravel project
composer create-project laravel/laravel mim-platform

# Navigate to project directory
cd mim-platform

# Install Laravel Breeze
composer require laravel/breeze --dev

# Install Breeze with Blade stack
php artisan breeze:install blade

# Install and compile assets
npm install && npm run dev
Step 2: Database Setup
Copy# Create database in phpMyAdmin or MySQL command line
mysql -u root -p
CREATE DATABASE mim_platform;
exit

# Run migrations
php artisan migrate
🛠️ Required Third-party Packages
Core Dependencies
Copy# Subscription management
composer require laravel/cashier

# Real-time features
composer require pusher/pusher-php-server
composer require laravel/echo

# Image processing
composer require intervention/image

# Search functionality
composer require laravel/scout
composer require algolia/algoliasearch-client-php

# API rate limiting
composer require spatie/laravel-rate-limited-job-middleware

# Permission management
composer require spatie/laravel-permission

# Activity logging
composer require spatie/laravel-activitylog

# Cryptocurrency integration
composer require binance/binance-connector-php

# Multi-language support
composer require spatie/laravel-translatable

# Settings management
composer require spatie/laravel-settings

# Media library
composer require spatie/laravel-medialibrary

# Backup functionality
composer require spatie/laravel-backup
Development Dependencies
Copy# Code formatting
composer require laravel/pint --dev

# Testing helpers
composer require pestphp/pest --dev
composer require pestphp/pest-plugin-laravel --dev

# Debugging
composer require laravel/telescope --dev

# IDE Helper
composer require barryvdh/laravel-ide-helper --dev
⚙️ Environment Configuration
Step 1: Environment Variables
Create or update .env file:

APP_NAME="Mim Platform"
APP_ENV=local
APP_KEY=base64:your-app-key-here
APP_DEBUG=true
APP_URL=http://localhost/mim-platform

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mim_platform
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=pusher
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Pusher Configuration
PUSHER_APP_ID=your-pusher-app-id
PUSHER_APP_KEY=your-pusher-key
PUSHER_APP_SECRET=your-pusher-secret
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

# Binance API Configuration
BINANCE_API_KEY=your-binance-api-key
BINANCE_SECRET_KEY=your-binance-secret-key
BINANCE_TESTNET=true

# Subscription Settings
MONTHLY_SUBSCRIPTION_AMOUNT=99
REWARD_AMOUNT=999
SUBSCRIPTION_CURRENCY=USDT

# Platform Settings
DEFAULT_CHALLENGE_DEBATE_DAYS=7
DEFAULT_EXPERT_REVIEW_DAYS=14
GRACE_PERIOD_DAYS=7
MAX_DAILY_SUBMISSIONS=5

# Search Configuration (Algolia)
ALGOLIA_APP_ID=your-algolia-app-id
ALGOLIA_SECRET=your-algolia-secret

# File Upload Limits
MAX_UPLOAD_SIZE=10240
ALLOWED_FILE_TYPES=jpg,jpeg,png,pdf,doc,docx

# Rate Limiting
API_RATE_LIMIT=60
CHALLENGE_SUBMISSION_LIMIT=5
COMMENT_RATE_LIMIT=30
Step 2: Generate Application Key
Copyphp artisan key:generate
🗄️ Database Configuration
Step 1: Run Core Migrations
Copy# Run default Laravel migrations
php artisan migrate

# Create custom migrations for platform
php artisan make:migration create_challenges_table
php artisan make:migration create_challenge_responses_table
php artisan make:migration create_user_subscriptions_table
php artisan make:migration create_reputation_points_table
php artisan make:migration create_achievement_badges_table
php artisan make:migration create_wallet_addresses_table
php artisan make:migration create_payment_transactions_table
php artisan make:migration create_challenge_votes_table
php artisan make:migration create_categories_table
php artisan make:migration create_notifications_table

# Run all migrations
php artisan migrate
Step 2: Install Package Migrations
Copy# Install Telescope (development only)
php artisan telescope:install

# Install permission tables
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Install activity log tables
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"

# Install media library tables
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider"

# Run package migrations
php artisan migrate
Step 3: Seed Database
Copy# Create seeders
php artisan make:seeder UserRolesSeeder
php artisan make:seeder CategoriesSeeder
php artisan make:seeder AdminUserSeeder
php artisan make:seeder DemoDataSeeder

# Run seeders
php artisan db:seed
🔐 Authentication & Authorization Setup
Step 1: Configure Laravel Breeze
Copy# Publish Breeze views (if customization needed)
php artisan vendor:publish --tag=laravel-breeze-views

# Configure two-factor authentication
composer require pragmarx/google2fa-laravel
php artisan vendor:publish --provider="PragmaRX\Google2FALaravel\ServiceProvider"
Step 2: Setup Roles and Permissions
Copy# Publish permission config
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="config"

# Create permissions seeder
php artisan make:seeder PermissionsSeeder

# Run permissions seeder
php artisan db:seed --class=PermissionsSeeder
💰 Payment Integration Setup
Step 1: Binance API Configuration
Copy# Install Binance PHP SDK
composer require binance/binance-connector-php

# Create Binance service provider
php artisan make:provider BinanceServiceProvider

# Create payment handling commands
php artisan make:command ProcessSubscriptionPayments
php artisan make:command ProcessRewardPayouts
Step 2: Cashier Configuration
Copy# Publish Cashier config
php artisan vendor:publish --tag="cashier-config"

# Create subscription management commands
php artisan make:command CheckExpiredSubscriptions
php artisan make:command SendPaymentReminders
🎮 Gamification System Setup
Step 1: Reputation System
Copy# Create reputation management commands
php artisan make:command CalculateReputationPoints
php artisan make:command UpdateLeaderboards
php artisan make:command AssignAchievementBadges

# Create reputation events
php artisan make:event ReputationPointsAwarded
php artisan make:event AchievementBadgeEarned
Step 2: Leaderboard Configuration
Copy# Create leaderboard update command
php artisan make:command UpdateDailyLeaderboards

# Schedule in app/Console/Kernel.php
🔍 Search Configuration
Step 1: Laravel Scout Setup
Copy# Publish Scout config
php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"

# Create searchable indexes
php artisan scout:import "App\Models\Challenge"
php artisan scout:import "App\Models\ChallengeResponse"
📡 Real-time Features Setup
Step 1: Broadcasting Configuration
Copy# Publish broadcasting config
php artisan vendor:publish --provider="Illuminate\Broadcasting\BroadcastServiceProvider"

# Create event classes
php artisan make:event ChallengeSubmitted
php artisan make:event ChallengeResolved
php artisan make:event NewResponsePosted
php artisan make:event ReputationUpdated

# Create notification classes
php artisan make:notification ChallengeStatusChanged
php artisan make:notification PaymentProcessed
php artisan make:notification AchievementEarned
📁 File Storage Configuration
Step 1: Storage Setup
Copy# Create storage link
php artisan storage:link

# Create storage directories
mkdir storage/app/public/challenges
mkdir storage/app/public/avatars
mkdir storage/app/public/documents
mkdir storage/app/public/exports

# Set permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache
Step 2: Media Library Setup
Copy# Publish media library config
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="config"
🌐 Multi-language Support
Step 1: Translation Setup
Copy# Create language files
mkdir resources/lang/ar
mkdir resources/lang/fr

# Create translation keys
php artisan make:command GenerateTranslationKeys

# Publish language files
php artisan vendor:publish --tag=laravel-translatable-migrations
🎨 Frontend Asset Compilation
Step 1: Install Frontend Dependencies
Copy# Install Node dependencies
npm install

# Install additional packages
npm install @tailwindcss/forms
npm install @tailwindcss/typography
npm install @alpinejs/persist
npm install axios
npm install laravel-echo pusher-js
npm install chart.js
npm install sweetalert2
Step 2: Configure Build Process
Copy# Development build
npm run dev

# Production build
npm run build

# Watch for changes (development)
npm run dev -- --watch
⚡ Queue Configuration
Step 1: Queue Setup
Copy# Create queue jobs
php artisan make:job ProcessChallengeSubmission
php artisan make:job SendNotificationEmail
php artisan make:job UpdateUserReputation
php artisan make:job ProcessPaymentWebhook

# Create failed jobs table
php artisan queue:failed-table
php artisan migrate

# Start queue worker (development)
php artisan queue:work --tries=3
🔄 Scheduled Tasks
Step 1: Task Scheduler Setup
Add to app/Console/Kernel.php:

Copy# Create scheduled commands
php artisan make:command DailyMaintenanceTask
php artisan make:command WeeklyReports
php artisan make:command MonthlyAnalytics
For Windows XAMPP, create batch file scheduler.bat:

@echo off
cd /d "C:\xampp\htdocs\mim-platform"
php artisan schedule:run
🧪 Testing Setup
Step 1: Configure Testing Environment
Copy# Create test database
mysql -u root -p
CREATE DATABASE mim_platform_test;
exit

# Configure phpunit.xml
cp phpunit.xml phpunit.xml.backup

# Create test environment file
cp .env .env.testing
Step 2: Create Test Files
Copy# Create feature tests
php artisan make:test ChallengeSubmissionTest
php artisan make:test UserAuthenticationTest
php artisan make:test PaymentProcessingTest
php artisan make:test ReputationSystemTest

# Create unit tests
php artisan make:test --unit ChallengeModelTest
php artisan make:test --unit UserModelTest
php artisan make:test --unit PaymentServiceTest

# Run tests
php artisan test
📊 Analytics & Monitoring
Step 1: Telescope Setup (Development)
Copy# Install Telescope
php artisan telescope:install
php artisan migrate

# Publish Telescope assets
php artisan telescope:publish
Step 2: Activity Logging
Copy# Publish activity log config
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="config"
🛡️ Security Configuration
Step 1: Security Headers
Copy# Create security middleware
php artisan make:middleware SecurityHeaders
php artisan make:middleware RateLimitMiddleware
php artisan make:middleware ValidateSubscription
Step 2: Additional Security
Copy# Install additional security packages
composer require spatie/laravel-csp
composer require spatie/laravel-honeypot

# Publish CSP config
php artisan vendor:publish --provider="Spatie\Csp\CspServiceProvider"
🚀 Production Deployment Preparation
Step 1: Optimize for Production
Copy# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Generate optimized autoloader
composer install --optimize-autoloader --no-dev

# Build production assets
npm run build
Step 2: Create Deployment Package
Copy# Create deployment directory
mkdir ../mim-platform-deploy

# Copy necessary files (exclude development files)
rsync -av --exclude='.git' --exclude='.env' --exclude='node_modules' --exclude='tests' --exclude='.phpunit.result.cache' . ../mim-platform-deploy/

# Create .env.production template
cp .env .env.production
Step 3: Production Environment Setup
Create deploy.md with production-specific instructions:

Upload files via FTP/cPanel File Manager
Create production database
Update .env file with production settings
Set proper file permissions
Configure cron jobs for scheduled tasks
🔧 Artisan Commands Summary
Essential Setup Commands
Copy# Initial setup
composer create-project laravel/laravel mim-platform
cd mim-platform
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install && npm run dev

# Database setup
php artisan migrate
php artisan db:seed

# Generate keys and links
php artisan key:generate
php artisan storage:link

# Install packages
composer require laravel/cashier spatie/laravel-permission spatie/laravel-activitylog intervention/image laravel/scout pusher/pusher-php-server binance/binance-connector-php

# Publish configurations
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Laravel\Cashier\CashierServiceProvider"
php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"

# Development tools
composer require laravel/telescope --dev
php artisan telescope:install

# Production optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
npm run build
Daily Development Commands
Copy# Start development server
php artisan serve

# Watch assets
npm run dev -- --watch

# Run queue worker
php artisan queue:work

# Run tests
php artisan test

# Clear caches (development)
php artisan cache:clear && php artisan config:clear && php artisan route:clear
✅ Installation Verification
Step 1: Basic Functionality Check
Visit http://localhost/mim-platform - Homepage loads
Register new account - Registration works
Login with credentials - Authentication works
Submit test challenge - Challenge system works
Check admin dashboard - Admin features accessible
Step 2: Integration Testing
Test payment integration (sandbox mode)
Verify real-time notifications
Test file upload functionality
Check search functionality
Verify email notifications
Step 3: Performance Check
Run php artisan optimize
Test page load speeds
Check database query performance
Verify asset compilation
Test queue processing
🆘 Troubleshooting
Common Issues
Permission Errors: Run chmod -R 775 storage bootstrap/cache
Database Connection: Check XAMPP MySQL service and .env settings
Composer Errors: Run composer dump-autoload
Node Errors: Delete node_modules and run npm install
Queue Not Processing: Start with php artisan queue:work
Log Locations
Application logs: storage/logs/laravel.log
Web server logs: C:\xampp\apache\logs\error.log
MySQL logs: C:\xampp\mysql\data\mysql_error.log
Complete this installation guide step-by-step to ensure proper platform setup. Each command should be executed in sequence for optimal results.