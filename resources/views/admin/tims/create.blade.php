<x-admin-layout>
    <h1>Buat Tim Baru</h1>
    <form action="{{ route('admin.tims.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Nama Tim</label>
            <input type="text" name="nama_tim" class="form-control" value="{{ old('nama_tim') }}" required>
        </div>
        <div class="form-group">
            <label>Keterangan</label>
            <textarea name="keterangan" class="form-control">{{ old('keterangan') }}</textarea>
        </div>
        <div class="form-group">
            <label>SK File</label>
            <input type="text" name="sk_file" class="form-control" value="{{ old('sk_file') }}" required>
        </div>
        <div class="form-group">
            <label>Pembuat</label>
            <select name="created_by" class="form-control" required>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('created_by') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Status</label>
            <input type="text" name="status" class="form-control" value="{{ old('status') }}" required>
        </div>
        <div class="form-group">
            <label>Anggota Tim</label>
            <select name="anggota[]" class="form-control" multiple required>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ (collect(old('anggota'))->contains($user->id)) ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
            <small class="form-text text-muted">Pilih lebih dari satu anggota dengan Ctrl/Shift.</small>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</x-admin-layout>