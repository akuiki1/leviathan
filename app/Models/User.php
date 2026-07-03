<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nip',
        'name',
        'email',
        'jabatan_id',
        'role',
        'status_akun',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function timsCreated()
    {
        return $this->hasMany(Tim::class, 'created_by');
    }

    /**
     * Inisial nama untuk avatar bubble, mis. "Budi Santoso" -> "BS".
     */
    public function getInitialsAttribute(): string
    {
        $parts = preg_split('/\s+/', trim($this->name ?? ''));
        $first = $parts[0][0] ?? '';
        $second = $parts[1][0] ?? '';

        return mb_strtoupper($first . $second);
    }

    public function tims()
    {
        return $this->belongsToMany(Tim::class, 'tim_user')
            ->withPivot('jabatan', 'nominal_honor')
            ->withTimestamps();
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function jabatanHistories()
    {
        return $this->hasMany(JabatanHistory::class);
    }

    /**
     * Jabatan yang efektif berlaku pada tanggal tertentu (dari jabatan_histories).
     * Fallback ke jabatan saat ini bila tak ada riwayat yang mencakup tanggal itu
     * (mis. data lama sebelum riwayat mulai dicatat).
     */
    public function jabatanPada(\DateTimeInterface $tanggal): ?Jabatan
    {
        $riwayat = $this->jabatanHistories()
            ->where('tanggal_mulai', '<=', $tanggal)
            ->where(function ($q) use ($tanggal) {
                $q->whereNull('tanggal_selesai')->orWhere('tanggal_selesai', '>=', $tanggal);
            })
            ->orderByDesc('tanggal_mulai')
            ->first();

        return $riwayat?->jabatan ?? $this->jabatan;
    }

    /**
     * Catat jabatan awal (dipakai saat ASN pertama kali dibuat). Idempotent:
     * tidak menggandakan bila riwayat awal sudah ada.
     */
    public function catatJabatanAwal(?\DateTimeInterface $tmt = null): void
    {
        if ($this->jabatanHistories()->exists()) {
            return;
        }

        $this->jabatanHistories()->create([
            'jabatan_id'    => $this->jabatan_id,
            'tanggal_mulai' => $tmt ?? now(),
        ]);
    }

    /**
     * Pindahkan ASN ke jabatan baru sekaligus catat riwayatnya. SATU-SATUNYA
     * jalur perubahan jabatan — dipakai form manual maupun import massal, agar
     * logika riwayat tidak tercecer di banyak tempat.
     *
     * @param  \DateTimeInterface|null  $tmt  Terhitung Mulai Tanggal jabatan baru berlaku.
     *                                        Penting untuk SK berlaku surut; default hari ini.
     * @return bool  true bila jabatan benar-benar berubah (riwayat dicatat).
     */
    public function pindahJabatan(int $jabatanId, ?\DateTimeInterface $tmt = null): bool
    {
        if ((int) $this->jabatan_id === $jabatanId) {
            return false;
        }

        $tmt = $tmt ?? now();

        // Tutup riwayat jabatan yang masih aktif per TMT jabatan baru.
        $this->jabatanHistories()
            ->whereNull('tanggal_selesai')
            ->update(['tanggal_selesai' => $tmt]);

        $this->jabatanHistories()->create([
            'jabatan_id'    => $jabatanId,
            'tanggal_mulai' => $tmt,
        ]);

        $this->update(['jabatan_id' => $jabatanId]);

        return true;
    }
}
