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
            <input type="text" name="name" class="form-control" value="{{ $tim->name }}" required>
        </div>
        <div class="form-group">
            <label>Leader</label>
            <input type="text" name="leader" class="form-control" value="{{ $tim->leader }}">
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</x-admin-layout>
