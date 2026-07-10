<?php

namespace App\Exports\Sheets;

use App\Services\HonorService;
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
 * Sheet 3 — buku besar audit: satu baris per keanggotaan ASN-per-tim, lengkap
 * dengan eselon & kuota yang berlaku saat bergabung dan alasan keputusan
 * dibayar/tidak. Dari sheet ini seluruh angka di sheet Ringkasan dapat
 * direkonstruksi (filter status "Dibayar"/"Tidak Dibayar").
 */
class KeanggotaanSheet extends NipStringBinder implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithCustomValueBinder, ShouldAutoSize
{
    public function __construct(private Collection $keanggotaan)
    {
    }

    public function collection(): Collection
    {
        return $this->keanggotaan;
    }

    public function headings(): array
    {
        return [
            'NIP',
            'Nama ASN',
            'Nama Tim',
            'Status Tim',
            'Tanggal Gabung',
            'Urutan Gabung',
            'Eselon Saat Gabung',
            'Kuota Saat Gabung',
            'Status Honor',
            'Keterangan',
        ];
    }

    public function map($baris): array
    {
        return [
            (string) $baris['asn']->nip,
            $baris['asn']->name,
            $baris['tim']->nama_tim,
            ucfirst($baris['status_tim']),
            $baris['tanggal_gabung']?->format('d/m/Y') ?? '-',
            $baris['urutan'],
            $baris['eselon_gabung']->name ?? 'Tanpa Eselon',
            $baris['kuota_gabung'],
            $this->labelStatus($baris['status_honor']),
            $this->keterangan($baris),
        ];
    }

    private function labelStatus(string $status): string
    {
        return match ($status) {
            HonorService::DIBAYAR                => 'Dibayar',
            HonorService::TIDAK_DIBAYAR          => 'Tidak Dibayar',
            HonorService::PREDIKSI_DIBAYAR       => 'Prediksi Dibayar (pending)',
            HonorService::PREDIKSI_TIDAK_DIBAYAR => 'Prediksi Tidak Dibayar (pending)',
            default                              => $status,
        };
    }

    private function keterangan(array $baris): string
    {
        return match ($baris['status_honor']) {
            HonorService::DIBAYAR                => sprintf('Masuk kuota — gabung urutan ke-%d, kuota saat gabung %d', $baris['urutan'], $baris['kuota_gabung']),
            HonorService::TIDAK_DIBAYAR          => sprintf('Melebihi kuota %d yang berlaku saat gabung', $baris['kuota_gabung']),
            HonorService::PREDIKSI_DIBAYAR       => 'Tim belum di-approve; akan dibayar bila disetujui',
            HonorService::PREDIKSI_TIDAK_DIBAYAR => 'Tim belum di-approve; tetap tidak dibayar walau disetujui (kuota habis)',
            default                              => '',
        };
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());

        return [1 => ['font' => ['bold' => true]]];
    }

    public function title(): string
    {
        return 'Rincian Keanggotaan';
    }
}
