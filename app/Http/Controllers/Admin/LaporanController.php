<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tim;
use App\Services\HonorService;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request, HonorService $honor)
    {
        $tahun = (int) $request->input('tahun', $honor->tahunBerjalan());

        $tahunTersedia = Tim::query()
            ->select('tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun');

        if (!$tahunTersedia->contains($tahun)) {
            $tahunTersedia = $tahunTersedia->push($tahun)->sortByDesc(fn($t) => $t)->values();
        }

        $rekap = $honor->rekapPerEselon($tahun);

        return view('admin.laporan.index', compact('rekap', 'tahun', 'tahunTersedia'));
    }
}
