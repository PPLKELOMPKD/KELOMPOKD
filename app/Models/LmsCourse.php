<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsCourse extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'company_id',
        'slug',
        'title',
        'provider',
        'description',
        'level',
        'status',
        'started_at',
        'start_time',
        'ends_at',
        'image_url',
        'image_alt',
        'location',
        'quota',
        'passing_grade',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(LmsChapter::class, 'course_id')->orderBy('position');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(LmsEnrollment::class, 'course_id');
    }
}
