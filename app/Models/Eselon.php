<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eselon extends Model
{
    use HasFactory;

    // maks_honor = jumlah maksimal tim yang DIBAYAR per tahun anggaran untuk ASN di eselon ini
    protected $fillable = ['name', 'maks_honor'];

    protected $casts = [
        'maks_honor' => 'integer',
    ];

    public function jabatans()
    {
        return $this->hasMany(Jabatan::class);
    }
}
