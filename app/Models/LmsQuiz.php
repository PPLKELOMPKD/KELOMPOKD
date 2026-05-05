<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsQuiz extends Model
{
    protected $fillable = ['chapter_id', 'title', 'description', 'passing_score'];

    public function chapter(): BelongsTo { return $this->belongsTo(LmsChapter::class, 'chapter_id'); }
    public function questions(): HasMany { return $this->hasMany(LmsQuizQuestion::class, 'quiz_id')->orderBy('position'); }
}
