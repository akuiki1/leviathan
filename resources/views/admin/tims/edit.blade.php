<x-admin-layout>
    <h1>Edit Tim</h1>
    <a href="{{ route('admin.tims.index') }}" class="btn btn-secondary mb-3">Kembali</a>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('admin.tims.update', $tim) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label>Nama Tim</label>
            <input type="text" name="nama_tim" class="form-control" value="{{ old('nama_tim', $tim->nama_tim) }}" required>
        </div>
        <div class="form-group">
            <label>Keterangan</label>
            <textarea name="keterangan" class="form-control">{{ old('keterangan', $tim->keterangan) }}</textarea>
        </div>
        <div class="form-group">
            <label>SK File</label>
            <input type="text" name="sk_file" class="form-control" value="{{ old('sk_file', $tim->sk_file) }}" required>
        </div>
        <div class="form-group">
            <label>Pembuat</label>
            <select name="created_by" class="form-control" required>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('created_by', $tim->created_by) == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Status</label>
            <input type="text" name="status" class="form-control" value="{{ old('status', $tim->status) }}" required>
        </div>
        <div class="form-group">
            <label>Anggota Tim</label>
            <select name="anggota[]" class="form-control" multiple required>
                @foreach($users as $user)
                    <option value="{{ $user->id }}"
                        {{ (collect(old('anggota', $tim->anggota->pluck('id')))->contains($user->id)) ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
            <small class="form-text text-muted">Pilih lebih dari satu anggota dengan Ctrl/Shift.</small>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</x-admin-layout>
