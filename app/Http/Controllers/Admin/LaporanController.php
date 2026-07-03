<?php

namespace App\Http\Controllers\Admin;

use App\Exports\LaporanHonorExport;
use App\Http\Controllers\Controller;
use App\Models\Tim;
use App\Services\HonorService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function index(Request $request, HonorService $honor)
    {
        $tahun = (int) $request->input('tahun', $honor->tahunBerjalan());

        $tahunTersedia = $this->tahunTersedia($tahun);

        $rekap = $honor->rekapPerEselon($tahun);

        return view('admin.laporan.index', compact('rekap', 'tahun', 'tahunTersedia'));
    }

    public function export(Request $request, HonorService $honor)
    {
        $tahun = (int) $request->input('tahun', $honor->tahunBerjalan());

        $rekap = $honor->rekapPerEselon($tahun);

        return Excel::download(new LaporanHonorExport($rekap, $tahun), "laporan-honor-{$tahun}.xlsx");
    }

    private function tahunTersedia(int $tahun)
    {
        $tahunTersedia = Tim::query()
            ->select('tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun');

        if (!$tahunTersedia->contains($tahun)) {
            $tahunTersedia = $tahunTersedia->push($tahun)->sortByDesc(fn($t) => $t)->values();
        }

        return $tahunTersedia;
    }
}
