<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'nip' => '0001111',
            'name' => 'Admin Jer',
            'email' => 'admin@gmail.com',
            'jabatan' => 'Administrator',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);
    }
}
