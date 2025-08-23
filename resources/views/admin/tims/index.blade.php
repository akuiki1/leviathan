<x-admin-layout>
    <h1>Tim</h1>
    <a href="{{ route('admin.tims.create') }}" class="btn btn-primary mb-3">Tambah Tim</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Tim</th>
                <th>Keterangan</th>
                <th>SK File</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tims as $tim)
            <tr>
                <td>{{ $tim->id }}</td>
                <td>{{ $tim->nama_tim }}</td>
                <td>{{ $tim->keterangan }}</td>
                <td>{{ $tim->sk_file }}</td>
                <td>
                    <a href="{{ route('admin.tims.edit', $tim) }}" class="btn btn-sm btn-info">Edit</a>
                    <form action="{{ route('admin.tims.destroy', $tim) }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center">Tidak ada data tim.</td></tr>
            @endforelse
        </tbody>
    </table>
</x-admin-layout>
