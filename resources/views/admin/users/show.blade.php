<x-admin-layout>
    <div class="container py-4">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detail User</li>
            </ol>
        </nav>
        <div class="row">
            <!-- Sidebar kiri -->
            <div class="col-md-4">
                <div class="card shadow-lg border-0">
                    <div class="card-body text-center">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=150"
                             alt="Foto Profil"
                             class="rounded-circle mb-3"
                             style="width: 150px; height: 150px; object-fit: cover;">
                        <h5 class="fw-bold">{{ $user->name }}</h5>
                        <span class="badge bg-primary">{{ ucfirst($user->role) }}</span>
                        <p class="text-muted mt-2">
                            {{ $user->jabatan->name ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Konten kanan -->
            <div class="col-md-8">
                <div class="card shadow-lg border-0">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">User Information</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">NIP</label>
                                <input type="text" class="form-control" value="{{ $user->nip }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="text" class="form-control" value="{{ $user->email }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jabatan</label>
                                <input type="text" class="form-control" value="{{ $user->jabatan->name ?? '-' }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="{{ ucfirst($user->role) }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status Akun</label>
                                <input type="text" class="form-control" value="{{ $user->status_akun }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Batch</label>
                                <input type="text" class="form-control" value="{{ $user->batch }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Verified At</label>
                                <input type="text" class="form-control" value="{{ $user->email_verified_at ?? '-' }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Dibuat Pada</label>
                                <input type="text" class="form-control" value="{{ $user->created_at->format('d M Y H:i') }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Warning / Progress Honorarium -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h6 class="fw-bold mb-3 text-danger">
                <i class="bi bi-exclamation-triangle"></i> Honorarium Progress
            </h6>

            @php
                $taken = $totalTim;
                $limit = $maksHonor;
                $percent = $limit > 0 ? ($taken / $limit) * 100 : 0;
                $percent = min($percent, 100);
            @endphp

            <p class="mb-1">Sudah diambil: <strong>{{ $taken }}/{{ $limit }}</strong></p>
            <div class="progress" style="height: 20px;">
                <div class="progress-bar {{ $taken > $limit ? 'bg-danger' : 'bg-success' }}"
                     role="progressbar"
                     style="width: {{ $percent }}%;"
                     aria-valuenow="{{ $percent }}"
                     aria-valuemin="0"
                     aria-valuemax="100">
                     {{ round($percent) }}%
                </div>
            </div>

            @if ($taken > $limit)
                <div class="alert alert-warning p-2 mt-2 mb-0">
                    <small>
                        Anda telah melebihi batas maksimal honorarium ({{ $limit }}).
                        Honor berikutnya tidak bisa diterima.
                    </small>
                </div>
            @endif
        </div>
    </div>

                <div class="mt-3">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
