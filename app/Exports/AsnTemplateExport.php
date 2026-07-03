<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Template Excel baku untuk import ASN. Berisi header + satu baris contoh
 * agar admin tahu format yang diharapkan (khususnya TMT).
 */
class AsnTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return ['NIP', 'Nama', 'Email', 'Jabatan', 'Role', 'TMT'];
    }

    public function array(): array
    {
        return [
            ['198501012010011001', 'Budi Santoso', 'budi@instansi.go.id', 'Kepala Seksi', 'staff', '2026-03-01'],
        ];
    }
}
