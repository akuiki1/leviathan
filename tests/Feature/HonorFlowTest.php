<?php

use App\Models\Tim;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
    $this->staff = User::where('role', 'staff')->first();
    $this->admin = User::where('role', 'admin')->first();
});

it('renders semua halaman staff tanpa error', function () {
    $this->actingAs($this->staff);

    $this->get('/staff/dashboard')->assertOk();
    $this->get('/staff/tim')->assertOk();
    $this->get('/staff/tim/create')->assertOk();
    $this->get('/staff/profile')->assertOk();
});

it('renders halaman admin tanpa error', function () {
    $this->actingAs($this->admin);

    $this->get('/admin/dashboard')->assertOk();
    $this->get('/admin/tims/create')->assertOk();

    $tim = Tim::first();
    $this->get("/admin/tims/{$tim->id}")->assertOk();          // show
    $this->get("/admin/tims/{$tim->id}/edit")->assertOk();     // edit

    $staff = User::where('role', 'staff')->first();
    $this->get("/admin/users/{$staff->id}")->assertOk();       // show

    $this->getJson("/admin/tims/{$tim->id}/check-members")
        ->assertOk()
        ->assertJsonStructure(['members', 'has_over_limit', 'has_warning']);
});

it('admin bisa mengelola master eselon & jabatan', function () {
    $this->actingAs($this->admin);

    $this->get('/admin/eselons')->assertOk();
    $this->get('/admin/eselons/create')->assertOk();
    $this->get('/admin/jabatans')->assertOk();
    $this->get('/admin/jabatans/create')->assertOk();

    $this->post('/admin/eselons', ['name' => 'Eselon Baru', 'maks_honor' => 5])
        ->assertRedirect(route('admin.eselons.index'));
    $eselon = \App\Models\Eselon::where('name', 'Eselon Baru')->first();
    expect($eselon->maks_honor)->toBe(5);

    $this->post('/admin/jabatans', ['name' => 'Kepala Baru', 'eselon_id' => $eselon->id])
        ->assertRedirect(route('admin.jabatans.index'));
    expect(\App\Models\Jabatan::where('name', 'Kepala Baru')->exists())->toBeTrue();
});

it('mencatat riwayat jabatan saat jabatan ASN berubah', function () {
    $this->actingAs($this->admin);

    $target = User::where('role', 'staff')->first();
    $jabatanBaru = \App\Models\Jabatan::where('id', '!=', $target->jabatan_id)->first();

    $this->put("/admin/users/{$target->id}", [
        'nip'        => $target->nip,
        'name'       => $target->name,
        'email'      => $target->email,
        'jabatan_id' => $jabatanBaru->id,
        'role'       => $target->role,
    ])->assertRedirect(route('admin.users.index'));

    // Ada riwayat lama yang ditutup + riwayat baru yang terbuka
    expect($target->jabatanHistories()->whereNotNull('tanggal_selesai')->count())->toBeGreaterThanOrEqual(1);
    expect($target->jabatanHistories()->where('jabatan_id', $jabatanBaru->id)->whereNull('tanggal_selesai')->exists())->toBeTrue();
});

it('staff bisa membuat tim dengan nominal honor per anggota', function () {
    Storage::fake('public');
    $this->actingAs($this->staff);

    $response = $this->post('/staff/tim', [
        'nama_tim'   => 'Tim Uji Otomatis',
        'keterangan' => 'keterangan uji',
        'sk_file'    => UploadedFile::fake()->create('sk.pdf', 50, 'application/pdf'),
        'anggota'    => [$this->staff->id],
        'nominal'    => [$this->staff->id => 1250000],
    ]);

    $response->assertRedirect(route('staff.tim.index'));

    $tim = Tim::where('nama_tim', 'Tim Uji Otomatis')->first();
    expect($tim)->not->toBeNull();
    expect((int) $tim->tahun)->toBe((int) date('Y'));
    expect((int) $tim->users()->where('user_id', $this->staff->id)->first()->pivot->nominal_honor)->toBe(1250000);
});
