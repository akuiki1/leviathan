<x-staff-layout>
    <div class="container py-4">
        <h2 class="mb-4 fw-bold text-primary">Profil Saya</h2>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fs-4 fw-bold me-3"
                                style="width:60px;height:60px;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">{{ $user->name }}</h5>
                                <small class="text-muted">NIP. {{ $user->nip }}</small>
                            </div>
                        </div>
                        <hr>
                        <p class="mb-2"><strong>Email:</strong> {{ $user->email }}</p>
                        <p class="mb-2"><strong>Jabatan:</strong> {{ $user->jabatan->name }}</p>
                        <p class="mb-0"><strong>Eselon:</strong> {{ $user->jabatan->eselon->name ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-primary mb-3">
                            <i class="bi bi-cash-coin me-2"></i>Honor Tahun {{ $ringkasan['tahun'] }}
                        </h5>

                        <div class="row text-center g-3">
                            <div class="col-6 col-md-3">
                                <div class="p-3 bg-light rounded-3">
                                    <div class="fs-4 fw-bold text-dark">{{ $ringkasan['jumlah_dibayar'] }}/{{ $ringkasan['maks_honor'] }}</div>
                                    <small class="text-muted">Tim Dibayar</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3 bg-light rounded-3">
                                    <div class="fs-4 fw-bold text-dark">{{ $ringkasan['jumlah_tim_approved'] }}</div>
                                    <small class="text-muted">Total Tim Approved</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3 bg-light rounded-3">
                                    <div class="fs-4 fw-bold text-dark">{{ $ringkasan['sisa_slot'] }}</div>
                                    <small class="text-muted">Sisa Slot</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3 bg-light rounded-3">
                                    <div class="fs-6 fw-bold text-success">Rp {{ number_format($ringkasan['total_honor'], 0, ',', '.') }}</div>
                                    <small class="text-muted">Total Honor</small>
                                </div>
                            </div>
                        </div>

                        @if ($ringkasan['is_over_limit'])
                            <div class="alert alert-warning mt-4 mb-0 rounded-3">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                Anda mengikuti {{ $ringkasan['jumlah_tim_approved'] }} tim approved, melebihi kuota
                                {{ $ringkasan['maks_honor'] }}. Tim di luar kuota tidak menerima honor.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-staff-layout>
