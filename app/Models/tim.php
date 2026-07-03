<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tim extends Model
{
    use HasFactory;

    protected $fillable = ['nama_tim', 'keterangan', 'sk_file', 'tahun', 'created_by', 'status'];

    protected $casts = [
        'tahun' => 'integer',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'tim_user')
            ->withPivot('jabatan', 'nominal_honor')
            ->withTimestamps();
    }

    // Alias historis; sama dengan users()
    public function anggota()
    {
        return $this->users();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
