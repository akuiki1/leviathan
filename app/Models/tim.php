<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tim extends Model
{
    use HasFactory;

    protected $fillable = ['nama_tim', 'keterangan', 'sk_file', 'created_by', 'status'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'tim_user')->withPivot('jabatan')->withTimestamps();
    }

    public function honoraria()
    {
        return $this->hasMany(Honorarium::class);
    }
}
