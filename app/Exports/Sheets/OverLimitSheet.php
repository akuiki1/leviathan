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
 * Sheet 4 — daftar pengecualian: hanya ASN yang punya keanggotaan approved
 * yang TIDAK dibayar (melebihi kuota). Ini temuan yang paling dicari auditor.
 */
class OverLimitSheet extends NipStringBinder implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithCustomValueBinder, ShouldAutoSize
{
    public function __construct(private Collection $overLimit)
    {
    }

    public function collection(): Collection
    {
        return $this->overLimit;
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
        return 'ASN Over Limit';
    }
}
