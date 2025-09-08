<x-admin-layout>
    <h1 class="mb-4 fw-bold container mt-4">Daftar Honoraria</h1>

    <div class="container mt-4">
        <div class="card shadow-lg border-0 rounded-3 overflow-hidden">

            <!-- Header gradient + Toolbar -->
            <div class="card-header text-white px-3 py-2 d-flex flex-wrap justify-content-between align-items-center gap-2"
                 style="background: linear-gradient(90deg, #007bff, #00c6ff); border-top-left-radius:.5rem; border-top-right-radius:.5rem;">

                <h5 class="mb-0">Honoraria Table</h5>

                <form method="GET" action="{{ route('admin.honoraria.index') }}" class="d-flex flex-wrap gap-2">
                    <select id="bulk-action" class="form-select form-select-sm w-auto">
                        <option value="">Bulk actions</option>
                        <option value="delete">Delete</option>
                    </select>
                </form>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th style="width:40px;"><input type="checkbox" id="select-all"></th>
                            <th>Tim</th>
                            <th>User</th>
                            <th>Dibuat</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($honoraria as $honor)
                        <tr>
                            <td><input type="checkbox" class="select-item" value="{{ $honor->id }}"></td>
                            <td>{{ $honor->tim?->nama_tim ?? '-' }}</td>
                            <td>{{ $honor->user?->name ?? '-' }}</td>
                            <td>{{ $honor->created_at->format('d M Y') }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.honoraria.show', $honor) }}" class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <form action="{{ route('admin.honoraria.destroy', $honor) }}" method="POST" class="d-inline form-delete">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">Tidak ada data honoraria.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                    Showing {{ $honoraria->firstItem() }} to {{ $honoraria->lastItem() }} of {{ $honoraria->total() }} results
                </div>
                <div>{{ $honoraria->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bulk Action Script -->
    <script>
    const bulkActionHonor = document.getElementById('bulk-action');
    const checkboxesHonor = document.querySelectorAll('.select-item');

    if (bulkActionHonor) {
        bulkActionHonor.addEventListener('change', () => {
            const action = bulkActionHonor.value;
            const selectedIds = Array.from(checkboxesHonor).filter(cb => cb.checked).map(cb => cb.value);

            if (action !== "delete") return;

            if (selectedIds.length === 0) {
                Swal.fire('Oops!', 'Pilih minimal 1 honoraria dulu.', 'info');
                bulkActionHonor.value = "";
                return;
            }

            Swal.fire({
                title: 'Yakin hapus honoraria terpilih?',
                text: "Data honoraria akan hilang permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = "{{ route('admin.honoraria.bulkDelete') }}";
                    form.innerHTML = `@csrf @method('DELETE')`;
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
                    bulkActionHonor.value = "";
                }
            });
        });
    }
    </script>
</x-admin-layout>
