<x-admin-layout>
    <div class="container my-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3"><h5 class="mb-0">Tambah Jabatan</h5></div>
            <div class="card-body">
                <form action="{{ route('admin.jabatans.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama Jabatan <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Eselon <span class="text-danger">*</span></label>
                        <select name="eselon_id" class="form-select @error('eselon_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Eselon --</option>
                            @foreach ($eselons as $eselon)
                                <option value="{{ $eselon->id }}" {{ old('eselon_id') == $eselon->id ? 'selected' : '' }}>
                                    {{ $eselon->name }} (maks {{ $eselon->maks_honor }} tim)
                                </option>
                            @endforeach
                        </select>
                        @error('eselon_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.jabatans.index') }}" class="btn btn-secondary">Batal</a>
                        <button class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
