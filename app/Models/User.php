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
        'jabatan',
        'role',
        'password',
    ];

    public function timsCreated()
    {
        return $this->hasMany(Tim::class, 'created_by');
    }

    public function tims()
    {
        return $this->belongsToMany(Tim::class, 'tim_user')->withPivot('jabatan')->withTimestamps();
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }
}
