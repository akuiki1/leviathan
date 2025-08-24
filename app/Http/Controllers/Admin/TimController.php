<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tim; // Pastikan model Tim sudah dibuat

class TimController extends Controller
{
    public function index()
    {
        $tims = Tim::all();
        return view('admin.tims.index', compact('tims'));
    }

    public function create()
    {
        $users = \App\Models\User::all();
        return view('admin.tims.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_tim'   => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'sk_file'    => 'required|string',
            'created_by' => 'required|exists:users,id',
            'status'     => 'required|string',
            'anggota'    => 'required|array',
            'anggota.*'  => 'exists:users,id',
        ]);

        $tim = Tim::create([
            'nama_tim'   => $request->nama_tim,
            'keterangan' => $request->keterangan,
            'sk_file'    => $request->sk_file,
            'created_by' => $request->created_by,
            'status'     => $request->status,
        ]);

        $tim->anggota()->sync($request->anggota);

        return redirect()->route('admin.tims.index')->with('success', 'Tim berhasil ditambahkan');
    }


    public function edit(Tim $tim)
    {
        $users = \App\Models\User::all();
        $tim->load('anggota');
        return view('admin.tims.edit', compact('tim', 'users'));
    }

    public function update(Request $request, Tim $tim)
    {
        $request->validate([
            'nama_tim'   => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'sk_file'    => 'required|string',
            'created_by' => 'required|exists:users,id',
            'status'     => 'required|string',
            'anggota'    => 'required|array',
            'anggota.*'  => 'exists:users,id',
        ]);

        $tim->update([
            'nama_tim'   => $request->nama_tim,
            'keterangan' => $request->keterangan,
            'sk_file'    => $request->sk_file,
            'created_by' => $request->created_by,
            'status'     => $request->status,
        ]);

        $tim->anggota()->sync($request->anggota);

        return redirect()->route('admin.tims.index')->with('success', 'Tim berhasil diperbarui');
    }

    public function destroy(Tim $tim)
    {
        $tim->delete();
        return redirect()->route('admin.tims.index')->with('success', 'Tim berhasil dihapus');
    }
}
