<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LmsChapter extends Model
{
    protected $fillable = ['course_id', 'title', 'description', 'position'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(LmsCourse::class, 'course_id');
    }
    public function lessons(): HasMany
    {
        return $this->hasMany(LmsLesson::class, 'chapter_id')->orderBy('position');
    }
    public function quiz(): HasOne
    {
        return $this->hasOne(LmsQuiz::class, 'chapter_id');
    }
    public function assignments(): HasMany
    {
        return $this->hasMany(LmsAssignment::class, 'chapter_id')->orderBy('position');
    }
}
