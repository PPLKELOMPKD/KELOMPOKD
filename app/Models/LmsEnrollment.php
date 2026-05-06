<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsEnrollment extends Model
{
    protected $fillable = [
        'course_id',
        'student_id',
        'status',
        'is_graduated',
        'enrolled_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return ['enrolled_at' => 'datetime', 'completed_at' => 'datetime'];
    }
    public function course(): BelongsTo
    {
        return $this->belongsTo(LmsCourse::class, 'course_id');
    }
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
    public function lessonCompletions(): HasMany
    {
        return $this->hasMany(LmsLessonCompletion::class, 'enrollment_id');
    }
    public function quizAttempts(): HasMany
    {
        return $this->hasMany(LmsQuizAttempt::class, 'enrollment_id');
    }
    public function chapterCompletions(): HasMany
    {
        return $this->hasMany(LmsChapterCompletion::class, 'enrollment_id');
    }
    public function assignmentSubmissions(): HasMany
    {
        return $this->hasMany(LmsAssignmentSubmission::class, 'enrollment_id');
    }
}
