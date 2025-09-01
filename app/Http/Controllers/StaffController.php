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
                    ->with(['jabatan.eselon', 'tims']);
            }])
            ->latest()
            ->get();

        // Hitung jumlah tim approved tercepat
        $totalTim = $user->tims()
            ->where('status', 'approved')
            ->orderBy('created_at')
            ->take($user->jabatan->eselon->maks_honor)
            ->count();

        // Batas honor sesuai eselon
        $maksHonor = $user->jabatan->eselon->maks_honor ?? 0;

        // Hitung jumlah tim untuk setiap anggota
        $timCountPerUser = [];
        foreach ($tims->flatMap->users as $anggota) {
            // Hitung tim approved tercepat untuk setiap anggota
            $timCountPerUser[$anggota->id] = $anggota->tims()
                ->where('status', 'approved')
                ->orderBy('created_at')
                ->take($anggota->jabatan->eselon->maks_honor)
                ->count();
        }

        return view('staff.index', compact('user', 'tims', 'totalTim', 'maksHonor', 'timCountPerUser'));
    }

    public function indexProfile()
    {
        return view('staff.profile.index');
    }

    public function indexTim(Request $request)
    {
        $user = Auth::user();
        $selectedBatch = $request->batch ?: $user->batch;

        // Ambil semua user dengan NIP yang sama
        $userIds = \App\Models\User::where('nip', $user->nip)
            ->pluck('id');

        // Ambil tim berdasarkan user_id yang memiliki NIP sama
        $tims = \App\Models\Tim::whereHas('users', function ($query) use ($userIds) {
            $query->whereIn('users.id', $userIds);
        })
            ->with(['users' => function ($query) use ($selectedBatch) {
                $query->when($selectedBatch, function ($q) use ($selectedBatch) {
                    $q->where('batch', $selectedBatch);
                })
                    ->with(['jabatan.eselon']);
            }])
            ->latest()
            ->get();

        // Hitung jumlah tim approved untuk setiap user
        $approvedTimCount = [];
        foreach ($userIds as $userId) {
            $approvedTimCount[$userId] = \App\Models\Tim::whereHas('users', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            })
                ->where('status', 'approved')
                ->count();
        }

        // Hitung progress (berdasarkan user yang login)
        $maksHonor = $user->jabatan->eselon->maks_honor ?? 0;
        $progress = $maksHonor > 0 ? ($approvedTimCount[$user->id] / $maksHonor) * 100 : 0;
        $progress = min($progress, 100); // Maksimal 100%

        // Ambil batch unik dari user dengan NIP yang sama
        $batches = \App\Models\User::where('nip', $user->nip)
            ->pluck('batch')
            ->unique()
            ->sortDesc()
            ->values();

        // Filter users dalam tim berdasarkan batch yang dipilih
        $tims->each(function ($tim) use ($selectedBatch) {
            $tim->setRelation('users', $tim->users->filter(function ($user) use ($selectedBatch) {
                return $selectedBatch ? $user->batch == $selectedBatch : true;
            }));
        });

        return view('staff.tim.index', compact('user', 'tims', 'selectedBatch', 'batches', 'approvedTimCount', 'maksHonor', 'progress'));
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
