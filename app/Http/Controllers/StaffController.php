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

        // DIPERBAIKI: Spesifikasi tabel untuk menghindari ambiguous column
        $timApproveIds = $user->tims()
            ->where('tims.status', 'approved')  // Spesifikasi tabel tims
            ->orderBy('tims.created_at', 'desc')  // Spesifikasi tabel tims
            ->limit($user->jabatan->eselon->maks_honor ?? 0)
            ->pluck('tims.id');  // Spesifikasi tabel tims

        $totalTim = $timApproveIds->count();
        $maksHonor = $user->jabatan->eselon->maks_honor ?? 0;

        // DIPERBAIKI: Spesifikasi tabel untuk setiap anggota
        $timCountPerUser = [];
        foreach ($tims->flatMap->users as $anggota) {
            $timApproveIds = $anggota->tims()
                ->where('tims.status', 'approved')  // Spesifikasi tabel tims
                ->orderBy('tims.created_at', 'desc')  // Spesifikasi tabel tims
                ->limit($anggota->jabatan->eselon->maks_honor)
                ->pluck('tims.id');  // Spesifikasi tabel tims

            $timCountPerUser[$anggota->id] = $timApproveIds->count();
        }

        return view('staff.index', compact('user', 'tims', 'totalTim', 'maksHonor', 'timCountPerUser'));
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

        // Ambil semua user yang ada di tim untuk menghitung approved count mereka
        $allUserIdsInTims = $tims->flatMap->users->pluck('id')->unique();

        // Hitung jumlah tim approved untuk setiap user dengan limit maks_honor (sama seperti createTim)
        $approvedTimCount = [];
        foreach ($allUserIdsInTims as $userId) {
            $userModel = \App\Models\User::find($userId);

            // Hitung actual tim count dengan urutan created_at ASC
            $actualTimCount = \App\Models\Tim::whereHas('users', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            })
                ->where('status', 'approved')
                ->orderBy('created_at', 'asc')  // Tim lama dulu yang dapat honor
                ->count();

            $maksHonor = $userModel->jabatan->eselon->maks_honor;

            // Tampilkan maksimal sesuai maks_honor, sama seperti logic di createTim
            $approvedTimCount[$userId] = min($actualTimCount, $maksHonor);
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
            ->with(['jabatan' => function ($query) {
                $query->with('eselon');
            }])
            ->get();

        // Hitung jumlah tim approved untuk setiap user dengan batasan maks_honor
        $timCounts = [];
        foreach ($availableUsers as $user) {
            $actualTimCount = $user->tims()
                ->where('status', 'approved')
                ->orderBy('created_at', 'asc')  // Tim lama dulu yang dapat honor
                ->count();
            $maksHonor = $user->jabatan->eselon->maks_honor;

            // Tampilkan maksimal sesuai maks_honor, tapi tetap tahu jumlah sebenarnya
            $timCounts[$user->id] = min($actualTimCount, $maksHonor);
        }

        return view('staff.tim.create', compact('availableUsers', 'timCounts'));
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

        // Cek anggota yang sudah mencapai batas honor (untuk informasi saja)
        $overLimitUsers = \App\Models\User::whereIn('id', $validated['anggota'])
            ->with(['jabatan.eselon'])
            ->get()
            ->filter(function ($user) {
                $approvedTimCount = $user->tims()
                    ->where('status', 'approved')
                    ->orderBy('created_at', 'asc')  // Tim lama dulu yang dapat honor
                    ->count();
                return $approvedTimCount >= $user->jabatan->eselon->maks_honor;
            });

        $warningMessage = '';
        if ($overLimitUsers->isNotEmpty()) {
            $userNames = $overLimitUsers->pluck('name')->join(', ');
            $warningMessage = " Catatan: $userNames sudah mencapai batas maksimal honor dan tidak akan menerima honor untuk tim ini.";
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

            $successMessage = 'Tim berhasil dibuat dan menunggu persetujuan admin.' . $warningMessage;

            return redirect()
                ->route('staff.tim.index')
                ->with('success', $successMessage);
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
