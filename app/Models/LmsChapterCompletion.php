<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsChapterCompletion extends Model
{
    protected $fillable = ['enrollment_id', 'chapter_id', 'completed_at'];

    protected function casts(): array { return ['completed_at' => 'datetime']; }
    public function enrollment(): BelongsTo { return $this->belongsTo(LmsEnrollment::class, 'enrollment_id'); }
    public function chapter(): BelongsTo { return $this->belongsTo(LmsChapter::class, 'chapter_id'); }
}
