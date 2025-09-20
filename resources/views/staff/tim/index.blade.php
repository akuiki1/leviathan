<x-staff-layout>
    <div class="container my-5">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-primary mb-0">Riwayat Tim Anda</h3>
            <a href="{{ route('staff.tim.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-lg me-2"></i> Buat Tim Baru
            </a>
        </div>

        <!-- User Profile Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fs-5 fw-bold"
                            style="width: 50px; height: 50px;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-0 fw-semibold">{{ Auth::user()->name }}</h5>
                        <p class="card-text text-muted small mb-0">NIP. {{ Auth::user()->nip }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Batch Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <div class="row g-2 align-items-center">
                    <div class="col-auto">
                        <label for="batchFilter" class="col-form-label text-muted">Filter Berdasarkan Batch:</label>
                    </div>
                    <div class="col-auto">
                        <select class="form-select form-select-sm" id="batchFilter">
                            @foreach ($batches as $batch)
                                <option value="{{ $batch }}" {{ $selectedBatch == $batch ? 'selected' : '' }}>
                                    Batch {{ $batch }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress and Team History Section -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <!-- Honor Progress Card -->
                <div class="card bg-light border-0 mb-4">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 fw-semibold text-secondary">Progress Penerimaan Honor</h6>
                            <span
                                class="badge rounded-pill {{ $progress >= 100 ? 'bg-warning text-dark' : 'bg-success' }} py-2 px-3">
                                {{ $approvedTimCount[$user->id] ?? 0 }}/{{ $maksHonor }}
                                @if ($progress >= 100)
                                    <i class="bi bi-exclamation-circle-fill ms-1"></i>
                                @else
                                    <i class="bi bi-check-circle-fill ms-1"></i>
                                @endif
                            </span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar {{ $progress >= 100 ? 'bg-warning' : 'bg-success' }}"
                                role="progressbar" style="width: {{ $progress }}%"
                                aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Team History Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Nama Tim</th>
                                <th scope="col" class="text-center">Terima Honor</th>
                                <th scope="col">Status Tim</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tims ?? [] as $tim)
                                <tr data-bs-toggle="collapse" data-bs-target="#detail-{{ $tim->id }}"
                                    class="accordion-toggle cursor-pointer">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-people-fill text-primary me-2 fs-5"></i>
                                            <span class="fw-medium">{{ $tim->nama_tim }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $honorStatus = $statusHonorPerTim[$user->id][$tim->id] ?? 'Tidak diketahui';
                                            $iconClass = '';

                                            if (
                                                $honorStatus === 'Honor Diterima' ||
                                                $honorStatus === 'Akan menerima honor jika disetujui'
                                            ) {
                                                $iconClass = 'text-success';
                                            } elseif (
                                                $honorStatus === 'Tidak menerima honor lagi' ||
                                                $honorStatus === 'Tidak akan menerima honor'
                                            ) {
                                                $iconClass = 'text-danger';
                                            } else {
                                                $iconClass = 'text-warning';
                                            }
                                        @endphp

                                        @if ($honorStatus === 'Honor Diterima')
                                            <i class="bi bi-check-circle-fill fs-5 {{ $iconClass }}"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Honor Diterima"></i>
                                        @elseif ($honorStatus === 'Tidak menerima honor lagi')
                                            <i class="bi bi-x-circle-fill fs-5 {{ $iconClass }}"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Tidak Menerima Honor Lagi"></i>
                                        @else
                                            <i class="bi bi-question-circle-fill fs-5 {{ $iconClass }}"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Status Honor Tidak Diketahui"></i>
                                        @endif
                                    </td>
                                    <td>
                                        <span
                                            class="badge rounded-pill px-3 py-2 {{ $tim->status === 'approved' ? 'bg-success' : ($tim->status === 'pending' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                            {{ ucfirst($tim->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="p-0 border-0">
                                        <div id="detail-{{ $tim->id }}" class="collapse bg-light border-top">
                                            <div class="card card-body m-3 p-4">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3 mb-md-0">
                                                        <h6 class="card-title fw-bold text-primary mb-3">Detail Tim</h6>
                                                        <p class="mb-2 small"><strong>Nama Tim:</strong> <span
                                                                class="text-muted">{{ $tim->nama_tim }}</span></p>
                                                        <p class="mb-2 small"><strong>Tanggal Dibuat:</strong> <span
                                                                class="text-muted">{{ $tim->created_at->format('d M Y H:i') }}</span>
                                                        </p>
                                                        <p class="mb-0 small"><strong>Status Tim:</strong>
                                                            <span
                                                                class="badge rounded-pill {{ $tim->status === 'approved' ? 'bg-success' : ($tim->status === 'pending' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                                                {{ ucfirst($tim->status) }}
                                                            </span>
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6 class="card-title fw-bold text-primary mb-3">Anggota Tim
                                                        </h6>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-borderless mb-0">
                                                                <thead class="table-secondary">
                                                                    <tr>
                                                                        <th scope="col" class="small">Nama</th>
                                                                        <th scope="col" class="small">Jabatan</th>
                                                                        <th scope="col" class="small">Status Honor
                                                                        </th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($tim->users as $anggota)
                                                                        <tr>
                                                                            <td class="small">{{ $anggota->name }}
                                                                            </td>
                                                                            <td class="small">
                                                                                {{ $anggota->jabatan->name }}</td>
                                                                            <td class="small">
                                                                                <span
                                                                                    class="badge bg-secondary-subtle text-secondary">
                                                                                    {{ $statusHonorPerTim[$anggota->id][$tim->id] ?? 'Tidak diketahui' }}
                                                                                </span>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        <i class="bi bi-info-circle me-2"></i> Belum ada data tim yang tersedia.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
        <style>
            .cursor-pointer {
                cursor: pointer;
            }

            .accordion-toggle:hover {
                background-color: var(--bs-table-hover-bg);
            }

            .table-hover>tbody>tr.accordion-toggle:hover>td {
                background-color: var(--bs-table-hover-bg);
            }

            .table-hover>tbody>tr.accordion-toggle:hover {
                --bs-table-hover-bg: #f8f9fa;
                /* Light gray on hover */
            }

            .card {
                border-radius: 0.75rem;
                /* Slightly more rounded corners */
            }

            .btn {
                border-radius: 0.5rem;
            }

            .form-select {
                border-radius: 0.5rem;
            }

            .badge {
                font-weight: 600;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.getElementById('batchFilter').addEventListener('change', function() {
                window.location.href = '{{ route('staff.tim.index') }}?batch=' + this.value;
            });

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        </script>
    @endpush
</x-staff-layout>