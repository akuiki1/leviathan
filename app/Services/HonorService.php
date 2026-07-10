<?php

namespace App\Services;

use App\Models\Eselon;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Sumber kebenaran TUNGGAL untuk logika honor.
 *
 * Aturan bisnis (dikonfirmasi klien):
 *  - Honor dilacak sebagai JUMLAH TIM (count), bukan nominal rupiah.
 *  - Tiap ASN punya kuota `maks_honor` (dari eselon jabatannya) = jumlah maksimal
 *    tim yang DIBAYAR dalam satu tahun anggaran.
 *  - Kuota RESET per tahun anggaran (tims.tahun).
 *  - ASN boleh ikut tim melebihi kuota, tapi tim ke-(maks+1) dst TIDAK dibayar.
 *  - Urutan penentu "siapa yang dibayar": waktu bergabung (tim_user.created_at)
 *    dari tim yang sudah APPROVED. Tim PENDING hanya prediksi.
 *  - Kuota yang dipakai untuk MENGEVALUASI tiap keanggotaan adalah kuota eselon
 *    yang berlaku PAS tanggal ASN bergabung ke tim itu (snapshot historis via
 *    jabatan_histories), bukan eselon ASN saat ini. Jadi kalau ASN promosi di
 *    tengah tahun, keanggotaan sebelum promosi tetap dinilai pakai kuota lama,
 *    dan sesudahnya pakai kuota baru (dikonfirmasi klien 2026-07-03).
 */
class HonorService
{
    public const DIBAYAR                = 'dibayar';
    public const TIDAK_DIBAYAR          = 'tidak_dibayar';
    public const PREDIKSI_DIBAYAR       = 'prediksi_dibayar';
    public const PREDIKSI_TIDAK_DIBAYAR = 'prediksi_tidak_dibayar';

    public function tahunBerjalan(): int
    {
        return (int) now()->year;
    }

    /**
     * Kuota eselon ASN SAAT INI. Dipakai untuk tampilan ringkasan (mis. "sisa slot
     * kalau gabung tim baru sekarang"), bukan untuk menilai keanggotaan lama.
     */
    public function maksHonor(User $asn): int
    {
        return $this->maksHonorPada($asn, now());
    }

    /**
     * Kuota eselon ASN yang berlaku PADA tanggal tertentu (snapshot historis).
     */
    public function maksHonorPada(User $asn, \DateTimeInterface $tanggal): int
    {
        return (int) ($asn->jabatanPada($tanggal)?->eselon?->maks_honor ?? 0);
    }

    /**
     * Status honor tiap keanggotaan tim milik ASN pada satu tahun anggaran.
     *
     * @return Collection<int, array{tim: \App\Models\Tim, status: string, dibayar: bool, urutan: int}>
     *         di-key berdasarkan tim_id.
     */
    public function statusPerTim(User $asn, ?int $tahun = null): Collection
    {
        $tahun = $tahun ?? $this->tahunBerjalan();

        $tims = $asn->tims()
            ->where('tims.tahun', $tahun)
            ->whereIn('tims.status', ['approved', 'pending'])
            ->orderBy('tim_user.created_at', 'asc') // urutan bergabung (stabil, tak berubah saat edit)
            ->orderBy('tim_user.id', 'asc')
            ->get();

        $approved = $tims->where('status', 'approved')->values();
        $pending  = $tims->where('status', 'pending')->values();

        $hasil = collect();

        // Kuota dievaluasi per keanggotaan: pakai eselon yang berlaku PAS tanggal
        // ASN bergabung ke tim itu, dibandingkan dengan running count yang sudah
        // dibayar sejauh ini (bukan angka kuota tunggal untuk seluruh tahun).
        $dibayarCount = 0;
        foreach ($approved as $i => $tim) {
            $maksSaatItu = $this->maksHonorPada($asn, $tim->pivot->created_at);
            $dibayar     = $dibayarCount < $maksSaatItu;
            if ($dibayar) {
                $dibayarCount++;
            }
            $hasil->put($tim->id, [
                'tim'     => $tim,
                'status'  => $dibayar ? self::DIBAYAR : self::TIDAK_DIBAYAR,
                'dibayar' => $dibayar,
                'urutan'  => $i + 1,
            ]);
        }

        // Prediksi: simulasikan tim pending disetujui berurutan, lanjutan dari
        // running count tim approved, masing-masing pakai kuota di tanggal gabungnya.
        $posisiRunning = $dibayarCount;
        foreach ($pending as $j => $tim) {
            $maksSaatItu = $this->maksHonorPada($asn, $tim->pivot->created_at);
            $dibayar     = $posisiRunning < $maksSaatItu;
            if ($dibayar) {
                $posisiRunning++;
            }
            $hasil->put($tim->id, [
                'tim'     => $tim,
                'status'  => $dibayar ? self::PREDIKSI_DIBAYAR : self::PREDIKSI_TIDAK_DIBAYAR,
                'dibayar' => false, // prediksi belum benar-benar dibayar
                'urutan'  => $j + 1 + count($approved),
            ]);
        }

        return $hasil;
    }

