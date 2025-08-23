<x-staff-layout>
<div class="container my-5">
    <div class="card shadow border-0 rounded-3">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Buat Tim Baru</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('tims.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <!-- Nama Tim -->
                <div class="mb-3">
                    <label class="form-label">Nama Tim</label>
                    <input type="text" name="nama_tim" class="form-control" required>
                </div>

                <!-- Keterangan -->
                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="3"></textarea>
                </div>

                <!-- Upload SK -->
                <div class="mb-3">
                    <label class="form-label">Upload SK</label>
                    <input type="file" name="sk_file" class="form-control" accept=".pdf,.jpg,.png" required>
                </div>

                <!-- Anggota Tim -->
                <div id="anggota-wrapper">
                    <label class="form-label">Anggota Tim</label>
                    <div class="row mb-2 anggota-item">
                        <div class="col-md-6">
                            <select name="anggota[0][user_id]" class="form-select" required>
                                <option value="">-- Pilih Staff --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->jabatan }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="anggota[0][jabatan]" class="form-control" placeholder="Jabatan di tim" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger remove-anggota">Hapus</button>
                        </div>
                    </div>
                </div>
                <button type="button" id="add-anggota" class="btn btn-sm btn-secondary mt-2">+ Tambah Anggota</button>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Simpan & Kirim</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let index = 1;
document.getElementById('add-anggota').addEventListener('click', function(){
    let wrapper = document.getElementById('anggota-wrapper');
    let div = document.createElement('div');
    div.classList.add('row','mb-2','anggota-item');
    div.innerHTML = `
        <div class="col-md-6">
            <select name="anggota[${index}][user_id]" class="form-select" required>
                <option value="">-- Pilih Staff --</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->jabatan }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <input type="text" name="anggota[${index}][jabatan]" class="form-control" placeholder="Jabatan di tim" required>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger remove-anggota">Hapus</button>
        </div>
    `;
    wrapper.appendChild(div);
    index++;
});

// hapus anggota
document.addEventListener('click', function(e){
    if(e.target.classList.contains('remove-anggota')){
        e.target.closest('.anggota-item').remove();
    }
});
</script>
</x-staff-layout>
