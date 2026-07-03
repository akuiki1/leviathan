<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Sheet 1 — rekap per eselon + blok metadata (kapan/oleh siapa digenerate,
 * cakupan data, dan catatan definisi) supaya file berdiri sendiri sebagai
 * dokumen audit tanpa perlu membuka aplikasinya.
 */
class RingkasanSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    /** Baris tempat header tabel berada (setelah blok metadata). */
    private const HEADER_ROW = 8;

    public function __construct(
        private Collection $rekap,
        private int $tahun,
        private string $oleh,
        private array $timCounts,
    ) {
    }

    public function array(): array
    {
        $rows = [
            ['LAPORAN HONORARIUM TIM PER ESELON'],
            ['Tahun Anggaran', $this->tahun],
            ['Digenerate', now()->format('d/m/Y H:i')],
            ['Digenerate oleh', $this->oleh],
            ['Cakupan', sprintf(
                '%d tim approved (dihitung) · %d tim pending (belum diproses, tidak dihitung) · %d tim ditolak (diabaikan)',
                $this->timCounts['approved'],
                $this->timCounts['pending'],
                $this->timCounts['rejected'],
            )],
            ['Catatan', 'Keanggotaan dihitung pada eselon yang berlaku saat ASN bergabung ke tim (snapshot). '
                . 'ASN yang mutasi eselon di tengah tahun dapat terhitung pada dua baris eselon.'],
            [],
            ['Eselon', 'Kuota / Tahun (tim)', 'Jumlah ASN', 'ASN Over Limit', 'Tim Dibayar', 'Tim Tidak Dibayar'],
        ];

        foreach ($this->rekap as $baris) {
            $rows[] = [
                $baris['eselon']->name ?? 'Tanpa Eselon',
                $baris['eselon']->maks_honor ?? '-',
                $baris['jumlah_asn'],
                $baris['jumlah_over_limit'],
                $baris['jumlah_tim_dibayar'],
                $baris['jumlah_tim_tidak_dibayar'],
            ];
        }

        $rows[] = [
            'TOTAL',
            '',
            $this->rekap->sum('jumlah_asn'),
            $this->rekap->sum('jumlah_over_limit'),
            $this->rekap->sum('jumlah_tim_dibayar'),
            $this->rekap->sum('jumlah_tim_tidak_dibayar'),
        ];

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $totalRow = self::HEADER_ROW + $this->rekap->count() + 1;

        return [
            1                => ['font' => ['bold' => true, 'size' => 13]],
            self::HEADER_ROW => ['font' => ['bold' => true]],
            $totalRow        => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Ringkasan';
    }
}
