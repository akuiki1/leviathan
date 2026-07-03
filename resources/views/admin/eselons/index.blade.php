<x-admin-layout>
    <div class="container my-4">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Master Eselon</h5>
                <a href="{{ route('admin.eselons.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Tambah Eselon
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Eselon</th>
                                <th>Maks Honor / Tahun</th>
                                <th>Jumlah Jabatan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($eselons as $eselon)
                                <tr>
                                    <td>{{ $eselon->name }}</td>
                                    <td><span class="badge bg-info">{{ $eselon->maks_honor }} tim</span></td>
                                    <td>{{ $eselon->jabatans_count }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.eselons.edit', $eselon) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.eselons.destroy', $eselon) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('Hapus eselon ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada eselon.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
