<?php

namespace App\Http\Controllers;

use App\Models\Eselon;
use Illuminate\Http\Request;

class EselonController extends Controller
{
    public function index()
    {
        $eselons = Eselon::withCount('jabatans')->orderBy('name')->get();
        return view('admin.eselons.index', compact('eselons'));
    }

    public function create()
    {
        return view('admin.eselons.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255|unique:eselons,name',
            'maks_honor' => 'required|integer|min:0|max:255',
        ]);

        Eselon::create($data);

        return redirect()->route('admin.eselons.index')->with('success', 'Eselon berhasil ditambahkan.');
    }

    public function edit(Eselon $eselon)
    {
        return view('admin.eselons.edit', compact('eselon'));
    }

    public function update(Request $request, Eselon $eselon)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255|unique:eselons,name,' . $eselon->id,
            'maks_honor' => 'required|integer|min:0|max:255',
        ]);

        $eselon->update($data);

        return redirect()->route('admin.eselons.index')->with('success', 'Eselon berhasil diperbarui.');
    }

    public function destroy(Eselon $eselon)
    {
        if ($eselon->jabatans()->exists()) {
            return back()->with('error', 'Eselon tidak bisa dihapus karena masih dipakai jabatan.');
        }

        $eselon->delete();

        return back()->with('success', 'Eselon berhasil dihapus.');
    }
}
