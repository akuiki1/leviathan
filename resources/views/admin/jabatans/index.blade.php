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
                <h5 class="mb-0">Master Jabatan</h5>
                <a href="{{ route('admin.jabatans.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Tambah Jabatan
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Jabatan</th>
                                <th>Eselon</th>
                                <th>Maks Honor</th>
                                <th>Jumlah ASN</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($jabatans as $jabatan)
                                <tr>
                                    <td>{{ $jabatan->name }}</td>
                                    <td>{{ $jabatan->eselon->name ?? '-' }}</td>
                                    <td><span class="badge bg-info">{{ $jabatan->eselon->maks_honor ?? 0 }} tim</span></td>
                                    <td>{{ $jabatan->users_count }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.jabatans.edit', $jabatan) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.jabatans.destroy', $jabatan) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('Hapus jabatan ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada jabatan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
