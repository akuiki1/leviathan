<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('jabatan'); // eager load

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('jabatan_id')) {
            $query->where('jabatan_id', $request->jabatan_id);
        }

        $users = $query->paginate(10)->withQueryString();
        $jabatans = \App\Models\Jabatan::all();

        return view('admin.users.index', compact('users', 'jabatans'));
    }

    public function create()
    {
        $jabatans = Jabatan::all(); // ambil semua data jabatan
        return view('admin.users.create', compact('jabatans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nip'      => 'required|string|max:20|unique:users,nip',
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'jabatan_id' => 'required|exists:jabatans,id',
            'role'     => 'required|string|max:50',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'nip'      => $request->nip,
            'name'     => $request->name,
            'email'    => $request->email,
            'jabatan_id' => $request->jabatan_id,
            'role'     => $request->role,
            'password' => Hash::make($request->password),
        ]);

        $user->catatJabatanAwal();

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function show(User $user, \App\Services\HonorService $honor)
    {
        $user->load('jabatan.eselon');
        // ?tahun= dipakai link drill-down dari laporan honor (audit tahun lampau)
        $tahun     = (int) request('tahun', $honor->tahunBerjalan());
        $ringkasan = $honor->ringkasan($user, $tahun);
        $maksHonor = $ringkasan['maks_honor'];
        $totalTim  = $ringkasan['jumlah_dibayar'];
        return view('admin.users.show', compact('user', 'maksHonor', 'totalTim', 'ringkasan'));
    }


    public function edit(User $user)
    {
        $jabatans = Jabatan::all();
        return view('admin.users.edit', compact('user', 'jabatans'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'nip'        => 'required|string|max:20|unique:users,nip,' . $user->id,
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email,' . $user->id,
            'jabatan_id' => 'required|exists:jabatans,id',
            'role'       => 'required|string|max:50',
            'password'   => 'nullable|string|min:6|confirmed',
            'tmt'        => 'nullable|date',
        ]);

        $data = $request->only(['nip', 'name', 'email', 'role']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Catat mutasi/promosi bila jabatan berubah (TMT dari input, mendukung SK berlaku surut).
        $tmt = $request->filled('tmt') ? \Carbon\Carbon::parse($request->tmt) : null;
        $user->pindahJabatan((int) $request->jabatan_id, $tmt);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if (\DB::table('tims')->where('created_by', $user->id)->exists()) {
            return redirect()->route('admin.users.index')->with('error', 'User tidak bisa dihapus karena masih dipakai.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || empty($ids)) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Tidak ada user yang dipilih.');
        }

        DB::transaction(function () use ($ids) {
            User::whereIn('id', $ids)->delete();
        });

        return redirect()->route('admin.users.index')
            ->with('success', count($ids) . ' user berhasil dihapus.');
    }
}
