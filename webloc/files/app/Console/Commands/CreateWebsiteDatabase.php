<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Website;
use App\Services\DatabaseConnectionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CreateWebsiteDatabase extends Command
{
    protected $signature = 'website:create-database 
                           {website? : Website ID or domain}
                           {--all : Create databases for all websites}
                           {--force : Force recreation of existing databases}
                           {--migrate : Run migrations after creation}
                           {--seed : Run seeders after creation}';

    protected $description = 'Create SQLite databases for websites';

    protected $databaseService;

    public function __construct(DatabaseConnectionService $databaseService)
    {
        parent::__construct();
        $this->databaseService = $databaseService;
    }

    public function handle()
    {
        if ($this->option('all')) {
            return $this->createAllDatabases();
        }

        $website = $this->getWebsite();
        if (!$website) {
            return 1;
        }

        return $this->createWebsiteDatabase($website);
    }

    protected function createAllDatabases()
    {
        $websites = Website::all();
        
        if ($websites->isEmpty()) {
            $this->warn('No websites found.');
            return 1;
        }

        $this->info("Creating databases for {$websites->count()} websites...");
        
        $progressBar = $this->output->createProgressBar($websites->count());
        $progressBar->start();

        $success = 0;
        $failed = 0;

        foreach ($websites as $website) {
            try {
                $this->createWebsiteDatabase($website, false);
                $success++;
            } catch (\Exception $e) {
                $this->error("\nFailed to create database for website {$website->id} ({$website->domain}): " . $e->getMessage());
                $failed++;
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        
        $this->newLine(2);
        $this->info("✅ Successfully created {$success} databases");
        
        if ($failed > 0) {
            $this->error("❌ Failed to create {$failed} databases");
        }

        return $failed > 0 ? 1 : 0;
    }

    protected function createWebsiteDatabase(Website $website, $verbose = true)
    {
        if ($verbose) {
            $this->info("Creating database for website: {$website->name} ({$website->domain})");
        }

        $databasePath = $this->databaseService->getDatabasePath($website->id);
        
        // Check if database already exists
        if (File::exists($databasePath) && !$this->option('force')) {
            if ($verbose) {
                $this->warn('Database already exists. Use --force to recreate.');
            }
            return 1;
        }

        try {
            // Create database
            $this->databaseService->createDatabase($website->id);
            
            if ($verbose) {
                $this->info('✅ Database created successfully');
            }

            // Run migrations if requested
            if ($this->option('migrate')) {
                $this->runMigrations($website, $verbose);
            }

            // Run seeders if requested
            if ($this->option('seed')) {
                $this->runSeeders($website, $verbose);
            }

            // Update website database path
            $website->update(['database_path' => $databasePath]);

            if ($verbose) {
                $this->displayDatabaseInfo($website);
            }

            return 0;

        } catch (\Exception $e) {
            if ($verbose) {
                $this->error('❌ Failed to create database: ' . $e->getMessage());
            }
            throw $e;
        }
    }

    protected function runMigrations(Website $website, $verbose = true)
    {
        if ($verbose) {
            $this->info('Running migrations...');
        }

        try {
            // Get SQLite connection for this website
            $connection = $this->databaseService->getConnection($website->id);
            
            // Create users table
            $connection->statement("
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR(255) NOT NULL,
                    email VARCHAR(255) UNIQUE NOT NULL,
                    email_verified_at DATETIME,
                    password VARCHAR(255),
                    remember_token VARCHAR(100),
                    avatar TEXT,
                    metadata JSON,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");

            // Create web_blocs table
            $connection->statement("
                CREATE TABLE IF NOT EXISTS web_blocs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    webbloc_type VARCHAR(50) NOT NULL,
                    user_id INTEGER,
                    page_url VARCHAR(500) NOT NULL,
                    data JSON NOT NULL,
                    metadata JSON,
                    status VARCHAR(20) DEFAULT 'active',
                    parent_id INTEGER,
                    sort_order INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id),
                    FOREIGN KEY (parent_id) REFERENCES web_blocs(id)
                )
            ");

            // Create indexes for performance
            $this->createIndexes($connection);

            if ($verbose) {
                $this->info('✅ Migrations completed');
            }

        } catch (\Exception $e) {
            if ($verbose) {
                $this->error('❌ Migration failed: ' . $e->getMessage());
            }
            throw $e;
        }
    }

    protected function createIndexes($connection)
    {
        $indexes = [
            "CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)",
            "CREATE INDEX IF NOT EXISTS idx_web_blocs_type ON web_blocs(webbloc_type)",
            "CREATE INDEX IF NOT EXISTS idx_web_blocs_user_id ON web_blocs(user_id)",
            "CREATE INDEX IF NOT EXISTS idx_web_blocs_page_url ON web_blocs(page_url)",
            "CREATE INDEX IF NOT EXISTS idx_web_blocs_status ON web_blocs(status)",
            "CREATE INDEX IF NOT EXISTS idx_web_blocs_parent_id ON web_blocs(parent_id)",
            "CREATE INDEX IF NOT EXISTS idx_web_blocs_created_at ON web_blocs(created_at)",
        ];

        foreach ($indexes as $index) {
            $connection->statement($index);
        }
    }

    protected function runSeeders(Website $website, $verbose = true)
    {
        if ($verbose) {
            $this->info('Running seeders...');
        }

        try {
            $connection = $this->databaseService->getConnection($website->id);

            // Create sample admin user if none exists
            $userCount = $connection->select("SELECT COUNT(*) as count FROM users")[0]->count;
            
            if ($userCount == 0) {
                $connection->insert("
                    INSERT INTO users (name, email, password, email_verified_at, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ", [
                    'Website Admin',
                    'admin@' . $website->domain,
                    bcrypt('password'),
                    now(),
                    now(),
                    now()
                ]);

                if ($verbose) {
                    $this->info('✅ Sample admin user created');
                }
            }

        } catch (\Exception $e) {
            if ($verbose) {
                $this->error('❌ Seeding failed: ' . $e->getMessage());
            }
            throw $e;
        }
    }

    protected function getWebsite()
    {
        $websiteInput = $this->argument('website');
        
        if (!$websiteInput) {
            $websiteInput = $this->ask('Enter website ID or domain');
        }

        // Try to find by ID first, then by domain
        $website = Website::find($websiteInput) ?? Website::where('domain', $websiteInput)->first();

        if (!$website) {
            $this->error("Website not found: {$websiteInput}");
            return null;
        }

        return $website;
    }

    protected function displayDatabaseInfo(Website $website)
    {
        $databasePath = $this->databaseService->getDatabasePath($website->id);
        $fileSize = File::exists($databasePath) ? File::size($databasePath) : 0;
        
        $this->info('📊 Database Information:');
        $this->table(
            ['Property', 'Value'],
            [
                ['Website ID', $website->id],
                ['Website Name', $website->name],
                ['Domain', $website->domain],
                ['Database Path', $databasePath],
                ['File Size', $this->formatBytes($fileSize)],
                ['Created', now()->format('Y-m-d H:i:s')],
            ]
        );
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}