<?php

namespace App\Services;

use App\Models\Jabatan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Import/update massal data ASN dari baris Excel (dicocokkan per NIP).
 *
 * Alur dua tahap yang aman:
 *  - analyze(): dry-run, hasilkan diff (baru / pindah jabatan / tidak berubah / error)
 *    TANPA menyentuh database. Dipakai untuk layar preview.
 *  - apply(): jalankan perubahan dalam SATU transaksi setelah admin konfirmasi.
 *
 * Perubahan jabatan SELALU lewat User::pindahJabatan() agar riwayat (JabatanHistory)
 * tercatat konsisten dengan form manual — TMT dari file mendukung SK berlaku surut.
 */
class AsnImportService
{
    public const BARU   = 'baru';
    public const PINDAH = 'pindah';
    public const SAMA   = 'sama';
    public const ERROR  = 'error';

    /**
     * Analisis baris (dry-run). Tidak menulis apa pun.
     *
     * @param  Collection<int, array<string,mixed>>  $rows  baris ter-key heading (nip, nama, email, jabatan, role, tmt)
     * @return array{rows: array<int,array<string,mixed>>, summary: array<string,int>}
     */
    public function analyze(Collection $rows): array
    {
        $jabatanMap = $this->jabatanLookup();
        $seenNip    = [];
        $hasil      = [];
        $summary    = [self::BARU => 0, self::PINDAH => 0, self::SAMA => 0, self::ERROR => 0];

        foreach ($rows as $i => $row) {
            $baris = $i + 2; // +1 index 0-based, +1 baris heading
            $nip   = trim((string) ($row['nip'] ?? ''));
            $nama  = trim((string) ($row['nama'] ?? ''));
            $email = trim((string) ($row['email'] ?? ''));
            $jab   = trim((string) ($row['jabatan'] ?? ''));
            $role  = strtolower(trim((string) ($row['role'] ?? 'staff'))) ?: 'staff';

            $error = null;

            if ($nip === '' || $nama === '' || $email === '') {
                $error = 'NIP, Nama, dan Email wajib diisi';
            } elseif (isset($seenNip[$nip])) {
                $error = "NIP duplikat dalam file (baris {$seenNip[$nip]})";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Format email tidak valid';
            } elseif (!in_array($role, ['staff', 'admin'], true)) {
                $error = "Role '{$role}' tidak dikenal (staff/admin)";
            } elseif (!isset($jabatanMap[$this->normalize($jab)])) {
                $error = "Jabatan '{$jab}' tidak ditemukan di master";
            }

            [$tmt, $tmtError] = $this->parseTmt($row['tmt'] ?? null);
            if (!$error && $tmtError) {
                $error = $tmtError;
            }

            if ($nip !== '') {
                $seenNip[$nip] = $baris;
            }

            if ($error) {
                $summary[self::ERROR]++;
                $hasil[] = $this->baris($baris, self::ERROR, compact('nip', 'nama', 'email', 'jab') + ['message' => $error, 'eselon' => null]);
                continue;
            }

            $jabatanBaru = $jabatanMap[$this->normalize($jab)];
            $eselonLabel = ($jabatanBaru->eselon?->name ?? '-') . ' · kuota ' . ($jabatanBaru->eselon?->maks_honor ?? 0) . ' tim/tahun';
            $user        = User::where('nip', $nip)->first();

            // Bentrok email dengan user lain (NIP berbeda).
            $emailMilikLain = User::where('email', $email)
                ->when($user, fn($q) => $q->where('id', '!=', $user->id))
                ->exists();
            if ($emailMilikLain) {
                $summary[self::ERROR]++;
                $hasil[] = $this->baris($baris, self::ERROR, compact('nip', 'nama', 'email', 'jab') + [
                    'message' => "Email '{$email}' sudah dipakai ASN lain",
                    'eselon'  => $eselonLabel,
                ]);
                continue;
            }

            if (!$user) {
                $aksi   = self::BARU;
                $detail = 'ASN baru → jabatan ' . $jab;
            } elseif ((int) $user->jabatan_id !== (int) $jabatanBaru->id) {
                $aksi   = self::PINDAH;
                $detail = 'Pindah: ' . ($user->jabatan->name ?? '-') . ' → ' . $jab
                        . ' (TMT ' . ($tmt ? $tmt->format('d-m-Y') : 'hari ini') . ')';
            } else {
                $aksi   = self::SAMA;
                $detail = 'Jabatan tidak berubah';
            }

            $summary[$aksi]++;
            $hasil[] = $this->baris($baris, $aksi, compact('nip', 'nama', 'email', 'jab') + ['message' => $detail, 'eselon' => $eselonLabel]);
        }

        return ['rows' => $hasil, 'summary' => $summary];
    }

