<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Internship extends Model
{
    /** @use HasFactory<\Database\Factories\InternshipFactory> */
    use HasFactory;

    protected $fillable = [
        'company_id',
        'title',
        'description',
        'company_name',
        'company_logo',
        'location',
        'work_type',
        'duration',
        'requirements',
        'benefits',
        'salary',
        'deadline_at',
        'quota',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'deadline_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