    /**
     * Status honor satu ASN untuk SATU tim tertentu (dipakai saat approval / daftar anggota).
     */
    public function statusUntukTim(User $asn, int $timId, ?int $tahun = null): ?array
    {
        return $this->statusPerTim($asn, $tahun)->get($timId);
    }

    /**
     * Ringkasan honor ASN pada satu tahun anggaran.
     *
     * @return array{tahun:int, maks_honor:int, jumlah_tim_approved:int, jumlah_dibayar:int,
     *               jumlah_tidak_dibayar:int, sisa_slot:int, is_over_limit:bool}
     */
    public function ringkasan(User $asn, ?int $tahun = null): array
    {
        $tahun  = $tahun ?? $this->tahunBerjalan();
        $status = $this->statusPerTim($asn, $tahun);

        $approved     = $status->whereIn('status', [self::DIBAYAR, self::TIDAK_DIBAYAR]);
        $dibayar      = $approved->where('dibayar', true);
        $tidakDibayar = $approved->where('dibayar', false);
        $maks         = $this->maksHonor($asn);

        return [
            'tahun'                => $tahun,
            'maks_honor'           => $maks,
            'jumlah_tim_approved'  => $approved->count(),
            'jumlah_dibayar'       => $dibayar->count(),
            'jumlah_tidak_dibayar' => $tidakDibayar->count(),
            'sisa_slot'            => max(0, $maks - $dibayar->count()),
            // Over limit = punya keanggotaan approved yang tidak dibayar. Definisi
            // "jumlah approved > kuota saat ini" salah pasca-promosi: kuota naik di
            // tengah tahun bisa menutupi keanggotaan lama yang dinilai kuota lama.
            'is_over_limit'        => $tidakDibayar->count() > 0,
        ];
    }

    /**
     * Rekap jumlah tim per eselon untuk satu tahun anggaran (lihat dataAudit()).
     */
    public function rekapPerEselon(int $tahun): Collection
    {
        return $this->dataAudit($tahun)['perEselon'];
    }

