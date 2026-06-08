<?php

namespace App\Http\Controllers;

use App\Models\LmsCourse;
use App\Models\LmsAssignment;
use App\Models\LmsAssignmentSubmission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyLmsGradingController extends Controller
{
    public function index(Request $request, LmsCourse $course, LmsAssignment $assignment)
    {
        abort_if($course->company_id !== $request->user()->id, 403);
        abort_if($assignment->chapter->course_id !== $course->id, 404);

        $submissions = $assignment->submissions()->with('enrollment.student.mahasiswaProfile')->get()->map(function ($submission) {
            return [
                'id' => $submission->id,
                'student_name' => $submission->enrollment->student->name,
                'student_email' => $submission->enrollment->student->email,
                'file_url' => $submission->file_url,
                'score' => $submission->score,
                'feedback' => $submission->feedback,
                'submitted_at' => $submission->submitted_at?->format('d M Y H:i:s'),
            ];
        });

        return Inertia::render('Perusahaan/Lms/Grading', [
            'course' => $course,
            'assignment' => $assignment,
            'submissions' => $submissions,
        ]);
    }

    public function update(Request $request, LmsCourse $course, LmsAssignment $assignment, LmsAssignmentSubmission $submission)
    {
        abort_if($course->company_id !== $request->user()->id, 403);
        abort_if($submission->assignment_id !== $assignment->id, 404);

        $validated = $request->validate([
            'score' => 'required|integer|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        $submission->update($validated);

        return back()->with('success', 'Grade and feedback saved successfully.');
    }
}
