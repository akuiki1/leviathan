<x-admin-layout>
    <h1>Honorarium</h1>
    <a href="{{ route('admin.honoraria.create') }}" class="btn btn-primary mb-3">Tambah Honorarium</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama User</th>
                <th>Tim</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($honoraria as $honor)
            <tr>
                <td>{{ $honor->id }}</td>
                <td>{{ $honor->user->name }}</td>
                <td>{{ $honor->tim->nama_tim }}</td>
                <td>
                    <a href="{{ route('admin.honoraria.edit', $honor) }}" class="btn btn-sm btn-info">Edit</a>
                    <form action="{{ route('admin.honoraria.destroy', $honor) }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">Tidak ada data honorarium.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</x-admin-layout>
