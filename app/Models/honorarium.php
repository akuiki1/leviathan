<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Honorarium extends Model
{
    use HasFactory;

    protected $fillable = ['tim_id', 'user_id'];

    public function tim()
    {
        return $this->belongsTo(Tim::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
