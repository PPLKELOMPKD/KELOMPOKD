<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LmsAssignmentSubmission extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function enrollment()
    {
        return $this->belongsTo(LmsEnrollment::class);
    }

    public function assignment()
    {
        return $this->belongsTo(LmsAssignment::class);
    }
}
