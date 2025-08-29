<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $role   = $request->input('role');
        $sort   = $request->input('sort', 'name'); // default sort by name
        $direction = $request->input('direction', 'asc');

        $users = User::query()
            ->when($search, fn($q) => $q->where('name','like',"%{$search}%")
                                         ->orWhere('email','like',"%{$search}%")
                                         ->orWhere('nip','like',"%{$search}%"))
            ->when($role, fn($q) => $q->where('role', $role))
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

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
        // Bisa buat view user detail
        return view('admin.users.show', compact('user'));
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

        $data = $request->only(['nip','name','email','jabatan','role']);
        if($request->filled('password')){
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success','User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success','User berhasil dihapus.');
    }

    // Optional: bulk delete via POST
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if(!empty($ids)){
            User::whereIn('id',$ids)->delete();
        }
        return redirect()->route('admin.users.index')->with('success','Users berhasil dihapus.');
    }
}
