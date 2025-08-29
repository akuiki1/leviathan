<x-admin-layout>
    <h1>Edit Honorarium</h1>

    <form action="{{ route('admin.honoraria.update', $honorarium) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label>User</label>
            <select name="user_id" class="form-control" required>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ $honorarium->user_id == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Tim</label>
            <select name="tim_id" class="form-control" required>
                @foreach($tims as $tim)
                    <option value="{{ $tim->id }}" {{ $honorarium->tim_id == $tim->id ? 'selected' : '' }}>
                        {{ $tim->nama_tim }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('admin.honoraria.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</x-admin-layout>
