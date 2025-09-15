<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Eselon;
use App\Models\Jabatan;
use App\Models\Tim;
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
        // Buat 3 eselon
        $eselons = Eselon::factory()->count(3)->create();

        // Buat jabatan untuk tiap eselon
        $eselons->each(function ($eselon) {
            Jabatan::factory()->count(5)->create([
                'eselon_id' => $eselon->id,
            ]);
        });

        // Buat 20 user
        $users = User::factory()->count(20)->create();

        // Buat 3 tim
        $tims = Tim::factory()->count(3)->create();

        // Assign user ke tim via pivot
        $tims->each(function ($tim) use ($users) {
            $randomUsers = $users->random(rand(3, 7)); // 3-7 anggota
            foreach ($randomUsers as $user) {
                $tim->users()->attach($user->id, [
                    'jabatan' => fake()->randomElement(['Ketua', 'Wakil', 'Anggota']),
                ]);
            }
        });

        // Tambahkan 1 akun admin default
        User::factory()->create([
            'nip' => '0987654321',
            'name' => 'Super Admin',
            'email' => 'admin@admin.com',
            'role' => 'admin',
            'status_akun' => 'aktif',
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'nip' => '1234567890',
            'name' => 'Staff',
            'email' => 'staff@staff.com',
            'role' => 'staff',
            'status_akun' => 'aktif',
            'password' => Hash::make('password'),
        ]);

        // Call AdminSeeder to seed admin users
        $this->call(AdminSeeder::class);
    }
}
