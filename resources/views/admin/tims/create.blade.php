<x-admin-layout>
    <div class="container my-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Buat Tim Baru</h5>
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

                    <input type="hidden" name="status" value="pending">

                    <!-- Pilih Anggota -->
                    <div class="mb-4">
                        <label for="anggota" class="form-label">Pilih Anggota Tim <span class="text-danger">*</span></label>
                        <select 
                            class="form-select select2-multiple @error('anggota') is-invalid @enderror"
                            name="anggota[]" id="anggota" multiple="multiple" required>
                            <option></option>
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
                                                <th>Aksi</th>
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

    @push('styles')
        <!-- Select2 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    @endpush

    @push('scripts')
        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <!-- Select2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            $(document).ready(function() {
                // Inisialisasi Select2
                $('#anggota').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'Cari & pilih anggota tim...',
                    allowClear: true,
                    closeOnSelect: false
                });

                function renderTable() {
                    let tbody = $('#selectedMembers tbody');
                    tbody.empty();

                    $('#anggota').find(':selected').each(function() {
                        let option = $(this);
                        let id = option.val();
                        tbody.append(`
                            <tr data-id="${id}">
                                <td>${option.text()}</td>
                                <td>${option.data('nip') || '-'}</td>
                                <td>${option.data('jabatan') || '-'}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger remove-member" data-id="${id}">
                                        <i class="bi bi-x-circle"></i> Hapus
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                }

                // Update tabel saat select berubah
                $('#anggota').on('change', renderTable);

                // Hapus anggota dari tabel + unselect di dropdown
                $(document).on('click', '.remove-member', function() {
                    let id = $(this).data('id');
                    let select = $('#anggota');
                    
                    // Unselect option
                    select.find(`option[value="${id}"]`).prop('selected', false);
                    select.trigger('change'); // Refresh Select2 & tabel
                });
            });
        </script>
    @endpush
</x-admin-layout>
