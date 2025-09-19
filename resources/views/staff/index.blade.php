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
            <!-- Data Diri -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Data Diri Anda</h5>
                        <p><strong>Nama:</strong> {{ $user->name }}</p>
                        <p><strong>NIP:</strong> {{ $user->nip }}</p>
                        <p><strong>Jabatan:</strong> {{ $user->jabatan->name }}</p>

                        <p class="fw-semibold mb-2"><strong>Jatah Penerimaan Honorarium</strong></p>

                        @php
                            $progress = $maksHonor > 0 ? ($totalTim / $maksHonor) * 100 : 0;
                        @endphp

                        <div class="progress mb-2">
                            <div class="progress-bar {{ $totalTim > $maksHonor ? 'bg-danger' : 'bg-success' }}"
                                role="progressbar" style="width: {{ $progress > 100 ? 100 : $progress }}%">
                                {{ $totalTim }} / {{ $maksHonor }} Honor
                            </div>
                        </div>

                        @if ($totalTim > $maksHonor)
                            <div class="alert alert-warning p-2 mb-0">
                                <small>
                                    Anda telah melebihi batas maksimal honorarium ({{ $maksHonor }}).
                                    Honor berikutnya tidak bisa diterima.
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>


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

                        <!-- Tim item -->
                        <div class="accordion" id="accordionTim">
                            @forelse($tims as $tim)
                                <div class="accordion-item mb-3 shadow-sm">
                                    <h2 class="accordion-header" id="heading-{{ $tim->id }}">
                                        <button
                                            class="accordion-button collapsed d-flex justify-content-between align-items-center shadow-sm rounded-3 p-3 w-100"
                                            type="button" data-bs-toggle="collapse"
                                            data-bs-target="#tim-{{ $tim->id }}"
                                            style="transition: background-color 0.3s;">

                                            <div class="d-flex align-items-center gap-3">
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold fs-6">{{ $tim->nama_tim }}</span>
                                                    <small class="text-muted">{{ $tim->users->count() }}
                                                        Anggota</small>
                                                </div>
                                            </div>

                                            <span
                                                class="badge 
                                                {{ $tim->status == 'approved' ? 'bg-success' : ($tim->status == 'pending' ? 'bg-warning text-dark' : 'bg-danger') }} 
                                                rounded-pill py-2 px-3 fs-6">
                                                {{ ucfirst($tim->status) }}
                                            </span>
                                        </button>
                                    </h2>


                                    <div id="tim-{{ $tim->id }}" class="accordion-collapse collapse"
                                        data-bs-parent="#accordionTim">
                                        <div class="accordion-body p-3">
                                            @if ($tim->keterangan)
                                                <p class="mb-3 text-muted">{{ $tim->keterangan }}</p>
                                            @endif
                                            <ul class="list-group list-group-flush">
                                                @forelse($tim->users as $anggota)
                                                    <li
                                                        class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center"
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
                                                                $maksHonor = $anggota->jabatan->eselon->maks_honor ?? 0;

                                                                // Hitung jumlah tim approved sebenarnya (tanpa limit maks_honor)
                                                                $actualTimCount = $anggota
                                                                    ->tims()
                                                                    ->where('status', 'approved')
                                                                    ->count();
                                                            @endphp

                                                            {{ $timCount }}/{{ $maksHonor }} Honor

                                                            @if ($timCount == $maksHonor && $maksHonor > 0)
                                                                <span class="badge bg-success ms-1">Honor
                                                                    Diterima</span>
                                                            @elseif ($actualTimCount > $maksHonor && $maksHonor > 0)
                                                                <span class="badge bg-warning text-dark ms-1">Tidak bisa
                                                                    menerima honor lagi</span>
                                                            @endif
                                                        </small>
                                                    </li>
                                                @empty
                                                    <li class="list-group-item text-muted">Belum ada anggota</li>
                                                @endforelse
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted">Belum ada tim yang terdaftar.</p>
                            @endforelse
                        </div>
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
