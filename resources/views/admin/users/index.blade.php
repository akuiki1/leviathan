<x-admin-layout>
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <!-- Tombol Tambah -->
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary d-flex align-items-center mb-2 mb-md-0">
            <i class="bi bi-plus-lg me-1"></i> Tambah User
        </a>

        <!-- Filter + Search + Bulk Delete -->
        <div class="d-flex align-items-center mb-2 mb-md-0 flex-wrap">
            <form method="GET" class="d-flex align-items-center mb-2 me-2">
                <select name="role" class="form-select me-2">
                    <option value="">All Roles</option>
                    <option value="Admin" {{ request('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                    <option value="User" {{ request('role') == 'User' ? 'selected' : '' }}>User</option>
                </select>
                <button type="submit" class="btn btn-outline-primary me-2">Filter</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Reset</a>
            </form>

            <form method="GET" action="{{ route('admin.users.index') }}" class="d-flex mb-2 me-2">
                <input type="text" name="search" class="form-control me-2" placeholder="Cari user..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-outline-success">Search</button>
            </form>

            <button id="bulk-delete" class="btn btn-danger mb-2" disabled>
                <i class="bi bi-trash"></i> Hapus Terpilih
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive shadow-sm rounded">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th><input type="checkbox" id="select-all"></th>

                    @php
                        $columns = ['id'=>'ID','nip'=>'NIP','name'=>'Nama','email'=>'Email','jabatan'=>'Jabatan','role'=>'Role'];
                        $direction = request('direction','asc') === 'asc' ? 'desc' : 'asc';
                    @endphp

                    @foreach($columns as $field => $label)
                        <th>
                            <a href="{{ route('admin.users.index', array_merge(request()->all(), ['sort'=>$field,'direction'=>$direction])) }}" class="text-white text-decoration-none">
                                {{ $label }}
                                @if(request('sort') === $field)
                                    <i class="bi {{ request('direction') === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill' }}"></i>
                                @endif
                            </a>
                        </th>
                    @endforeach
                    <th class="text-center">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($users as $user)
                <tr>
                    <td><input type="checkbox" class="select-user" value="{{ $user->id }}"></td>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->nip }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><span class="badge bg-info">{{ $user->jabatan }}</span></td>
                    <td><span class="badge bg-success">{{ $user->role }}</span></td>
                    <td class="text-center">
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-secondary me-1"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-info me-1"><i class="bi bi-pencil-square"></i></a>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus user ini?')"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">Tidak ada data user.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-3">
        {{ $users->links('pagination::bootstrap-5') }}
    </div>

    <!-- Scripts Bulk Select -->
    <script>
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.select-user');
        const bulkDeleteBtn = document.getElementById('bulk-delete');

        function toggleBulkBtn(){
            const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
            bulkDeleteBtn.disabled = !anyChecked;
        }

        selectAll.addEventListener('change', function(){
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            toggleBulkBtn();
        });

        checkboxes.forEach(cb => cb.addEventListener('change', toggleBulkBtn));

        bulkDeleteBtn.addEventListener('click', function(){
            if(confirm('Yakin hapus semua user yang dipilih?')){
                const selectedIds = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('admin.users.bulkDelete') }}";
                form.innerHTML = `@csrf<input type="hidden" name="ids[]" value="">`;
                selectedIds.forEach(id=>{
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    form.appendChild(input);
                });
                document.body.appendChild(form);
                form.submit();
            }
        });
    </script>
</x-admin-layout>
