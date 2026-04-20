<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Internship extends Model
{
    /** @use HasFactory<\Database\Factories\InternshipFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'company_name',
        'location',
        'requirements',
        'deadline_at',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'deadline_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
