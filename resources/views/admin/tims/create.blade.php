<x-admin-layout>
    <div class="container mt-4">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.tims.index') }}">Tims</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create Tim</li>
            </ol>
        </nav>
    </div>

    <div class="container my-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Buat Tim Baru</h5>
                    <a href="{{ route('admin.tims.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.tims.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Nama Tim -->
                    <div class="mb-3">
                        <label for="nama_tim" class="form-label">Nama Tim <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_tim') is-invalid @enderror"
                            id="nama_tim" name="nama_tim" value="{{ old('nama_tim') }}" required>
                        @error('nama_tim')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Keterangan -->
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan"
                            rows="3" required>{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Upload SK -->
                    <div class="mb-3">
                        <label for="sk_file" class="form-label">Upload SK (PDF) <span class="text-danger">*</span></label>
                        <input type="file" class="form-control @error('sk_file') is-invalid @enderror" id="sk_file"
                            name="sk_file" accept="application/pdf" required>
                        @error('sk_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Pilih Status -->
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Pilih Anggota -->
                    <div class="mb-4">
                        <label class="form-label">Pilih Anggota Tim <span class="text-danger">*</span></label>
                        <select class="form-control select2-multiple @error('anggota') is-invalid @enderror"
                            name="anggota[]" id="anggota" multiple>
                            <option></option> <!-- biar placeholder jalan -->
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}"
                                    data-nip="{{ $user->nip }}"
                                    data-jabatan="{{ $user->jabatan->name }}">
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('anggota')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Preview Anggota Terpilih -->
                    <div class="mb-4">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Anggota Terpilih</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="selectedMembers">
                                        <thead>
                                            <tr>
                                                <th>Nama</th>
                                                <th>NIP</th>
                                                <th>Jabatan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Diisi otomatis oleh JS -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Submit -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.tims.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Tambahkan Tim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                // Inisialisasi Select2
                $('.select2-multiple').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'Cari & pilih anggota tim...',
                    allowClear: true
                });

                // Update tabel preview saat selection berubah
                $('#anggota').on('change', function() {
                    let tbody = $('#selectedMembers tbody');
                    tbody.empty();

                    $(this).find(':selected').each(function() {
                        let option = $(this);
                        tbody.append(`
                            <tr>
                                <td>${option.text()}</td>
                                <td>${option.data('nip') ?? '-'}</td>
                                <td>${option.data('jabatan') ?? '-'}</td>
                            </tr>
                        `);
                    });
                });
            });
        </script>
    @endpush
</x-admin-layout>
