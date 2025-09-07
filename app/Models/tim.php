<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tim extends Model
{
    use HasFactory;
    
    protected $fillable = ['nama_tim', 'keterangan', 'sk_file', 'created_by', 'status'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'tim_user')
            ->withPivot('jabatan')
            ->withTimestamps();
    }
    public function anggota()
    {
        return $this->belongsToMany(\App\Models\User::class, 'tim_user');
    }
}