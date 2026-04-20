<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelamar extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lowongan_kerja_id',
    ];

    public function lowonganKerja()
    {
        return $this->belongsTo(LowonganKerja::class);
    }
}
