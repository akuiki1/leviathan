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
                    <a href="{{ route('admin.laporan-honor.export', ['tahun' => $tahun]) }}" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-file-earmark-excel"></i> Export Excel
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                        <i class="bi bi-printer"></i> Cetak
                    </button>
                </div>
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
                                <th class="text-end">Total Dibayar</th>
                                <th class="text-end">Total Tidak Dibayar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rekap as $baris)
                                <tr>
                                    <td>{{ $baris['eselon']->name }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ $baris['eselon']->maks_honor }} tim</span>
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
                                        Rp {{ number_format($baris['total_dibayar'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-end {{ $baris['total_tidak_dibayar'] > 0 ? 'text-danger fw-semibold' : 'text-muted' }}">
                                        Rp {{ number_format($baris['total_tidak_dibayar'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data eselon.</td></tr>
                            @endforelse
                        </tbody>
                        @if ($rekap->isNotEmpty())
                            <tfoot class="table-light">
                                <tr class="fw-bold">
                                    <td colspan="4" class="text-end">Total Tahun {{ $tahun }}</td>
                                    <td class="text-end text-success">
                                        Rp {{ number_format($rekap->sum('total_dibayar'), 0, ',', '.') }}
                                    </td>
                                    <td class="text-end text-danger">
                                        Rp {{ number_format($rekap->sum('total_tidak_dibayar'), 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white text-muted small">
                "Total Tidak Dibayar" adalah nominal honor dari tim yang melebihi kuota ASN di eselon
                tersebut &mdash; potensi kekurangan yang perlu diwaspadai sebelum audit akhir tahun.
            </div>
        </div>
    </div>
</x-admin-layout>
