<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('admin.users.index', compact('users'));
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
            'password' => 'required|string|min:6|confirmed', // pastikan ada password_confirmation
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

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'nip'      => 'required|string|max:20|unique:users,nip,'.$user->id,
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,'.$user->id,
            'jabatan'  => 'nullable|string|max:255',
            'role'     => 'required|string|max:50',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = $request->only(['nip', 'name', 'email', 'jabatan', 'role']);

        if($request->filled('password')){
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }
}
