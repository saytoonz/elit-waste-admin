<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            'user.manage',
            'user.view',
            'customer.create',
            'customer.view',
            'customer.edit',
            'customer.delete',
            'invoice.create',
            'invoice.view',
            'invoice.edit',
            'invoice.delete',
            'payment.create', // Cash collection
            'payment.view',
            'payment.approve', // Cash approval
            'expense.create',
            'expense.view',
            'expense.edit',
            'expense.delete',
            'expense.approve',
            'vendor.manage',
            'budget.manage',
            'report.view',
            'view audit logs',
            'manage users',
            'settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Roles and Assign Permissions

        // 1. Collector (Add customers, record cash, cannot approve)
        $collector = Role::firstOrCreate(['name' => 'Collector']);
        $collector->givePermissionTo([
            'customer.create',
            'customer.view',
            'payment.create',
            'invoice.view'
        ]);

        // 2. Supervisor (View + manage customers, limited finance, view expenses)
        $supervisor = Role::firstOrCreate(['name' => 'Supervisor']);
        $supervisor->givePermissionTo([
            'customer.create',
            'customer.view',
            'customer.edit',
            'invoice.view',
            'payment.view',
            'expense.create',
            'expense.view',
            'vendor.manage',
            'report.view'
        ]);

        // 3. Accountant (Finance + approvals)
        $accountant = Role::firstOrCreate(['name' => 'Accountant']);
        $accountant->givePermissionTo([
            'customer.view',
            'invoice.create',
            'invoice.view',
            'invoice.edit',
            'invoice.delete',
            'payment.view',
            'payment.approve',
            'expense.create',
            'expense.view',
            'expense.edit',
            'expense.delete',
            'expense.approve',
            'vendor.manage',
            'budget.manage',
            'report.view'
        ]);

        // 4. Admin (Full access except system locks - conceptually same as Owner for now but good to separate)
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->givePermissionTo(Permission::all());

        // 5. Owner (Full access)
        $owner = Role::firstOrCreate(['name' => 'Owner']);
        $owner->givePermissionTo(Permission::all());
        
        // Create a default Owner User
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'admin@elitwaste.com'],
            [
                'name' => 'System Owner',
                'password' => bcrypt('password'), // Change mechanism in prod
            ]
        );
        $user->assignRole($owner);
    }
}
