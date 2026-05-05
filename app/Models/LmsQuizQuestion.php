<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsQuizQuestion extends Model
{
    protected $fillable = ['quiz_id', 'question', 'position'];

    public function quiz(): BelongsTo { return $this->belongsTo(LmsQuiz::class, 'quiz_id'); }
    public function options(): HasMany { return $this->hasMany(LmsQuizOption::class, 'question_id')->orderBy('position'); }
}
