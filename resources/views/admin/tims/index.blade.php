<x-admin-layout>
    <div class="container mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tims</li>
            </ol>
        </nav>

        <!-- Card -->
        <div class="card shadow-lg rounded-2 overflow-hidden">

            <!-- Header -->
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i> Daftar Tim</h5>
                <div class="d-flex flex-wrap gap-2 align-items-center">

                    <!-- Filter -->
                    <form method="GET" action="{{ route('admin.tims.index') }}" class="d-flex flex-wrap gap-2">
                        <select name="status" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                            <option value="">Filter Status</option>
                            <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status')=='approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status')=='rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </form>

                    <!-- Create -->
                    <a href="{{ route('admin.tims.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Create
                    </a>
                </div>
            </div>

            <!-- Table -->
            <div class="card-body">
                <div class="table-responsive">
                    @php
                        function timStatusBadge($status) {
                            switch($status) {
                                case 'pending': return '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Pending</span>';
                                case 'approved': return '<span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Approved</span>';
                                case 'rejected': return '<span class="badge bg-danger"><i class="bi bi-x-circle-fill"></i> Rejected</span>';
                                default: return '<span class="badge bg-secondary">-</span>';
                            }
                        }
                    @endphp

                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Nama Tim</th>
                                <th>Keterangan</th>
                                <th>File SK</th>
                                <th>Dibuat Oleh</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tims as $tim)
                                <tr>
                                    <td>{{ $tim->nama_tim }}</td>
                                    <td>{{ Str::limit($tim->keterangan,50) }}</td>
                                    <td>
                                        @if($tim->sk_file)
                                            <a href="{{ asset('storage/'.$tim->sk_file) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat</a>
                                        @else
                                            <span class="badge bg-secondary">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $tim->creator?->name ?? '-' }}</td>
                                    <td>{!! timStatusBadge($tim->status) !!}</td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('admin.tims.show', $tim) }}" class="btn btn-sm btn-outline-secondary" title="View"><i class="bi bi-eye"></i></a>
                                            <a href="{{ route('admin.tims.edit', $tim) }}" class="btn btn-sm btn-outline-info" title="Edit"><i class="bi bi-pencil-square"></i></a>

                                            <!-- Delete -->
                                            <form action="{{ route('admin.tims.destroy', $tim) }}" method="POST" class="d-inline form-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                            </form>

                                            <!-- Approve / Reject -->
                                            @if($tim->status === 'pending')
                                                <button type="button" class="btn btn-success btn-sm" title="Approve" onclick="checkMembersBeforeApprove('{{ $tim->id }}')">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>

                                                <form id="approve-form-{{ $tim->id }}" action="{{ route('admin.tims.approve', $tim) }}" method="POST" style="display:none;">
                                                    @csrf
                                                    @method('PATCH')
                                                </form>

                                                <form action="{{ route('admin.tims.reject', $tim) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Reject"><i class="bi bi-x-circle"></i></button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Tidak ada data tim.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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

    <!-- JS -->
    <script>
        // Delete confirm
        document.querySelectorAll('.form-delete').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Yakin ingin hapus tim ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then(result => { if(result.isConfirmed) form.submit(); });
            });
        });

        // Approve check
        async function checkMembersBeforeApprove(timId) {
            Swal.fire({title:'Mengecek Status Anggota...', html:'<div class="spinner-border"></div>', allowOutsideClick:false, showConfirmButton:false});
            try {
                const res = await fetch(`/admin/tims/${timId}/check-members`);
                if(!res.ok) throw new Error('Failed fetch');
                const { members, has_over_limit, has_warning } = await res.json();

                let membersHtml = members.map(m => {
                    const cls = m.status==='over_limit'?'danger':m.status==='warning'?'warning':'success';
                    const perc = m.max_honor>0?Math.round((m.current_count/m.max_honor)*100):0;
                    return `<div class="border border-${cls} p-3 mb-2 rounded">
                        <h6>${m.name}</h6>
                        <small>${m.nip} - ${m.jabatan}</small>
                        <div class="progress mt-2" style="height:6px;">
                            <div class="progress-bar bg-${cls}" style="width:${perc}%;"></div>
                        </div>
                    </div>`;
                }).join('');

                const alertType = has_over_limit?'error':has_warning?'warning':'question';
                const alertTitle = has_over_limit?'❌ Tidak Dapat Approve':has_warning?'⚠️ Peringatan':'✅ Semua Aman';
                const confirmText = has_over_limit?'Tutup':'Approve Tim';

                const result = await Swal.fire({
                    title: alertTitle,
                    html: membersHtml,
                    icon: alertType,
                    showCancelButton: !has_over_limit,
                    confirmButtonText: confirmText
                });

                if(result.isConfirmed && !has_over_limit) document.getElementById(`approve-form-${timId}`).submit();
            } catch(e) {
                Swal.fire({title:'Error!', text:e.message, icon:'error'});
            }
        }
    </script>
</x-admin-layout>