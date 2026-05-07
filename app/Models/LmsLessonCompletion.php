<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsLessonCompletion extends Model
{
    protected $fillable = ['enrollment_id', 'lesson_id', 'completed_at'];

    protected function casts(): array { return ['completed_at' => 'datetime']; }
    public function enrollment(): BelongsTo { return $this->belongsTo(LmsEnrollment::class, 'enrollment_id'); }
    public function lesson(): BelongsTo { return $this->belongsTo(LmsLesson::class, 'lesson_id'); }
}
