<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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

        // Hitung tim yang dapat honor (prioritas tim terbaru)
        $timApproveIds = $user->tims()
            ->where('tims.status', 'approved')
            ->orderBy('tims.created_at', 'desc')  // Tim baru dulu yang dapat honor
            ->limit($user->jabatan->eselon->maks_honor ?? 0)
            ->pluck('tims.id');

        $totalTim = $timApproveIds->count();
        $maksHonor = $user->jabatan->eselon->maks_honor ?? 0;

        // Hitung data untuk setiap anggota
        $timCountPerUser = [];
        $terimaHonorPerUser = [];
        $statusHonorPerUser = []; // Array baru untuk status honor per tim per user
        $statusHonorPerTim = []; // LOGIC BARU: Status honor per tim untuk setiap user

        foreach ($tims->flatMap->users as $anggota) {
            // Tim yang dapat honor (limit sesuai maks_honor, prioritas terbaru)
            $timApproveIdsAnggota = $anggota->tims()
                ->where('tims.status', 'approved')
                ->orderBy('tims.created_at', 'desc')  // Tim baru dulu
                ->limit($anggota->jabatan->eselon->maks_honor)
                ->pluck('tims.id');

            // Jumlah tim approved SEMUA (tanpa limit) untuk logic pesan
            $totalTimApproved = $anggota->tims()
                ->where('tims.status', 'approved')
                ->count();

            // LOGIC BARU: Hitung status honor untuk setiap tim berdasarkan updated_at tercepat
            // Ambil tim approved yang sudah ada
            $timsApprovedSorted = $anggota->tims()
                ->where('tims.status', 'approved')
                ->orderBy('tims.updated_at', 'asc') // Tim dengan updated_at tercepat dulu (yang dapat honor)
                ->get();

            // Ambil tim pending untuk prediksi
            $timsPendingSorted = $anggota->tims()
                ->where('tims.status', 'pending')
                ->orderBy('tims.created_at', 'asc') // Tim pending diurutkan berdasarkan created_at
                ->get();

            $statusHonorPerUser[$anggota->id] = [];
            $maksHonorAnggota = $anggota->jabatan->eselon->maks_honor;

            // LOGIC KHUSUS: Tentukan status honor per tim APPROVED
            foreach ($timsApprovedSorted as $index => $timApproved) {
                if ($index < $maksHonorAnggota) {
                    // Tim ini mendapat honor (urutan 1 sampai maks_honor berdasarkan updated_at tercepat)
                    $statusHonorPerUser[$anggota->id][$timApproved->id] = 'honor_diterima';
                    $statusHonorPerTim[$anggota->id][$timApproved->id] = 'Honor Diterima';
                } else {
                    // Tim ini tidak mendapat honor (sudah melebihi maks_honor)
                    $statusHonorPerUser[$anggota->id][$timApproved->id] = 'tidak_honor';
                    $statusHonorPerTim[$anggota->id][$timApproved->id] = 'Tidak menerima honor lagi';
                }
            }

            // LOGIC KHUSUS: Tentukan prediksi status honor untuk tim PENDING
            $currentApprovedCount = $timsApprovedSorted->count();
            foreach ($timsPendingSorted as $index => $timPending) {
                $posisiJikaApproved = $currentApprovedCount + $index; // Posisi jika tim ini di-approve

                if ($posisiJikaApproved < $maksHonorAnggota) {
                    // Tim pending ini akan mendapat honor jika di-approve
                    $statusHonorPerUser[$anggota->id][$timPending->id] = 'prediksi_honor_diterima';
                    $statusHonorPerTim[$anggota->id][$timPending->id] = 'Akan menerima honor jika disetujui';
                } else {
                    // Tim pending ini tidak akan mendapat honor jika di-approve
                    $statusHonorPerUser[$anggota->id][$timPending->id] = 'prediksi_tidak_honor';
                    $statusHonorPerTim[$anggota->id][$timPending->id] = 'Tidak akan menerima honor';
                }
            }

            $timCountPerUser[$anggota->id] = $timApproveIdsAnggota->count();
            $terimaHonorPerUser[$anggota->id] = $totalTimApproved;
        }

        return view('staff.index', compact(
            'user',
            'tims',
            'totalTim',
            'maksHonor',
            'timCountPerUser',
            'terimaHonorPerUser',
            'statusHonorPerUser',
            'statusHonorPerTim' // Pass variable baru untuk status honor per tim
        ));
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
        $statusHonorPerTim = []; // Inisialisasi array untuk status honor per tim

        foreach ($allUserIdsInTims as $userId) {
            $userModel = \App\Models\User::find($userId);
            $maksHonorAnggota = $userModel->jabatan->eselon->maks_honor ?? 0;

            // Ambil tim approved yang sudah ada, diurutkan berdasarkan updated_at (tercepat dulu)
            $timsApprovedSorted = $userModel->tims()
                ->where('tims.status', 'approved')
                ->orderBy('tims.updated_at', 'asc')
                ->get();

            // Hitung actual tim count dengan urutan updated_at ASC
            $actualTimCount = $timsApprovedSorted->count();

            // Tampilkan maksimal sesuai maks_honor
            $approvedTimCount[$userId] = min($actualTimCount, $maksHonorAnggota);

            // Tentukan status honor untuk setiap tim approved
            foreach ($timsApprovedSorted as $index => $timApproved) {
                if ($index < $maksHonorAnggota) {
                    $statusHonorPerTim[$userId][$timApproved->id] = 'Honor Diterima';
                } else {
                    $statusHonorPerTim[$userId][$timApproved->id] = 'Tidak menerima honor lagi';
                }
            }

            // Ambil tim pending untuk prediksi
            $timsPendingSorted = $userModel->tims()
                ->where('tims.status', 'pending')
                ->orderBy('tims.created_at', 'asc') // Tim pending diurutkan berdasarkan created_at
                ->get();

            $currentApprovedCount = $timsApprovedSorted->count();
            foreach ($timsPendingSorted as $index => $timPending) {
                $posisiJikaApproved = $currentApprovedCount + $index;

                if ($posisiJikaApproved < $maksHonorAnggota) {
                    $statusHonorPerTim[$userId][$timPending->id] = 'Akan menerima honor jika disetujui';
                } else {
                    $statusHonorPerTim[$userId][$timPending->id] = 'Tidak akan menerima honor';
                }
            }
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

        return view('staff.tim.index', compact('user', 'tims', 'selectedBatch', 'batches', 'approvedTimCount', 'maksHonor', 'progress', 'statusHonorPerTim'));
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

        $timCounts = [];
        $statusHonorPerTim = []; // Variabel baru untuk menyimpan status honor per tim per user

        // Pastikan user yang sedang login ada di availableUsers
        $loggedInUser = Auth::user();
        if (!$availableUsers->contains('id', $loggedInUser->id)) {
            $availableUsers->push($loggedInUser);
        }

        foreach ($availableUsers as $user) {
            $maksHonor = $user->jabatan->eselon->maks_honor ?? 0;

            // Ambil semua tim approved yang diikuti user, urutkan berdasarkan updated_at (tercepat dulu)
            $timsApprovedSorted = $user->tims()
                ->where('status', 'approved')
                ->orderBy('updated_at', 'asc') // Urutkan berdasarkan updated_at tercepat
                ->get();

            $currentHonorCount = 0; // Counter untuk tim yang menerima honor
            $timCounts[$user->id] = 0; // Inisialisasi jumlah tim yang menerima honor

            foreach ($timsApprovedSorted as $index => $timApproved) {
                if ($currentHonorCount < $maksHonor) {
                    // Tim ini mendapat honor
                    $statusHonorPerTim[$user->id][$timApproved->id] = 'Honor Diterima';
                    $currentHonorCount++;
                } else {
                    // Tim ini tidak mendapat honor lagi
                    $statusHonorPerTim[$user->id][$timApproved->id] = 'Tidak menerima honor lagi';
                }
            }
            // timCounts[$user->id] akan berisi jumlah tim yang benar-benar menerima honor
            $timCounts[$user->id] = $currentHonorCount;

            // Ambil tim pending untuk prediksi (logic ini sudah ada di indexTim, bisa disesuaikan jika perlu di createTim)
            // Untuk createTim, kita hanya perlu tim approved yang sudah ada.
            // Jika ingin menampilkan prediksi untuk tim pending di create.blade.php, logic ini perlu ditambahkan.
            // Namun, untuk kebutuhan "jumlah tim yang statusnya approved" dan "honorarium" di create.blade.php,
            // kita fokus pada tim yang sudah approved.
        }

        return view('staff.tim.create', compact('availableUsers', 'timCounts', 'statusHonorPerTim'));
    }

    public function storeTim(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'nama_tim' => 'required|string|max:255',
            'keterangan' => 'required|string',
            'sk_file' => 'required|file|mimes:pdf|max:2048', // maksimal 2MB
            'anggota' => 'required|array|min:1',
            'anggota.*' => 'exists:users,id',
        ]);

        // Validasi kustom: Pastikan user yang sedang login selalu ada di array anggota
        if (!in_array(Auth::id(), $validated['anggota'])) {
            throw ValidationException::withMessages([
                'anggota' => 'Anda harus menjadi bagian dari tim yang Anda buat.',
            ]);
        }

        // Pastikan user yang sedang login selalu ada di array anggota, bahkan jika ada manipulasi frontend
        $validated['anggota'] = array_unique(array_merge($validated['anggota'], [Auth::id()]));


        // Cek anggota yang sudah mencapai batas honor (untuk informasi saja)
        $overLimitUsers = \App\Models\User::whereIn('id', $validated['anggota'])
            ->with(['jabatan.eselon'])
            ->get()
            ->filter(function ($user) {
                // Ambil tim approved yang sudah ada, diurutkan berdasarkan updated_at (tercepat dulu)
                $timsApprovedSorted = $user->tims()
                    ->where('status', 'approved')
                    ->orderBy('updated_at', 'asc')
                    ->get();

                $maksHonor = $user->jabatan->eselon->maks_honor ?? 0;
                $currentHonorCount = 0;
                foreach ($timsApprovedSorted as $timApproved) {
                    if ($currentHonorCount < $maksHonor) {
                        $currentHonorCount++;
                    } else {
                        break; // Sudah mencapai batas honor
                    }
                }
                return $currentHonorCount >= $maksHonor;
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
                'status' => 'pending',
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
