<?php

namespace App\Http\Controllers;

use App\Models\Tim;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TimController extends Controller
{
    public function index()
    {
        $tims = Tim::with('users')->latest()->get();
        return view('tims.index', compact('tims'));
    }

    public function create()
    {
        $users = User::all();
        return view('tims.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_tim'   => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'sk_file'    => 'required|file|mimes:pdf,png,jpg,jpeg|max:2048',
            'anggota'    => 'required|array',
        ]);

        // simpan file SK
        $skPath = $request->file('sk_file')->store('sk', 'public');

        // buat tim
        $tim = Tim::create([
            'nama_tim'   => $request->nama_tim,
            'keterangan' => $request->keterangan,
            'sk_file'    => $skPath,
            'status'     => 'pending', // default sebelum admin approve
        ]);

        // simpan anggota pivot
        foreach ($request->anggota as $anggota) {
            $tim->users()->attach($anggota['user_id'], [
                'jabatan' => $anggota['jabatan'],
            ]);
        }

        return redirect()->route('tims.index')->with('success', 'Tim berhasil dibuat, menunggu approval admin.');
    }
}
