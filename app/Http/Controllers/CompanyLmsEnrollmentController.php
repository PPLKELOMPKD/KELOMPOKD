<?php

namespace App\Http\Controllers;

use App\Models\LmsCourse;
use App\Models\LmsEnrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\LmsProgressService;

class CompanyLmsEnrollmentController extends Controller
{
    public function index(Request $request, LmsCourse $course)
    {
        abort_if($course->company_id !== $request->user()->id, 403);

        $enrollments = $course->enrollments()->with(['student.mahasiswaProfile', 'quizAttempts', 'assignmentSubmissions.assignment'])->get()->map(function ($enrollment) {
            return [
                'id' => $enrollment->id,
                'student_name' => $enrollment->student->name,
                'student_email' => $enrollment->student->email,
                'enrolled_at' => $enrollment->enrolled_at->format('d M Y'),
                'progress' => app(LmsProgressService::class)->courseProgress($enrollment),
                'final_grade' => app(LmsProgressService::class)->calculateFinalGrade($enrollment),
                'is_graduated' => $enrollment->is_graduated,
                'quiz_results' => $enrollment->quizAttempts->groupBy('quiz_id')->map(fn($attempts) => $attempts->max('score')),
                'submissions' => $enrollment->assignmentSubmissions->map(function ($sub) {
                    return [
                        'id' => $sub->id,
                        'assignment_id' => $sub->assignment_id,
                        'assignment_title' => $sub->assignment->title ?? 'Tugas Terhapus',
                        'score' => $sub->score,
                        'submitted_at' => $sub->submitted_at?->format('d M Y'),
                    ];
                }),
            ];
        });

        return Inertia::render('Perusahaan/Lms/Enrollments', [
            'course' => $course,
            'enrollments' => $enrollments,
        ]);
    }

    public function toggleGraduation(Request $request, LmsCourse $course, LmsEnrollment $enrollment)
    {
        abort_if($course->company_id !== $request->user()->id, 403);
        abort_if($enrollment->course_id !== $course->id, 404);

        $isGraduated = !$enrollment->is_graduated;
        $enrollment->update(['is_graduated' => $isGraduated]);

        if ($isGraduated) {
            \App\Services\ActivityLogger::log(
                'Meluluskan Mahasiswa',
                "Perusahaan {$request->user()->name} meluluskan mahasiswa '{$enrollment->student->name}' pada course '{$course->title}'",
                'enrollment'
            );
        } else {
            \App\Services\ActivityLogger::log(
                'Membatalkan Kelulusan',
                "Perusahaan {$request->user()->name} membatalkan kelulusan mahasiswa '{$enrollment->student->name}' pada course '{$course->title}'",
                'enrollment'
            );
        }

        return back()->with('success', $enrollment->is_graduated ? 'Participant has been marked as graduated.' : 'Participant graduation status has been revoked.');
    }


    public function resetProgress(Request $request, LmsCourse $course, LmsEnrollment $enrollment)
    {
        abort_if($course->company_id !== $request->user()->id, 403);
        abort_if($enrollment->course_id !== $course->id, 404);

        $enrollment->assignmentSubmissions()->delete();
        $enrollment->quizAttempts()->delete();
        $enrollment->lessonCompletions()->delete();
        $enrollment->chapterCompletions()->delete();
        $enrollment->update(['is_graduated' => false]);

        \App\Services\ActivityLogger::log(
            'Reset Enrollment',
            "Perusahaan {$request->user()->name} mereset progress belajar '{$enrollment->student->name}' pada course '{$course->title}'",
            'moderasi'
        );

        return back()->with('success', 'Participant progress has been reset. The participant can retake the training.');
    }

    public function destroy(Request $request, LmsCourse $course, LmsEnrollment $enrollment)
    {
        abort_if($course->company_id !== $request->user()->id, 403);
        abort_if($enrollment->course_id !== $course->id, 404);

        $enrollment->delete();

        return back()->with('success', 'Participant removed successfully.');
    }
}
