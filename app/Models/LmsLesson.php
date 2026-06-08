<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsLesson extends Model
{
    protected $fillable = ['chapter_id', 'title', 'type', 'content', 'video_url', 'video_image_url', 'position', 'status'];

    public function chapter(): BelongsTo { return $this->belongsTo(LmsChapter::class, 'chapter_id'); }
}
