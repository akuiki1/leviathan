<x-staff-layout>
    <div class="container my-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Riwayat Tim</h4>
            <a href="{{ route('staff.tim.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Buat Tim Baru
            </a>
        </div>

        <!-- Profile Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                            style="width: 48px; height: 48px;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                    </div>
                    <div class="col">
                        <h5 class="card-title mb-1">{{ Auth::user()->name }}</h5>
                        <p class="card-text text-muted mb-0">NIP. {{ Auth::user()->nip }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Batch -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-2 align-items-center">
                    <div class="col-auto">
                        <label class="col-form-label">Filter Batch:</label>
                    </div>
                    <div class="col-auto">
                        <select class="form-select" id="batchFilter">
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

        <!-- Tabel Riwayat -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Progress Penerimaan Honor</h6>
                                <span class="badge {{ $progress >= 100 ? 'bg-warning' : 'bg-success' }}">
                                    {{ $approvedTimCount[$user->id] ?? 0 }}/{{ $maksHonor }}
                                    @if ($progress >= 100)
                                        <i class="bi bi-exclamation-circle-fill ms-1"></i>
                                    @else
                                        <i class="bi bi-check-circle-fill ms-1"></i>
                                    @endif
                                </span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar {{ $progress >= 100 ? 'bg-warning' : 'bg-success' }}"
                                    role="progressbar" style="width: {{ $progress }}%"
                                    aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </div>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nama Tim</th>
                                <th class="text-center">Terima Honor</th>
                                <th>Status Tim</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tims ?? [] as $tim)
                                <tr data-bs-toggle="collapse" data-bs-target="#detail-{{ $tim->id }}"
                                    class="accordion-toggle" style="cursor: pointer;">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-people-fill text-primary me-2"></i>
                                            {{ $tim->nama_tim }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            // Ambil status honor untuk tim ini dari user yang login
                                            $honorStatus = $statusHonorPerTim[$user->id][$tim->id] ?? 'Tidak diketahui';
                                            $iconClass = '';
                                            $textClass = '';

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
                                                $iconClass = 'text-warning'; // Default for 'Tidak diketahui' or other states
                                            }
                                        @endphp

                                        @if ($honorStatus === 'Honor Diterima')
                                            <svg class="w-6 h-6 {{ $iconClass }}" aria-hidden="true"
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                fill="currentColor" viewBox="0 0 24 24">
                                                <path fill-rule="evenodd"
                                                    d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm13.707-1.293a1 1 0 0 0-1.414-1.414L11 12.586l-1.793-1.793a1 1 0 0 0-1.414 1.414l2.5 2.5a1 1 0 0 0 1.414 0l4-4Z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @elseif ($honorStatus === 'Tidak menerima honor lagi')
                                            <svg class="w-6 h-6 {{ $iconClass }}" aria-hidden="true"
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-width="2"
                                                    d="m6 6 12 12m3-6a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                        @else
                                            {{-- For pending or other statuses --}}
                                            <svg class="w-6 h-6 {{ $iconClass }}" aria-hidden="true"
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-width="2"
                                                    d="m6 6 12 12m3-6a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                        @endif
                                    </td>
                                    <td>
                                        <span
                                            class="badge align-middle {{ $tim->status === 'approved' ? 'bg-success text-white' : ($tim->status === 'pending' ? 'bg-warning text-white' : 'bg-danger') }}">
                                            {{ ucfirst($tim->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="p-0">
                                        <div id="detail-{{ $tim->id }}" class="collapse">
                                            <div class="card m-3">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6 class="card-title">Detail Tim</h6>
                                                            <p class="mb-1"><strong>Nama Tim:</strong>
                                                                {{ $tim->nama_tim }}</p>
                                                            <p class="mb-1"><strong>Tanggal Dibuat:</strong>
                                                                {{ $tim->created_at->format('d M Y') }}</p>
                                                            <p class="mb-1"><strong>Status Tim:</strong>
                                                                <span
                                                                    class="badge {{ $tim->status === 'approved' ? 'bg-success' : ($tim->status === 'pending' ? 'bg-warning' : 'bg-danger') }}">
                                                                    {{ ucfirst($tim->status) }}
                                                                </span>
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6 class="card-title">Anggota Tim</h6>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Nama</th>
                                                                            <th>Jabatan</th>
                                                                            <th>Status Honor</th> {{-- Ubah header ini --}}
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($tim->users as $anggota)
                                                                            <tr>
                                                                                <td>{{ $anggota->name }}</td>
                                                                                <td>{{ $anggota->jabatan->name }}</td>
                                                                                <td>
                                                                                    {{ $statusHonorPerTim[$anggota->id][$tim->id] ?? 'Tidak diketahui' }}
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
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        Belum ada data tim
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
    @endpush

    @push('scripts')
        <script>
            document.getElementById('batchFilter').addEventListener('change', function() {
                window.location.href = '{{ route('staff.tim.index') }}?batch=' + this.value;
            });
        </script>
    @endpush
</x-staff-layout>
