<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MahasiswaProfile extends Model
{
    /** @use HasFactory<\Database\Factories\MahasiswaProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nim',
        'department',
        'study_program',
        'gpa',
        'phone',
        'university',
        'location',
        'bio',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
