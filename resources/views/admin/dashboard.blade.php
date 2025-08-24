<x-admin-layout>
    <div class="container mt-4">
        <h1 class="mb-4">Dashboard</h1>

        <div class="row">
            <!-- Users Card -->
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Users</h5>
                        <p class="card-text display-4">{{ $userCount }}</p>
                    </div>
                </div>
            </div>

            <!-- Tim Card -->
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Tim</h5>
                        <p class="card-text display-4">{{ $timCount }}</p>
                    </div>
                </div>
            </div>

            <!-- Honor Card -->
            <div class="col-md-4">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Honor</h5>
                        <p class="card-text display-4">{{ $honorCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Table -->
        <div class="card mt-4">
            <div class="card-header">
                Tim Butuh Approve
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead class="thead-light">
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
                            <td colspan="3" class="text-center">Tidak ada tim yang butuh approve.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-admin-layout>