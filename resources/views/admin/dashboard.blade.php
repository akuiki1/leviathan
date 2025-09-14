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
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPendingTims as $tim)
                        <tr>
                            <td>{{ $tim->nama_tim }}</td>
                            <td>{{ $tim->creator->name ?? '-' }}</td>
                            <td>{{ $tim->created_at->format('d-m-Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">Tidak ada tim yang butuh approve.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-admin-layout>
