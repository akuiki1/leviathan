<x-admin-layout>
    <div class="container mt-4 d-flex justify-content-between align-items-center">
        <h1 class="mb-0 fw-bold">Daftar User</h1>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Create
        </a>
    </div>
    <div class="container mt-4">
        <div class="card shadow-lg border-0 rounded-3 overflow-hidden">

            <!-- Header gradient + Toolbar -->
            <div class="card-header text-white px-3 py-2 d-flex flex-wrap justify-content-between align-items-center gap-2"
                style="background: linear-gradient(90deg, #007bff, #00c6ff); border-top-left-radius:.5rem; border-top-right-radius:.5rem;">

                <!-- Judul -->
                <h5 class="mb-0">User Table</h5>

                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <!-- Toolbar -->
                    <form method="GET" action="{{ route('admin.users.index') }}" class="d-flex flex-wrap gap-2">
                        <select id="bulk-action" class="form-select form-select-sm w-auto">
                            <option value="">Bulk actions</option>
                            <option value="delete">Delete</option>
                        </select>

                        <select name="role" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                            <option value="">Filter Role</option>
                            <option value="Admin" {{ request('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                            <option value="User" {{ request('role') == 'User'  ? 'selected' : '' }}>User</option>
                        </select>

                        <select name="jabatan_id" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                            <option value="">Filter Jabatan</option>
                            @foreach($jabatans as $jabatan)
                            <option value="{{ $jabatan->id }}" {{ request('jabatan_id') == $jabatan->id ? 'selected' : '' }}>
                                {{ $jabatan->name }}
                            </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>


            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th style="width:40px;"><input type="checkbox" id="select-all"></th>
                            <th>Nama</th>
                            <th>NIP</th>
                            <th>Email</th>
                            <th>Jabatan</th>
                            <th>Role</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td><input type="checkbox" class="select-user" value="{{ $user->id }}"></td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->nip }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->jabatan)
                                <span class="badge bg-info">{{ $user->jabatan->name }}</span>
                                @else
                                <span class="badge bg-secondary">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $user->role === 'Admin' ? 'bg-primary' : 'bg-success' }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.users.show', $user) }}"
                                    class="btn btn-sm btn-outline-secondary me-1" title="Lihat">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}"
                                    class="btn btn-sm btn-outline-info me-1" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('admin.users.destroy', $user) }}"
                                    method="POST" class="d-inline form-delete">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Tidak ada data user.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                    Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} results
                </div>
                <div>{{ $users->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bulk Action Script -->
    <script>
        (function() {
            const bulkAction = document.getElementById('bulk-action');
            const checkboxes = () => document.querySelectorAll('.select-user');
            const selectAll = document.getElementById('select-all');
            const csrfToken = '{{ csrf_token() }}';
            const bulkRoute = "{{ route('admin.users.bulkDelete') }}";

            // select all checkbox behaviour
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    const checked = this.checked;
                    document.querySelectorAll('.select-user').forEach(cb => cb.checked = checked);
                });
            }

            // update select-all if individual checkbox toggled (nice to have)
            document.addEventListener('change', (e) => {
                if (!e.target.classList.contains('select-user')) return;
                const all = document.querySelectorAll('.select-user');
                const checked = Array.from(all).every(cb => cb.checked);
                if (selectAll) selectAll.checked = checked;
            });

            if (bulkAction) {
                bulkAction.addEventListener('change', () => {
                    const action = bulkAction.value;
                    const selectedIds = Array.from(checkboxes()).filter(cb => cb.checked).map(cb => cb.value);

                    if (action !== "delete") return;

                    if (selectedIds.length === 0) {
                        Swal.fire('Oops!', 'Pilih minimal 1 user dulu.', 'info');
                        bulkAction.value = "";
                        return;
                    }

                    Swal.fire({
                        title: 'Yakin hapus user terpilih?',
                        text: "Data user akan hilang permanen!",
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