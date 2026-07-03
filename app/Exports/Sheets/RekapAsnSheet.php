<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Sheet 2 — satu baris per ASN: posisi kuota & jumlah tim dibayar/tidak dibayar.
 * NIP disertakan sebagai kunci pencocokan dengan dokumen kepegawaian/SPJ.
 */
class RekapAsnSheet extends NipStringBinder implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithCustomValueBinder, ShouldAutoSize
{
    public function __construct(private Collection $perAsn)
    {
    }

    public function collection(): Collection
    {
        return $this->perAsn;
    }

    public function headings(): array
    {
        return [
            'NIP',
            'Nama',
            'Jabatan Saat Ini',
            'Eselon Saat Ini',
            'Kuota Saat Ini',
            'Tim Approved',
            'Tim Dibayar',
            'Tim Tidak Dibayar',
            'Tim Menunggu Approve',
            'Over Limit',
        ];
    }

    public function map($baris): array
    {
        $asn = $baris['asn'];

        return [
            (string) $asn->nip,
            $asn->name,
            $asn->jabatan->name ?? '-',
            $asn->jabatan->eselon->name ?? '-',
            $baris['maks_honor'],
            $baris['jumlah_tim_approved'],
            $baris['jumlah_dibayar'],
            $baris['jumlah_tidak_dibayar'],
            $baris['jumlah_pending'],
            $baris['is_over_limit'] ? 'YA' : '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());

        return [1 => ['font' => ['bold' => true]]];
    }

    public function title(): string
    {
        return 'Rekap per ASN';
    }
}
