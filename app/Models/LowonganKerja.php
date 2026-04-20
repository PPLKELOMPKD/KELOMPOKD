<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LowonganKerja extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'posisi',
        'deskripsi',
        'kualifikasi',
        'lokasi',
        'kuota',
        'deadline',
        'status',
    ];

    /**
     * Get the pelamars for the lowongan.
     */
    public function pelamars()
    {
        return $this->hasMany(Pelamar::class);
    }
}
