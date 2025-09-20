<x-staff-layout>

    <div class="container my-4">
        <div class="row g-2">
            <!-- Card Statistik -->
            <div class="col-4">
                <div class="card text-center shadow-sm">
                    <div class="card-body p-2">
                        <!-- Teks deskripsi -->
                        <p class="text-muted mb-1 d-none d-sm-block fs-6">Jumlah Tim Anda</p> <!-- Desktop -->
                        <p class="text-muted mb-1 d-block d-sm-none fs-7">Jumlah Tim</p> <!-- Mobile -->

                        <!-- Angka -->
                        <h3 class="fw-bold mb-0 d-none d-sm-block">{{ $totalTim }} Tim</h3> <!-- Desktop -->
                        <h6 class="fw-bold mb-0 d-block d-sm-none">{{ $totalTim }}</h6> <!-- Mobile -->
                    </div>
                </div>
            </div>

            <div class="col-4">
                <div class="card text-center shadow-sm">
                    <div class="card-body p-2">
                        <p class="text-muted mb-1 d-none d-sm-block fs-6">Jumlah honor yang diterima</p>
                        <p class="text-muted mb-1 d-block d-sm-none fs-7">Honor diterima</p>

                        @php
                            $progress = $maksHonor > 0 ? ($totalTim / $maksHonor) * 100 : 0;
                            $progress = $progress > 100 ? 100 : $progress;
                        @endphp

                        <h3 class="fw-bold mb-0 d-none d-sm-block">{{ $totalTim }}/{{ $maksHonor }} Honor</h3>
                        <h6 class="fw-bold mb-0 d-block d-sm-none">{{ $totalTim }}/{{ $maksHonor }}</h6>

                        @if ($totalTim > $maksHonor)
                            <div class="alert alert-warning p-2 mt-2 mb-0">
                                <small>
                                    Anda telah melebihi batas maksimal honorarium ({{ $maksHonor }}).
                                    Honor berikutnya tidak bisa diterima.
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-4">
                <div class="card text-center shadow-sm">
                    <div class="card-body p-2">
                        <p class="text-muted mb-1 d-none d-sm-block fs-6">Diperbaharui pada</p>
                        <p class="text-muted mb-1 d-block d-sm-none fs-7">Diupdate</p>

                        <h3 class="fw-bold mb-0 d-none d-sm-block">
                            {{ \Carbon\Carbon::parse($user->created_at)->translatedFormat('d F Y') }}
                        </h3>
                        <h6 class="fw-bold mb-0 d-block d-sm-none">
                            {{ \Carbon\Carbon::parse($user->created_at)->translatedFormat('d-m-Y') }}
                        </h6>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-4">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Data Diri Anda</h5>
                        <p><strong>Nama:</strong> {{ $user->name }}</p>
                        <p><strong>NIP:</strong> {{ $user->nip }}</p>
                        <p><strong>Jabatan:</strong> {{ $user->jabatan->name }}</p>

                        <p class="fw-semibold mb-2"><strong>Jatah Penerimaan Honorarium</strong></p>

                        @php
                            // LOGIC KONSISTEN: Sama dengan card honor
                            $timsApprovedUser = $user
                                ->tims()
                                ->where('tims.status', 'approved')
                                ->orderBy('tims.updated_at', 'asc')
                                ->get();

                            $actualHonorCount = min($timsApprovedUser->count(), $maksHonor);
                            $totalTimApproved = $timsApprovedUser->count();

                            $progress = $maksHonor > 0 ? ($actualHonorCount / $maksHonor) * 100 : 0;
                        @endphp

                        <div class="progress mb-2">
                            <div class="progress-bar {{ $totalTimApproved > $maksHonor ? 'bg-warning' : ($actualHonorCount == $maksHonor ? 'bg-success' : 'bg-primary') }}"
                                role="progressbar" style="width: {{ $progress }}%">
                                {{ $actualHonorCount }} / {{ $maksHonor }} Honor
                            </div>
                        </div>

                        @if ($totalTimApproved > $maksHonor)
                            <div class="alert alert-warning p-2 mb-0">
                                <small>
                                    Anda memiliki {{ $totalTimApproved }} tim approved, tetapi hanya menerima honor
                                    dari {{ $actualHonorCount }} tim pertama (berdasarkan waktu persetujuan).
                                </small>
                            </div>
                        @elseif ($actualHonorCount == $maksHonor)
                            <div class="alert alert-success p-2 mb-0">
                                <small>
                                    Anda telah mencapai batas maksimal honorarium ({{ $maksHonor }}).
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Daftar Tim -->
            <!-- Daftar Tim -->
            <div class="col-md-8 mt-3 mt-md-0">
                <div class="card shadow-sm">
                    <div class="card-body">
                        {{-- header --}}
                        <h5 class="fw-bold mb-3">Daftar Tim Anda</h5>
                        <div class="d-flex mb-3">
                            <input type="text" class="form-control me-2" placeholder="Cari Tim..." id="searchTim">
                            <a href="{{ route('staff.tim.create') }}" class="btn btn-primary btn-sm">Buat Tim</a>
                        </div>

                        <!-- BAGIAN 1: TIM YANG MENDAPAT HONOR -->
                        @php
                            $timsWithHonor = $tims->filter(function ($tim) use ($statusHonorPerTim, $user) {
                                // Cek apakah user mendapat honor dari tim ini
                                return isset($statusHonorPerTim[$user->id][$tim->id]) &&
                                    $statusHonorPerTim[$user->id][$tim->id] === 'Honor Diterima';
                            });
                        @endphp

                        @if ($timsWithHonor->isNotEmpty())
                            <div class="mb-4">
                                <h6 class="fw-bold text-success mb-3">
                                    <i class="bi bi-check-circle-fill me-2"></i>Tim yang Menerima Honor
                                    ({{ $timsWithHonor->count() }})
                                </h6>

                                <div class="accordion" id="accordionTimHonor">
                                    @foreach ($timsWithHonor as $tim)
                                        <div class="accordion-item mb-3 shadow-sm border-success">
                                            <h2 class="accordion-header" id="heading-honor-{{ $tim->id }}">
                                                <button
                                                    class="accordion-button collapsed d-flex justify-content-between align-items-center shadow-sm rounded-3 p-3 w-100 bg-light-success"
                                                    type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#tim-honor-{{ $tim->id }}"
                                                    style="transition: background-color 0.3s;">

                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="text-success">
                                                            <i class="bi bi-award-fill fs-4"></i>
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <span class="fw-bold fs-6">{{ $tim->nama_tim }}</span>
                                                            <small class="text-muted">{{ $tim->users->count() }}
                                                                Anggota</small>
                                                        </div>
                                                    </div>

                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="badge bg-success rounded-pill py-2 px-3 fs-6">
                                                            Honor Diterima
                                                        </span>
                                                        <span
                                                            class="badge 
                                                {{ $tim->status == 'approved' ? 'bg-success' : ($tim->status == 'pending' ? 'bg-warning text-dark' : 'bg-danger') }} 
                                                rounded-pill py-2 px-3 fs-6">
                                                            {{ ucfirst($tim->status) }}
                                                        </span>
                                                    </div>
                                                </button>
                                            </h2>

                                            <div id="tim-honor-{{ $tim->id }}" class="accordion-collapse collapse"
                                                data-bs-parent="#accordionTimHonor">
                                                <div class="accordion-body p-3 bg-light">
                                                    @if ($tim->keterangan)
                                                        <p class="mb-3 text-muted">{{ $tim->keterangan }}</p>
                                                    @endif
                                                    <ul class="list-group list-group-flush">
                                                        @forelse($tim->users as $anggota)
                                                            <li
                                                                class="list-group-item d-flex justify-content-between align-items-center border-0">
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <div class="rounded-circle bg-success text-white d-flex justify-content-center align-items-center"
                                                                        style="width:36px; height:36px;">
                                                                        {{ strtoupper(substr($anggota->name, 0, 1)) }}
                                                                    </div>
                                                                    <div>
                                                                        <strong>{{ $anggota->name }}</strong>
                                                                        <div class="small text-muted">
                                                                            {{ $anggota->jabatan->name }}</div>
                                                                    </div>
                                                                </div>
                                                                <small class="text-muted">
                                                                    @php
                                                                        $timCount = $timCountPerUser[$anggota->id] ?? 0;
                                                                        $maksHonor =
                                                                            $anggota->jabatan->eselon->maks_honor ?? 0;
                                                                    @endphp
                                                                    {{ $timCount }}/{{ $maksHonor }} Honor

                                                                    @if (isset($statusHonorPerTim[$anggota->id][$tim->id]))
                                                                        @if ($statusHonorPerTim[$anggota->id][$tim->id] === 'Honor Diterima')
                                                                            <span class="badge bg-success ms-1">Honor
                                                                                Diterima</span>
                                                                        @else
                                                                            <span
                                                                                class="badge bg-warning text-dark ms-1">
                                                                                {{ $statusHonorPerTim[$anggota->id][$tim->id] }}
                                                                            </span>
                                                                        @endif
                                                                    @endif
                                                                </small>
                                                            </li>
                                                        @empty
                                                            <li class="list-group-item text-muted border-0">Belum ada
                                                                anggota</li>
                                                        @endforelse
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- BAGIAN 2: TIM YANG TIDAK MENDAPAT HONOR LAGI -->
                        @php
                            $timsWithoutHonor = $tims->filter(function ($tim) use ($statusHonorPerTim, $user) {
                                // Cek apakah user tidak mendapat honor dari tim ini
                                return !isset($statusHonorPerTim[$user->id][$tim->id]) ||
                                    !in_array($statusHonorPerTim[$user->id][$tim->id], [
                                        'Honor Diterima',
                                        'Akan menerima honor jika disetujui',
                                    ]);
                            });
                        @endphp

                        @if ($timsWithoutHonor->isNotEmpty())
                            <div class="mb-4">
                                <h6 class="fw-bold text-secondary mb-3">
                                    <i class="bi bi-x-circle-fill me-2"></i>Tim Tanpa Honor
                                    ({{ $timsWithoutHonor->count() }})
                                </h6>

                                <div class="accordion" id="accordionTimNoHonor">
                                    @foreach ($timsWithoutHonor as $tim)
                                        <div class="accordion-item mb-3 shadow-sm border-secondary">
                                            <h2 class="accordion-header" id="heading-no-honor-{{ $tim->id }}">
                                                <button
                                                    class="accordion-button collapsed d-flex justify-content-between align-items-center shadow-sm rounded-3 p-3 w-100 bg-light-secondary"
                                                    type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#tim-no-honor-{{ $tim->id }}"
                                                    style="transition: background-color 0.3s;">

                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="text-secondary">
                                                            <i class="bi bi-dash-circle-fill fs-4"></i>
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <span class="fw-bold fs-6">{{ $tim->nama_tim }}</span>
                                                            <small class="text-muted">{{ $tim->users->count() }}
                                                                Anggota</small>
                                                        </div>
                                                    </div>

                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="badge bg-secondary rounded-pill py-2 px-3 fs-6">
                                                            Tidak Ada Honor
                                                        </span>
                                                        <span
                                                            class="badge 
                                                {{ $tim->status == 'approved' ? 'bg-success' : ($tim->status == 'pending' ? 'bg-warning text-dark' : 'bg-danger') }} 
                                                rounded-pill py-2 px-3 fs-6">
                                                            {{ ucfirst($tim->status) }}
                                                        </span>
                                                    </div>
                                                </button>
                                            </h2>

                                            <div id="tim-no-honor-{{ $tim->id }}"
                                                class="accordion-collapse collapse"
                                                data-bs-parent="#accordionTimNoHonor">
                                                <div class="accordion-body p-3">
                                                    @if ($tim->keterangan)
                                                        <p class="mb-3 text-muted">{{ $tim->keterangan }}</p>
                                                    @endif
                                                    <ul class="list-group list-group-flush">
                                                        @forelse($tim->users as $anggota)
                                                            <li
                                                                class="list-group-item d-flex justify-content-between align-items-center">
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center"
                                                                        style="width:36px; height:36px;">
                                                                        {{ strtoupper(substr($anggota->name, 0, 1)) }}
                                                                    </div>
                                                                    <div>
                                                                        <strong>{{ $anggota->name }}</strong>
                                                                        <div class="small text-muted">
                                                                            {{ $anggota->jabatan->name }}</div>
                                                                    </div>
                                                                </div>
                                                                <small class="text-muted">
                                                                    @php
                                                                        $timCount = $timCountPerUser[$anggota->id] ?? 0;
                                                                        $maksHonor =
                                                                            $anggota->jabatan->eselon->maks_honor ?? 0;
                                                                        $terimaHonor =
                                                                            $terimaHonorPerUser[$anggota->id] ?? 0;
                                                                    @endphp
                                                                    {{ $timCount }}/{{ $maksHonor }} Honor

                                                                    @if (isset($statusHonorPerTim[$anggota->id][$tim->id]))
                                                                        @if ($statusHonorPerTim[$anggota->id][$tim->id] === 'Tidak akan menerima honor')
                                                                            <span class="badge bg-secondary ms-1">Tidak
                                                                                akan menerima honor</span>
                                                                        @elseif($statusHonorPerTim[$anggota->id][$tim->id] === 'Tidak menerima honor lagi')
                                                                            <span
                                                                                class="badge bg-warning text-dark ms-1">Tidak
                                                                                menerima honor lagi</span>
                                                                        @else
                                                                            <span
                                                                                class="badge bg-info text-white ms-1">
                                                                                {{ $statusHonorPerTim[$anggota->id][$tim->id] }}
                                                                            </span>
                                                                        @endif
                                                                    @endif
                                                                </small>
                                                            </li>
                                                        @empty
                                                            <li class="list-group-item text-muted">Belum ada anggota
                                                            </li>
                                                        @endforelse
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- BAGIAN 3: TIM PENDING YANG BERPOTENSI MENDAPAT HONOR -->
                        @php
                            $timsPendingWithHonor = $tims->filter(function ($tim) use ($statusHonorPerTim, $user) {
                                return isset($statusHonorPerTim[$user->id][$tim->id]) &&
                                    $statusHonorPerTim[$user->id][$tim->id] === 'Akan menerima honor jika disetujui';
                            });
                        @endphp

                        @if ($timsPendingWithHonor->isNotEmpty())
                            <div class="mb-4">
                                <h6 class="fw-bold text-info mb-3">
                                    <i class="bi bi-clock-fill me-2"></i>Tim Pending - Berpotensi Honor
                                    ({{ $timsPendingWithHonor->count() }})
                                </h6>

                                <div class="accordion" id="accordionTimPendingHonor">
                                    @foreach ($timsPendingWithHonor as $tim)
                                        <div class="accordion-item mb-3 shadow-sm border-info">
                                            <h2 class="accordion-header"
                                                id="heading-pending-honor-{{ $tim->id }}">
                                                <button
                                                    class="accordion-button collapsed d-flex justify-content-between align-items-center shadow-sm rounded-3 p-3 w-100 bg-light-info"
                                                    type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#tim-pending-honor-{{ $tim->id }}"
                                                    style="transition: background-color 0.3s;">

                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="text-info">
                                                            <i class="bi bi-hourglass-split fs-4"></i>
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <span class="fw-bold fs-6">{{ $tim->nama_tim }}</span>
                                                            <small class="text-muted">{{ $tim->users->count() }}
                                                                Anggota</small>
                                                        </div>
                                                    </div>

                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="badge bg-info rounded-pill py-2 px-3 fs-6">
                                                            Akan Dapat Honor
                                                        </span>
                                                        <span
                                                            class="badge bg-warning text-dark rounded-pill py-2 px-3 fs-6">
                                                            Pending
                                                        </span>
                                                    </div>
                                                </button>
                                            </h2>

                                            <div id="tim-pending-honor-{{ $tim->id }}"
                                                class="accordion-collapse collapse"
                                                data-bs-parent="#accordionTimPendingHonor">
                                                <div class="accordion-body p-3 bg-light">
                                                    @if ($tim->keterangan)
                                                        <p class="mb-3 text-muted">{{ $tim->keterangan }}</p>
                                                    @endif
                                                    <ul class="list-group list-group-flush">
                                                        @forelse($tim->users as $anggota)
                                                            <li
                                                                class="list-group-item d-flex justify-content-between align-items-center border-0">
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <div class="rounded-circle bg-info text-white d-flex justify-content-center align-items-center"
                                                                        style="width:36px; height:36px;">
                                                                        {{ strtoupper(substr($anggota->name, 0, 1)) }}
                                                                    </div>
                                                                    <div>
                                                                        <strong>{{ $anggota->name }}</strong>
                                                                        <div class="small text-muted">
                                                                            {{ $anggota->jabatan->name }}</div>
                                                                    </div>
                                                                </div>
                                                                <small class="text-muted">
                                                                    @php
                                                                        $timCount = $timCountPerUser[$anggota->id] ?? 0;
                                                                        $maksHonor =
                                                                            $anggota->jabatan->eselon->maks_honor ?? 0;
                                                                    @endphp
                                                                    {{ $timCount }}/{{ $maksHonor }} Honor

                                                                    @if (isset($statusHonorPerTim[$anggota->id][$tim->id]))
                                                                        @if ($statusHonorPerTim[$anggota->id][$tim->id] === 'Akan menerima honor jika disetujui')
                                                                            <span
                                                                                class="badge bg-info text-white ms-1">Akan
                                                                                menerima honor jika disetujui</span>
                                                                        @else
                                                                            <span class="badge bg-secondary ms-1">
                                                                                {{ $statusHonorPerTim[$anggota->id][$tim->id] }}
                                                                            </span>
                                                                        @endif
                                                                    @endif
                                                                </small>
                                                            </li>
                                                        @empty
                                                            <li class="list-group-item text-muted border-0">Belum ada
                                                                anggota</li>
                                                        @endforelse
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Pesan jika tidak ada tim -->
                        @if ($tims->isEmpty())
                            <p class="text-muted text-center">Belum ada tim yang terdaftar.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- script search tim --}}
    <script>
        document.getElementById('searchTim').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let found = false;
            document.querySelectorAll('.accordion-item').forEach(function(item) {
                // Cari nama tim di dalam button accordion
                let nama = item.querySelector('.accordion-button .fw-bold').textContent.toLowerCase();
                if (nama.includes(filter)) {
                    item.style.display = '';
                    found = true;
                } else {
                    item.style.display = 'none';
                }
            });

            // Cek dan tampilkan pesan jika tidak ditemukan
            let notFoundId = 'tim-not-found-msg';
            let accordion = document.getElementById('accordionTim');
            let notFoundElem = document.getElementById(notFoundId);

            if (!found) {
                if (!notFoundElem) {
                    let msg = document.createElement('p');
                    msg.id = notFoundId;
                    msg.className = 'text-muted text-center my-3';
                    msg.textContent = 'Tim tidak ditemukan.';
                    accordion.appendChild(msg);
                }
            } else {
                if (notFoundElem) {
                    notFoundElem.remove();
                }
            }
        });
    </script>
</x-staff-layout>
