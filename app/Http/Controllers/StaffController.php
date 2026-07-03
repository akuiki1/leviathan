<?php

namespace App\Http\Controllers;

use App\Models\Tim;
use App\Models\User;
use App\Services\HonorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class StaffController extends Controller
{
    /**
     * Map status HonorService -> label lama yang dipakai blade.
     */
    private array $legacyLabel = [
        HonorService::DIBAYAR                => 'Honor Diterima',
        HonorService::TIDAK_DIBAYAR          => 'Tidak menerima honor lagi',
        HonorService::PREDIKSI_DIBAYAR       => 'Akan menerima honor jika disetujui',
        HonorService::PREDIKSI_TIDAK_DIBAYAR => 'Tidak akan menerima honor',
    ];

    /**
     * Bangun peta status honor per (anggota, tim) untuk sekumpulan tim.
     * Menghitung per tahun anggaran masing-masing tim, dengan cache per (anggota, tahun).
     *
     * @return array{0: array<int,array<int,array{status:string,label:string}>>, 1: array<int,int>}
     *         [statusHonorPerTim, timCountPerUser(tahun berjalan)]
     */
    private function buildStatusMap($tims, HonorService $honor): array
    {
        $cache = [];
        $resolve = function (User $asn, int $tahun) use (&$cache, $honor) {
            return $cache[$asn->id][$tahun] ??= $honor->statusPerTim($asn, $tahun);
        };

        $statusHonorPerTim = [];
        foreach ($tims as $tim) {
            foreach ($tim->users as $anggota) {
                $s = $resolve($anggota, (int) $tim->tahun)->get($tim->id);
                if ($s) {
                    $statusHonorPerTim[$anggota->id][$tim->id] = [
                        'status' => $s['status'],
                        'label'  => $this->legacyLabel[$s['status']] ?? $s['status'],
                    ];
                }
            }
        }

        // Jumlah tim DIBAYAR tahun berjalan per anggota (untuk tampilan "x/maks")
        $currentYear = $honor->tahunBerjalan();
        $timCountPerUser = [];
        foreach ($tims->flatMap->users->unique('id') as $anggota) {
            $timCountPerUser[$anggota->id] = $resolve($anggota, $currentYear)
                ->where('status', HonorService::DIBAYAR)->count();
        }

        return [$statusHonorPerTim, $timCountPerUser];
    }

    public function indexDashboard(HonorService $honor)
    {
        $user = Auth::user()->load('jabatan.eselon');
        $tahunBerjalan = $honor->tahunBerjalan();

        $tims = $user->tims()
            ->where('tims.tahun', $tahunBerjalan)
            ->with(['users.jabatan.eselon'])
            ->latest()
            ->get();

        [$statusHonorPerTim, $timCountPerUser] = $this->buildStatusMap($tims, $honor);

        $ringkasanDiri = $honor->ringkasan($user, $tahunBerjalan);
        $maksHonor = $ringkasanDiri['maks_honor'];
        $totalTim  = $ringkasanDiri['jumlah_dibayar']; // honor diterima tahun berjalan

        return view('staff.index', compact(
            'user',
            'tims',
            'totalTim',
            'maksHonor',
            'ringkasanDiri',
            'timCountPerUser',
            'statusHonorPerTim',
            'tahunBerjalan'
        ));
    }

    public function indexProfile(HonorService $honor)
    {
        $user = Auth::user()->load('jabatan.eselon');
        $ringkasan = $honor->ringkasan($user);

        return view('staff.profile', compact('user', 'ringkasan'));
    }

    public function indexTim(Request $request, HonorService $honor)
    {
        $user = Auth::user()->load('jabatan.eselon');

        $tahunList = $user->tims()
            ->distinct()
            ->orderByDesc('tims.tahun')
            ->pluck('tims.tahun');

        if ($tahunList->isEmpty()) {
            $tahunList = collect([$honor->tahunBerjalan()]);
        }

        $tahun = (int) ($request->tahun ?: $tahunList->first());

        $tims = $user->tims()
            ->where('tims.tahun', $tahun)
            ->with(['users.jabatan.eselon'])
            ->latest()
            ->get();

        [$statusHonorPerTim] = $this->buildStatusMap($tims, $honor);

        $ringkasan = $honor->ringkasan($user, $tahun);
        $maksHonor = $ringkasan['maks_honor'];
        $approvedTimCount = [$user->id => $ringkasan['jumlah_dibayar']];
        $progress = $maksHonor > 0 ? min(100, ($ringkasan['jumlah_dibayar'] / $maksHonor) * 100) : 0;

        return view('staff.tim.index', compact(
            'user',
            'tims',
            'tahun',
            'tahunList',
            'approvedTimCount',
            'maksHonor',
            'progress',
            'statusHonorPerTim',
            'ringkasan'
        ));
    }

    public function createTim(HonorService $honor)
    {
        $availableUsers = User::where('role', 'staff')
            ->with('jabatan.eselon')
            ->orderBy('name')
            ->get();

        $me = Auth::user()->load('jabatan.eselon');
        if (!$availableUsers->contains('id', $me->id)) {
            $availableUsers->push($me);
        }

        $currentYear = $honor->tahunBerjalan();
        $timCounts = [];
        foreach ($availableUsers as $u) {
            $timCounts[$u->id] = $honor->ringkasan($u, $currentYear)['jumlah_dibayar'];
        }

        return view('staff.tim.create', compact('availableUsers', 'timCounts'));
    }

    public function storeTim(Request $request, HonorService $honor)
    {
        $validated = $request->validate([
            'nama_tim'    => 'required|string|max:255',
            'keterangan'  => 'required|string',
            'sk_file'     => 'required|file|mimes:pdf|max:2048',
            'anggota'     => 'required|array|min:1',
            'anggota.*'   => 'exists:users,id',
            'nominal'     => 'array',
            'nominal.*'   => 'nullable|numeric|min:0',
        ]);

        // Pembuat tim wajib jadi anggota
        if (!in_array(Auth::id(), $validated['anggota'])) {
            throw ValidationException::withMessages([
                'anggota' => 'Anda harus menjadi bagian dari tim yang Anda buat.',
            ]);
        }
        $validated['anggota'] = array_values(array_unique(array_merge($validated['anggota'], [Auth::id()])));

        $skPath = $request->file('sk_file')->store('sk_files', 'public');
        $nominals = $request->input('nominal', []);

        try {
            DB::beginTransaction();

            $tim = Tim::create([
                'nama_tim'   => $validated['nama_tim'],
                'keterangan' => $validated['keterangan'],
                'sk_file'    => $skPath,
                'tahun'      => $honor->tahunBerjalan(),
                'created_by' => Auth::id(),
                'status'     => 'pending',
            ]);

            $anggotaData = collect($validated['anggota'])->mapWithKeys(function ($userId) use ($nominals) {
                $u = User::with('jabatan')->find($userId);
                return [$userId => [
                    'jabatan'       => $u->jabatan->name ?? null,
                    'nominal_honor' => (int) ($nominals[$userId] ?? 0),
                ]];
            });

            $tim->users()->attach($anggotaData->all());

            DB::commit();

            return redirect()
                ->route('staff.tim.index')
                ->with('success', 'Tim berhasil dibuat dan menunggu persetujuan admin.')
                ->with('teamName', $tim->nama_tim);
        } catch (\Throwable $e) {
            DB::rollBack();
            if (isset($skPath)) {
                Storage::disk('public')->delete($skPath);
            }
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat tim. Silakan coba lagi.');
        }
    }
}