    /**
     * Terapkan perubahan dalam satu transaksi. Baris error DILEWATI (tidak membatalkan
     * baris lain yang valid), lalu dihitung di ringkasan hasil.
     *
     * @param  Collection<int, array<string,mixed>>  $rows
     * @return array<string,int>  ringkasan {baru, pindah, sama, error}
     */
    public function apply(Collection $rows): array
    {
        $jabatanMap = $this->jabatanLookup();

        return DB::transaction(function () use ($rows, $jabatanMap) {
            $summary  = [self::BARU => 0, self::PINDAH => 0, self::SAMA => 0, self::ERROR => 0];
            $seenNip  = [];

            foreach ($rows as $row) {
                $nip   = trim((string) ($row['nip'] ?? ''));
                $nama  = trim((string) ($row['nama'] ?? ''));
                $email = trim((string) ($row['email'] ?? ''));
                $jab   = trim((string) ($row['jabatan'] ?? ''));
                $role  = strtolower(trim((string) ($row['role'] ?? 'staff'))) ?: 'staff';

                [$tmt, $tmtError] = $this->parseTmt($row['tmt'] ?? null);
                $jabatanId = $jabatanMap[$this->normalize($jab)]?->id ?? null;

                // Lewati baris yang tak lolos validasi dasar (mirror analyze()).
                if ($nip === '' || $nama === '' || $email === '' || isset($seenNip[$nip])
                    || !filter_var($email, FILTER_VALIDATE_EMAIL)
                    || !in_array($role, ['staff', 'admin'], true)
                    || !$jabatanId || $tmtError) {
                    $summary[self::ERROR]++;
                    continue;
                }
                $seenNip[$nip] = true;

                $user = User::where('nip', $nip)->first();

                if (User::where('email', $email)->when($user, fn($q) => $q->where('id', '!=', $user->id))->exists()) {
                    $summary[self::ERROR]++;
                    continue;
                }

                if (!$user) {
                    $user = User::create([
                        'nip'         => $nip,
                        'name'        => $nama,
                        'email'       => $email,
                        'jabatan_id'  => $jabatanId,
                        'role'        => $role,
                        'status_akun' => 'aktif',
                        'password'    => Hash::make($nip), // password awal = NIP (imbau ganti saat login pertama)
                    ]);
                    $user->catatJabatanAwal($tmt);
                    $summary[self::BARU]++;
                    continue;
                }

                // Sinkronkan data kontak dari file (sumber kebenaran), lalu pindah jabatan bila beda.
                $user->update(['name' => $nama, 'email' => $email, 'role' => $role]);
                $pindah = $user->pindahJabatan((int) $jabatanId, $tmt);
                $summary[$pindah ? self::PINDAH : self::SAMA]++;
            }

            return $summary;
        });
    }

    private function baris(int $baris, string $aksi, array $data): array
    {
        return array_merge(['baris' => $baris, 'aksi' => $aksi], $data);
    }

    /** Peta nama-jabatan-ternormalisasi → model Jabatan (dengan eselon). */
    private function jabatanLookup(): array
    {
        return Jabatan::with('eselon')->get()
            ->mapWithKeys(fn($j) => [$this->normalize($j->name) => $j])
            ->all();
    }

    private function normalize(string $name): string
    {
        return strtolower(preg_replace('/\s+/', ' ', trim($name)));
    }

    /**
     * @return array{0: ?Carbon, 1: ?string}  [tanggal, pesanError]
     */
    private function parseTmt(mixed $value): array
    {
        if ($value === null || $value === '' ) {
            return [null, null];
        }

        try {
            if (is_numeric($value)) {
                // Serial date Excel.
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value);
                return [Carbon::instance($dt), null];
            }
            return [Carbon::parse((string) $value), null];
        } catch (\Throwable $e) {
            return [null, "TMT '{$value}' bukan tanggal yang valid"];
        }
    }
}
