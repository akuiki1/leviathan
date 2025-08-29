<x-admin-layout>
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <a href="{{ route('admin.tims.create') }}" class="btn btn-primary d-flex align-items-center mb-2 mb-md-0">
            <i class="bi bi-plus-lg me-1"></i> Tambah Tim
        </a>

        <div class="d-flex align-items-center mb-2 mb-md-0 flex-wrap">
            <!-- Filter Status -->
            <form method="GET" class="d-flex mb-2 me-2">
                <select name="status" class="form-select me-2">
                    <option value="">Semua Status</option>
                    <option value="Active" {{ request('status')=='Active'?'selected':'' }}>Active</option>
                    <option value="Inactive" {{ request('status')=='Inactive'?'selected':'' }}>Inactive</option>
                </select>
                <button type="submit" class="btn btn-outline-primary me-2">Filter</button>
                <a href="{{ route('admin.tims.index') }}" class="btn btn-outline-secondary">Reset</a>
            </form>

            <!-- Search -->
            <form method="GET" action="{{ route('admin.tims.index') }}" class="d-flex mb-2 me-2">
                <input type="text" name="search" class="form-control me-2" placeholder="Cari tim..." value="{{ request('search') }}">
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
                    <th>Nama TIM</th>
                    <th>Keterangan</th>
                    <th>SK File</th>
                    <th>Pembuat</th>
                    <th>Status</th>
                    <th>Anggota Tim</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tims as $tim)
                <tr>
                    <td><input type="checkbox" class="select-user" value="{{ $tim->id }}"></td>
                    <td>{{ $tim->nama_tim }}</td>
                    <td>{{ $tim->keterangan }}</td>
                    <td>{{ $tim->sk_file }}</td>
                    <td>{{ $tim->creator->name ?? '-' }}</td>
                    <td>{{ $tim->status }}</td>
                    <td>
                        @foreach($tim->anggota as $anggota)
                            <span class="badge bg-info text-dark">{{ $anggota->name }}</span>
                        @endforeach
                    </td>
                    <td class="text-center">
                        <a href="{{ route('admin.tims.edit', $tim) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i></a>
                        <form action="{{ route('admin.tims.destroy', $tim) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus tim?')"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">Tidak ada data tim.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-3">
        {{ $tims->links('pagination::bootstrap-5') }}
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
            if(confirm('Yakin hapus semua tim yang dipilih?')){
                const selectedIds = Array.from(checkboxes).filter(cb=>cb.checked).map(cb=>cb.value);
                const form = document.createElement('form');
                form.method='POST';
                form.action='{{ route("admin.tims.bulkDelete") }}';
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
