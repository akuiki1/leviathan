<x-staff-layout>

    <!-- Section Data Diri -->
    <section id="dataDiri" class="container my-5">
        <div class="row">
            <div class="col text-center mb-4">
                <h2 class="fw-bold">Data Diri</h2>
                <p class="text-secondary">Berikut adalah informasi personal staf</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th class="w-25">NIP</th>
                                <td>{{ $user->nip ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Nama</th>
                                <td>{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <th>Jabatan</th>
                                <td>{{ $user->jabatan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Jumlah Honorarium</th>
                                <td>
                                    @php
                                        $terima = $user->honor_terima ?? 0;
                                        $total = $user->honor_total ?? 1; // biar nggak bagi 0
                                        $persen = ($terima / $total) * 100;
                                    @endphp

                                    {{ $terima }} dari {{ $total }}
                                    <div class="progress mt-2" style="height: 20px;">
                                        <div class="progress-bar bg-success fw-bold" role="progressbar"
                                            style="width: {{ $persen }}%;" aria-valuenow="{{ $terima }}"
                                            aria-valuemin="0" aria-valuemax="{{ $total }}">
                                            {{ round($persen) }}%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Data Tim -->
    <section id="dataTim" class="container my-5">
        <div class="row">
            <div class="col text-center">
                <h2 class="fw-bold">Data Tim</h2>
                <p class="text-secondary">Berikut adalah data tim yang telah terdaftar</p>
            </div>
        </div>

        <div class="row">
            {{-- Tim 1 --}}
            <div class="col-md-6 mb-3">
                <div class="card shadow">
                    <div class="card-header bg-success text-white fw-bold">
                        Tim Pengembangan Website
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">Budi Santoso (Staff IT)</li>
                            <li class="list-group-item">Andi Wijaya (Programmer)</li>
                            <li class="list-group-item">Siti Aminah (UI/UX Designer)</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Tim 2 --}}
            <div class="col-md-6 mb-3">
                <div class="card shadow">
                    <div class="card-header bg-info text-white fw-bold">
                        Tim Keamanan Sistem
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">Budi Santoso (Staff IT)</li>
                            <li class="list-group-item">Rudi Hartono (Cyber Security)</li>
                            <li class="list-group-item">Dewi Kusuma (Network Engineer)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-staff-layout>
