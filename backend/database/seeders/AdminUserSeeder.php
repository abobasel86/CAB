<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin'
            ]
        );

        // Create test users for different roles
        $users = [
            ['name' => 'Importer User', 'email' => 'importer@example.com', 'role' => 'importer'],
            ['name' => 'Editor User', 'email' => 'editor@example.com', 'role' => 'editor'],
            ['name' => 'Viewer User', 'email' => 'viewer@example.com', 'role' => 'viewer'],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => Hash::make('password'),
                    'role' => $userData['role']
                ]
            );
        }
    }
}
