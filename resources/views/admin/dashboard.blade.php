<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <h4 class="mb-4">Dashboard</h4>
        </div>
    </div>

    <div class="row">
        <!-- Users Card -->
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-primary">
                <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-semibold">{{ $userCount }}</div>
                        <div>Users</div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-transparent text-white p-0" type="button">
                            <i class="bi bi-people fs-2"></i>
                        </button>
                    </div>
                </div>
                <div class="card-footer px-3 py-2 bg-primary border-top border-white border-opacity-25">
                    <a class="text-white text-decoration-none" href="{{ route('admin.users.index') }}">
                        <small class="text-white-50">View Details</small>
                        <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Tim Card -->
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-info">
                <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-semibold">{{ $timCount }}</div>
                        <div>Tim</div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-transparent text-white p-0" type="button">
                            <i class="bi bi-people-fill fs-2"></i>
                        </button>
                    </div>
                </div>
                <div class="card-footer px-3 py-2 bg-info border-top border-white border-opacity-25">
                    <a class="text-white text-decoration-none" href="{{ route('admin.tims.index') }}">
                        <small class="text-white-50">View Details</small>
                        <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Approved Tim Card -->
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-success">
                <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-semibold">{{ $approvedTimCount }}</div>
                        <div>Tim Disetujui</div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-transparent text-white p-0" type="button">
                            <i class="bi bi-check-circle fs-2"></i>
                        </button>
                    </div>
                </div>
                <div class="card-footer px-3 py-2 bg-success border-top border-white border-opacity-25">
                    <a class="text-white text-decoration-none" href="#">
                        <small class="text-white-50">View Details</small>
                        <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Pending Tim Card -->
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-danger">
                <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-semibold">{{ $rejectedTimCount }}</div>
                        <div>Tim DiTolak</div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-transparent text-white p-0" type="button">
                            <i class="bi bi-clock fs-2"></i>
                        </button>
                    </div>
                </div>
                <div class="card-footer px-3 py-2 bg-danger border-top border-white border-opacity-25">
                    <a class="text-white text-decoration-none" href="#">
                        <small class="text-white-50">View Details</small>
                        <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-list-ul me-2"></i>Tim Pending
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nama Tim</th>
                                    <th>Pembuat</th>
                                    <th>Tanggal Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPendingTims as $tim)
                                <tr>
                                    <td>{{ $tim->nama_tim }}</td>
                                    <td>{{ $tim->creator->name ?? '-' }}</td>
                                    <td>{{ $tim->created_at->format('d-m-Y') }}</td>
                                    <td>
                                        <!-- Updated Approve Button dengan Notification -->
                                        <button type="button"
                                            class="btn btn-success btn-sm"
                                            onclick="checkMembersBeforeApprove('{{ $tim->id }}')">
                                            <i class="bi bi-check-circle me-1"></i>Terima
                                        </button>

                                        <!-- Hidden form untuk actual approval -->
                                        <form id="approve-form-{{ $tim->id }}"
                                            action="{{ route('admin.tims.approve', $tim) }}"
                                            method="POST" style="display: none;">
                                            @csrf
                                            @method('PATCH')
                                        </form>

                                        <!-- Reject Button dengan Confirmation -->
                                        <button type="button"
                                            class="btn btn-danger btn-sm ms-2"
                                            onclick="confirmReject('{{ $tim->id }}')">
                                            <i class="bi bi-x-circle me-1"></i>Tolak
                                        </button>

                                        <!-- Hidden form untuk reject -->
                                        <form id="reject-form-{{ $tim->id }}"
                                            action="{{ route('admin.tims.reject', $tim) }}"
                                            method="POST" style="display: none;">
                                            @csrf
                                            @method('PATCH')
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Tidak Ada Tim Yang Sedang Pending.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- JavaScript Functions -->
<script>
// Fungsi untuk check member sebelum approve
async function checkMembersBeforeApprove(timId) {
    console.log('Dashboard - Function called with timId:', timId);
    
    // Show loading
    Swal.fire({
        title: 'Mengecek Status Anggota...',
        html: '<div class="spinner-border text-primary" role="status"></div>',
        allowOutsideClick: false,
        showConfirmButton: false
    });

    try {
        const response = await fetch(`/admin/tims/${timId}/check-members`);
        console.log('Dashboard - Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Dashboard - Data received:', data);
        
        // Build member status HTML
        let membersHtml = data.members.map(member => {
            const statusClass = member.status === 'over_limit' ? 'danger' : 
                               (member.status === 'warning' ? 'warning' : 'success');
            const statusText = member.status === 'over_limit' ? 'Melebihi Batas' : 
                              (member.status === 'warning' ? 'Hampir Batas' : 'Aman');
            const statusIcon = member.status === 'over_limit' ? '🔴' : 
                              (member.status === 'warning' ? '🟡' : '🟢');
            
            const progressPercent = member.max_honor > 0 ? (member.current_count / member.max_honor) * 100 : 0;
            
            return `
                <div class="border border-${statusClass} p-3 mb-2 rounded">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold">${member.name}</h6>
                            <small class="text-muted">${member.nip} - ${member.jabatan}</small>
                            <div class="progress mt-2" style="height: 6px;">
                                <div class="progress-bar bg-${statusClass}" 
                                     role="progressbar" 
                                     style="width: ${Math.min(progressPercent, 100)}%">
                                </div>
                            </div>
                        </div>
                        <div class="text-end ms-3">
                            <span class="badge bg-${statusClass} mb-2">
                                ${statusIcon} ${statusText}
                            </span>
                            <br><small class="fw-bold">${member.current_count}/${member.max_honor} Tim</small>
                            ${member.remaining > 0 ? `<br><small class="text-muted">Sisa: ${member.remaining}</small>` : ''}
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        // Tentukan alert
        let alertType, alertTitle, confirmButtonText, alertMessage, btnClass;
        
        if (data.has_over_limit) {
            alertType = 'error';
            alertTitle = '❌ Anggota Melebihi Batas';
            confirmButtonText = 'Tetap Approve';
            alertMessage = 'Beberapa anggota sudah melebihi batas maksimal. Anda tetap bisa melanjutkan approval, tapi mohon perhatikan risikonya.';
            btnClass = 'btn btn-danger';
        } else if (data.has_warning) {
            alertType = 'warning';
            alertTitle = '⚠️ Peringatan Status Anggota';
            confirmButtonText = 'Lanjutkan Approve';
            alertMessage = 'Beberapa anggota hampir mencapai batas maksimal. Yakin ingin melanjutkan approval?';
            btnClass = 'btn btn-warning';
        } else {
            alertType = 'question';
            alertTitle = '✅ Semua Anggota Aman';
            confirmButtonText = 'Approve Tim';
            alertMessage = 'Semua anggota masih dalam batas aman untuk mengambil honor.';
            btnClass = 'btn btn-success';
        }
        
        // Show modal detail
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
            width: 700,
            customClass: {
                confirmButton: btnClass,
                cancelButton: 'btn btn-light'
            },
            buttonsStyling: false
        });
        
        // Proses approval (selalu bisa, walaupun ada over_limit)
        if (result.isConfirmed) {
            const finalConfirm = await Swal.fire({
                title: 'Konfirmasi Final',
                text: 'Yakin ingin approve tim ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Approve!',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-light'
                },
                buttonsStyling: false
            });
            
            if (finalConfirm.isConfirmed) {
                document.getElementById(`approve-form-${timId}`).submit();
            }
        }
        
    } catch (error) {
        console.error('Dashboard - Error:', error);
        Swal.fire({
            title: 'Error!',
            text: 'Gagal mengecek status anggota: ' + error.message,
            icon: 'error',
            confirmButtonText: 'OK',
            customClass: {
                confirmButton: 'btn btn-danger'
            },
            buttonsStyling: false
        });
    }
}

// Fungsi untuk konfirmasi reject
async function confirmReject(timId) {
    const result = await Swal.fire({
        title: '⚠️ Konfirmasi Penolakan',
        html: `
            <div class="text-start">
                <p class="mb-2">Yakin ingin <strong>menolak</strong> tim ini?</p>
                <div class="alert alert-warning">
                    <small><i class="bi bi-exclamation-triangle me-1"></i>
                    Tim yang ditolak tidak dapat dikembalikan ke status pending.</small>
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Tolak Tim!',
        cancelButtonText: 'Batal',
        customClass: {
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-light'
        },
        buttonsStyling: false
    });
    
    if (result.isConfirmed) {
        document.getElementById(`reject-form-${timId}`).submit();
    }
}

console.log('Dashboard JavaScript loaded successfully!');
</script>
</x-admin-layout>