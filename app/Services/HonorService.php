<?php

namespace App\Services;

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

    public function maksHonor(User $asn): int
    {
        return (int) ($asn->jabatan?->eselon?->maks_honor ?? 0);
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
        $maks  = $this->maksHonor($asn);

        $tims = $asn->tims()
            ->where('tims.tahun', $tahun)
            ->whereIn('tims.status', ['approved', 'pending'])
            ->orderBy('tim_user.created_at', 'asc') // urutan bergabung (stabil, tak berubah saat edit)
            ->orderBy('tim_user.id', 'asc')
            ->get();

        $approved = $tims->where('status', 'approved')->values();
        $pending  = $tims->where('status', 'pending')->values();

        $hasil = collect();

        foreach ($approved as $i => $tim) {
            $dibayar = $i < $maks;
            $hasil->put($tim->id, [
                'tim'     => $tim,
                'nominal' => (int) $tim->pivot->nominal_honor,
                'status'  => $dibayar ? self::DIBAYAR : self::TIDAK_DIBAYAR,
                'dibayar' => $dibayar,
                'urutan'  => $i + 1,
            ]);
        }

        $approvedCount = $approved->count();
        foreach ($pending as $j => $tim) {
            $posisi  = $approvedCount + $j; // posisi (0-based) bila tim ini kelak disetujui
            $dibayar = $posisi < $maks;
            $hasil->put($tim->id, [
                'tim'     => $tim,
                'nominal' => (int) $tim->pivot->nominal_honor,
                'status'  => $dibayar ? self::PREDIKSI_DIBAYAR : self::PREDIKSI_TIDAK_DIBAYAR,
                'dibayar' => false, // prediksi belum benar-benar dibayar
                'urutan'  => $posisi + 1,
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

        $approved = $status->whereIn('status', [self::DIBAYAR, self::TIDAK_DIBAYAR]);
        $dibayar  = $approved->where('dibayar', true);
        $maks     = $this->maksHonor($asn);

        return [
            'tahun'                => $tahun,
            'maks_honor'           => $maks,
            'jumlah_tim_approved'  => $approved->count(),
            'jumlah_dibayar'       => $dibayar->count(),
            'jumlah_tidak_dibayar' => $approved->count() - $dibayar->count(),
            'sisa_slot'            => max(0, $maks - $dibayar->count()),
            'total_honor'          => (int) $dibayar->sum('nominal'),
            'is_over_limit'        => $approved->count() > $maks,
        ];
    }
}
