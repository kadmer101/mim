<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Website management
            'websites.view',
            'websites.create',
            'websites.edit',
            'websites.delete',
            'websites.verify',
            
            // API Key management
            'api-keys.view',
            'api-keys.create',
            'api-keys.edit',
            'api-keys.delete',
            'api-keys.regenerate',
            
            // WebBloc management
            'webblocs.view',
            'webblocs.create',
            'webblocs.edit',
            'webblocs.delete',
            'webblocs.install',
            'webblocs.uninstall',
            
            // Statistics and analytics
            'statistics.view',
            'statistics.export',
            
            // Admin functions
            'admin.dashboard',
            'admin.system-info',
            'admin.logs',
            'admin.maintenance',
            'admin.cache-management',
            'admin.backup',
            
            // User management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.impersonate',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Super Admin - has all permissions
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        // Admin - has most permissions except user management
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo([
            'websites.view',
            'websites.create',
            'websites.edit',
            'websites.verify',
            'api-keys.view',
            'api-keys.create',
            'api-keys.edit',
            'api-keys.regenerate',
            'webblocs.view',
            'webblocs.create',
            'webblocs.edit',
            'webblocs.install',
            'webblocs.uninstall',
            'statistics.view',
            'statistics.export',
            'admin.dashboard',
            'admin.system-info',
            'admin.cache-management',
        ]);

        // Website Owner - can manage their own websites
        $websiteOwnerRole = Role::create(['name' => 'website-owner']);
        $websiteOwnerRole->givePermissionTo([
            'websites.view',
            'websites.edit',
            'api-keys.view',
            'api-keys.create',
            'api-keys.edit',
            'api-keys.regenerate',
            'webblocs.view',
            'webblocs.install',
            'webblocs.uninstall',
            'statistics.view',
        ]);

        // Developer - technical access
        $developerRole = Role::create(['name' => 'developer']);
        $developerRole->givePermissionTo([
            'websites.view',
            'websites.create',
            'websites.edit',
            'webblocs.view',
            'webblocs.create',
            'webblocs.edit',
            'webblocs.install',
            'statistics.view',
            'admin.system-info',
            'admin.logs',
        ]);

        // Support - limited access for customer support
        $supportRole = Role::create(['name' => 'support']);
        $supportRole->givePermissionTo([
            'websites.view',
            'api-keys.view',
            'webblocs.view',
            'statistics.view',
            'users.view',
        ]);

        // Create default super admin user if none exists
        if (User::count() === 0) {
            $superAdmin = User::create([
                'name' => 'Super Admin',
                'email' => 'admin@' . parse_url(config('app.url'), PHP_URL_HOST),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);

            $superAdmin->assignRole($superAdminRole);
            
            $this->command->info('Super admin user created: ' . $superAdmin->email);
            $this->command->warn('Default password: password (please change immediately!)');
        }

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
