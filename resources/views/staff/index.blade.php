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
                                <td>1987654321</td>
                            </tr>
                            <tr>
                                <th>Nama</th>
                                <td>Ahmad Prasetyo</td>
                            </tr>
                            <tr>
                                <th>Jabatan</th>
                                <td>Sekretaris Desa</td>
                            </tr>
                            <tr>
                                <th>Jumlah Honorarium</th>
                                <td>
                                    2 dari 3
                                    <div class="progress mt-2" style="height: 20px;">
                                        <div class="progress-bar bg-success fw-bold" role="progressbar"
                                             style="width: {{ (2/3)*100 }}%;"
                                             aria-valuenow="2" aria-valuemin="0" aria-valuemax="3">
                                            67%
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
        <div class="row mb-4">
            <div class="col text-center">
                <h2 class="fw-bold">Data Tim</h2>
                <p class="text-secondary">Berikut adalah tim yang melibatkan staf ini</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Tim 1 -->
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Tim Penyusun APBDes 2025</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Deskripsi:</strong> Menyusun anggaran pendapatan dan belanja desa</p>
                        <h6 class="fw-bold">Personil Tim:</h6>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Ahmad Prasetyo
                                <span class="badge bg-secondary">Sekretaris Desa</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Siti Aminah
                                <span class="badge bg-secondary">Bendahara</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Budi Santoso
                                <span class="badge bg-secondary">Kepala Dusun</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Tim 2 -->
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Tim Pembangunan Infrastruktur</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Deskripsi:</strong> Mengawasi pembangunan jalan desa</p>
                        <h6 class="fw-bold">Personil Tim:</h6>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Ahmad Prasetyo
                                <span class="badge bg-secondary">Sekretaris Desa</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Joko Widodo
                                <span class="badge bg-secondary">Ketua Tim</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Rahmat Hidayat
                                <span class="badge bg-secondary">Anggota</span>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </section>

</x-staff-layout>
