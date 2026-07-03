<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export rekap honor per eselon (hasil HonorService::rekapPerEselon) untuk audit akhir tahun.
 */
class LaporanHonorExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(private Collection $rekap, private int $tahun)
    {
    }

    public function collection(): Collection
    {
        return $this->rekap;
    }

    public function headings(): array
    {
        return [
            'Eselon',
            'Kuota / Tahun (tim)',
            'Jumlah ASN',
            'ASN Over Limit',
            'Total Dibayar (Rp)',
            'Total Tidak Dibayar (Rp)',
        ];
    }

    public function map($baris): array
    {
        return [
            $baris['eselon']->name,
            $baris['eselon']->maks_honor,
            $baris['jumlah_asn'],
            $baris['jumlah_over_limit'],
            $baris['total_dibayar'],
            $baris['total_tidak_dibayar'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return "Laporan Honor {$this->tahun}";
    }
}
