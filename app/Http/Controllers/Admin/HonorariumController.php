<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Honorarium;
use App\Models\User;
use App\Models\Tim;

class HonorariumController extends Controller
{
    public function index()
    {
        // Ambil semua honorarium beserta relasi user & tim
        $honoraria = Honorarium::with('user', 'tim')->get();
        return view('admin.honoraria.index', compact('honoraria'));
    }

    public function create()
    {
        $users = User::all();
        $tims  = Tim::all();
        return view('admin.honoraria.create', compact('users', 'tims'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tim_id'  => 'required|exists:tims,id',
        ]);

        Honorarium::create([
            'user_id' => $request->user_id,
            'tim_id'  => $request->tim_id,
        ]);

        return redirect()->route('admin.honoraria.index')->with('success', 'Honorarium berhasil ditambahkan.');
    }

    public function edit(Honorarium $honorarium)
    {
        $users = User::all();
        $tims  = Tim::all();
        return view('admin.honoraria.edit', compact('honorarium', 'users', 'tims'));
    }

    public function update(Request $request, Honorarium $honorarium)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tim_id'  => 'required|exists:tims,id',
        ]);

        $honorarium->update([
            'user_id' => $request->user_id,
            'tim_id'  => $request->tim_id,
        ]);

        return redirect()->route('admin.honoraria.index')->with('success', 'Honorarium berhasil diperbarui.');
    }

    public function destroy(Honorarium $honorarium)
    {
        $honorarium->delete();
        return redirect()->route('admin.honoraria.index')->with('success', 'Honorarium berhasil dihapus.');
    }
}
