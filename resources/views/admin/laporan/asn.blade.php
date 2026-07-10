<x-admin-layout>
    <div class="container my-4">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.laporan-honor.index', ['tahun' => $tahun]) }}">Laporan Honor</a></li>
                <li class="breadcrumb-item active" aria-current="page">Rincian per ASN</li>
            </ol>
        </nav>

        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">Rincian Honor per ASN &mdash; Tahun {{ $tahun }}</h5>
                <a href="{{ route('admin.laporan-honor.export', ['tahun' => $tahun]) }}" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-file-earmark-excel"></i> Export Excel
                </a>
            </div>
            <div class="card-body border-bottom">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label for="tahun" class="form-label form-label-sm mb-1">Tahun Anggaran</label>
                        <select name="tahun" id="tahun" class="form-select form-select-sm">
                            @foreach ($tahunTersedia as $t)
                                <option value="{{ $t }}" @selected($t == $tahun)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <label for="eselon_id" class="form-label form-label-sm mb-1">Eselon</label>
                        <select name="eselon_id" id="eselon_id" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach ($eselons as $e)
                                <option value="{{ $e->id }}" @selected($filterEselon !== null && $filterEselon !== '' && (int) $filterEselon === $e->id)>{{ $e->name }}</option>
                            @endforeach
                            <option value="0" @selected($filterEselon === '0')>Tanpa Eselon</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label for="q" class="form-label form-label-sm mb-1">Cari</label>
                        <input type="text" name="q" id="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Nama atau NIP&hellip;">
                    </div>
                    <div class="col-auto form-check ms-2 mb-1">
                        <input type="checkbox" name="over_limit" value="1" id="over_limit" class="form-check-input" @checked($filterOver)>
                        <label for="over_limit" class="form-check-label small">Hanya over limit</label>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Terapkan</button>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>NIP</th>
                                <th>Nama</th>
                                <th>Jabatan Saat Ini</th>
                                <th>Eselon Saat Ini</th>
                                <th class="text-center">Kuota</th>
                                <th class="text-center">Tim Approved</th>
                                <th class="text-center">Dibayar</th>
                                <th class="text-center">Tidak Dibayar</th>
                                <th class="text-center">Menunggu</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($perAsn as $baris)
                                <tr>
                                    <td class="font-monospace">{{ $baris['asn']->nip }}</td>
                                    <td>
                                        {{ $baris['asn']->name }}
                                        @if ($baris['is_over_limit'])
                                            <span class="badge bg-danger ms-1">Over Limit</span>
                                        @endif
                                    </td>
                                    <td>{{ $baris['asn']->jabatan->name ?? '-' }}</td>
                                    <td>{{ $baris['asn']->jabatan->eselon->name ?? '-' }}</td>
                                    <td class="text-center">{{ $baris['maks_honor'] }}</td>
                                    <td class="text-center">{{ $baris['jumlah_tim_approved'] }}</td>
                                    <td class="text-center text-success fw-semibold">{{ $baris['jumlah_dibayar'] }}</td>
                                    <td class="text-center {{ $baris['jumlah_tidak_dibayar'] > 0 ? 'text-danger fw-semibold' : 'text-muted' }}">
                                        {{ $baris['jumlah_tidak_dibayar'] }}
                                    </td>
                                    <td class="text-center text-muted">{{ $baris['jumlah_pending'] }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.users.show', ['user' => $baris['asn']->id, 'tahun' => $tahun]) }}"
                                           class="btn btn-sm btn-outline-secondary">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center text-muted py-4">Tidak ada ASN yang cocok dengan filter.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white text-muted small">
                Menampilkan {{ $perAsn->count() }} ASN.
                Filter eselon memakai atribusi yang sama dengan rekap: eselon yang berlaku saat ASN
                bergabung ke tim; ASN tanpa tim approved dihitung pada eselon saat ini.
            </div>
        </div>
    </div>
</x-admin-layout>
