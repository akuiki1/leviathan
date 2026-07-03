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

    $this->get('/admin/laporan-honor')->assertOk();
    $this->get('/admin/laporan-honor?tahun=' . date('Y'))->assertOk();
});

it('admin bisa export laporan honor ke excel', function () {
    $this->actingAs($this->admin);

    $this->get('/admin/laporan-honor/export?tahun=' . date('Y'))
        ->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
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

it('menilai kuota honor pakai eselon yang berlaku SAAT ASN bergabung ke tim, bukan eselon saat ini', function () {
    $eselonLama = \App\Models\Eselon::create(['name' => 'Eselon Lama', 'maks_honor' => 1]);
    $eselonBaru = \App\Models\Eselon::create(['name' => 'Eselon Baru', 'maks_honor' => 3]);
    $jabatanLama = \App\Models\Jabatan::create(['name' => 'Jabatan Lama', 'eselon_id' => $eselonLama->id]);
    $jabatanBaru = \App\Models\Jabatan::create(['name' => 'Jabatan Baru', 'eselon_id' => $eselonBaru->id]);

    $tahun          = (int) date('Y');
    $awalTahun      = \Carbon\Carbon::createFromDate($tahun, 1, 1);
    $tanggalPromosi = \Carbon\Carbon::createFromDate($tahun, 6, 1);

    // ASN mulai tahun di eselon lama (kuota 1), promosi ke eselon baru (kuota 3) di Juni.
    $asn = User::factory()->create(['jabatan_id' => $jabatanBaru->id]);
    $asn->jabatanHistories()->create([
        'jabatan_id'      => $jabatanLama->id,
        'tanggal_mulai'   => $awalTahun,
        'tanggal_selesai' => $tanggalPromosi->copy()->subDay(),
    ]);
    $asn->jabatanHistories()->create([
        'jabatan_id'      => $jabatanBaru->id,
        'tanggal_mulai'   => $tanggalPromosi,
        'tanggal_selesai' => null,
    ]);

    $timA = Tim::factory()->create(['tahun' => $tahun, 'status' => 'approved']);
    $timB = Tim::factory()->create(['tahun' => $tahun, 'status' => 'approved']);
    $timC = Tim::factory()->create(['tahun' => $tahun, 'status' => 'approved']);

    foreach ([$timA, $timB, $timC] as $tim) {
        $tim->users()->attach($asn->id);
    }

    // Paksa tanggal gabung: A=Feb & B=Mar (sebelum promosi), C=Jul (sesudah promosi).
    \Illuminate\Support\Facades\DB::table('tim_user')->where('tim_id', $timA->id)->where('user_id', $asn->id)
        ->update(['created_at' => $awalTahun->copy()->addMonth()]);
    \Illuminate\Support\Facades\DB::table('tim_user')->where('tim_id', $timB->id)->where('user_id', $asn->id)
        ->update(['created_at' => $awalTahun->copy()->addMonths(2)]);
    \Illuminate\Support\Facades\DB::table('tim_user')->where('tim_id', $timC->id)->where('user_id', $asn->id)
        ->update(['created_at' => $tanggalPromosi->copy()->addMonth()]);

    $status = app(\App\Services\HonorService::class)->statusPerTim($asn, $tahun);

    // Tim A: gabung sebelum promosi, slot pertama di kuota lama (1) -> dibayar.
    expect($status->get($timA->id)['status'])->toBe(\App\Services\HonorService::DIBAYAR);
    // Tim B: gabung sebelum promosi juga, tapi kuota lama cuma 1 & sudah terpakai -> tidak dibayar.
    expect($status->get($timB->id)['status'])->toBe(\App\Services\HonorService::TIDAK_DIBAYAR);
    // Tim C: gabung setelah promosi, dinilai pakai kuota baru (3), baru 1 terpakai -> dibayar.
    expect($status->get($timC->id)['status'])->toBe(\App\Services\HonorService::DIBAYAR);
});

/**
 * Helper: ASN yang promosi dari eselon kuota-1 ke eselon kuota-3 per 1 Juni.
 *
 * @return array{0:User,1:\App\Models\Eselon,2:\App\Models\Eselon,3:\Carbon\Carbon,4:\Carbon\Carbon}
 */
function leviathan_buatAsnPromosi(): array
{
    $eselonLama = \App\Models\Eselon::create(['name' => 'Eselon Uji Lama', 'maks_honor' => 1]);
    $eselonBaru = \App\Models\Eselon::create(['name' => 'Eselon Uji Baru', 'maks_honor' => 3]);
    $jabatanLama = \App\Models\Jabatan::create(['name' => 'Jabatan Uji Lama', 'eselon_id' => $eselonLama->id]);
    $jabatanBaru = \App\Models\Jabatan::create(['name' => 'Jabatan Uji Baru', 'eselon_id' => $eselonBaru->id]);

    $tahun          = (int) date('Y');
    $awalTahun      = \Carbon\Carbon::createFromDate($tahun, 1, 1);
    $tanggalPromosi = \Carbon\Carbon::createFromDate($tahun, 6, 1);

    $asn = User::factory()->create(['jabatan_id' => $jabatanBaru->id]);
    $asn->jabatanHistories()->create([
        'jabatan_id'      => $jabatanLama->id,
        'tanggal_mulai'   => $awalTahun,
        'tanggal_selesai' => $tanggalPromosi->copy()->subDay(),
    ]);
    $asn->jabatanHistories()->create([
        'jabatan_id'      => $jabatanBaru->id,
        'tanggal_mulai'   => $tanggalPromosi,
        'tanggal_selesai' => null,
    ]);

    return [$asn, $eselonLama, $eselonBaru, $awalTahun, $tanggalPromosi];
}

/** Helper: gabungkan ASN ke tim approved dengan tanggal gabung tertentu. */
function leviathan_gabungTim(User $asn, \Carbon\Carbon $tanggalGabung): Tim
{
    $tim = Tim::factory()->create(['tahun' => (int) date('Y'), 'status' => 'approved']);
    $tim->users()->attach($asn->id);
    \Illuminate\Support\Facades\DB::table('tim_user')
        ->where('tim_id', $tim->id)->where('user_id', $asn->id)
        ->update(['created_at' => $tanggalGabung]);

    return $tim;
}

it('mengatribusikan rekap eselon ke eselon saat ASN bergabung (snapshot), bukan eselon saat ini', function () {
    [$asn, $eselonLama, $eselonBaru, $awalTahun, $tanggalPromosi] = leviathan_buatAsnPromosi();

    // A (Feb, dibayar kuota lama) & B (Mar, melebihi kuota lama) -> eselon LAMA;
    // C (Jul, dibayar kuota baru) -> eselon BARU.
    leviathan_gabungTim($asn, $awalTahun->copy()->addMonth());
    leviathan_gabungTim($asn, $awalTahun->copy()->addMonths(2));
    leviathan_gabungTim($asn, $tanggalPromosi->copy()->addMonth());

    $rekap = app(\App\Services\HonorService::class)->rekapPerEselon((int) date('Y'));

    $barisLama = $rekap->firstWhere('eselon.id', $eselonLama->id);
    $barisBaru = $rekap->firstWhere('eselon.id', $eselonBaru->id);

    expect($barisLama['jumlah_tim_dibayar'])->toBe(1);
    expect($barisLama['jumlah_tim_tidak_dibayar'])->toBe(1);
    expect($barisLama['jumlah_over_limit'])->toBe(1);
    expect($barisBaru['jumlah_tim_dibayar'])->toBe(1);
    expect($barisBaru['jumlah_tim_tidak_dibayar'])->toBe(0);
    // ASN yang mutasi di tengah tahun terhitung di kedua baris eselon
    expect($barisLama['jumlah_asn'])->toBe(1);
    expect($barisBaru['jumlah_asn'])->toBe(1);
});

it('menandai over limit dari keanggotaan tidak dibayar, walau kuota saat ini sudah lebih besar', function () {
    [$asn, , , $awalTahun] = leviathan_buatAsnPromosi();

    // 3 tim semua gabung SEBELUM promosi (kuota lama = 1): 1 dibayar, 2 tidak.
    // Definisi lama (approved > kuota saat ini: 3 > 3) akan salah bilang aman.
    leviathan_gabungTim($asn, $awalTahun->copy()->addMonth());
    leviathan_gabungTim($asn, $awalTahun->copy()->addMonths(2));
    leviathan_gabungTim($asn, $awalTahun->copy()->addMonths(3));

    $ringkasan = app(\App\Services\HonorService::class)->ringkasan($asn, (int) date('Y'));

    expect($ringkasan['jumlah_tidak_dibayar'])->toBe(2);
    expect($ringkasan['is_over_limit'])->toBeTrue();
});

it('halaman rincian per ASN dapat diakses & difilter', function () {
    $this->actingAs($this->admin);

    $this->get('/admin/laporan-honor/asn')->assertOk();
    $this->get('/admin/laporan-honor/asn?tahun=' . date('Y') . '&over_limit=1&q=x&eselon_id=0')->assertOk();
});

it('merekap jumlah tim dibayar & tidak dibayar per eselon', function () {
    $eselon  = \App\Models\Eselon::create(['name' => 'Eselon Rekap', 'maks_honor' => 1]);
    $jabatan = \App\Models\Jabatan::create(['name' => 'Jabatan Rekap', 'eselon_id' => $eselon->id]);
    $tahun   = (int) date('Y');

    $asn = User::factory()->create(['jabatan_id' => $jabatan->id]);
    $asn->jabatanHistories()->create([
        'jabatan_id'    => $jabatan->id,
        'tanggal_mulai' => now()->startOfYear(),
    ]);

    $timDibayar     = Tim::factory()->create(['tahun' => $tahun, 'status' => 'approved']);
    $timTidakDibayar = Tim::factory()->create(['tahun' => $tahun, 'status' => 'approved']);

    $timDibayar->users()->attach($asn->id);
    $timTidakDibayar->users()->attach($asn->id);

    // Pastikan urutan gabung: timDibayar duluan (dapat slot ke-1, kuota=1), timTidakDibayar belakangan (tidak kebagian).
    \Illuminate\Support\Facades\DB::table('tim_user')->where('tim_id', $timDibayar->id)
        ->update(['created_at' => now()->startOfYear()->addDay()]);
    \Illuminate\Support\Facades\DB::table('tim_user')->where('tim_id', $timTidakDibayar->id)
        ->update(['created_at' => now()->startOfYear()->addDays(2)]);

    $rekap = app(\App\Services\HonorService::class)->rekapPerEselon($tahun);
    $baris = $rekap->firstWhere('eselon.id', $eselon->id);

    expect($baris['jumlah_asn'])->toBe(1);
    expect($baris['jumlah_over_limit'])->toBe(1);
    expect($baris['jumlah_tim_dibayar'])->toBe(1);
    expect($baris['jumlah_tim_tidak_dibayar'])->toBe(1);
});

it('staff bisa membuat tim', function () {
    Storage::fake('public');
    $this->actingAs($this->staff);

    $response = $this->post('/staff/tim', [
        'nama_tim'   => 'Tim Uji Otomatis',
        'keterangan' => 'keterangan uji',
        'sk_file'    => UploadedFile::fake()->create('sk.pdf', 50, 'application/pdf'),
        'anggota'    => [$this->staff->id],
    ]);

    $response->assertRedirect(route('staff.tim.index'));

    $tim = Tim::where('nama_tim', 'Tim Uji Otomatis')->first();
    expect($tim)->not->toBeNull();
    expect((int) $tim->tahun)->toBe((int) date('Y'));
    expect($tim->users()->where('user_id', $this->staff->id)->exists())->toBeTrue();
});
