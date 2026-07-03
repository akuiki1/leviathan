<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tim;
use App\Models\User;
use App\Services\HonorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TimController extends Controller
{
    public function index(Request $request)
    {
        $search     = $request->input('search');
        $status     = $request->input('status');
        $sort       = $request->input('sort', 'nama_tim');
        $direction  = $request->input('direction', 'asc');

        $tims = Tim::query()
            ->when($search, function ($q) use ($search) {
                $q->where('nama_tim', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%");
            })
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderByRaw("CASE WHEN status = 'pending' THEN 1 WHEN status = 'approved' THEN 2 ELSE 3 END")
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('admin.tims.index', compact('tims'));
    }

    public function create()
    {
        $users = User::with('jabatan')->orderBy('name')->get();
        return view('admin.tims.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_tim'   => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'sk_file'    => 'required|file|mimes:pdf|max:2048',
            'tahun'      => 'required|integer|min:2000|max:2100',
            'anggota'    => 'required|array|min:1',
            'anggota.*'  => 'exists:users,id',
            'nominal'    => 'array',
            'nominal.*'  => 'nullable|numeric|min:0',
        ]);

        $skPath = $request->file('sk_file')->store('sk_files', 'public');
        $nominals = $request->input('nominal', []);

        try {
            DB::beginTransaction();

            $tim = Tim::create([
                'nama_tim'   => $validated['nama_tim'],
                'keterangan' => $validated['keterangan'] ?? '',
                'sk_file'    => $skPath,
                'tahun'      => $validated['tahun'],
                'created_by' => Auth::id(),
                'status'     => 'pending',
            ]);

            $tim->users()->attach($this->pivotData($validated['anggota'], $nominals));

            DB::commit();

            return redirect()->route('admin.tims.index')->with('success', 'Tim berhasil ditambahkan');
        } catch (\Throwable $e) {
            DB::rollBack();
            Storage::disk('public')->delete($skPath);
            return back()->withInput()->with('error', 'Gagal menambahkan tim.');
        }
    }

    public function show(Tim $tim)
    {
        $tim->load('anggota.jabatan.eselon', 'creator');
        return view('admin.tims.show', compact('tim'));
    }

    public function edit(Tim $tim)
    {
        $users = User::with('jabatan')->orderBy('name')->get();
        $tim->load('users');
        return view('admin.tims.edit', compact('tim', 'users'));
    }

    public function update(Request $request, Tim $tim)
    {
        $validated = $request->validate([
            'nama_tim'   => 'required|string|max:255',
            'keterangan' => 'required|string',
            'sk_file'    => 'nullable|file|mimes:pdf|max:2048',
            'tahun'      => 'required|integer|min:2000|max:2100',
            'anggota'    => 'required|array|min:1',
            'anggota.*'  => 'exists:users,id',
            'nominal'    => 'array',
            'nominal.*'  => 'nullable|numeric|min:0',
        ]);

        $data = [
            'nama_tim'   => $validated['nama_tim'],
            'keterangan' => $validated['keterangan'],
            'tahun'      => $validated['tahun'],
        ];

        if ($request->hasFile('sk_file')) {
            $old = $tim->sk_file;
            $data['sk_file'] = $request->file('sk_file')->store('sk_files', 'public');
        }

        DB::transaction(function () use ($tim, $data, $validated, $request, &$old) {
            $tim->update($data);
            // sync TANPA menghilangkan data pivot (jabatan & nominal_honor)
            $tim->users()->sync($this->pivotData($validated['anggota'], $request->input('nominal', [])));
        });

        if (isset($old) && $old) {
            Storage::disk('public')->delete($old);
        }

        return redirect()->route('admin.tims.index')->with('success', 'Tim berhasil diperbarui!');
    }

    public function destroy(Tim $tim)
    {
        Storage::disk('public')->delete($tim->sk_file);
        $tim->delete();
        return redirect()->route('admin.tims.index')->with('success', 'Tim berhasil dihapus');
    }

    /**
     * Bangun payload pivot [user_id => ['jabatan'=>..., 'nominal_honor'=>...]].
     */
    private function pivotData(array $anggotaIds, array $nominals): array
    {
        return User::with('jabatan')
            ->whereIn('id', $anggotaIds)
            ->get()
            ->mapWithKeys(fn($u) => [$u->id => [
                'jabatan'       => $u->jabatan->name ?? null,
                'nominal_honor' => (int) ($nominals[$u->id] ?? 0),
            ]])
            ->all();
    }

    /** =========================
     *  ACTION APPROVE / REJECT
     *  ========================= */
    public function checkMemberStatus(Tim $tim, HonorService $honor)
    {
        $tim->load('users.jabatan.eselon');

        $members = $tim->users->map(function ($user) use ($tim, $honor) {
            $ringkasan = $honor->ringkasan($user, (int) $tim->tahun);
            $s = $honor->statusUntukTim($user, $tim->id, (int) $tim->tahun);

            $akanDibayar = $s && in_array($s['status'], [HonorService::DIBAYAR, HonorService::PREDIKSI_DIBAYAR]);
            $maks = $ringkasan['maks_honor'];

            return [
                'id'            => $user->id,
                'name'          => $user->name,
                'nip'           => $user->nip,
                'jabatan'       => $user->jabatan->name,
                'current_count' => $ringkasan['jumlah_tim_approved'],
                'max_honor'     => $maks,
                'remaining'     => $ringkasan['sisa_slot'],
                'nominal'       => $s['nominal'] ?? 0,
                'status'        => !$akanDibayar ? 'over_limit' : ($ringkasan['sisa_slot'] <= 1 ? 'warning' : 'safe'),
                'percentage'    => $maks > 0 ? min(100, ($ringkasan['jumlah_tim_approved'] / $maks) * 100) : 0,
            ];
        });

        return response()->json([
            'members'        => $members,
            'has_over_limit' => $members->contains('status', 'over_limit'),
            'has_warning'    => $members->contains('status', 'warning'),
        ]);
    }

    public function approve(Tim $tim)
    {
        DB::transaction(function () use ($tim) {
            $locked = Tim::whereKey($tim->id)->lockForUpdate()->firstOrFail();
            $locked->update(['status' => 'approved']);
            $tim->status = $locked->status;
        });

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'status' => $tim->status]);
        }

        return redirect()->route('admin.tims.index')->with('success', 'Tim berhasil di-approve.');
    }

    public function reject(Tim $tim)
    {
        DB::transaction(function () use ($tim) {
            $locked = Tim::whereKey($tim->id)->lockForUpdate()->firstOrFail();
            $locked->update(['status' => 'rejected']);
            $tim->status = $locked->status;
        });

        return back()->with('success', 'Tim ditolak!');
    }
}
