<x-admin-layout>
    <style>
        @media print {
            .no-print { display: none !important; }
            .container { margin: 0; max-width: 100%; }
        }
    </style>
    <div class="container my-4">
        @if (session('success'))
            <div class="alert alert-success no-print">{{ session('success') }}</div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">Laporan Rekap Honor per Eselon &mdash; Tahun {{ $tahun }}</h5>
                <div class="d-flex align-items-center gap-2 flex-wrap no-print">
                    <form method="GET" class="d-flex align-items-center gap-2">
                        <label for="tahun" class="col-form-label col-form-label-sm">Tahun Anggaran</label>
                        <select name="tahun" id="tahun" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                            @foreach ($tahunTersedia as $t)
                                <option value="{{ $t }}" @selected($t == $tahun)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </form>
                    <a href="{{ route('admin.laporan-honor.asn', ['tahun' => $tahun]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-people"></i> Rincian per ASN
                    </a>
                    <a href="{{ route('admin.laporan-honor.export', ['tahun' => $tahun]) }}" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-file-earmark-excel"></i> Export Excel
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                        <i class="bi bi-printer"></i> Cetak
                    </button>
                </div>
            </div>
            <div class="card-body py-2 border-bottom bg-light text-muted small">
                Cakupan: <strong>{{ $timCounts['approved'] }}</strong> tim approved (dihitung)
                &middot; <strong>{{ $timCounts['pending'] }}</strong> tim pending (belum diproses, tidak dihitung)
                &middot; <strong>{{ $timCounts['rejected'] }}</strong> tim ditolak (diabaikan)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Eselon</th>
                                <th class="text-center">Kuota / Tahun</th>
                                <th class="text-center">Jumlah ASN</th>
                                <th class="text-center">ASN Over Limit</th>
                                <th class="text-end">Jumlah Tim Dibayar</th>
                                <th class="text-end">Jumlah Tim Tidak Dibayar</th>
                                <th class="no-print"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rekap as $baris)
                                <tr>
                                    <td>{{ $baris['eselon']->name ?? 'Tanpa Eselon' }}</td>
                                    <td class="text-center">
                                        @if ($baris['eselon'])
                                            <span class="badge bg-info">{{ $baris['eselon']->maks_honor }} tim</span>
                                        @else
                                            <span class="badge bg-secondary">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $baris['jumlah_asn'] }}</td>
                                    <td class="text-center">
                                        @if ($baris['jumlah_over_limit'] > 0)
                                            <span class="badge bg-danger">{{ $baris['jumlah_over_limit'] }}</span>
                                        @else
                                            <span class="badge bg-secondary">0</span>
                                        @endif
                                    </td>
                                    <td class="text-end text-success fw-semibold">
                                        {{ $baris['jumlah_tim_dibayar'] }}
                                    </td>
                                    <td class="text-end {{ $baris['jumlah_tim_tidak_dibayar'] > 0 ? 'text-danger fw-semibold' : 'text-muted' }}">
                                        {{ $baris['jumlah_tim_tidak_dibayar'] }}
                                    </td>
                                    <td class="text-end no-print">
                                        <a href="{{ route('admin.laporan-honor.asn', ['tahun' => $tahun, 'eselon_id' => $baris['eselon']->id ?? 0]) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Lihat ASN di eselon ini">
                                            <i class="bi bi-search"></i> Rincian
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data eselon.</td></tr>
                            @endforelse
                        </tbody>
                        @if ($rekap->isNotEmpty())
                            <tfoot class="table-light">
                                <tr class="fw-bold">
                                    <td colspan="4" class="text-end">Total Tahun {{ $tahun }}</td>
                                    <td class="text-end text-success">
                                        {{ $rekap->sum('jumlah_tim_dibayar') }}
                                    </td>
                                    <td class="text-end text-danger">
                                        {{ $rekap->sum('jumlah_tim_tidak_dibayar') }}
                                    </td>
                                    <td class="no-print"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white text-muted small">
                Keanggotaan dihitung pada eselon yang berlaku <em>saat ASN bergabung ke tim</em> (snapshot) &mdash;
                ASN yang mutasi eselon di tengah tahun dapat terhitung di dua baris eselon.
                "Jumlah Tim Tidak Dibayar" adalah keanggotaan yang melebihi kuota ASN &mdash; potensi
                kekurangan yang perlu diwaspadai sebelum audit akhir tahun.
            </div>
        </div>

        {{-- Daftar pengecualian: temuan yang paling dicari auditor --}}
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-exclamation-triangle text-danger"></i>
                    ASN Melebihi Kuota &mdash; Tahun {{ $tahun }}
                </h6>
                @if ($overLimit->isNotEmpty())
                    <a href="{{ route('admin.laporan-honor.asn', ['tahun' => $tahun, 'over_limit' => 1]) }}" class="btn btn-sm btn-outline-danger no-print">
                        Lihat semua di Rincian per ASN
                    </a>
                @endif
            </div>
            <div class="card-body p-0">
                @if ($overLimit->isEmpty())
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-check-circle text-success"></i>
                        Tidak ada ASN yang melebihi kuota pada tahun ini.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>NIP</th>
                                    <th>Nama</th>
                                    <th>Eselon Saat Ini</th>
                                    <th class="text-center">Kuota</th>
                                    <th class="text-center">Tim Approved</th>
                                    <th class="text-center">Tidak Dibayar</th>
                                    <th class="no-print"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($overLimit as $baris)
                                    <tr>
                                        <td class="font-monospace">{{ $baris['asn']->nip }}</td>
                                        <td>{{ $baris['asn']->name }}</td>
                                        <td>{{ $baris['asn']->jabatan->eselon->name ?? '-' }}</td>
                                        <td class="text-center">{{ $baris['maks_honor'] }}</td>
                                        <td class="text-center">{{ $baris['jumlah_tim_approved'] }}</td>
                                        <td class="text-center"><span class="badge bg-danger">{{ $baris['jumlah_tidak_dibayar'] }}</span></td>
                                        <td class="text-end no-print">
                                            <a href="{{ route('admin.users.show', ['user' => $baris['asn']->id, 'tahun' => $tahun]) }}"
                                               class="btn btn-sm btn-outline-secondary">Detail</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
