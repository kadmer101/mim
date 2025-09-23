<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Website;
use App\Models\WebBloc;
use App\Models\WebBlocInstance;
use App\Services\DatabaseConnectionService;
use App\Jobs\BuildWebBlocCdnFiles;
use Illuminate\Support\Facades\DB;

class InstallWebBloc extends Command
{
    protected $signature = 'webbloc:install 
                           {type : WebBloc type to install (auth, comments, reviews, notifications)}
                           {--websites= : Comma-separated website IDs or "all" for all websites}
                           {--settings= : JSON settings for the WebBloc installation}
                           {--force : Force reinstallation if already exists}
                           {--rebuild-cdn : Rebuild CDN files after installation}';

    protected $description = 'Install WebBloc components to websites';

    protected $databaseService;

    public function __construct(DatabaseConnectionService $databaseService)
    {
        parent::__construct();
        $this->databaseService = $databaseService;
    }

    public function handle()
    {
        $type = $this->argument('type');
        $websitesInput = $this->option('websites');
        $settings = $this->option('settings');
        
        // Validate WebBloc type
        $webBloc = WebBloc::where('type', $type)->where('status', 'active')->first();
        if (!$webBloc) {
            $this->error("WebBloc type '{$type}' not found or inactive.");
            return 1;
        }

        // Parse settings
        $installationSettings = [];
        if ($settings) {
            $installationSettings = json_decode($settings, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Invalid JSON in settings parameter.');
                return 1;
            }
        }

        // Get target websites
        $websites = $this->getTargetWebsites($websitesInput);
        if ($websites->isEmpty()) {
            $this->warn('No websites found for installation.');
            return 1;
        }

        $this->info("Installing {$webBloc->type} WebBloc to {$websites->count()} website(s)...");
        
        $progressBar = $this->output->createProgressBar($websites->count());
        $progressBar->start();

        $success = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($websites as $website) {
            try {
                $result = $this->installWebBlocToWebsite($webBloc, $website, $installationSettings);
                
                switch ($result) {
                    case 'installed':
                        $success++;
                        break;
                    case 'updated':
                        $success++;
                        break;
                    case 'skipped':
                        $skipped++;
                        break;
                }
            } catch (\Exception $e) {
                $this->error("\nFailed to install WebBloc to website {$website->id} ({$website->domain}): " . $e->getMessage());
                $failed++;
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display results
        $this->displayResults($success, $failed, $skipped);

        // Rebuild CDN if requested
        if ($this->option('rebuild-cdn')) {
            $this->info('Rebuilding CDN files...');
            BuildWebBlocCdnFiles::dispatch(null, [$type], true);
            $this->info('✅ CDN rebuild queued');
        }

        return $failed > 0 ? 1 : 0;
    }

    protected function installWebBlocToWebsite(WebBloc $webBloc, Website $website, array $settings = [])
    {
        // Check if already installed
        $existingInstance = WebBlocInstance::where('website_id', $website->id)
                                         ->where('webbloc_id', $webBloc->id)
                                         ->first();

        if ($existingInstance && !$this->option('force')) {
            return 'skipped';
        }

        DB::beginTransaction();
        try {
            // Ensure website database exists
            if (!$this->databaseService->databaseExists($website->id)) {
                $this->databaseService->createDatabase($website->id);
            }

            // Create or update WebBloc instance
            if ($existingInstance) {
                $existingInstance->update([
                    'version' => $webBloc->version,
                    'settings' => array_merge($existingInstance->settings ?? [], $settings),
                    'status' => 'active',
                    'updated_at' => now(),
                ]);
                $instance = $existingInstance;
                $action = 'updated';
            } else {
                $instance = WebBlocInstance::create([
                    'website_id' => $website->id,
                    'webbloc_id' => $webBloc->id,
                    'version' => $webBloc->version,
                    'settings' => $this->getDefaultSettings($webBloc->type, $settings),
                    'status' => 'active',
                ]);
                $action = 'installed';
            }

            // Install component-specific data structures
            $this->installComponentSpecificStructures($webBloc, $website);

            DB::commit();

            // Send notification
            $website->owner?->notify(new \App\Notifications\WebBlocInstalled(
                $website, 
                $webBloc, 
                $instance, 
                $action === 'updated'
            ));

            return $action;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function installComponentSpecificStructures(WebBloc $webBloc, Website $website)
    {
        $connection = $this->databaseService->getConnection($website->id);

        switch ($webBloc->type) {
            case 'comments':
                $this->installCommentsStructure($connection);
                break;
            case 'reviews':
                $this->installReviewsStructure($connection);
                break;
            case 'auth':
                $this->installAuthStructure($connection);
                break;
            case 'notifications':
                $this->installNotificationsStructure($connection);
                break;
        }
    }

    protected function installCommentsStructure($connection)
    {
        // Create comments-specific indexes and triggers
        $connection->statement("
            CREATE INDEX IF NOT EXISTS idx_web_blocs_comments 
            ON web_blocs(webbloc_type, page_url) 
            WHERE webbloc_type = 'comment'
        ");
        
        $connection->statement("
            CREATE INDEX IF NOT EXISTS idx_web_blocs_comment_replies 
            ON web_blocs(parent_id) 
            WHERE webbloc_type = 'comment' AND parent_id IS NOT NULL
        ");
    }

    protected function installReviewsStructure($connection)
    {
        // Create reviews-specific indexes
        $connection->statement("
            CREATE INDEX IF NOT EXISTS idx_web_blocs_reviews 
            ON web_blocs(webbloc_type, page_url) 
            WHERE webbloc_type = 'review'
        ");
        
        $connection->statement("
            CREATE INDEX IF NOT EXISTS idx_web_blocs_review_ratings 
            ON web_blocs(webbloc_type, JSON_EXTRACT(data, '$.rating')) 
            WHERE webbloc_type = 'review'
        ");
    }

    protected function installAuthStructure($connection)
    {
        // Ensure users table has auth-specific fields
        $connection->statement("
            ALTER TABLE users ADD COLUMN IF NOT EXISTS 
            social_provider VARCHAR(50)
        ");
        
        $connection->statement("
            ALTER TABLE users ADD COLUMN IF NOT EXISTS 
            social_id VARCHAR(255)
        ");
        
        $connection->statement("
            CREATE INDEX IF NOT EXISTS idx_users_social 
            ON users(social_provider, social_id)
        ");
    }

    protected function installNotificationsStructure($connection)
    {
        // Create notifications table if it doesn't exist
        $connection->statement("
            CREATE TABLE IF NOT EXISTS notifications (
                id VARCHAR(36) PRIMARY KEY,
                type VARCHAR(255) NOT NULL,
                notifiable_id INTEGER NOT NULL,
                notifiable_type VARCHAR(255) NOT NULL,
                data JSON NOT NULL,
                read_at DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $connection->statement("
            CREATE INDEX IF NOT EXISTS idx_notifications_user 
            ON notifications(notifiable_id, notifiable_type)
        ");
    }

    protected function getDefaultSettings(string $type, array $customSettings = []): array
    {
        $defaults = match ($type) {
            'comments' => [
                'allow_guest' => false,
                'require_approval' => true,
                'max_depth' => 3,
                'enable_reactions' => true,
                'enable_mentions' => true,
                'spam_protection' => true,
            ],
            'reviews' => [
                'require_purchase_verification' => false,
                'allow_anonymous' => false,
                'rating_scale' => 5,
                'allow_media' => true,
                'require_review_text' => true,
                'auto_approve' => false,
            ],
            'auth' => [
                'registration_enabled' => true,
                'email_verification' => true,
                'social_login' => [],
                'password_requirements' => [
                    'min_length' => 8,
                    'require_uppercase' => true,
                    'require_numbers' => true,
                    'require_symbols' => false,
                ],
            ],
            'notifications' => [
                'position' => 'top-right',
                'auto_dismiss' => true,
                'dismiss_delay' => 5000,
                'sound_enabled' => false,
                'max_visible' => 5,
            ],
            default => [],
        };

        return array_merge($defaults, $customSettings);
    }

    protected function getTargetWebsites($websitesInput)
    {
        if (!$websitesInput) {
            $websitesInput = $this->ask('Enter website IDs (comma-separated) or "all"', 'all');
        }

        if (strtolower($websitesInput) === 'all') {
            return Website::where('status', 'active')->get();
        }

        $websiteIds = array_map('trim', explode(',', $websitesInput));
        return Website::whereIn('id', $websiteIds)->get();
    }

    protected function displayResults($success, $failed, $skipped)
    {
        if ($success > 0) {
            $this->info("✅ Successfully processed {$success} installation(s)");
        }
        
        if ($skipped > 0) {
            $this->warn("⚠️  Skipped {$skipped} installation(s) (already exists, use --force to reinstall)");
        }
        
        if ($failed > 0) {
            $this->error("❌ Failed {$failed} installation(s)");
        }

        if ($success > 0) {
            $this->info('💡 Next steps:');
            $this->line('- Update your website integration code');
            $this->line('- Configure component settings in the dashboard');
            $this->line('- Test the component functionality');
        }
    }
}
