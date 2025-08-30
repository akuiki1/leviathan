<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function eselon()
    {
        return $this->belongsTo(Eselon::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
