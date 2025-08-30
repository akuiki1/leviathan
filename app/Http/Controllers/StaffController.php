<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indexDashboard()
    {
        $latestBatch = \App\Models\User::max('batch');
        $user = Auth::user();

        $tims = $user->tims()
            ->with(['users' => function ($q) use ($latestBatch) {
                $q->where('batch', $latestBatch)
                    ->with(['jabatan.eselon', 'tims']); // tambahkan eager load tims
            }])
            ->latest()
            ->get();

        $timCountPerUser = [];
        foreach ($tims->flatMap->users as $anggota) {
            $timCountPerUser[$anggota->id] = $anggota->tims->count(); // akses koleksi hasil eager load
        }

        // jumlah tim yang diikuti user
        $totalTim = $tims->count();

        // batas honor sesuai eselon
        $maksHonor = $user->jabatan->eselon->maks_honor ?? 0;

        return view('staff.index', compact('user', 'tims', 'totalTim', 'maksHonor', 'timCountPerUser'));
    }

    public function indexProfile(){
        return view('staff.profile.index');
    }

    public function indexTim(){
        return view('staff.tim.index');
    }

    public function createTim()
    {
        return view('staff.tim.create');
    }

    public function storeTim(Request $request)
    {
        //
    }
}
