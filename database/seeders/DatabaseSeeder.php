<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        User::factory()->create([
            'nip' => '1234567890',
            'name' => 'Test User',
            'email' => 'staff@staff.com',
            'jabatan' => 'Staff IT',
            'role' => 'staff',
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'nip' => '0987654321',
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'jabatan' => 'Administrator',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        User::factory()->count(10)->create();
    }
}
