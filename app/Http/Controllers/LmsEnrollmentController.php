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
            'status' => 'accepted',
        ]);

        \App\Services\ActivityLogger::log('Mendaftar Kursus', "Mendaftar pada kursus LMS: {$course->title}", 'enrollment');

        return redirect()->route('lms.module.show', $course);
    }

    public function destroy(Request $request, LmsCourse $course)
    {
        abort_if($request->user()->role !== 'mahasiswa', 403);

        $enrollment = LmsEnrollment::where('course_id', $course->id)
            ->where('student_id', $request->user()->id)
            ->firstOrFail();

        $enrollment->delete();

        \App\Services\ActivityLogger::log('Batal Kursus', "Membatalkan pendaftaran pada kursus LMS: {$course->title}", 'enrollment');

        return redirect()->route('lms')->with('success', 'Berhasil membatalkan pendaftaran pelatihan.');
    }
}
