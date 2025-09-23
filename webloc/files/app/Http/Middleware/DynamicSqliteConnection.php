<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Services\DatabaseConnectionService;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class DynamicSqliteConnection
{
    protected $dbService;

    public function __construct(DatabaseConnectionService $dbService)
    {
        $this->dbService = $dbService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Get website from request attributes (set by ValidateApiKey middleware)
            $website = $request->attributes->get('website');

            if (!$website) {
                return $this->errorResponse('Website context not found', 500);
            }

            // Validate SQLite database exists
            if (!$this->dbService->databaseExists($website->id)) {
                // Attempt to create the database
                if (!$this->dbService->createDatabase($website->id)) {
                    return $this->errorResponse('Website database not available', 503);
                }
            }

            // Configure dynamic SQLite connection
            $this->configureSqliteConnection($website);

            // Test connection
            if (!$this->testConnection()) {
                return $this->errorResponse('Database connection failed', 503);
            }

            // Add database info to request
            $request->attributes->add([
                'sqlite_connection' => 'sqlite_website',
                'database_path' => $this->getDatabasePath($website->id)
            ]);

            $response = $next($request);

            // Cleanup connection after request
            $this->cleanup();

            return $response;

        } catch (\Exception $e) {
            Log::error('Dynamic SQLite connection failed', [
                'website_id' => $website->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse('Database connection error', 500);
        }
    }

    /**
     * Configure SQLite connection for the website
     */
    private function configureSqliteConnection($website): void
    {
        $databasePath = $this->getDatabasePath($website->id);

        // Set up dynamic SQLite connection
        Config::set('database.connections.sqlite_website', [
            'driver' => 'sqlite',
            'database' => $databasePath,
            'prefix' => '',
            'foreign_key_constraints' => true,
            'journal_mode' => 'WAL', // Write-Ahead Logging for better performance
            'synchronous' => 'NORMAL', // Balance between speed and safety
            'cache_size' => '-64000', // 64MB cache
            'temp_store' => 'MEMORY',
            'mmap_size' => '268435456', // 256MB memory mapping
        ]);

        // Purge any existing connection
        DB::purge('sqlite_website');

        // Set as default connection for this request
        Config::set('database.default', 'sqlite_website');
    }

    /**
     * Get database path for website
     */
    private function getDatabasePath(int $websiteId): string
    {
        $storagePath = storage_path('databases');
        
        // Create directory if it doesn't exist
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        return $storagePath . "/website_{$websiteId}.sqlite";
    }

    /**
     * Test database connection
     */
    private function testConnection(): bool
    {
        try {
            // Test the connection with a simple query
            DB::connection('sqlite_website')->select('SELECT 1');
            return true;
        } catch (\Exception $e) {
            Log::error('SQLite connection test failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Cleanup connection
     */
    private function cleanup(): void
    {
        try {
            // Disconnect SQLite connection to free up resources
            DB::disconnect('sqlite_website');
            
            // Reset default connection to main database
            Config::set('database.default', config('database.default'));
            
        } catch (\Exception $e) {
            Log::error('Failed to cleanup SQLite connection', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Optimize SQLite database settings
     */
    private function optimizeDatabase(): void
    {
        try {
            DB::connection('sqlite_website')->statement('PRAGMA journal_mode=WAL');
            DB::connection('sqlite_website')->statement('PRAGMA synchronous=NORMAL');
            DB::connection('sqlite_website')->statement('PRAGMA cache_size=-64000');
            DB::connection('sqlite_website')->statement('PRAGMA temp_store=MEMORY');
            DB::connection('sqlite_website')->statement('PRAGMA mmap_size=268435456');
            
            // Enable foreign key constraints
            DB::connection('sqlite_website')->statement('PRAGMA foreign_keys=ON');
            
        } catch (\Exception $e) {
            Log::warning('Failed to optimize SQLite database', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Error response helper
     */
    private function errorResponse(string $message, int $status): Response
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => 'DATABASE_ERROR'
        ], $status);
    }
}