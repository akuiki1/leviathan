<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tim extends Model
{
    protected $fillable = ['nama_tim', 'keterangan', 'sk_file', 'created_by'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'tim_user')
            ->withPivot('jabatan')
            ->withTimestamps();
    }
}