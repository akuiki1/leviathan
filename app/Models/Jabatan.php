<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'eselon_id'];

    public function eselon()
    {
        return $this->belongsTo(Eselon::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function histories()
    {
        return $this->hasMany(JabatanHistory::class);
    }
}
