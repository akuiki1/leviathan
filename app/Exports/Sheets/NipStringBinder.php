<?php

namespace App\Exports\Sheets;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

/**
 * Value binder untuk sheet laporan: string numerik (NIP 18 digit dsb.) tetap
 * disimpan sebagai teks, agar tidak kehilangan presisi / berubah jadi notasi
 * ilmiah di Excel. Angka asli (int) tetap tersimpan sebagai angka.
 */
class NipStringBinder extends DefaultValueBinder
{
    public function bindValue(Cell $cell, $value)
    {
        if (is_string($value) && is_numeric($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
