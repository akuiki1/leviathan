<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('jabatan'); // eager load

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('jabatan_id')) {
            $query->where('jabatan_id', $request->jabatan_id);
        }

        $users = $query->paginate(10);
        $jabatans = \App\Models\Jabatan::all();

        return view('admin.users.index', compact('users', 'jabatans'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nip'      => 'required|string|max:20|unique:users,nip',
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'jabatan'  => 'nullable|string|max:255',
            'role'     => 'required|string|max:50',
            'password' => 'required|string|min:6|confirmed',
        ]);

        User::create([
            'nip'      => $request->nip,
            'name'     => $request->name,
            'email'    => $request->email,
            'jabatan'  => $request->jabatan,
            'role'     => $request->role,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function show(User $user)
    {
        $user->load('jabatan.eselon'); // pastikan relasi sudah dibuat
        $maksHonor = $user->jabatan->eselon->maks_honor ?? 0;
        $totalTim = $user->tims()->where('status', 'approved')->orderBy('created_at')->take($maksHonor)->count();
        return view('admin.users.show', compact('user', 'maksHonor', 'totalTim'));
    }


    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'nip'      => 'required|string|max:20|unique:users,nip,' . $user->id,
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'jabatan'  => 'nullable|string|max:255',
            'role'     => 'required|string|max:50',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = $request->only(['nip', 'name', 'email', 'jabatan', 'role']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

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
