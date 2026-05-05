<?php

namespace App\Http\Controllers;

use App\Models\LmsCourse;
use App\Models\LmsEnrollment;
use Illuminate\Http\Request;

class LmsEnrollmentController extends Controller
{
    public function store(Request $request, LmsCourse $course)
    {
        abort_if($request->user()->role !== 'mahasiswa', 403);

        LmsEnrollment::query()->firstOrCreate([
            'course_id' => $course->id,
            'student_id' => $request->user()->id,
        ], [
            'enrolled_at' => now(),
        ]);

        return redirect()->route('lms.module.show', $course);
    }
}
