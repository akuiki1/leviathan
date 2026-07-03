<?php

namespace App\Http\Controllers;

use App\Models\Eselon;
use App\Models\Jabatan;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    public function index()
    {
        $jabatans = Jabatan::with('eselon')->withCount('users')->orderBy('name')->get();
        return view('admin.jabatans.index', compact('jabatans'));
    }

    public function create()
    {
        $eselons = Eselon::orderBy('name')->get();
        return view('admin.jabatans.create', compact('eselons'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'eselon_id' => 'required|exists:eselons,id',
        ]);

        Jabatan::create($data);

        return redirect()->route('admin.jabatans.index')->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function edit(Jabatan $jabatan)
    {
        $eselons = Eselon::orderBy('name')->get();
        return view('admin.jabatans.edit', compact('jabatan', 'eselons'));
    }

    public function update(Request $request, Jabatan $jabatan)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'eselon_id' => 'required|exists:eselons,id',
        ]);

        $jabatan->update($data);

        return redirect()->route('admin.jabatans.index')->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function destroy(Jabatan $jabatan)
    {
        if ($jabatan->users()->exists()) {
            return back()->with('error', 'Jabatan tidak bisa dihapus karena masih dipakai ASN.');
        }

        $jabatan->delete();

        return back()->with('success', 'Jabatan berhasil dihapus.');
    }
}
