<x-admin-layout>
    <h1>Daftar Tim</h1>
    <a href="{{ route('admin.tims.create') }}" class="btn btn-primary mb-3">Tambah Tim</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nama Tim</th>
                <th>Keterangan</th>
                <th>SK File</th>
                <th>Pembuat</th>
                <th>Status</th>
                <th>Anggota Tim</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tims as $tim)
                <tr>
                    <td>{{ $tim->nama_tim }}</td>
                    <td>{{ $tim->keterangan }}</td>
                    <td>{{ $tim->sk_file }}</td>
                    <td>
                        @php
                            $creator = \App\Models\User::find($tim->created_by);
                        @endphp
                        {{ $creator ? $creator->name : '-' }}
                    </td>
                    <td>{{ $tim->status }}</td>
                    <td>
                        @foreach($tim->anggota as $anggota)
                            <span class="badge bg-info text-dark">{{ $anggota->name }}</span>
                        @endforeach
                    </td>
                    <td>
                        <a href="{{ route('admin.tims.edit', $tim) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('admin.tims.destroy', $tim) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus tim?')">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-admin-layout>
