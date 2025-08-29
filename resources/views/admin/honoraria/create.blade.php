<x-admin-layout>
    <h1>Tambah Honorarium</h1>

    <form action="{{ route('admin.honoraria.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>User</label>
            <select name="user_id" class="form-control" required>
                <option value="">-- Pilih User --</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Tim</label>
            <select name="tim_id" class="form-control" required>
                <option value="">-- Pilih Tim --</option>
                @foreach($tims as $tim)
                    <option value="{{ $tim->id }}">{{ $tim->nama_tim }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('admin.honoraria.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</x-admin-layout>
