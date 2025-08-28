<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Honorarium;
use App\Models\User;
use App\Models\Tim;

class HonorariumController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort   = $request->input('sort','id');
        $direction = $request->input('direction','asc');

        $honoraria = Honorarium::with('user','tim')
            ->when($search, fn($q) => $q->whereHas('user', fn($q2) => $q2->where('name','like',"%{$search}%"))
                                     ->orWhereHas('tim', fn($q2) => $q2->where('nama_tim','like',"%{$search}%")))
            ->orderBy($sort,$direction)
            ->paginate(10)
            ->withQueryString();

        return view('admin.honoraria.index', compact('honoraria'));
    }

    public function create()
    {
        $users = User::all();
        $tims  = Tim::all();
        return view('admin.honoraria.create', compact('users','tims'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tim_id'  => 'required|exists:tims,id',
        ]);

        Honorarium::create($request->only('user_id','tim_id'));

        return redirect()->route('admin.honoraria.index')->with('success','Honorarium berhasil ditambahkan.');
    }

    public function edit(Honorarium $honorarium)
    {
        $users = User::all();
        $tims  = Tim::all();
        return view('admin.honoraria.edit', compact('honorarium','users','tims'));
    }

    public function update(Request $request, Honorarium $honorarium)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tim_id'  => 'required|exists:tims,id',
        ]);

        $honorarium->update($request->only('user_id','tim_id'));

        return redirect()->route('admin.honoraria.index')->with('success','Honorarium berhasil diperbarui.');
    }

    public function destroy(Honorarium $honorarium)
    {
        $honorarium->delete();
        return redirect()->route('admin.honoraria.index')->with('success','Honorarium berhasil dihapus.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids',[]);
        if(!empty($ids)){
            Honorarium::whereIn('id',$ids)->delete();
        }
        return redirect()->route('admin.honoraria.index')->with('success','Honorarium berhasil dihapus.');
    }
}
