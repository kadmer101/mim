<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class DatabaseConnectionService
{
    /**
     * Connect to a website's SQLite database
     */
    public function connectToWebsite(int $websiteId): void
    {
        $databasePath = $this->getDatabasePath($websiteId);

        // Ensure database exists
        if (!$this->databaseExists($websiteId)) {
            $this->createDatabase($websiteId);
        }

        // Configure connection
        $this->configureSqliteConnection($websiteId, $databasePath);
    }

    /**
     * Check if a website's database exists
     */
    public function databaseExists(int $websiteId): bool
    {
        $databasePath = $this->getDatabasePath($websiteId);
        return File::exists($databasePath);
    }

    /**
     * Create a new SQLite database for a website
     */
    public function createDatabase(int $websiteId): bool
    {
        try {
            $databasePath = $this->getDatabasePath($websiteId);
            $directory = dirname($databasePath);

            // Ensure directory exists
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            // Create empty SQLite file
            File::put($databasePath, '');

            // Configure temporary connection for migration
            $this->configureSqliteConnection($websiteId, $databasePath, "sqlite_temp_{$websiteId}");

            // Run SQLite migrations
            $this->runSqliteMigrations("sqlite_temp_{$websiteId}");

            // Optimize database
            $this->optimizeDatabase("sqlite_temp_{$websiteId}");

            // Clean up temporary connection
            DB::purge("sqlite_temp_{$websiteId}");

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to create SQLite database', [
                'website_id' => $websiteId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    /**
     * Delete a website's database
     */
    public function deleteDatabase(int $websiteId): bool
    {
        try {
            $databasePath = $this->getDatabasePath($websiteId);
            
            // Disconnect any existing connections
            $this->disconnectWebsite($websiteId);

            // Delete database file
            if (File::exists($databasePath)) {
                File::delete($databasePath);
            }

            // Delete any WAL and SHM files
            $walPath = $databasePath . '-wal';
            $shmPath = $databasePath . '-shm';
            
            if (File::exists($walPath)) {
                File::delete($walPath);
            }
            
            if (File::exists($shmPath)) {
                File::delete($shmPath);
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to delete SQLite database', [
                'website_id' => $websiteId,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Backup a website's database
     */
    public function backupDatabase(int $websiteId): ?string
    {
        try {
            $sourcePath = $this->getDatabasePath($websiteId);
            $backupPath = $this->getBackupPath($websiteId);

            if (!File::exists($sourcePath)) {
                return null;
            }

            // Ensure backup directory exists
            $backupDir = dirname($backupPath);
            if (!File::exists($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }

            // Copy database file
            File::copy($sourcePath, $backupPath);

            return $backupPath;

        } catch (\Exception $e) {
            Log::error('Failed to backup SQLite database', [
                'website_id' => $websiteId,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Restore a website's database from backup
     */
    public function restoreDatabase(int $websiteId, string $backupPath): bool
    {
        try {
            $targetPath = $this->getDatabasePath($websiteId);

            if (!File::exists($backupPath)) {
                return false;
            }

            // Disconnect any existing connections
            $this->disconnectWebsite($websiteId);

            // Restore from backup
            File::copy($backupPath, $targetPath);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to restore SQLite database', [
                'website_id' => $websiteId,
                'backup_path' => $backupPath,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Get database size in bytes
     */
    public function getDatabaseSize(int $websiteId): int
    {
        $databasePath = $this->getDatabasePath($websiteId);
        
        return File::exists($databasePath) ? File::size($databasePath) : 0;
    }

    /**
     * Get database statistics
     */
    public function getDatabaseStats(int $websiteId): array
    {
        try {
            $this->connectToWebsite($websiteId);

            $stats = [
                'size_bytes' => $this->getDatabaseSize($websiteId),
                'tables' => [],
                'total_records' => 0,
                'created_at' => null,
                'last_modified' => null
            ];

            $databasePath = $this->getDatabasePath($websiteId);
            
            if (File::exists($databasePath)) {
                $stats['created_at'] = date('Y-m-d H:i:s', File::lastModified($databasePath));
                $stats['last_modified'] = date('Y-m-d H:i:s', File::lastModified($databasePath));
            }

            // Get table statistics
            $tables = ['users', 'web_blocs'];
            
            foreach ($tables as $table) {
                try {
                    $count = DB::connection('sqlite_website')->table($table)->count();
                    $stats['tables'][$table] = $count;
                    $stats['total_records'] += $count;
                } catch (\Exception $e) {
                    $stats['tables'][$table] = 0;
                }
            }

            return $stats;

        } catch (\Exception $e) {
            Log::error('Failed to get database statistics', [
                'website_id' => $websiteId,
                'error' => $e->getMessage()
            ]);

            return [
                'size_bytes' => 0,
                'tables' => [],
                'total_records' => 0,
                'created_at' => null,
                'last_modified' => null
            ];
        }
    }

    /**
     * Vacuum database to reclaim space
     */
    public function vacuumDatabase(int $websiteId): bool
    {
        try {
            $this->connectToWebsite($websiteId);

            DB::connection('sqlite_website')->statement('VACUUM');
            DB::connection('sqlite_website')->statement('PRAGMA optimize');

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to vacuum SQLite database', [
                'website_id' => $websiteId,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Disconnect from website database
     */
    public function disconnectWebsite(int $websiteId): void
    {
        try {
            DB::purge('sqlite_website');
            DB::purge("sqlite_temp_{$websiteId}");
        } catch (\Exception $e) {
            // Ignore disconnection errors
        }
    }

    /**
     * Get database path for website
     */
    public function getDatabasePath(int $websiteId): string
    {
        $storagePath = storage_path('databases');
        return $storagePath . "/website_{$websiteId}.sqlite";
    }

    /**
     * Get database connection for a specific website
     */
    public function getConnection(int $websiteId)
    {
        try {
            $databasePath = $this->getDatabasePath($websiteId);
            
            // Ensure database exists
            if (!$this->databaseExists($websiteId)) {
                $this->createDatabase($websiteId);
            }
            
            // Configure connection if not already configured
            $connectionName = "website_{$websiteId}";
            
            if (!array_key_exists($connectionName, config('database.connections'))) {
                $this->configureSqliteConnection($websiteId, $databasePath, $connectionName);
            }
            
            return DB::connection($connectionName);
            
        } catch (\Exception $e) {
            Log::error('Failed to get database connection', [
                'website_id' => $websiteId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get the default website connection (for backward compatibility)
     */
    public function getDefaultConnection()
    {
        return DB::connection('sqlite_website');
    }

    /**
     * Get backup path for website
     */
    private function getBackupPath(int $websiteId): string
    {
        $backupPath = storage_path('backups/databases');
        $timestamp = date('Y-m-d_H-i-s');
        return $backupPath . "/website_{$websiteId}_{$timestamp}.sqlite";
    }

    /**
     * Configure SQLite connection
     */
    private function configureSqliteConnection(int $websiteId, string $databasePath, string $connectionName = 'sqlite_website'): void
    {
        Config::set("database.connections.{$connectionName}", [
            'driver' => 'sqlite',
            'database' => $databasePath,
            'prefix' => '',
            'foreign_key_constraints' => true,
            'journal_mode' => 'WAL',
            'synchronous' => 'NORMAL',
            'cache_size' => '-64000',
            'temp_store' => 'MEMORY',
            'mmap_size' => '268435456',
        ]);

        // Purge existing connection
        DB::purge($connectionName);
    }

    /**
     * Run SQLite migrations
     */
    private function runSqliteMigrations(string $connectionName): void
    {
        // User table
        DB::connection($connectionName)->statement('
            CREATE TABLE users (
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
        ');

        // WebBlocs table
        DB::connection($connectionName)->statement('
            CREATE TABLE web_blocs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                webbloc_type VARCHAR(50) NOT NULL,
                user_id INTEGER,
                page_url VARCHAR(500) NOT NULL,
                data JSON NOT NULL,
                metadata JSON,
                status VARCHAR(20) DEFAULT "active",
                parent_id INTEGER,
                sort_order INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id),
                FOREIGN KEY (parent_id) REFERENCES web_blocs(id)
            )
        ');

        // Create indexes
        $indexes = [
            'CREATE INDEX idx_web_blocs_type ON web_blocs(webbloc_type)',
            'CREATE INDEX idx_web_blocs_page_url ON web_blocs(page_url)',
            'CREATE INDEX idx_web_blocs_user_id ON web_blocs(user_id)',
            'CREATE INDEX idx_web_blocs_parent_id ON web_blocs(parent_id)',
            'CREATE INDEX idx_web_blocs_created_at ON web_blocs(created_at)',
            'CREATE INDEX idx_web_blocs_status ON web_blocs(status)',
            'CREATE INDEX idx_users_email ON users(email)'
        ];

        foreach ($indexes as $index) {
            DB::connection($connectionName)->statement($index);
        }
    }

    /**
     * Optimize database settings
     */
    private function optimizeDatabase(string $connectionName): void
    {
        $optimizations = [
            'PRAGMA journal_mode=WAL',
            'PRAGMA synchronous=NORMAL',
            'PRAGMA cache_size=-64000',
            'PRAGMA temp_store=MEMORY',
            'PRAGMA mmap_size=268435456',
            'PRAGMA foreign_keys=ON',
            'PRAGMA automatic_index=ON',
            'PRAGMA optimize'
        ];

        foreach ($optimizations as $pragma) {
            try {
                DB::connection($connectionName)->statement($pragma);
            } catch (\Exception $e) {
                Log::warning("Failed to apply optimization: {$pragma}", [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}