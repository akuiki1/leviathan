<x-admin-layout>
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.tims.index') }}">Tims</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $tim->nama_tim }}</li>
        </ol>
    </nav>
    <div class="container py-4">
        <div class="row">
            <!-- Sidebar kiri -->
            <div class="col-lg-3 col-md-4">
                <div class="card shadow-lg border-0 mb-4">
                    <div class="card-body text-center">
                        <i class="bi bi-people fs-1 text-primary mb-3"></i>
                        <h5 class="fw-bold">{{ $tim->nama_tim }}</h5>
                        <span class="badge bg-{{ $tim->status == 'approved' ? 'success' : ($tim->status == 'pending' ? 'warning' : 'danger') }}">{{ ucfirst($tim->status) }}</span>
                        <p class="text-muted mt-2">
                            Dibuat oleh: {{ $tim->creator->name ?? '-' }}
                        </p>
                        <p class="text-muted">
                            Dibuat pada: {{ $tim->created_at->format('d M Y H:i') }}
                        </p>
                        <p class="text-muted">
                            Jumlah Anggota: {{ $tim->anggota->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Konten kanan -->
            <div class="col-lg-9 col-md-8">
                <div class="card shadow-lg border-0 mb-4">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-info-circle me-2"></i>Tim Information
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Keterangan</label>
                                <textarea class="form-control" readonly>{{ $tim->keterangan }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <input type="text" class="form-control" value="{{ ucfirst($tim->status) }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">SK File</label>
                                <br>
                                @if($tim->sk_file)
                                    <a href="{{ asset('storage/' . $tim->sk_file) }}" target="_blank" class="btn btn-outline-primary btn-sm">Lihat SK</a>
                                    <span class="ms-2">{{ basename($tim->sk_file) }}</span>
                                @else
                                    <span class="text-muted">Tidak ada</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Anggota Tim -->
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-people-fill me-2"></i>Anggota Tim
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Jabatan</th>
                                        <th>Email</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($tim->anggota as $anggota)
                                    <tr>
                                        <td>{{ $anggota->name }}</td>
                                        <td>{{ $anggota->jabatan->name ?? '-' }}</td>
                                        <td>{{ $anggota->email }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Tidak ada anggota.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="{{ route('admin.tims.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <a href="{{ route('admin.tims.edit', $tim->id) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
