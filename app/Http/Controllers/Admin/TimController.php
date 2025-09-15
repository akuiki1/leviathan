<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tim;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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

        $validated['created_by'] = auth()->id();

        $tim = Tim::create($validated);

        $tim->anggota()->sync($request->anggota);

        return redirect()->route('admin.tims.index')
            ->with('success', 'Tim berhasil ditambahkan');
    }


    public function edit(Tim $tim)
    {
        $users = User::all();
        $tim->load('anggota');
        return view('admin.tims.edit', compact('tim', 'users'));
    }

    public function update(Request $request, Tim $tim)
    {
        $validated = $request->validate([
            'nama_tim'   => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'sk_file'    => 'nullable|file|mimes:pdf|max:2048', // boleh kosong
            'status'     => 'required|string',
            'anggota'    => 'required|array',
            'anggota.*'  => 'exists:users,id',
        ]);

        // Kalau user upload file baru, replace
        if ($request->hasFile('sk_file')) {
            $file = $request->file('sk_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('sk', $filename, 'public');
            $validated['sk_file'] = $path;
        } else {
            unset($validated['sk_file']); // biar gak overwrite kosong
        }

        $validated['created_by'] = auth()->id();

        $tim->update($validated);

        $tim->anggota()->sync($request->anggota);

        return redirect()->route('admin.tims.index')
            ->with('success', 'Tim berhasil diperbarui');
    }

    public function destroy(Tim $tim)
    {
        $tim->delete();
        return redirect()->route('admin.tims.index')->with('success', 'Tim berhasil dihapus');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return redirect()->route('admin.tims.index')->with('error', 'Tidak ada tim yang dipilih.');
        }

        DB::transaction(function () use ($ids) {
            Tim::whereIn('id', $ids)->delete();
        });

        return redirect()->route('admin.tims.index')->with('success', count($ids) . ' tim berhasil dihapus.');
    }

    /** =========================
     *  ACTION APPROVE / REJECT
     *  ========================= */
    public function approve(Tim $tim)
    {
        $tim->update(['status' => 'approved']);
        return back()->with('success', 'Tim berhasil diterima!');
    }

    public function reject(Tim $tim)
    {
        $tim->update(['status' => 'rejected']);
        return back()->with('success', 'Tim ditolak!');
    }
}
