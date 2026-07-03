<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AsnTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\AsnImport;
use App\Services\AsnImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class AsnImportController extends Controller
{
    private const DISK = 'local';
    private const DIR  = 'imports';

    public function form()
    {
        return view('admin.users.import');
    }

    public function template()
    {
        return Excel::download(new AsnTemplateExport, 'template_import_asn.xlsx');
    }

    public function preview(Request $request, AsnImportService $service)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        // Simpan sementara agar bisa dibaca ulang saat konfirmasi (jangan percaya diff dari klien).
        $token = Str::uuid() . '.' . $request->file('file')->getClientOriginalExtension();
        $request->file('file')->storeAs(self::DIR, $token, self::DISK);

        $rows = $this->readRows($token);
        if ($rows === null) {
            Storage::disk(self::DISK)->delete(self::DIR . '/' . $token);
            return back()->with('error', 'File tidak bisa dibaca atau kosong.');
        }

        $analisis = $service->analyze($rows);

        return view('admin.users.import-preview', [
            'token'   => $token,
            'rows'    => $analisis['rows'],
            'summary' => $analisis['summary'],
            'total'   => $rows->count(),
        ]);
    }

    public function apply(Request $request, AsnImportService $service)
    {
        $request->validate(['token' => 'required|string']);

        $token = basename($request->token); // cegah path traversal
        $path  = self::DIR . '/' . $token;

        if (!Storage::disk(self::DISK)->exists($path)) {
            return redirect()->route('admin.users.import.form')
                ->with('error', 'Sesi import kedaluwarsa, silakan unggah ulang.');
        }

        $rows = $this->readRows($token);
        if ($rows === null) {
            Storage::disk(self::DISK)->delete($path);
            return redirect()->route('admin.users.import.form')->with('error', 'File gagal dibaca.');
        }

        $summary = $service->apply($rows);
        Storage::disk(self::DISK)->delete($path);

        $pesan = "Import selesai: {$summary['baru']} ASN baru, {$summary['pindah']} pindah jabatan, "
               . "{$summary['sama']} tidak berubah, {$summary['error']} dilewati (error).";

        return redirect()->route('admin.users.index')->with('success', $pesan);
    }

    /**
     * Baca file tersimpan → Collection baris ter-key heading. Null bila gagal/kosong.
     */
    private function readRows(string $token): ?\Illuminate\Support\Collection
    {
        $fullPath = Storage::disk(self::DISK)->path(self::DIR . '/' . $token);

        try {
            $sheet = Excel::toCollection(new AsnImport, $fullPath)->first();
        } catch (\Throwable $e) {
            return null;
        }

        if (!$sheet || $sheet->isEmpty()) {
            return null;
        }

        // Buang baris yang seluruh selnya kosong.
        return $sheet
            ->map(fn($row) => $row->toArray())
            ->reject(fn($row) => collect($row)->filter(fn($v) => trim((string) $v) !== '')->isEmpty())
            ->values();
    }
}
