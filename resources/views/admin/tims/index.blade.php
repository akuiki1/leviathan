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
                                                <button type="button" class="btn btn-success btn-sm" title="Approve" onclick="approveWithConfirmation('{{ $tim->id }}')">
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

        // Approve function with member list but simplified confirmation
        async function approveWithConfirmation(timId) {
            // Show loading first
            Swal.fire({
                title: 'Mengecek Status Anggota...', 
                html: '<div class="spinner-border text-primary"></div>', 
                allowOutsideClick: false, 
                showConfirmButton: false
            });
            
            try {
                // Try to get member info
                const res = await fetch(`/admin/tims/${timId}/check-members`);
                
                if(res.ok) {
                    // If member check works, show member info
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
                            <small class="text-${cls}">
                                ${m.status === 'over_limit' ? '⚠️ Melebihi batas honor' : 
                                  m.status === 'warning' ? '⚠️ Mendekati batas honor' : 
                                  '✅ Normal'}
                            </small>
                        </div>`;
                    }).join('');

                    // Add warning message if there are issues
                    let warningMessage = '';
                    if (has_over_limit) {
                        warningMessage = '<div class="alert alert-warning mt-3"><strong>Catatan:</strong> Tim akan di-approve, staff yang melebihi batas akan mendapat peringatan di sistem staff.</div>';
                    } else if (has_warning) {
                        warningMessage = '<div class="alert alert-info mt-3"><strong>Catatan:</strong> Beberapa anggota mendekati batas honor.</div>';
                    }

                    const result = await Swal.fire({
                        title: 'Approve Tim?',
                        html: membersHtml + warningMessage,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Approve Tim!',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#28a745',
                        width: '600px'
                    });

                    if(result.isConfirmed) {
                        submitApproval(timId);
                    }
                    
                } else {
                    // If member check fails, show simple confirmation
                    const result = await Swal.fire({
                        title: 'Approve Tim?',
                        text: 'Tidak dapat mengecek status anggota. Tim akan tetap di-approve.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Approve Tim!',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#28a745'
                    });

                    if(result.isConfirmed) {
                        submitApproval(timId);
                    }
                }
                
            } catch(error) {
                console.error('Error checking members:', error);
                
                // If there's an error, show simple confirmation
                const result = await Swal.fire({
                    title: 'Approve Tim?',
                    text: 'Tidak dapat mengecek status anggota. Tim akan tetap di-approve.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Approve Tim!',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#28a745'
                });

                if(result.isConfirmed) {
                    submitApproval(timId);
                }
            }
        }

        // Separate function to handle the actual approval submission
        function submitApproval(timId) {
            // Show loading
            Swal.fire({
                title: 'Memproses...',
                html: '<div class="spinner-border text-success"></div>',
                allowOutsideClick: false,
                showConfirmButton: false
            });
            
            console.log('=== DEBUG INFO ===');
            console.log('Tim ID:', timId);
            console.log('Current URL:', window.location.href);
            
            // Always use fetch for better error handling
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                             document.querySelector('input[name="_token"]')?.value;
            
            console.log('CSRF Token:', csrfToken);
            
            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('_method', 'PATCH');
            
            const approveUrl = `/admin/tims/${timId}/approve`;
            console.log('Approve URL:', approveUrl);
            
            fetch(approveUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                if(response.ok) {
                    return response.json().catch(() => {
                        // If not JSON, assume success
                        return { success: true };
                    });
                } else {
                    return response.text().then(text => {
                        console.error('Error response:', text);
                        throw new Error(`HTTP ${response.status}: ${text}`);
                    });
                }
            })
            .then(data => {
                console.log('Success response:', data);
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Tim telah di-approve',
                    icon: 'success'
                }).then(() => {
                    console.log('Reloading page...');
                    window.location.reload();
                });
            })
            .catch(error => {
                console.error('Error details:', error);
                Swal.fire({
                    title: 'Error!',
                    text: `Terjadi kesalahan: ${error.message}`,
                    icon: 'error'
                });
            });
        }
    </script>
</x-admin-layout>