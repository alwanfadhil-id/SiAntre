<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::updateOrCreate(
            ['email' => 'admin@siantre.test'],
            [
                'name' => 'Administrator',
                'email_verified_at' => now(),
                'password' => Hash::make(env('DEFAULT_ADMIN_PASSWORD', 'AdminPass123!')),
                'remember_token' => Str::random(10),
                'role' => 'admin',
            ]
        );

        // Create operator user
        User::updateOrCreate(
            ['email' => 'operator@siantre.test'],
            [
                'name' => 'Operator',
                'email_verified_at' => now(),
                'password' => Hash::make(env('DEFAULT_OPERATOR_PASSWORD', 'OperatorPass123!')),
                'remember_token' => Str::random(10),
                'role' => 'operator',
            ]
        );

        // Optionally create additional sample users for testing
        if (app()->environment('local', 'testing')) {
            User::updateOrCreate(
                ['email' => 'test.admin@siantre.test'],
                [
                    'name' => 'Test Administrator',
                    'email_verified_at' => now(),
                    'password' => Hash::make('TestAdmin123!'),
                    'remember_token' => Str::random(10),
                    'role' => 'admin',
                ]
            );

            User::updateOrCreate(
                ['email' => 'test.operator@siantre.test'],
                [
                    'name' => 'Test Operator',
                    'email_verified_at' => now(),
                    'password' => Hash::make('TestOperator123!'),
                    'remember_token' => Str::random(10),
                    'role' => 'operator',
                ]
            );
        }
    }
}
