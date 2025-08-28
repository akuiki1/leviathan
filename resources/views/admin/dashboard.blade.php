<x-admin-layout>
    <div class="container mt-4">
        <h1 class="mb-4">Dashboard</h1>

        <div class="row g-4">
            <!-- Users Card -->
            <div class="col-md-4">
                <div class="card border-0 rounded-3" style="box-shadow: 0 6px 15px rgba(25, 135, 84, 0.4);">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi bi-people fs-1 text-success me-3"></i>
                        <div>
                            <h6 class="text-muted">Users</h6>
                            <h3 class="fw-bold">{{ $userCount }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tim Card -->
            <div class="col-md-4">
                <div class="card border-0 rounded-3" style="box-shadow: 0 6px 15px rgba(13, 110, 253, 0.4);">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi bi-people-fill fs-1 text-primary me-3"></i>
                        <div>
                            <h6 class="text-muted">Tim</h6>
                            <h3 class="fw-bold">{{ $timCount }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Honor Card -->
            <div class="col-md-4">
                <div class="card border-0 rounded-3" style="box-shadow: 0 6px 15px rgba(255, 193, 7, 0.5);">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi bi-cash-stack fs-1 text-warning me-3"></i>
                        <div>
                            <h6 class="text-muted">Honor</h6>
                            <h3 class="fw-bold">{{ $honorCount }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Table -->
        <div class="card mt-4 shadow-sm border-0 rounded-3">
            <div class="card-header bg-dark text-white fw-bold">
                <i class="bi bi-clipboard-check me-2"></i> Tim Butuh Approve
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
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
                                <td class="fw-semibold">{{ $tim->nama_tim }}</td>
                                <td>{{ $tim->creator->name ?? '-' }}</td>
                                <td>{{ $tim->created_at->format('d-m-Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">
                                    Tidak ada tim yang butuh approve.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

</x-admin-layout>