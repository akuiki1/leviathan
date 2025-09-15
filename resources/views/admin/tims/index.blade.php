<x-admin-layout>
    <div class="container mt-4">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tims</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0 fw-bold">Daftar Tim</h1>
            <a href="{{ route('admin.tims.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Create
            </a>
        </div>
    </div>
    <div class="container mt-4">
        <div class="card shadow-lg border-0 rounded-3 overflow-hidden">

            <!-- Header gradient + Toolbar -->
            <div class="card-header text-white px-3 py-2 d-flex flex-wrap justify-content-between align-items-center gap-2"
                 style="background: linear-gradient(90deg, #007bff, #00c6ff); border-top-left-radius:.5rem; border-top-right-radius:.5rem;">

                <h5 class="mb-0">Tim Table</h5>

                <form method="GET" action="{{ route('admin.tims.index') }}" class="d-flex flex-wrap gap-2">
                    <select id="bulk-action" class="form-select form-select-sm w-auto">
                        <option value="">Bulk actions</option>
                        <option value="delete">Delete</option>
                    </select>

                    <select name="status" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        <option value="">Filter Status</option>
                        <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status')=='approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status')=='rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </form>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th style="width:40px;"><input type="checkbox" id="select-all"></th>
                            <th>Nama Tim</th>
                            <th>Keterangan</th>
                            <th>File SK</th>
                            <th>Dibuat Oleh</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tims as $tim)
                        <tr>
                            <td><input type="checkbox" class="select-item" value="{{ $tim->id }}"></td>
                            <td>{{ $tim->nama_tim }}</td>
                            <td>{{ Str::limit($tim->keterangan, 50) }}</td>
                            <td>
                                @if($tim->sk_file)
                                    <a href="{{ asset('storage/'.$tim->sk_file) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat</a>
                                @else
                                    <span class="badge bg-secondary">-</span>
                                @endif
                            </td>
                            <td>{{ $tim->creator?->name ?? '-' }}</td>
                            <td>
                                <span class="badge
                                    @if($tim->status=='pending') bg-warning
                                    @elseif($tim->status=='approved') bg-success
                                    @else bg-danger @endif">
                                    {{ ucfirst($tim->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.tims.show', $tim) }}" class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.tims.edit', $tim) }}" class="btn btn-sm btn-outline-info me-1">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('admin.tims.destroy', $tim) }}" method="POST" class="d-inline form-delete">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>

                                {{-- Tombol Accept / Reject kalau status masih pending --}}
                                @if($tim->status === 'pending')
                                    <form action="{{ route('admin.tims.approve', $tim) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-success me-1">
                                            <i class="bi bi-check-circle"></i> Accept
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.tims.reject', $tim) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-warning">
                                            <i class="bi bi-x-circle"></i> Reject
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted">Tidak ada data tim.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                    Showing {{ $tims->firstItem() }} to {{ $tims->lastItem() }} of {{ $tims->total() }} results
                </div>
                <div>{{ $tims->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bulk Action Script -->
    <script>
        (function() {
            const bulkAction = document.getElementById('bulk-action');
            const checkboxes = () => document.querySelectorAll('.select-item');
            const selectAll = document.getElementById('select-all');
            const csrfToken = '{{ csrf_token() }}';
            const bulkRoute = "{{ route('admin.tims.bulkDelete') }}";

            // select all checkbox behaviour
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    const checked = this.checked;
                    document.querySelectorAll('.select-item').forEach(cb => cb.checked = checked);
                });
            }

            // update select-all if individual checkbox toggled (nice to have)
            document.addEventListener('change', (e) => {
                if (!e.target.classList.contains('select-item')) return;
                const all = document.querySelectorAll('.select-item');
                const checked = Array.from(all).every(cb => cb.checked);
                if (selectAll) selectAll.checked = checked;
            });

            if (bulkAction) {
                bulkAction.addEventListener('change', () => {
                    const action = bulkAction.value;
                    const selectedIds = Array.from(checkboxes()).filter(cb => cb.checked).map(cb => cb.value);

                    if (action !== "delete") return;

                    if (selectedIds.length === 0) {
                        Swal.fire('Oops!', 'Pilih minimal 1 tim dulu.', 'info');
                        bulkAction.value = "";
                        return;
                    }

                    Swal.fire({
                        title: 'Yakin hapus tim terpilih?',
                        text: "Data tim akan hilang permanen!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Build a simple POST form (no method spoofing) with CSRF
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = bulkRoute;
                            form.style.display = 'none';

                            // CSRF token
                            const token = document.createElement('input');
                            token.type = 'hidden';
                            token.name = '_token';
                            token.value = csrfToken;
                            form.appendChild(token);

                            selectedIds.forEach(id => {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'ids[]';
                                input.value = id;
                                form.appendChild(input);
                            });

                            document.body.appendChild(form);
                            form.submit();
                        } else {
                            bulkAction.value = "";
                        }
                    });
                });
            }
        })();
    </script>
</x-admin-layout>
