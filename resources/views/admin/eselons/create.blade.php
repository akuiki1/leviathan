<x-admin-layout>
    <div class="container my-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3"><h5 class="mb-0">Tambah Eselon</h5></div>
            <div class="card-body">
                <form action="{{ route('admin.eselons.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama Eselon <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Maks Honor per Tahun (jumlah tim yang dibayar) <span class="text-danger">*</span></label>
                        <input type="number" name="maks_honor" min="0" max="255"
                            class="form-control @error('maks_honor') is-invalid @enderror"
                            value="{{ old('maks_honor') }}" required>
                        @error('maks_honor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.eselons.index') }}" class="btn btn-secondary">Batal</a>
                        <button class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
