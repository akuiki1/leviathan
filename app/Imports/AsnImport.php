<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Pembaca sheet ASN. Cukup WithHeadingRow agar tiap baris ter-key nama kolom
 * (di-snake_case oleh Laravel Excel: "NIP"->nip, "Nama"->nama, "TMT"->tmt).
 * Analisis & penyimpanan ditangani AsnImportService, bukan di sini.
 */
class AsnImport implements WithHeadingRow
{
}
