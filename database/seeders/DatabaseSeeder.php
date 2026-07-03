<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Eselon;
use App\Models\Jabatan;
use App\Models\Tim;
use App\Models\JabatanHistory;
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
        // 3 eselon dengan kuota tim-dibayar per tahun yang berbeda
        $eselons = collect([
            Eselon::create(['name' => 'Eselon II', 'maks_honor' => 4]),
            Eselon::create(['name' => 'Eselon III', 'maks_honor' => 3]),
            Eselon::create(['name' => 'Eselon IV', 'maks_honor' => 2]),
        ]);

        // Jabatan untuk tiap eselon
        $eselons->each(function ($eselon) {
            Jabatan::factory()->count(5)->create([
                'eselon_id' => $eselon->id,
            ]);
        });

        // 20 ASN (staff)
        $users = User::factory()->count(20)->create();

        // Catat jabatan saat ini sebagai riwayat awal
        $users->each(function ($user) {
            JabatanHistory::create([
                'user_id' => $user->id,
                'jabatan_id' => $user->jabatan_id,
                'tanggal_mulai' => now()->startOfYear(),
            ]);
        });

        // 6 tim tahun berjalan
        $tims = Tim::factory()->count(6)->create();

        // Assign ASN ke tim + nominal honor per orang
        $tims->each(function ($tim) use ($users) {
            $randomUsers = $users->random(rand(3, 7)); // 3-7 anggota
            foreach ($randomUsers as $user) {
                $tim->users()->attach($user->id, [
                    'jabatan'       => fake()->randomElement(['Ketua', 'Wakil', 'Anggota']),
                    'nominal_honor' => fake()->randomElement([500000, 750000, 1000000, 1500000]),
                ]);
            }
        });

        // Akun demo
        $jabatanStaff = Jabatan::inRandomOrder()->first();

        User::create([
            'nip' => '1234567890',
            'name' => 'Staff Demo',
            'email' => 'staff@staff.com',
            'jabatan_id' => $jabatanStaff->id,
            'role' => 'staff',
            'status_akun' => 'aktif',
            'password' => Hash::make('password'),
        ]);

        // Admin default
        $this->call(AdminSeeder::class);
    }
}
