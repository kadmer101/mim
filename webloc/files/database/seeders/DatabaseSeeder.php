<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            WebBlocSeeder::class,
            // Add other seeders as needed
        ]);

        // Create sample data only in development environment
        if (app()->environment('local', 'development')) {
            $this->call([
                DevelopmentSeeder::class,
            ]);
        }
    }
}