<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Jabatan;
use App\Models\Eselon;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create or find an Eselon for admin
        $eselon = Eselon::firstOrCreate(
            ['name' => 'Admin Eselon'],
            ['maks_honor' => 10]
        );

        // Create or find a Jabatan for Administrator
        $jabatan = Jabatan::firstOrCreate(
            ['name' => 'Administrator'],
            ['eselon_id' => $eselon->id]
        );

        User::create([
            'nip' => '0001111',
            'name' => 'Admin Jer',
            'email' => 'admin@gmail.com',
            'jabatan_id' => $jabatan->id,
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'status_akun' => 'aktif',
        ]);
    }
}
