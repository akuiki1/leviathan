<?php

namespace App\Http\Controllers\Admin;

use App\Exports\LaporanHonorExport;
use App\Http\Controllers\Controller;
use App\Models\Eselon;
use App\Models\Tim;
use App\Services\HonorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function index(Request $request, HonorService $honor)
    {
        $tahun = (int) $request->input('tahun', $honor->tahunBerjalan());

        $data = $honor->dataAudit($tahun);

        return view('admin.laporan.index', [
            'tahun'         => $tahun,
            'tahunTersedia' => $this->tahunTersedia($tahun),
            'rekap'         => $data['perEselon'],
            'overLimit'     => $data['perAsn']->where('is_over_limit', true)->values(),
            'timCounts'     => $this->timCounts($tahun),
        ]);
    }

    /**
     * Rincian per ASN (level 2 drill-down dari rekap eselon).
     */
    public function asn(Request $request, HonorService $honor)
    {
        $tahun = (int) $request->input('tahun', $honor->tahunBerjalan());

        $perAsn = $honor->dataAudit($tahun)['perAsn'];

        // Filter eselon: memakai atribusi yang sama dengan rekap (eselon saat
        // bergabung ke tim; 0 = tanpa eselon) supaya angkanya rekonsiliasi.
        $eselonId = $request->input('eselon_id');
        if ($eselonId !== null && $eselonId !== '') {
            $perAsn = $perAsn->filter(fn ($r) => in_array((int) $eselonId, $r['eselon_keys']));
        }

        if ($request->boolean('over_limit')) {
            $perAsn = $perAsn->where('is_over_limit', true);
        }

        $q = trim((string) $request->input('q', ''));
        if ($q !== '') {
            $needle = mb_strtolower($q);
            $perAsn = $perAsn->filter(fn ($r) => str_contains(mb_strtolower($r['asn']->name), $needle)
                || str_contains($r['asn']->nip ?? '', $q));
        }

        return view('admin.laporan.asn', [
            'tahun'         => $tahun,
            'tahunTersedia' => $this->tahunTersedia($tahun),
            'perAsn'        => $perAsn->values(),
            'eselons'       => Eselon::orderBy('name')->get(),
            'filterEselon'  => $eselonId,
            'filterOver'    => $request->boolean('over_limit'),
            'q'             => $q,
        ]);
    }

    public function export(Request $request, HonorService $honor)
    {
        $tahun = (int) $request->input('tahun', $honor->tahunBerjalan());

        $export = new LaporanHonorExport(
            $honor->dataAudit($tahun),
            $tahun,
            Auth::user()->name,
            $this->timCounts($tahun),
        );

        $nama = sprintf('laporan-honor-%d-%s.xlsx', $tahun, now()->format('Ymd-His'));

        return Excel::download($export, $nama);
    }

    /**
     * Jumlah tim per status pada tahun anggaran (metadata cakupan laporan).
     *
     * @return array{approved:int, pending:int, rejected:int}
     */
    private function timCounts(int $tahun): array
    {
        $counts = Tim::query()
            ->where('tahun', $tahun)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'approved' => (int) ($counts['approved'] ?? 0),
            'pending'  => (int) ($counts['pending'] ?? 0),
            'rejected' => (int) ($counts['rejected'] ?? 0),
        ];
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
