<x-admin-layout>
    <div class="container mt-4">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tims</li>
            </ol>
        </nav>

        <div class="card shadow-lg rounded-2 overflow-hidden">
            <!-- Header -->
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="mb-0">
                    <i class="bi bi-people-fill me-2"></i> Daftar Tim
                </h5>

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
                                    @if($tim->status == 'pending')
                                        <span class="badge d-inline-flex align-items-center gap-1 px-3 py-2 rounded-pill text-white fw-semibold shadow-sm" style="background:#ffc107;">
                                            <i class="bi bi-hourglass-split"></i> {{ ucfirst($tim->status) }}
                                        </span>
                                    @elseif($tim->status == 'approved')
                                        <span class="badge d-inline-flex align-items-center gap-1 px-3 py-2 rounded-pill text-white fw-semibold shadow-sm" style="background:#198754;">
                                            <i class="bi bi-check-circle-fill"></i> {{ ucfirst($tim->status) }}
                                        </span>
                                    @else
                                        <span class="badge d-inline-flex align-items-center gap-1 px-3 py-2 rounded-pill text-white fw-semibold shadow-sm" style="background:#dc3545;">
                                            <i class="bi bi-x-circle-fill"></i> {{ ucfirst($tim->status) }}
                                        </span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <!-- View -->
                                        <a href="{{ route('admin.tims.show', $tim) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <!-- Edit -->
                                        <a href="{{ route('admin.tims.edit', $tim) }}" class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <!-- Delete -->
                                        <form action="{{ route('admin.tims.destroy', $tim) }}" method="POST" class="d-inline form-delete">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>

                                        <!-- Approve / Reject -->
                                        @if($tim->status === 'pending')
                                            <!-- Approve -->
                                            <button type="button"
                                                class="btn btn-success btn-sm"
                                                onclick="checkMembersBeforeApprove('{{ $tim->id }}')">
                                                <i class="bi bi-check-circle"></i>
                                            </button>

                                            <form id="approve-form-{{ $tim->id }}"
                                                  action="{{ route('admin.tims.approve', $tim) }}"
                                                  method="POST" style="display:none;">
                                                @csrf
                                                @method('PATCH')
                                            </form>

                                            <!-- Reject -->
                                            <form action="{{ route('admin.tims.reject', $tim) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
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

    <!-- JavaScript Function -->
    <script>
        async function checkMembersBeforeApprove(timId) {
            Swal.fire({
                title: 'Mengecek Status Anggota...',
                html: '<div class="spinner-border text-primary" role="status"></div>',
                allowOutsideClick: false,
                showConfirmButton: false
            });

            try {
                const response = await fetch(`/admin/tims/${timId}/check-members`);
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                const data = await response.json();

                // Build member status HTML
                let membersHtml = data.members.map(member => {
                    const statusClass = member.status === 'over_limit' ? 'danger' :
                        (member.status === 'warning' ? 'warning' : 'success');
                    const statusText = member.status === 'over_limit' ? 'Melebihi Batas' :
                        (member.status === 'warning' ? 'Hampir Batas' : 'Aman');
                    const statusIcon = member.status === 'over_limit' ? '🔴' :
                        (member.status === 'warning' ? '🟡' : '🟢');

                    const progressPercent = member.max_honor > 0 ?
                        Math.round((member.current_count / member.max_honor) * 100) :
                        0;

                    return `
                    <div class="border border-${statusClass} p-3 mb-2 rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold">${member.name}</h6>
                                <small class="text-muted">${member.nip} - ${member.jabatan}</small>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-${statusClass}" 
                                         role="progressbar" 
                                         style="width: ${progressPercent}%;">
                                    </div>
                                </div>
                            </div>
                            <div class="text-end ms-3">
                                <span class="badge bg-${statusClass} mb-2">
                                    ${statusIcon} ${statusText}
                                </span>
                                <br>
                                <small class="fw-bold">${member.current_count}/${member.max_honor} Tim</small>
                                ${member.remaining > 0 
                                    ? `<br><small class="text-muted">Sisa: ${member.remaining}</small>` 
                                    : ''
                                }
                            </div>
                        </div>
                    </div>
                `;
                }).join('');

                // Determine alert type
                let alertType, alertTitle, confirmButtonText, alertMessage;

                if (data.has_over_limit) {
                    alertType = 'error';
                    alertTitle = '❌ Tidak Dapat Approve Tim';
                    confirmButtonText = 'Tutup';
                    alertMessage = 'Tim tidak dapat disetujui karena beberapa anggota sudah melebihi batas maksimal.';
                } else if (data.has_warning) {
                    alertType = 'warning';
                    alertTitle = '⚠️ Peringatan Status Anggota';
                    confirmButtonText = 'Lanjutkan Approve';
                    alertMessage = 'Beberapa anggota hampir mencapai batas maksimal.';
                } else {
                    alertType = 'question';
                    alertTitle = '✅ Semua Anggota Aman';
                    confirmButtonText = 'Approve Tim';
                    alertMessage = 'Semua anggota masih dalam batas aman.';
                }

                // Show modal
                const result = await Swal.fire({
                    title: alertTitle,
                    html: `
                    <div class="text-start">
                        <p class="mb-3 text-muted">${alertMessage}</p>
                        <h6 class="mb-2">Detail Status Anggota:</h6>
                        ${membersHtml}
                    </div>
                `,
                    icon: alertType,
                    showCancelButton: true,
                    confirmButtonText: confirmButtonText,
                    cancelButtonText: 'Batal',
                    width: 700
                });

                // Process approval
                if (result.isConfirmed && !data.has_over_limit) {
                    const finalConfirm = await Swal.fire({
                        title: 'Konfirmasi Final',
                        text: 'Yakin ingin approve tim ini?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Approve!',
                        cancelButtonText: 'Batal'
                    });

                    if (finalConfirm.isConfirmed) {
                        document.getElementById(`approve-form-${timId}`).submit();
                    }
                }

            } catch (error) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Gagal mengecek status anggota: ' + error.message,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        }
    </script>
</x-admin-layout>