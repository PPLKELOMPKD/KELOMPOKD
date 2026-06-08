<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LmsAssignment extends Model
{
    protected $fillable = ['chapter_id', 'title', 'description', 'file_url', 'deadline_at', 'allowed_formats', 'position', 'status'];

    protected $casts = [
        'deadline_at' => 'datetime',
    ];

    public function chapter()
    {
        return $this->belongsTo(LmsChapter::class);
    }

    public function submissions()
    {
        return $this->hasMany(LmsAssignmentSubmission::class, 'assignment_id');
    }
}
