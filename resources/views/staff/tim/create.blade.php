<x-staff-layout>
    @push('styles')
        {{-- Contoh import font dari Google Fonts --}}
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Inter', sans-serif;
                background-color: #f8f9fa; /* Warna latar belakang yang lebih lembut */
            }
            .card {
                border: none; /* Hapus border default */
                border-radius: 0.75rem; /* Sudut yang lebih membulat */
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08); /* Shadow yang lebih halus */
            }
            .card-header {
                border-bottom: 1px solid #e9ecef; /* Border bawah yang lebih halus */
                background-color: #ffffff;
                border-top-left-radius: 0.75rem;
                border-top-right-radius: 0.75rem;
            }
            .form-label {
                font-weight: 500; /* Label sedikit lebih tebal */
                color: #343a40;
            }
            .form-control, .select2-container--bootstrap-5 .select2-selection--multiple {
                border-radius: 0.5rem; /* Sudut input yang lebih membulat */
                border-color: #ced4da;
            }
            .form-control:focus, .select2-container--bootstrap-5.select2-container--focus .select2-selection--multiple {
                border-color: #86b7fe; /* Warna fokus yang lebih lembut */
                box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            }
            /* Custom file input styling */
            .custom-file-input-wrapper {
                position: relative;
                overflow: hidden;
                display: inline-block;
                width: 100%;
            }
            .custom-file-input-wrapper input[type="file"] {
                position: absolute;
                left: 0;
                top: 0;
                opacity: 0;
                cursor: pointer;
                width: 100%;
                height: 100%;
            }
            .custom-file-input-display {
                display: flex;
                align-items: center;
                border: 1px solid #ced4da;
                border-radius: 0.5rem;
                padding: 0.375rem 0.75rem;
                background-color: #fff;
                cursor: pointer;
                transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
            }
            .custom-file-input-display:hover {
                border-color: #86b7fe;
            }
            .custom-file-input-display .file-name {
                flex-grow: 1;
                margin-left: 0.5rem;
                color: #6c757d;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .custom-file-input-display .btn-upload {
                flex-shrink: 0;
                background-color: #0d6efd;
                color: #fff;
                border: none;
                padding: 0.25rem 0.75rem;
                border-radius: 0.3rem;
            }
            .badge {
                border-radius: 1rem; /* Badge berbentuk pil */
                padding: 0.4em 0.7em;
                font-weight: 500;
            }
            .table thead th {
                background-color: #f1f4f8; /* Latar belakang header tabel yang lebih lembut */
                color: #495057;
                font-weight: 600;
            }
        </style>
    @endpush

    <div class="container my-4">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Buat Tim Baru</h5>
                    <a href="{{ route('staff.tim.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('staff.tim.store') }}" method="POST" enctype="multipart/form-data">
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

                    <!-- Upload SK (Customized) -->
                    <div class="mb-3">
                        <label for="sk_file" class="form-label">Upload SK (PDF) <span
                                class="text-danger">*</span></label>
                        <div class="custom-file-input-wrapper">
                            <input type="file" class="form-control @error('sk_file') is-invalid @enderror" id="sk_file"
                                name="sk_file" accept="application/pdf" required>
                            <div class="custom-file-input-display">
                                <span class="btn-upload"><i class="bi bi-upload"></i> Pilih File</span>
                                <span class="file-name" id="sk_file_name">Tidak ada file dipilih</span>
                            </div>
                        </div>
                        @error('sk_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Pilih Anggota -->
                    <div class="mb-4">
                        <label class="form-label">Pilih Anggota Tim <span class="text-danger">*</span></label>
                        {{-- Input tersembunyi untuk user yang sedang login --}}
                        <input type="hidden" name="anggota[]" value="{{ Auth::id() }}" id="loggedInUserHiddenInput">
                        <select class="form-control select2-multiple @error('anggota') is-invalid @enderror"
                            name="anggota[]" id="anggota" multiple="multiple">
                            @foreach ($availableUsers as $user)
                                <option value="{{ $user->id }}" data-nip="{{ $user->nip }}"
                                    data-jabatan="{{ $user->jabatan->name }}"
                                    data-tim-count="{{ $timCounts[$user->id] ?? 0 }}"
                                    data-maks-honor="{{ $user->jabatan->eselon->maks_honor }}"
                                    @if ($user->id == Auth::id()) selected disabled data-is-logged-in="true" @endif>
                                    {{ $user->name }} @if ($user->id == Auth::id()) (Anda) @endif
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
                            <div class="card-header">
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
                                                <th>Honorarium</th>
                                                <th>Aksi</th> {{-- Tambah kolom Aksi --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Diisi oleh JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Submit -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('staff.tim.index') }}" class="btn btn-secondary">Batal</a>
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
                    placeholder: 'Pilih anggota tim',
                    allowClear: true,
                    templateSelection: function (data, container) {
                        // Jika opsi adalah user yang sedang login, tambahkan kelas untuk menonaktifkan penghapusan
                        if ($(data.element).data('is-logged-in')) {
                            $(container).addClass('select2-selection__choice--disabled');
                        }
                        return data.text;
                    }
                });

                // Fungsi untuk memperbarui tabel preview
                function updateSelectedMembersTable() {
                    let tbody = $('#selectedMembers tbody');
                    tbody.empty();

                    // Dapatkan ID user yang sedang login
                    const loggedInUserId = {{ Auth::id() }};

                    // Ambil semua opsi yang terpilih, termasuk yang disabled
                    let selectedOptions = $('#anggota').find(':selected');

                    // Tambahkan opsi user yang sedang login jika belum ada di selectedOptions
                    let loggedInUserOptionExists = false;
                    selectedOptions.each(function() {
                        if ($(this).val() == loggedInUserId) {
                            loggedInUserOptionExists = true;
                            return false; // break loop
                        }
                    });

                    if (!loggedInUserOptionExists) {
                        // Jika user yang login tidak ada di selectedOptions (misal karena Select2 clear all),
                        // tambahkan secara manual dari opsi yang disabled
                        let loggedInUserOption = $('#anggota option[data-is-logged-in="true"]');
                        if (loggedInUserOption.length) {
                            selectedOptions = selectedOptions.add(loggedInUserOption);
                        }
                    }


                    selectedOptions.each(function() {
                        let option = $(this);
                        let userId = option.val();
                        let timCount = option.data('tim-count');
                        let maksHonor = option.data('maks-honor');
                        let isLoggedInUser = option.data('is-logged-in');

                        let honorStatusText = '';
                        let badgeClass = 'bg-success';

                        if (maksHonor === 0) {
                            honorStatusText = 'Tidak ada honor';
                            badgeClass = 'bg-secondary';
                        } else if (timCount < maksHonor) {
                            honorStatusText = `Honor Diterima (${timCount}/${maksHonor})`;
                            badgeClass = 'bg-success';
                        } else {
                            honorStatusText = `Tidak menerima honor lagi (${timCount}/${maksHonor})`;
                            badgeClass = 'bg-danger';
                        }

                        if (maksHonor > 0 && timCount >= maksHonor * 0.8 && timCount < maksHonor) {
                            badgeClass = 'bg-warning';
                        }

                        let actionButton = '';
                        let rowClass = '';
                        if (isLoggedInUser) {
                            actionButton = '<span class="text-muted">Tidak dapat dihapus</span>';
                            rowClass = 'table-primary'; // Menandai baris user yang login
                        } else {
                            actionButton = `<button type="button" class="btn btn-sm btn-danger remove-member" data-id="${userId}"><i class="bi bi-trash"></i></button>`;
                        }

                        tbody.append(`
                            <tr class="${rowClass}">
                                <td>${option.text()}</td>
                                <td>${option.data('nip')}</td>
                                <td>${option.data('jabatan')}</td>
                                <td>
                                    <span class="badge ${badgeClass}">
                                        ${honorStatusText}
                                    </span>
                                </td>
                                <td>${actionButton}</td>
                            </tr>
                        `);
                    });
                }

                // Panggil fungsi update saat Select2 berubah
                $('#anggota').on('change', function() {
                    updateSelectedMembersTable();
                });

                // Panggil fungsi update saat halaman dimuat untuk menampilkan user yang login
                updateSelectedMembersTable();

                // Handle penghapusan anggota dari tabel preview
                $(document).on('click', '.remove-member', function() {
                    const userIdToRemove = $(this).data('id');
                    const select2Instance = $('#anggota').select2();

                    // Dapatkan nilai saat ini dari Select2
                    let currentSelections = select2Instance.val();

                    // Filter array untuk menghapus user yang dipilih
                    currentSelections = currentSelections.filter(id => id != userIdToRemove);

                    // Setel nilai Select2 yang baru
                    select2Instance.val(currentSelections).trigger('change');
                });


                // Handle custom file input display
                $('#sk_file').on('change', function() {
                    const fileName = $(this).val().split('\\').pop();
                    $('#sk_file_name').text(fileName || 'Tidak ada file dipilih');
                });
            });
        </script>
    @endpush
</x-staff-layout>