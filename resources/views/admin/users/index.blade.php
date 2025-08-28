<x-admin-layout>
    <h1>User</h1>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary mb-3">
        <i class="bi bi-plus-lg"></i> Tambah User
    </a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-striped table-hover table-bordered align-middle shadow-lg">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>NIP</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Jabatan</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->nip }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td><span class="badge bg-secondary">{{ $user->jabatan }}</span></td>
                <td><span class="badge bg-primary">{{ $user->role }}</span></td>
                <td>
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-info">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus user ini?')">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center">Tidak ada data user.</td></tr>
            @endforelse
        </tbody>
    </table>
</x-admin-layout>
