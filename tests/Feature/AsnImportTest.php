<?php

use App\Models\Eselon;
use App\Models\Jabatan;
use App\Models\User;
use App\Services\AsnImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->eselon   = Eselon::create(['name' => 'Eselon Uji', 'maks_honor' => 3]);
    $this->jabatanA = Jabatan::create(['name' => 'Kepala Seksi A', 'eselon_id' => $this->eselon->id]);
    $this->jabatanB = Jabatan::create(['name' => 'Kepala Seksi B', 'eselon_id' => $this->eselon->id]);

    $this->admin = User::create([
        'nip' => '9990001', 'name' => 'Admin', 'email' => 'admin@t.com',
        'jabatan_id' => $this->jabatanA->id, 'role' => 'admin',
        'status_akun' => 'aktif', 'password' => Hash::make('secret'),
    ]);
});

function rows(array $data): \Illuminate\Support\Collection
{
    return collect($data);
}

it('analyze mengkategorikan baru / pindah / sama / error', function () {
    // ASN eksisting di jabatan A
    User::create([
        'nip' => '111', 'name' => 'Lama', 'email' => 'lama@t.com',
        'jabatan_id' => $this->jabatanA->id, 'role' => 'staff',
        'status_akun' => 'aktif', 'password' => Hash::make('x'),
    ]);

    $hasil = app(AsnImportService::class)->analyze(rows([
        ['nip' => '222', 'nama' => 'Baru',  'email' => 'baru@t.com', 'jabatan' => 'Kepala Seksi A', 'role' => 'staff', 'tmt' => null],
        ['nip' => '111', 'nama' => 'Lama',  'email' => 'lama@t.com', 'jabatan' => 'Kepala Seksi B', 'role' => 'staff', 'tmt' => '2026-03-01'],
        ['nip' => '111', 'nama' => 'Dupe',  'email' => 'z@t.com',    'jabatan' => 'Kepala Seksi A', 'role' => 'staff', 'tmt' => null],
        ['nip' => '333', 'nama' => 'Salah', 'email' => 'c@t.com',    'jabatan' => 'Jabatan Ghaib',  'role' => 'staff', 'tmt' => null],
    ]));

    expect($hasil['summary'])->toMatchArray([
        'baru' => 1, 'pindah' => 1, 'error' => 2, 'sama' => 0,
    ]);

    // Baris valid menampilkan label eselon & kuota hasil pencocokan Jabatan -> Eselon.
    $barisBaru = collect($hasil['rows'])->firstWhere('nip', '222');
    expect($barisBaru['eselon'])->toBe('Eselon Uji · kuota 3 tim/tahun');

    // Baris error (jabatan tak ditemukan) tidak punya info eselon.
    $barisSalah = collect($hasil['rows'])->firstWhere('nip', '333');
    expect($barisSalah['eselon'])->toBeNull();
});

it('apply membuat ASN baru dengan password = NIP dan mencatat riwayat awal', function () {
    app(AsnImportService::class)->apply(rows([
        ['nip' => '555', 'nama' => 'Andi', 'email' => 'andi@t.com', 'jabatan' => 'Kepala Seksi A', 'role' => 'staff', 'tmt' => null],
    ]));

    $u = User::where('nip', '555')->first();
    expect($u)->not->toBeNull();
    expect(Hash::check('555', $u->password))->toBeTrue();
    expect($u->jabatan_id)->toBe($this->jabatanA->id);
    expect($u->jabatanHistories()->count())->toBe(1);
});

it('apply mencatat mutasi jabatan pakai TMT dari file (mendukung berlaku surut)', function () {
    $u = User::create([
        'nip' => '777', 'name' => 'Rina', 'email' => 'rina@t.com',
        'jabatan_id' => $this->jabatanA->id, 'role' => 'staff',
        'status_akun' => 'aktif', 'password' => Hash::make('x'),
    ]);
    $u->catatJabatanAwal(now()->startOfYear());

    app(AsnImportService::class)->apply(rows([
        ['nip' => '777', 'nama' => 'Rina', 'email' => 'rina@t.com', 'jabatan' => 'Kepala Seksi B', 'role' => 'staff', 'tmt' => '2026-06-01'],
    ]));

    $u->refresh();
    expect($u->jabatan_id)->toBe($this->jabatanB->id);

    // Riwayat baru bertanggal TMT dari file, bukan hari ini
    $riwayatBaru = $u->jabatanHistories()->where('jabatan_id', $this->jabatanB->id)->first();
    expect($riwayatBaru->tanggal_mulai->format('Y-m-d'))->toBe('2026-06-01');

    // jabatanPada() menghormati TMT: sebelum Juni masih jabatan lama
    expect($u->jabatanPada(\Carbon\Carbon::parse('2026-04-01'))->id)->toBe($this->jabatanA->id);
    expect($u->jabatanPada(\Carbon\Carbon::parse('2026-07-01'))->id)->toBe($this->jabatanB->id);
});

it('apply melewati baris error tanpa membatalkan baris valid', function () {
    $summary = app(AsnImportService::class)->apply(rows([
        ['nip' => '801', 'nama' => 'Valid', 'email' => 'v@t.com', 'jabatan' => 'Kepala Seksi A', 'role' => 'staff', 'tmt' => null],
        ['nip' => '802', 'nama' => 'Rusak', 'email' => 'bukan-email', 'jabatan' => 'Kepala Seksi A', 'role' => 'staff', 'tmt' => null],
    ]));

    expect($summary['baru'])->toBe(1);
    expect($summary['error'])->toBe(1);
    expect(User::where('nip', '801')->exists())->toBeTrue();
    expect(User::where('nip', '802')->exists())->toBeFalse();
});

it('halaman form import & download template dapat diakses admin', function () {
    $this->actingAs($this->admin);
    $this->get(route('admin.users.import.form'))->assertOk();
    $this->get(route('admin.users.import.template'))->assertOk();
});

it('alur HTTP: upload xlsx -> preview -> apply menyimpan data', function () {
    $this->actingAs($this->admin);

    // Bangun xlsx nyata dari array
    $export = new class implements FromArray, WithHeadings {
        public function headings(): array { return ['NIP', 'Nama', 'Email', 'Jabatan', 'Role', 'TMT']; }
        public function array(): array {
            return [['901', 'Dewi', 'dewi@t.com', 'Kepala Seksi A', 'staff', '2026-02-01']];
        }
    };
    Excel::store($export, 'uji-import.xlsx', 'local');
    $path = \Illuminate\Support\Facades\Storage::disk('local')->path('uji-import.xlsx');
    $file = new \Illuminate\Http\UploadedFile($path, 'uji-import.xlsx', null, null, true);

    $preview = $this->post(route('admin.users.import.preview'), ['file' => $file]);
    $preview->assertOk()->assertViewIs('admin.users.import-preview');
    $token = $preview->viewData('token');
    expect($token)->not->toBeNull();

    $this->post(route('admin.users.import.apply'), ['token' => $token])
        ->assertRedirect(route('admin.users.index'));

    expect(User::where('nip', '901')->exists())->toBeTrue();
});
