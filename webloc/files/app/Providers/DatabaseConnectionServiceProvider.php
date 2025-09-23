<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use App\Services\DatabaseConnectionService;

class DatabaseConnectionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DatabaseConnectionService::class, function ($app) {
            return new DatabaseConnectionService();
        });

        $this->app->alias(DatabaseConnectionService::class, 'db.sqlite.connection.service');
    }

    public function boot(): void
    {
        // Configure dynamic database connections
        $this->configureDynamicConnections();
        
        // Register custom DB macros
        $this->registerDatabaseMacros();
    }

    protected function configureDynamicConnections(): void
    {
        // Extend the database manager to support dynamic SQLite connections
        DB::extend('sqlite_dynamic', function ($config, $name) {
            $config['database'] = $config['database'] ?? ':memory:';
            
            return new \Illuminate\Database\SQLiteConnection(
                new \PDO("sqlite:{$config['database']}", null, null, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]),
                $config['database'],
                $config['prefix'] ?? '',
                $config
            );
        });
    }

    protected function registerDatabaseMacros(): void
    {
        // Add a macro to get website-specific connection
        DB::macro('website', function ($websiteId) {
            return app(DatabaseConnectionService::class)->getConnection($websiteId);
        });

        // Add a macro to switch to website database
        DB::macro('useWebsiteDatabase', function ($websiteId) {
            $service = app(DatabaseConnectionService::class);
            $connectionName = "website_sqlite_{$websiteId}";
            
            if (!array_key_exists($connectionName, config('database.connections'))) {
                $databasePath = $service->getDatabasePath($websiteId);
                
                config([
                    "database.connections.{$connectionName}" => [
                        'driver' => 'sqlite',
                        'database' => $databasePath,
                        'prefix' => '',
                        'foreign_key_constraints' => true,
                    ]
                ]);
            }
            
            return DB::connection($connectionName);
        });
    }
}