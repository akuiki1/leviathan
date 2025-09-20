<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tim;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

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
        $users = User::all();
        return view('admin.tims.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_tim'   => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'sk_file'    => 'required|file|mimes:pdf|max:2048',
            'status'     => 'required|string',
            'anggota'    => 'required|array',
            'anggota.*'  => 'exists:users,id',
        ]);

        // Handle upload file SK
        if ($request->hasFile('sk_file')) {
            $file = $request->file('sk_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('sk', $filename, 'public');
            $validated['sk_file'] = $path;
        }

        $validated['created_by'] = Auth::id();

        $tim = Tim::create($validated);

        $tim->anggota()->sync($request->anggota);

        return redirect()->route('admin.tims.index')
            ->with('success', 'Tim berhasil ditambahkan');
    }


    public function show(Tim $tim)
    {
        $tim->load('anggota', 'creator');
        return view('admin.tims.show', compact('tim'));
    }

    public function edit(Tim $tim)
    {
        $users = User::all();
        $tim->load('anggota');
        return view('admin.tims.edit', compact('tim', 'users'));
    }

    public function update(Request $request, Tim $tim)
    {
        $data = $request->validate([
            'nama_tim' => 'required|string|max:255',
            'keterangan' => 'required|string',
            'sk_file' => 'nullable|file|mimes:pdf',
            'anggota' => 'required|array',
            'anggota.*' => 'exists:users,id',
        ]);

        if ($request->hasFile('sk_file')) {
            $file = $request->file('sk_file')->store('sk_files');
            $data['sk_file'] = $file;
        }

        $tim->update($data);

        // Sync anggota tim
        $tim->users()->sync($request->anggota);

        return redirect()->route('admin.tims.index')->with('success', 'Tim berhasil diperbarui!');
    }


    public function destroy(Tim $tim)
    {
        $tim->delete();
        return redirect()->route('admin.tims.index')->with('success', 'Tim berhasil dihapus');
    }


    /** =========================
     *  ACTION APPROVE / REJECT
     *  ========================= */
    public function checkMemberStatus(Tim $tim)
    {
        $memberStatus = $tim->anggota()->with('jabatan.eselon')->get()->map(function ($user) {
            $currentApprovedCount = $user->tims()
                ->where('status', 'approved')
                ->count();

            $maxHonor = $user->jabatan->eselon->maks_honor ?? 0;
            $remaining = max(0, $maxHonor - $currentApprovedCount);

            return [
                'id' => $user->id,
                'name' => $user->name,
                'nip' => $user->nip,
                'jabatan' => $user->jabatan->name,
                'current_count' => $currentApprovedCount,
                'max_honor' => $maxHonor,
                'remaining' => $remaining,
                'status' => $currentApprovedCount >= $maxHonor ? 'over_limit' : ($remaining <= 1 ? 'warning' : 'safe'),
                'percentage' => $maxHonor > 0 ? ($currentApprovedCount / $maxHonor) * 100 : 0
            ];
        });

        return response()->json([
            'members' => $memberStatus,
            'has_over_limit' => $memberStatus->contains('status', 'over_limit'),
            'has_warning' => $memberStatus->contains('status', 'warning')
        ]);
    }
    public function approve(Tim $tim)
    {
        $tim->update(['status' => 'approved']);

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'status' => $tim->status]);
        }

        return redirect()->route('admin.tims.index')->with('success', 'Tim berhasil di-approve.');
    }


    public function reject(Tim $tim)
    {
        $tim->update(['status' => 'rejected']);
        return back()->with('success', 'Tim ditolak!');
    }
}
