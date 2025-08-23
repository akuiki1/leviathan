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
        return view('admin.tims.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_tim'   => 'required|string|max:255',
            'keterangan' => 'required|string',
            'sk_file'    => 'required|string',
        ]);

        Tim::create([
            'nama_tim'   => $request->nama_tim,
            'keterangan' => $request->keterangan,
            'sk_file'    => $request->sk_file,
            'created_by' => auth()->id(), // siapa yang buat
        ]);

        return redirect()->route('admin.tims.index')->with('success', 'Tim berhasil ditambahkan');
    }


    public function edit(Tim $tim)
    {
        return view('admin.tims.edit', compact('tim'));
    }

    public function update(Request $request, Tim $tim)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'leader' => 'nullable|string|max:255',
        ]);

        $tim->update($request->all());
        return redirect()->route('admin.tims.index')->with('success', 'Tim berhasil diperbarui');
    }

    public function destroy(Tim $tim)
    {
        $tim->delete();
        return redirect()->route('admin.tims.index')->with('success', 'Tim berhasil dihapus');
    }
}
