<x-admin-layout>
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <a href="{{ route('admin.honoraria.create') }}" class="btn btn-primary d-flex align-items-center mb-2 mb-md-0">
            <i class="bi bi-plus-lg me-1"></i> Tambah Honorarium
        </a>

        <div class="d-flex align-items-center mb-2 mb-md-0 flex-wrap">
            <!-- Search -->
            <form method="GET" action="{{ route('admin.honoraria.index') }}" class="d-flex mb-2 me-2">
                <input type="text" name="search" class="form-control me-2" placeholder="Cari user atau tim..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-outline-success">Search</button>
            </form>

            <!-- Bulk Delete -->
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
                    <th>
                        <a href="{{ route('admin.honoraria.index', array_merge(request()->all(), ['sort'=>'id','direction'=>request('direction','asc')==='asc'?'desc':'asc'])) }}">
                            ID
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('admin.honoraria.index', array_merge(request()->all(), ['sort'=>'user_id','direction'=>request('direction','asc')==='asc'?'desc':'asc'])) }}">
                            Nama User
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('admin.honoraria.index', array_merge(request()->all(), ['sort'=>'tim_id','direction'=>request('direction','asc')==='asc'?'desc':'asc'])) }}">
                            Tim
                        </a>
                    </th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($honoraria as $honor)
                <tr>
                    <td><input type="checkbox" class="select-user" value="{{ $honor->id }}"></td>
                    <td>{{ $honor->id }}</td>
                    <td>{{ $honor->user->name }}</td>
                    <td>{{ $honor->tim->nama_tim }}</td>
                    <td class="text-center">
                        <a href="{{ route('admin.honoraria.edit', $honor) }}" class="btn btn-sm btn-info me-1"><i class="bi bi-pencil-square"></i></a>
                        <form action="{{ route('admin.honoraria.destroy', $honor) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus honorarium?')"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">Tidak ada data honorarium.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-3">
        {{ $honoraria->links('pagination::bootstrap-5') }}
    </div>

    <!-- Bulk delete script -->
    <script>
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.select-user');
        const bulkDeleteBtn = document.getElementById('bulk-delete');

        function toggleBulkBtn(){ bulkDeleteBtn.disabled = !Array.from(checkboxes).some(cb=>cb.checked); }
        selectAll.addEventListener('change',()=>{ checkboxes.forEach(cb=>cb.checked=selectAll.checked); toggleBulkBtn(); });
        checkboxes.forEach(cb=>cb.addEventListener('change',toggleBulkBtn));

        bulkDeleteBtn.addEventListener('click',function(){
            if(confirm('Yakin hapus semua honorarium yang dipilih?')){
                const selectedIds = Array.from(checkboxes).filter(cb=>cb.checked).map(cb=>cb.value);
                const form = document.createElement('form');
                form.method='POST';
                form.action='{{ route("admin.honoraria.bulkDelete") }}';
                form.innerHTML='@csrf';
                selectedIds.forEach(id=>{
                    const input = document.createElement('input');
                    input.type='hidden'; input.name='ids[]'; input.value=id;
                    form.appendChild(input);
                });
                document.body.appendChild(form);
                form.submit();
            }
        });
    </script>
</x-admin-layout>
