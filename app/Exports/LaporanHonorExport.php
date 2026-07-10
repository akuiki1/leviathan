<?php

namespace App\Exports;

use App\Exports\Sheets\KeanggotaanSheet;
use App\Exports\Sheets\OverLimitSheet;
use App\Exports\Sheets\RekapAsnSheet;
use App\Exports\Sheets\RingkasanSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Laporan honor untuk audit akhir tahun — 4 sheet yang saling rekonsiliasi
 * karena dibangun dari satu sumber data (HonorService::dataAudit):
 *
 *  1. Ringkasan            — rekap per eselon + metadata (kapan/oleh siapa, cakupan).
 *  2. Rekap per ASN        — satu baris per ASN, posisi kuota masing-masing.
 *  3. Rincian Keanggotaan  — buku besar: satu baris per keanggotaan ASN-per-tim
 *                            dengan snapshot eselon/kuota saat gabung + alasan.
 *  4. ASN Over Limit       — daftar pengecualian yang perlu ditindaklanjuti.
 */
class LaporanHonorExport implements WithMultipleSheets
{
    public function __construct(
        private array $data,
        private int $tahun,
        private string $oleh,
        private array $timCounts,
    ) {
    }

    public function sheets(): array
    {
        return [
            new RingkasanSheet($this->data['perEselon'], $this->tahun, $this->oleh, $this->timCounts),
            new RekapAsnSheet($this->data['perAsn']),
            new KeanggotaanSheet($this->data['keanggotaan']),
            new OverLimitSheet($this->data['perAsn']->where('is_over_limit', true)->values()),
        ];
    }
}
