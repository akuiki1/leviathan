<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <h4 class="mb-4">Dashboard</h4>
        </div>
    </div>

    <div class="row">
        <!-- Users Card -->
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-primary">
                <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-semibold">{{ $userCount }}</div>
                        <div>Users</div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-transparent text-white p-0" type="button">
                            <i class="bi bi-people fs-2"></i>
                        </button>
                    </div>
                </div>
                <div class="card-footer px-3 py-2 bg-primary border-top border-white border-opacity-25">
                    <a class="text-white text-decoration-none" href="{{ route('admin.users.index') }}">
                        <small class="text-white-50">View Details</small>
                        <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Tim Card -->
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-info">
                <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-semibold">{{ $timCount }}</div>
                        <div>Tim</div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-transparent text-white p-0" type="button">
                            <i class="bi bi-people-fill fs-2"></i>
                        </button>
                    </div>
                </div>
                <div class="card-footer px-3 py-2 bg-info border-top border-white border-opacity-25">
                    <a class="text-white text-decoration-none" href="{{ route('admin.tims.index') }}">
                        <small class="text-white-50">View Details</small>
                        <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Approved Tim Card -->
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-success">
                <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-semibold">{{ $approvedTimCount }}</div>
                        <div>Tim Disetujui</div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-transparent text-white p-0" type="button">
                            <i class="bi bi-check-circle fs-2"></i>
                        </button>
                    </div>
                </div>
                <div class="card-footer px-3 py-2 bg-success border-top border-white border-opacity-25">
                    <a class="text-white text-decoration-none" href="#">
                        <small class="text-white-50">View Details</small>
                        <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Pending Tim Card -->
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-danger">
                <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-semibold">{{ $rejectedTimCount }}</div>
                        <div>Tim DiTolak</div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-transparent text-white p-0" type="button">
                            <i class="bi bi-clock fs-2"></i>
                        </button>
                    </div>
                </div>
                <div class="card-footer px-3 py-2 bg-danger border-top border-white border-opacity-25">
                    <a class="text-white text-decoration-none" href="#">
                        <small class="text-white-50">View Details</small>
                        <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-list-ul me-2"></i>Tim Pending
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
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
                                    <td colspan="4" class="text-center text-muted">Tidak Ada Tim Yang Sedaang Pending.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
