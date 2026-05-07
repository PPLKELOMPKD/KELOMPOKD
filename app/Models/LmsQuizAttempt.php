<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsQuizAttempt extends Model
{
    protected $fillable = ['enrollment_id', 'quiz_id', 'score', 'passed', 'answers', 'submitted_at'];

    protected function casts(): array {
        return [
            'passed' => 'boolean',
            'answers' => 'array',
            'submitted_at' => 'datetime',
        ];
    }
    public function enrollment(): BelongsTo { return $this->belongsTo(LmsEnrollment::class, 'enrollment_id'); }
    public function quiz(): BelongsTo { return $this->belongsTo(LmsQuiz::class, 'quiz_id'); }
}
