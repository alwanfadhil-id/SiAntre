<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for admin
        $adminPermissions = [
            'view-dashboard',
            'manage-services',
            'manage-users',
            'reset-queue',
            'view-reports',
            'manage-settings',
        ];

        foreach ($adminPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create permissions for operator
        $operatorPermissions = [
            'view-operator-dashboard',
            'view-queues',
            'call-queue',
            'complete-queue',
            'cancel-queue',
        ];

        foreach ($operatorPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create permissions for patient
        $patientPermissions = [
            'view-home',
            'generate-queue',
            'view-queue-status',
        ];

        foreach ($patientPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo($adminPermissions);

        $operatorRole = Role::create(['name' => 'operator']);
        $operatorRole->givePermissionTo($operatorPermissions);

        $patientRole = Role::create(['name' => 'patient']);
        $patientRole->givePermissionTo($patientPermissions);

        // Assign roles to existing users if they exist
        $adminUsers = User::where('role', 'admin')->get();
        foreach ($adminUsers as $user) {
            $user->assignRole('admin');
        }

        $operatorUsers = User::where('role', 'operator')->get();
        foreach ($operatorUsers as $user) {
            $user->assignRole('operator');
        }
    }
}