    /**
     * Data lengkap laporan audit honor satu tahun anggaran, dibangun sekali jalan.
     * Dipakai halaman laporan admin + export Excel, supaya semua angka (rekap
     * eselon, rekap ASN, rincian keanggotaan) dijamin berasal dari sumber yang
     * sama dan saling rekonsiliasi.
     *
     * Atribusi eselon per KEANGGOTAAN memakai eselon yang berlaku PAS tanggal ASN
     * bergabung ke tim — konsisten dengan logika kuota snapshot — bukan eselon ASN
     * saat ini. ASN yang mutasi di tengah tahun bisa terhitung di dua baris eselon.
     * ASN tanpa keanggotaan approved dihitung pada eselon saat ini agar tidak
     * hilang dari rekap; yang tak punya eselon masuk baris "Tanpa Eselon" (null).
     *
     * @return array{
     *     perEselon: Collection<int, array{eselon: ?Eselon, jumlah_asn: int, jumlah_over_limit: int,
     *                jumlah_tim_dibayar: int, jumlah_tim_tidak_dibayar: int}>,
     *     perAsn: Collection<int, array{asn: User, jumlah_tim_approved: int, jumlah_dibayar: int,
     *                jumlah_tidak_dibayar: int, jumlah_pending: int, maks_honor: int,
     *                is_over_limit: bool, eselon_keys: array<int>}>,
     *     keanggotaan: Collection<int, array{asn: User, tim: \App\Models\Tim, status_tim: string,
     *                tanggal_gabung: \Illuminate\Support\Carbon, urutan: int, eselon_gabung: ?Eselon,
     *                kuota_gabung: int, status_honor: string, dibayar: bool}>,
     * }
     */
    public function dataAudit(int $tahun): array
    {
        $asns = User::where('role', 'staff')
            ->with(['jabatan.eselon', 'jabatanHistories.jabatan.eselon'])
            ->orderBy('name')
            ->get();

        $keanggotaan = collect();
        $perAsn      = collect();

        foreach ($asns as $asn) {
            $status = $this->statusPerTim($asn, $tahun);

            $eselonKeys = [];
            foreach ($status as $s) {
                $eselonGabung = $asn->jabatanPada($s['tim']->pivot->created_at)?->eselon;

                $keanggotaan->push([
                    'asn'            => $asn,
                    'tim'            => $s['tim'],
                    'status_tim'     => $s['tim']->status,
                    'tanggal_gabung' => $s['tim']->pivot->created_at,
                    'urutan'         => $s['urutan'],
                    'eselon_gabung'  => $eselonGabung,
                    'kuota_gabung'   => (int) ($eselonGabung?->maks_honor ?? 0),
                    'status_honor'   => $s['status'],
                    'dibayar'        => $s['dibayar'],
                ]);

                if (in_array($s['status'], [self::DIBAYAR, self::TIDAK_DIBAYAR])) {
                    $eselonKeys[$eselonGabung?->id ?? 0] = true;
                }
            }

            $approved     = $status->whereIn('status', [self::DIBAYAR, self::TIDAK_DIBAYAR]);
            $tidakDibayar = $approved->where('dibayar', false)->count();

            // ASN tanpa keanggotaan approved tetap muncul di rekap eselonnya saat ini.
            if (empty($eselonKeys)) {
                $eselonKeys[$asn->jabatan?->eselon?->id ?? 0] = true;
            }

            $perAsn->push([
                'asn'                  => $asn,
                'jumlah_tim_approved'  => $approved->count(),
                'jumlah_dibayar'       => $approved->where('dibayar', true)->count(),
                'jumlah_tidak_dibayar' => $tidakDibayar,
                'jumlah_pending'       => $status->count() - $approved->count(),
                'maks_honor'           => $this->maksHonor($asn),
                'is_over_limit'        => $tidakDibayar > 0,
                'eselon_keys'          => array_keys($eselonKeys),
            ]);
        }

        return [
            'perEselon'   => $this->rekapDariKeanggotaan($keanggotaan, $perAsn),
            'perAsn'      => $perAsn,
            'keanggotaan' => $keanggotaan,
        ];
    }

    /**
     * Agregasi rekap per eselon dari baris keanggotaan (kunci 0 = tanpa eselon).
     */
    private function rekapDariKeanggotaan(Collection $keanggotaan, Collection $perAsn): Collection
    {
        $asnIds = []; $overIds = []; $dibayar = []; $tidak = [];

        foreach ($keanggotaan as $row) {
            if (!in_array($row['status_honor'], [self::DIBAYAR, self::TIDAK_DIBAYAR])) {
                continue; // prediksi (pending) tidak masuk hitungan rekap
            }
            $key = $row['eselon_gabung']?->id ?? 0;
            $asnIds[$key][$row['asn']->id] = true;
            if ($row['dibayar']) {
                $dibayar[$key] = ($dibayar[$key] ?? 0) + 1;
            } else {
                $tidak[$key] = ($tidak[$key] ?? 0) + 1;
                $overIds[$key][$row['asn']->id] = true;
            }
        }

        // ASN tanpa aktivitas approved: eselon_keys-nya berisi eselon saat ini.
        foreach ($perAsn as $p) {
            if ($p['jumlah_tim_approved'] === 0) {
                foreach ($p['eselon_keys'] as $key) {
                    $asnIds[$key][$p['asn']->id] = true;
                }
            }
        }

        $rekap = Eselon::orderBy('name')->get()->map(fn (Eselon $e) => [
            'eselon'                   => $e,
            'jumlah_asn'               => count($asnIds[$e->id] ?? []),
            'jumlah_over_limit'        => count($overIds[$e->id] ?? []),
            'jumlah_tim_dibayar'       => $dibayar[$e->id] ?? 0,
            'jumlah_tim_tidak_dibayar' => $tidak[$e->id] ?? 0,
        ]);

        if (isset($asnIds[0]) || isset($dibayar[0]) || isset($tidak[0])) {
            $rekap->push([
                'eselon'                   => null,
                'jumlah_asn'               => count($asnIds[0] ?? []),
                'jumlah_over_limit'        => count($overIds[0] ?? []),
                'jumlah_tim_dibayar'       => $dibayar[0] ?? 0,
                'jumlah_tim_tidak_dibayar' => $tidak[0] ?? 0,
            ]);
        }

        return $rekap;
    }
}
