<x-admin-layout>
    <div class="container mt-4">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Users</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0 fw-bold">Daftar User</h1>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Create
            </a>
        </div>
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

                        <select name="role" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                            <option value="">Filter Role</option>
                            <option value="Admin" {{ request('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                            <option value="Staff" {{ request('role') == 'Staff'  ? 'selected' : '' }}>Staff</option>
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
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->nip }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
    @if($user->jabatan)
        <span class="badge d-inline-flex align-items-center gap-1 px-3 py-2 rounded-pill text-white fw-semibold shadow-sm" style="background:#0dcaf0;">
            <i class="bi bi-briefcase-fill"></i> {{ ucfirst($user->jabatan->name) }}
        </span>
    @else
        <span class="badge d-inline-flex align-items-center gap-1 px-3 py-2 rounded-pill text-white fw-semibold shadow-sm" style="background:#6c757d;">
            <i class="bi bi-dash-circle-fill"></i> -
        </span>
    @endif
</td>

                            <td>
                                @if (strtolower($user->role) === 'admin')
                                <span class="badge d-inline-flex align-items-center gap-1 px-3 py-2 rounded-pill text-white shadow-sm" style="background:#0d6efd;">
                                    <i class="bi bi-shield-lock-fill"></i> {{ ucfirst($user->role) }}
                                </span>
                                @elseif (strtolower($user->role) === 'staff')
                                <span class="badge d-inline-flex align-items-center gap-1 px-3 py-2 rounded-pill text-white shadow-sm" style="background:#198754;">
                                    <i class="bi bi-person-badge-fill"></i> {{ ucfirst($user->role) }}
                                </span>
                                @else
                                <span class="badge d-inline-flex align-items-center gap-1 px-3 py-2 rounded-pill text-dark shadow-sm" style="background:#ffc107;">
                                    <i class="bi bi-question-circle-fill"></i> {{ ucfirst($user->role) }}
                                </span>
                                @endif
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

</x-admin-layout>