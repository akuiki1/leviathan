<x-admin-layout>
    <div class="container my-4">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                <li class="breadcrumb-item active" aria-current="page">Import ASN</li>
            </ol>
        </nav>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-file-earmark-excel me-2"></i>Import / Update Massal ASN</h5>
                <a href="{{ route('admin.users.import.template') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-download me-1"></i> Download Template
                </a>
            </div>
            <div class="card-body">
                <div class="alert alert-info small">
                    <strong>Cara kerja:</strong> data dicocokkan per <strong>NIP</strong>.
                    ASN yang NIP-nya belum ada akan <strong>dibuat baru</strong> (password awal = NIP),
                    yang jabatannya berubah akan <strong>dicatat mutasinya otomatis</strong> sesuai
                    <strong>TMT</strong> di file, dan yang tidak berubah dilewati.
                    Kolom <strong>Jabatan</strong> harus sesuai nama di master Jabatan.
                    Anda akan melihat <strong>ringkasan perubahan</strong> dulu sebelum data disimpan.
                </div>

                <form action="{{ route('admin.users.import.preview') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="file" class="form-label">File Excel (.xlsx / .xls / .csv, maks 5MB)</label>
                        <input type="file" name="file" id="file"
                            class="form-control @error('file') is-invalid @enderror"
                            accept=".xlsx,.xls,.csv" required>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i> Cek &amp; Pratinjau
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
