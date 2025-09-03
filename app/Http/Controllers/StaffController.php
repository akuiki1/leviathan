<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        $latestBatch = \App\Models\User::max('batch');
        
        $availableUsers = \App\Models\User::where('batch', $latestBatch)
            ->where('role', 'staff')
            ->with(['jabatan' => function($query) {
                $query->with('eselon');
            }])
            ->get();

        return view('staff.tim.create', compact('availableUsers'));
    }

    public function storeTim(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'nama_tim' => 'required|string|max:255',
            'keterangan' => 'required|string',
            'sk_file' => 'required|file|mimes:pdf|max:2048', // maksimal 2MB
            'anggota' => 'required|array|min:1',
            'anggota.*' => 'exists:users,id'
        ]);

        // Cek batas tim untuk setiap anggota
        $overLimitUsers = \App\Models\User::whereIn('id', $validated['anggota'])
            ->get()
            ->filter(function($user) {
                $approvedTimCount = $user->tims()
                    ->where('status', 'approved')
                    ->count();
                return $approvedTimCount >= $user->jabatan->eselon->maks_honor;
            });

        if ($overLimitUsers->isNotEmpty()) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Beberapa anggota sudah mencapai batas maksimal tim.');
        }

        // Upload file SK
        $skPath = $request->file('sk_file')->store('sk_files', 'public');

        try {
            DB::beginTransaction();

            $tim = \App\Models\Tim::create([
                'nama_tim' => $validated['nama_tim'],
                'keterangan' => $validated['keterangan'],
                'sk_file' => $skPath,
                'created_by' => Auth::id(),
                'status' => 'pending'
            ]);

            // Attach dengan jabatan dari user
            $anggotaData = collect($validated['anggota'])->mapWithKeys(function ($userId) {
                $user = \App\Models\User::find($userId);
                return [$userId => ['jabatan' => $user->jabatan->name]];
            });
            
            $tim->users()->attach($anggotaData);

            DB::commit();

            return redirect()
                ->route('staff.tim.index')
                ->with('success', 'Tim berhasil dibuat dan menunggu persetujuan admin.');

        } catch (\Exception $e) {
            // Rollback jika terjadi error
            DB::rollBack();
            
            // Hapus file yang sudah diupload jika ada error
            if (isset($skPath)) {
                Storage::disk('public')->delete($skPath);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat tim. Silakan coba lagi.');
        }
    }
}
