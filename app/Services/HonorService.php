<?php

namespace App\Services;

use App\Models\Eselon;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Sumber kebenaran TUNGGAL untuk logika honor.
 *
 * Aturan bisnis (dikonfirmasi klien):
 *  - Honor berbentuk rupiah, PER ORANG PER TIM (disimpan di pivot tim_user.nominal_honor).
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
     * @return Collection<int, array{tim: \App\Models\Tim, nominal: int, status: string, dibayar: bool, urutan: int}>
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
                'nominal' => (int) $tim->pivot->nominal_honor,
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
                'nominal' => (int) $tim->pivot->nominal_honor,
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
     *               jumlah_tidak_dibayar:int, sisa_slot:int, total_honor:int, is_over_limit:bool}
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
            'total_honor'          => (int) $dibayar->sum('nominal'),
            'total_tidak_dibayar'  => (int) $tidakDibayar->sum('nominal'),
            'is_over_limit'        => $approved->count() > $maks,
        ];
    }

    /**
     * Rekap rupiah per eselon untuk satu tahun anggaran — dasar laporan audit
     * akhir tahun: total honor yang DIBAYAR vs total yang TIDAK DIBAYAR (nominal
     * dari tim yang melebihi kuota ASN, yang berpotensi jadi "kekurangan" kalau
     * tetap dibayar di luar sistem tanpa ketahuan sejak awal).
     *
     * @return Collection<int, array{eselon: Eselon, jumlah_asn: int, jumlah_over_limit: int,
     *                total_dibayar: int, total_tidak_dibayar: int}>
     */
    public function rekapPerEselon(int $tahun): Collection
    {
        return Eselon::with('jabatans.users')
            ->get()
            ->map(function (Eselon $eselon) use ($tahun) {
                $asns = $eselon->jabatans->flatMap->users;

                $totalDibayar      = 0;
                $totalTidakDibayar = 0;
                $jumlahOverLimit   = 0;

                foreach ($asns as $asn) {
                    $ringkasan = $this->ringkasan($asn, $tahun);
                    $totalDibayar      += $ringkasan['total_honor'];
                    $totalTidakDibayar += $ringkasan['total_tidak_dibayar'];
                    if ($ringkasan['is_over_limit']) {
                        $jumlahOverLimit++;
                    }
                }

                return [
                    'eselon'              => $eselon,
                    'jumlah_asn'          => $asns->count(),
                    'jumlah_over_limit'   => $jumlahOverLimit,
                    'total_dibayar'       => $totalDibayar,
                    'total_tidak_dibayar' => $totalTidakDibayar,
                ];
            });
    }
}
