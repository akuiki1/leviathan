<x-admin-layout>
    <div class="container mt-4">
        <h1 class="mb-4">Dashboard</h1>

        <div class="row">
            <!-- Users Card -->
            <div class="col-md-4">
                <div class="card shadow-lg border-0 rounded-3 p-3">
                    <div class="d-flex align-items-center">
                        <!-- Icon -->
                        <div class="me-3">
                            <i class="bi bi-people fs-1 text-success"></i>
                        </div>
                        <!-- Text -->
                        <div>
                            <small class="text-muted">Users</small>
                            <h3 class="fw-bold mb-0">{{ $userCount }}</h3>
                        </div>
                    </div>
                    <hr class="my-3">
                    <!-- Footer -->
                    <div class="d-flex align-items-center text-muted small">
                        <i class="bi bi-arrow-repeat me-2"></i> Update Now
                    </div>
                </div>
            </div>

            <!-- Tim Card -->
            <div class="col-md-4">
                <div class="card shadow-lg border-0 rounded-3 p-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-people-fill fs-1 text-primary"></i>
                        </div>
                        <div>
                            <small class="text-muted">Tim</small>
                            <h3 class="fw-bold mb-0">{{ $timCount }}</h3>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="d-flex align-items-center text-muted small">
                        <i class="bi bi-arrow-repeat me-2"></i> Update Now
                    </div>
                </div>
            </div>

            <!-- Approved Tim Card -->
            <div class="col-md-4">
                <div class="card shadow-lg border-0 rounded-3 p-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-check-circle-fill fs-1 text-success"></i>
                        </div>
                        <div>
                            <small class="text-muted">Tim Disetujui</small>
                            <h3 class="fw-bold mb-0">{{ $approvedTimCount }}</h3>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="d-flex align-items-center text-muted small">
                        <i class="bi bi-arrow-repeat me-2"></i> Update Now
                    </div>
                </div>
            </div>

        <!-- Recent Activity Table -->
        <div class="card mt-4 shadow-lg border-0 rounded-3">
            <div class="card-header bg-white fw-bold text-center">
                Tim Butuh Approve
            </div>
            <div class="card-body p-0">
                <table class="table mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Tim</th>
                            <th>Pembuat</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPendingTims as $tim)
                        <tr>
                            <td>{{ $tim->nama_tim }}</td>
                            <td>{{ $tim->creator->name ?? '-' }}</td>
                            <td>{{ $tim->created_at->format('d-m-Y') }}</td>
                            <td>
                                <form action="{{ route('admin.tims.approve', $tim) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm">Terima</button>
                                </form>
                                <form action="{{ route('admin.tims.reject', $tim) }}" method="POST" class="d-inline ms-2">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-danger btn-sm">Tolak</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Tidak ada tim yang butuh approve.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-admin-layout>
