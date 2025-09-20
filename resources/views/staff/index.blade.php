<x-staff-layout>

    <div class="container py-4">
        <h2 class="mb-4 fw-bold text-primary">Dashboard Staff</h2>

        {{-- Statistik Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card text-center shadow-sm h-100 border-0 rounded-3">
                    <div class="card-body p-3 d-flex flex-column justify-content-center align-items-center">
                        <i class="bi bi-people-fill text-primary fs-3 mb-2"></i>
                        <p class="text-muted mb-1 fs-6">Jumlah Tim Anda</p>
                        <h4 class="fw-bold mb-0 text-dark">{{ $totalTim }} Tim</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center shadow-sm h-100 border-0 rounded-3">
                    <div class="card-body p-3 d-flex flex-column justify-content-center align-items-center">
                        <i class="bi bi-currency-dollar text-success fs-3 mb-2"></i>
                        <p class="text-muted mb-1 fs-6">Honor Diterima</p>
                        <h4 class="fw-bold mb-0 text-dark">{{ $totalTim }}/{{ $maksHonor }} Honor</h4>

                        @if ($totalTim > $maksHonor)
                            <div class="alert alert-warning p-2 mt-3 mb-0 rounded-3" role="alert">
                                <small class="d-flex align-items-center justify-content-center">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    Anda telah melebihi batas maksimal honorarium ({{ $maksHonor }}). Honor
                                    berikutnya tidak bisa diterima.
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center shadow-sm h-100 border-0 rounded-3">
                    <div class="card-body p-3 d-flex flex-column justify-content-center align-items-center">
                        <i class="bi bi-calendar-check-fill text-info fs-3 mb-2"></i>
                        <p class="text-muted mb-1 fs-6">Diperbarui Pada</p>
                        <h4 class="fw-bold mb-0 text-dark">
                            {{ \Carbon\Carbon::parse($user->created_at)->translatedFormat('d F Y') }}
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-4">
            {{-- Data Diri Anda Card --}}
            <div class="col-lg-4">
                <div class="card shadow-sm h-100 border-0 rounded-3">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3 text-primary d-flex align-items-center">
                            <i class="bi bi-person-circle me-2"></i>Data Diri Anda
                        </h5>
                        <p class="mb-2"><strong>Nama:</strong> {{ $user->name }}</p>
                        <p class="mb-2"><strong>NIP:</strong> {{ $user->nip }}</p>
                        <p class="mb-3"><strong>Jabatan:</strong> {{ $user->jabatan->name }}</p>

                        <h6 class="fw-semibold mb-2 text-secondary d-flex align-items-center">
                            <i class="bi bi-cash-coin me-2"></i>Jatah Penerimaan Honorarium
                        </h6>

                        @php
                            $timsApprovedUser = $user
                                ->tims()
                                ->where('tims.status', 'approved')
                                ->orderBy('tims.updated_at', 'asc')
                                ->get();

                            $actualHonorCount = min($timsApprovedUser->count(), $maksHonor);
                            $totalTimApproved = $timsApprovedUser->count();

                            $progress = $maksHonor > 0 ? ($actualHonorCount / $maksHonor) * 100 : 0;
                        @endphp

                        <div class="progress mb-3" style="height: 25px;">
                            <div class="progress-bar {{ $totalTimApproved > $maksHonor ? 'bg-warning text-dark' : ($actualHonorCount == $maksHonor ? 'bg-success' : 'bg-primary') }}"
                                role="progressbar" style="width: {{ $progress }}%;"
                                aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                                <span class="fw-bold">{{ $actualHonorCount }} / {{ $maksHonor }} Honor</span>
                            </div>
                        </div>

                        @if ($totalTimApproved > $maksHonor)
                            <div class="alert alert-warning p-2 mb-0 rounded-3" role="alert">
                                <small class="d-flex align-items-center">
                                    <i class="bi bi-info-circle-fill me-2"></i>
                                    Anda memiliki {{ $totalTimApproved }} tim approved, tetapi hanya menerima honor
                                    dari {{ $actualHonorCount }} tim pertama (berdasarkan waktu persetujuan).
                                </small>
                            </div>
                        @elseif ($actualHonorCount == $maksHonor)
                            <div class="alert alert-success p-2 mb-0 rounded-3" role="alert">
                                <small class="d-flex align-items-center">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    Anda telah mencapai batas maksimal honorarium ({{ $maksHonor }}).
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Daftar Tim Card --}}
            <div class="col-lg-8">
                <div class="card shadow-sm h-100 border-0 rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0 text-primary d-flex align-items-center">
                                <i class="bi bi-list-task me-2"></i>Daftar Tim Anda
                            </h5>
                            <a href="{{ route('staff.tim.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
                                <i class="bi bi-plus-circle me-1"></i>Buat Tim
                            </a>
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control rounded-pill" placeholder="Cari Tim..."
                                id="searchTim">
                        </div>

                        @if ($tims->isEmpty())
                            <div class="alert alert-info text-center mt-4 rounded-3" role="alert">
                                <i class="bi bi-info-circle-fill me-2"></i>Belum ada tim yang terdaftar.
                            </div>
                        @else
                            {{-- BAGIAN 1: TIM YANG MENDAPAT HONOR --}}
                            @php
                                $timsWithHonor = $tims->filter(function ($tim) use ($statusHonorPerTim, $user) {
                                    return isset($statusHonorPerTim[$user->id][$tim->id]) &&
                                        $statusHonorPerTim[$user->id][$tim->id] === 'Honor Diterima';
                                });
                            @endphp

                            @if ($timsWithHonor->isNotEmpty())
                                <div class="mb-4">
                                    <h6 class="fw-bold text-success mb-3 d-flex align-items-center">
                                        <i class="bi bi-award-fill me-2"></i>Tim yang Menerima Honor
                                        <span
                                            class="badge bg-success-subtle text-success ms-2">{{ $timsWithHonor->count() }}</span>
                                    </h6>

                                    <div class="accordion accordion-flush" id="accordionTimHonor">
                                        @foreach ($timsWithHonor as $tim)
                                            <div class="accordion-item mb-2 border border-success rounded-3 shadow-sm">
                                                <h2 class="accordion-header" id="heading-honor-{{ $tim->id }}">
                                                    <button
                                                        class="accordion-button collapsed bg-success-subtle text-success fw-bold rounded-3 p-3"
                                                        type="button" data-bs-toggle="collapse"
                                                        data-bs-target="#tim-honor-{{ $tim->id }}"
                                                        aria-expanded="false"
                                                        aria-controls="tim-honor-{{ $tim->id }}">
                                                        <div class="d-flex align-items-center w-100">
                                                            <i class="bi bi-check-circle-fill me-3 fs-5"></i>
                                                            <div class="flex-grow-1">
                                                                <span class="fs-6">{{ $tim->nama_tim }}</span>
                                                                <small
                                                                    class="d-block text-muted">{{ $tim->users->count() }}
                                                                    Anggota</small>
                                                            </div>
                                                            <span
                                                                class="badge bg-success rounded-pill py-2 px-3 ms-2">Honor
                                                                Diterima</span>
                                                            <span
                                                                class="badge {{ $tim->status == 'approved' ? 'bg-success' : ($tim->status == 'pending' ? 'bg-warning text-dark' : 'bg-danger') }} rounded-pill py-2 px-3 ms-2">
                                                                {{ ucfirst($tim->status) }}
                                                            </span>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="tim-honor-{{ $tim->id }}"
                                                    class="accordion-collapse collapse"
                                                    aria-labelledby="heading-honor-{{ $tim->id }}"
                                                    data-bs-parent="#accordionTimHonor">
                                                    <div class="accordion-body p-3 bg-light">
                                                        @if ($tim->keterangan)
                                                            <p class="mb-3 text-muted small">{{ $tim->keterangan }}</p>
                                                        @endif
                                                        <ul class="list-group list-group-flush">
                                                            @forelse($tim->users as $anggota)
                                                                <li
                                                                    class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 bg-light">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="rounded-circle bg-success text-white d-flex justify-content-center align-items-center me-2"
                                                                            style="width:32px; height:32px; font-size: 0.8rem;">
                                                                            {{ strtoupper(substr($anggota->name, 0, 1)) }}
                                                                        </div>
                                                                        <div>
                                                                            <strong
                                                                                class="d-block">{{ $anggota->name }}</strong>
                                                                            <small
                                                                                class="text-muted">{{ $anggota->jabatan->name }}</small>
                                                                        </div>
                                                                    </div>
                                                                    <small class="text-muted">
                                                                        @php
                                                                            $timCount =
                                                                                $timCountPerUser[$anggota->id] ?? 0;
                                                                            $maksHonor =
                                                                                $anggota->jabatan->eselon->maks_honor ??
                                                                                0;
                                                                        @endphp
                                                                        {{ $timCount }}/{{ $maksHonor }} Honor
                                                                        @if (isset($statusHonorPerTim[$anggota->id][$tim->id]))
                                                                            @if ($statusHonorPerTim[$anggota->id][$tim->id] === 'Honor Diterima')
                                                                                <span
                                                                                    class="badge bg-success ms-1">Honor
                                                                                    Diterima</span>
                                                                            @else
                                                                                <span
                                                                                    class="badge bg-warning text-dark ms-1">{{ $statusHonorPerTim[$anggota->id][$tim->id] }}</span>
                                                                            @endif
                                                                        @endif
                                                                    </small>
                                                                </li>
                                                            @empty
                                                                <li
                                                                    class="list-group-item text-muted px-0 py-2 bg-light">
                                                                    Belum ada anggota</li>
                                                            @endforelse
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- BAGIAN 2: TIM YANG TIDAK MENDAPAT HONOR LAGI --}}
                            @php
                                $timsWithoutHonor = $tims->filter(function ($tim) use ($statusHonorPerTim, $user) {
                                    return !isset($statusHonorPerTim[$user->id][$tim->id]) ||
                                        !in_array($statusHonorPerTim[$user->id][$tim->id], [
                                            'Honor Diterima',
                                            'Akan menerima honor jika disetujui',
                                        ]);
                                });
                            @endphp

                            @if ($timsWithoutHonor->isNotEmpty())
                                <div class="mb-4">
                                    <h6 class="fw-bold text-secondary mb-3 d-flex align-items-center">
                                        <i class="bi bi-x-circle-fill me-2"></i>Tim Tanpa Honor
                                        <span
                                            class="badge bg-secondary-subtle text-secondary ms-2">{{ $timsWithoutHonor->count() }}</span>
                                    </h6>

                                    <div class="accordion accordion-flush" id="accordionTimNoHonor">
                                        @foreach ($timsWithoutHonor as $tim)
                                            <div
                                                class="accordion-item mb-2 border border-secondary rounded-3 shadow-sm">
                                                <h2 class="accordion-header"
                                                    id="heading-no-honor-{{ $tim->id }}">
                                                    <button
                                                        class="accordion-button collapsed bg-secondary-subtle text-secondary fw-bold rounded-3 p-3"
                                                        type="button" data-bs-toggle="collapse"
                                                        data-bs-target="#tim-no-honor-{{ $tim->id }}"
                                                        aria-expanded="false"
                                                        aria-controls="tim-no-honor-{{ $tim->id }}">
                                                        <div class="d-flex align-items-center w-100">
                                                            <i class="bi bi-dash-circle-fill me-3 fs-5"></i>
                                                            <div class="flex-grow-1">
                                                                <span class="fs-6">{{ $tim->nama_tim }}</span>
                                                                <small
                                                                    class="d-block text-muted">{{ $tim->users->count() }}
                                                                    Anggota</small>
                                                            </div>
                                                            <span
                                                                class="badge bg-secondary rounded-pill py-2 px-3 ms-2">Tidak
                                                                Ada Honor</span>
                                                            <span
                                                                class="badge {{ $tim->status == 'approved' ? 'bg-success' : ($tim->status == 'pending' ? 'bg-warning text-dark' : 'bg-danger') }} rounded-pill py-2 px-3 ms-2">
                                                                {{ ucfirst($tim->status) }}
                                                            </span>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="tim-no-honor-{{ $tim->id }}"
                                                    class="accordion-collapse collapse"
                                                    aria-labelledby="heading-no-honor-{{ $tim->id }}"
                                                    data-bs-parent="#accordionTimNoHonor">
                                                    <div class="accordion-body p-3 bg-light">
                                                        @if ($tim->keterangan)
                                                            <p class="mb-3 text-muted small">{{ $tim->keterangan }}
                                                            </p>
                                                        @endif
                                                        <ul class="list-group list-group-flush">
                                                            @forelse($tim->users as $anggota)
                                                                <li
                                                                    class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 bg-light">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center me-2"
                                                                            style="width:32px; height:32px; font-size: 0.8rem;">
                                                                            {{ strtoupper(substr($anggota->name, 0, 1)) }}
                                                                        </div>
                                                                        <div>
                                                                            <strong
                                                                                class="d-block">{{ $anggota->name }}</strong>
                                                                            <small
                                                                                class="text-muted">{{ $anggota->jabatan->name }}</small>
                                                                        </div>
                                                                    </div>
                                                                    <small class="text-muted">
                                                                        @php
                                                                            $timCount =
                                                                                $timCountPerUser[$anggota->id] ?? 0;
                                                                            $maksHonor =
                                                                                $anggota->jabatan->eselon->maks_honor ??
                                                                                0;
                                                                        @endphp
                                                                        {{ $timCount }}/{{ $maksHonor }} Honor
                                                                        @if (isset($statusHonorPerTim[$anggota->id][$tim->id]))
                                                                            @if ($statusHonorPerTim[$anggota->id][$tim->id] === 'Tidak akan menerima honor')
                                                                                <span
                                                                                    class="badge bg-secondary ms-1">Tidak
                                                                                    akan menerima honor</span>
                                                                            @elseif($statusHonorPerTim[$anggota->id][$tim->id] === 'Tidak menerima honor lagi')
                                                                                <span
                                                                                    class="badge bg-warning text-dark ms-1">Tidak
                                                                                    menerima honor lagi</span>
                                                                            @else
                                                                                <span
                                                                                    class="badge bg-info text-white ms-1">{{ $statusHonorPerTim[$anggota->id][$tim->id] }}</span>
                                                                            @endif
                                                                        @endif
                                                                    </small>
                                                                </li>
                                                            @empty
                                                                <li
                                                                    class="list-group-item text-muted px-0 py-2 bg-light">
                                                                    Belum ada anggota</li>
                                                            @endforelse
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- BAGIAN 3: TIM PENDING YANG BERPOTENSI MENDAPAT HONOR --}}
                            @php
                                $timsPendingWithHonor = $tims->filter(function ($tim) use ($statusHonorPerTim, $user) {
                                    return isset($statusHonorPerTim[$user->id][$tim->id]) &&
                                        $statusHonorPerTim[$user->id][$tim->id] ===
                                            'Akan menerima honor jika disetujui';
                                });
                            @endphp

                            @if ($timsPendingWithHonor->isNotEmpty())
                                <div class="mb-4">
                                    <h6 class="fw-bold text-info mb-3 d-flex align-items-center">
                                        <i class="bi bi-hourglass-split me-2"></i>Tim Pending - Berpotensi Honor
                                        <span
                                            class="badge bg-info-subtle text-info ms-2">{{ $timsPendingWithHonor->count() }}</span>
                                    </h6>

                                    <div class="accordion accordion-flush" id="accordionTimPendingHonor">
                                        @foreach ($timsPendingWithHonor as $tim)
                                            <div class="accordion-item mb-2 border border-info rounded-3 shadow-sm">
                                                <h2 class="accordion-header"
                                                    id="heading-pending-honor-{{ $tim->id }}">
                                                    <button
                                                        class="accordion-button collapsed bg-info-subtle text-info fw-bold rounded-3 p-3"
                                                        type="button" data-bs-toggle="collapse"
                                                        data-bs-target="#tim-pending-honor-{{ $tim->id }}"
                                                        aria-expanded="false"
                                                        aria-controls="tim-pending-honor-{{ $tim->id }}">
                                                        <div class="d-flex align-items-center w-100">
                                                            <i class="bi bi-clock-fill me-3 fs-5"></i>
                                                            <div class="flex-grow-1">
                                                                <span class="fs-6">{{ $tim->nama_tim }}</span>
                                                                <small
                                                                    class="d-block text-muted">{{ $tim->users->count() }}
                                                                    Anggota</small>
                                                            </div>
                                                            <span
                                                                class="badge bg-info rounded-pill py-2 px-3 ms-2">Akan
                                                                Dapat Honor</span>
                                                            <span
                                                                class="badge bg-warning text-dark rounded-pill py-2 px-3 ms-2">Pending</span>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="tim-pending-honor-{{ $tim->id }}"
                                                    class="accordion-collapse collapse"
                                                    aria-labelledby="heading-pending-honor-{{ $tim->id }}"
                                                    data-bs-parent="#accordionTimPendingHonor">
                                                    <div class="accordion-body p-3 bg-light">
                                                        @if ($tim->keterangan)
                                                            <p class="mb-3 text-muted small">{{ $tim->keterangan }}
                                                            </p>
                                                        @endif
                                                        <ul class="list-group list-group-flush">
                                                            @forelse($tim->users as $anggota)
                                                                <li
                                                                    class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 bg-light">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="rounded-circle bg-info text-white d-flex justify-content-center align-items-center me-2"
                                                                            style="width:32px; height:32px; font-size: 0.8rem;">
                                                                            {{ strtoupper(substr($anggota->name, 0, 1)) }}
                                                                        </div>
                                                                        <div>
                                                                            <strong
                                                                                class="d-block">{{ $anggota->name }}</strong>
                                                                            <small
                                                                                class="text-muted">{{ $anggota->jabatan->name }}</small>
                                                                        </div>
                                                                    </div>
                                                                    <small class="text-muted">
                                                                        @php
                                                                            $timCount =
                                                                                $timCountPerUser[$anggota->id] ?? 0;
                                                                            $maksHonor =
                                                                                $anggota->jabatan->eselon->maks_honor ??
                                                                                0;
                                                                        @endphp
                                                                        {{ $timCount }}/{{ $maksHonor }} Honor
                                                                        @if (isset($statusHonorPerTim[$anggota->id][$tim->id]))
                                                                            @if ($statusHonorPerTim[$anggota->id][$tim->id] === 'Akan menerima honor jika disetujui')
                                                                                <span
                                                                                    class="badge bg-info text-white ms-1">Akan
                                                                                    menerima honor jika disetujui</span>
                                                                            @else
                                                                                <span
                                                                                    class="badge bg-secondary ms-1">{{ $statusHonorPerTim[$anggota->id][$tim->id] }}</span>
                                                                            @endif
                                                                        @endif
                                                                    </small>
                                                                </li>
                                                            @empty
                                                                <li
                                                                    class="list-group-item text-muted px-0 py-2 bg-light">
                                                                    Belum ada anggota</li>
                                                            @endforelse
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif {{-- End of if ($tims->isEmpty()) --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Script Search Tim --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchTim');
            const accordions = document.querySelectorAll('.accordion-flush'); // Select all accordion containers

            searchInput.addEventListener('keyup', function() {
                let filter = this.value.toLowerCase();
                let anyTimFound = false;

                accordions.forEach(accordion => {
                    let accordionItems = accordion.querySelectorAll('.accordion-item');
                    let accordionHasVisibleItems = false;

                    accordionItems.forEach(item => {
                        let timNameElement = item.querySelector('.accordion-button .fs-6');
                        if (timNameElement) {
                            let timName = timNameElement.textContent.toLowerCase();
                            if (timName.includes(filter)) {
                                item.style.display = ''; // Show item
                                accordionHasVisibleItems = true;
                                anyTimFound = true;
                            } else {
                                item.style.display = 'none'; // Hide item
                            }
                        }
                    });

                    // Hide or show the accordion section header if no items are visible
                    const sectionHeader = accordion
                        .previousElementSibling; // Assuming header is right before accordion
                    if (sectionHeader && sectionHeader.tagName === 'H6') {
                        if (accordionHasVisibleItems) {
                            sectionHeader.style.display = '';
                            accordion.style.display = '';
                        } else {
                            sectionHeader.style.display = 'none';
                            accordion.style.display = 'none';
                        }
                    }
                });

                // Handle "Tim tidak ditemukan" message globally
                let notFoundId = 'tim-not-found-msg';
                let notFoundElem = document.getElementById(notFoundId);
                const timListContainer = document.querySelector(
                    '.col-lg-8 .card-body'); // Adjust selector if needed

                if (!anyTimFound && filter.length > 0) { // Only show if search input is not empty
                    if (!notFoundElem) {
                        let msg = document.createElement('p');
                        msg.id = notFoundId;
                        msg.className = 'text-muted text-center my-3 fs-5';
                        msg.innerHTML = '<i class="bi bi-exclamation-circle me-2"></i>Tim tidak ditemukan.';
                        timListContainer.appendChild(msg);
                    }
                } else {
                    if (notFoundElem) {
                        notFoundElem.remove();
                    }
                }
            });
        });
    </script>
</x-staff-layout>