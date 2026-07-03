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
}
