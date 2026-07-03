<x-admin-layout>
    @php
        $badge = [
            'baru'   => ['bg-success', 'ASN Baru'],
            'pindah' => ['bg-primary', 'Pindah Jabatan'],
            'sama'   => ['bg-secondary', 'Tidak Berubah'],
            'error'  => ['bg-danger', 'Error'],
        ];
    @endphp

    <div class="container my-4">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.import.form') }}">Import ASN</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pratinjau</li>
            </ol>
        </nav>

        <div class="row g-3 mb-3">
            <div class="col-6 col-md-3">
                <div class="card shadow-sm text-center border-success">
                    <div class="card-body py-3">
                        <div class="fs-3 fw-bold text-success">{{ $summary['baru'] }}</div>
                        <div class="small text-muted">ASN Baru</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card shadow-sm text-center border-primary">
                    <div class="card-body py-3">
                        <div class="fs-3 fw-bold text-primary">{{ $summary['pindah'] }}</div>
                        <div class="small text-muted">Pindah Jabatan</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card shadow-sm text-center">
                    <div class="card-body py-3">
                        <div class="fs-3 fw-bold text-secondary">{{ $summary['sama'] }}</div>
                        <div class="small text-muted">Tidak Berubah</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card shadow-sm text-center {{ $summary['error'] > 0 ? 'border-danger' : '' }}">
                    <div class="card-body py-3">
                        <div class="fs-3 fw-bold text-danger">{{ $summary['error'] }}</div>
                        <div class="small text-muted">Error (dilewati)</div>
                    </div>
                </div>
            </div>
        </div>

        @if ($summary['error'] > 0)
            <div class="alert alert-warning">
                Ada <strong>{{ $summary['error'] }}</strong> baris bermasalah. Baris tersebut akan
                <strong>dilewati</strong> saat disimpan; baris lain tetap diproses. Perbaiki file lalu
                unggah ulang bila ingin memasukkan baris yang error.
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Pratinjau Perubahan ({{ $total }} baris)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 480px;">
                    <table class="table table-hover table-sm mb-0 align-middle">
                        <thead class="table-light" style="position: sticky; top: 0;">
                            <tr>
                                <th>#</th>
                                <th>Status</th>
                                <th>NIP</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Jabatan</th>
                                <th>Eselon &amp; Kuota</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                <tr class="{{ $row['aksi'] === 'error' ? 'table-danger' : '' }}">
                                    <td class="text-muted">{{ $row['baris'] }}</td>
                                    <td>
                                        <span class="badge {{ $badge[$row['aksi']][0] }}">{{ $badge[$row['aksi']][1] }}</span>
                                    </td>
                                    <td>{{ $row['nip'] ?: '-' }}</td>
                                    <td>{{ $row['nama'] ?: '-' }}</td>
                                    <td>{{ $row['email'] ?: '-' }}</td>
                                    <td>{{ $row['jab'] ?: '-' }}</td>
                                    <td class="small text-muted">{{ $row['eselon'] ?? '-' }}</td>
                                    <td class="small {{ $row['aksi'] === 'error' ? 'text-danger' : 'text-muted' }}">
                                        {{ $row['message'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                <a href="{{ route('admin.users.import.form') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Unggah Ulang
                </a>
                @if ($summary['baru'] + $summary['pindah'] + $summary['sama'] > 0)
                    <form action="{{ route('admin.users.import.apply') }}" method="POST"
                        onsubmit="return confirm('Terapkan perubahan ini ke database?')">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i> Terapkan Perubahan
                        </button>
                    </form>
                @else
                    <span class="text-muted small">Tidak ada baris valid untuk diterapkan.</span>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
