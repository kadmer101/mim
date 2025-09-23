<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Website;
use App\Models\ApiKey;
use App\Services\ApiKeyService;
use Illuminate\Support\Str;

class GenerateApiKeys extends Command
{
    protected $signature = 'apikey:generate 
                           {--website-id= : Website ID to generate API key for}
                           {--all : Generate API keys for all websites without active keys}
                           {--regenerate : Regenerate existing API keys}
                           {--expires= : Expiration date (Y-m-d format) or days from now}
                           {--rate-limit= : Rate limit per hour}
                           {--permissions= : Comma-separated permissions}
                           {--domains= : Comma-separated allowed domains}';

    protected $description = 'Generate API keys for websites';

    protected $apiKeyService;

    public function __construct(ApiKeyService $apiKeyService)
    {
        parent::__construct();
        $this->apiKeyService = $apiKeyService;
    }

    public function handle()
    {
        if ($this->option('all')) {
            return $this->generateForAllWebsites();
        }

        $websiteId = $this->option('website-id');
        if (!$websiteId) {
            $websiteId = $this->ask('Enter Website ID');
        }

        $website = Website::find($websiteId);
        if (!$website) {
            $this->error("Website not found: {$websiteId}");
            return 1;
        }

        return $this->generateApiKeyForWebsite($website);
    }

    protected function generateForAllWebsites()
    {
        $websites = Website::where('status', 'active')
                           ->whereDoesntHave('api_keys', function ($query) {
                               $query->where('status', 'active');
                           })
                           ->get();

        if ($websites->isEmpty()) {
            $this->info('All active websites already have API keys.');
            return 0;
        }

        $this->info("Generating API keys for {$websites->count()} websites...");
        
        $progressBar = $this->output->createProgressBar($websites->count());
        $progressBar->start();

        $success = 0;
        $failed = 0;

        foreach ($websites as $website) {
            try {
                $this->generateApiKeyForWebsite($website, false);
                $success++;
            } catch (\Exception $e) {
                $this->error("\nFailed to generate API key for website {$website->id}: " . $e->getMessage());
                $failed++;
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("✅ Successfully generated {$success} API keys");
        if ($failed > 0) {
            $this->error("❌ Failed to generate {$failed} API keys");
        }

        return $failed > 0 ? 1 : 0;
    }

    protected function generateApiKeyForWebsite(Website $website, $verbose = true)
    {
        if ($verbose) {
            $this->info("Generating API key for: {$website->name} ({$website->domain})");
        }

        // Check if active API key exists
        $existingKey = $website->api_keys()->where('status', 'active')->first();
        
        if ($existingKey && !$this->option('regenerate')) {
            if ($verbose) {
                $this->warn('Active API key already exists. Use --regenerate to create a new one.');
                $this->displayApiKeyInfo($existingKey);
            }
            return 1;
        }

        try {
            // Prepare API key data
            $keyData = $this->prepareApiKeyData($website);
            
            if ($existingKey && $this->option('regenerate')) {
                // Deactivate existing key
                $existingKey->update(['status' => 'inactive']);
                
                if ($verbose) {
                    $this->warn('Previous API key deactivated');
                }
            }

            // Generate new API key
            $apiKey = $this->apiKeyService->generateApiKey($website, $keyData);

            if ($verbose) {
                $this->info('✅ API key generated successfully');
                $this->displayApiKeyInfo($apiKey);
            }

            // Send notification
            $website->owner?->notify(new \App\Notifications\ApiKeyGenerated(
                $apiKey, 
                $website, 
                (bool) $existingKey
            ));

            return 0;

        } catch (\Exception $e) {
            if ($verbose) {
                $this->error('❌ Failed to generate API key: ' . $e->getMessage());
            }
            throw $e;
        }
    }

    protected function prepareApiKeyData(Website $website): array
    {
        $data = [
            'name' => "API Key for {$website->name}",
        ];

        // Handle expiration
        if ($expires = $this->option('expires')) {
            if (is_numeric($expires)) {
                $data['expires_at'] = now()->addDays((int) $expires);
            } else {
                try {
                    $data['expires_at'] = \Carbon\Carbon::parse($expires);
                } catch (\Exception $e) {
                    $this->warn("Invalid expiration date format: {$expires}. Using no expiration.");
                }
            }
        }

        // Handle rate limit
        if ($rateLimit = $this->option('rate-limit')) {
            $data['rate_limit'] = (int) $rateLimit;
        } else {
            $data['rate_limit'] = config('webbloc.api.default_rate_limit', 1000);
        }

        // Handle permissions
        if ($permissions = $this->option('permissions')) {
            $data['permissions'] = array_map('trim', explode(',', $permissions));
        } else {
            $data['permissions'] = $this->getDefaultPermissions();
        }

        // Handle allowed domains
        if ($domains = $this->option('domains')) {
            $data['allowed_domains'] = array_map('trim', explode(',', $domains));
        } else {
            $data['allowed_domains'] = [$website->domain];
        }

        return $data;
    }

    protected function getDefaultPermissions(): array
    {
        return [
            'webbloc.read',
            'webbloc.create',
            'webbloc.update',
            'webbloc.delete',
            'auth.login',
            'auth.register',
            'auth.profile',
        ];
    }

    protected function displayApiKeyInfo(ApiKey $apiKey)
    {
        $this->info('📊 API Key Information:');
        $this->table(
            ['Property', 'Value'],
            [
                ['ID', $apiKey->id],
                ['Key', $apiKey->key],
                ['Name', $apiKey->name],
                ['Status', $apiKey->status],
                ['Rate Limit', $apiKey->rate_limit . ' requests/hour'],
                ['Expires', $apiKey->expires_at ? $apiKey->expires_at->format('Y-m-d H:i:s') : 'Never'],
                ['Allowed Domains', implode(', ', $apiKey->allowed_domains ?? ['Any'])],
                ['Permissions', implode(', ', $apiKey->permissions ?? ['All'])],
                ['Created', $apiKey->created_at->format('Y-m-d H:i:s')],
            ]
        );

        $this->info('🔗 Integration Code:');
        $this->line('<script src="' . config('webbloc.cdn.base_url') . '/webbloc.min.js" data-api-key="' . $apiKey->key . '"></script>');
    }
}