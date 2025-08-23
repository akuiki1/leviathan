<x-admin-layout>
    <h1>Tambah Tim</h1>
    <a href="{{ route('admin.tims.index') }}" class="btn btn-secondary mb-3">Kembali</a>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

<form action="{{ route('admin.tims.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label>Nama Tim</label>
        <input type="text" name="nama_tim" class="form-control" required>
    </div>

    <div class="form-group">
        <label>Keterangan</label>
        <textarea name="keterangan" class="form-control" required></textarea>
    </div>

    <div class="form-group">
        <label>SK File</label>
        <input type="text" name="sk_file" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
</form>

</x-admin-layout>