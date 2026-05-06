<?php

namespace App\Http\Controllers;

use App\Models\LmsCourse;
use App\Models\LmsAssignment;
use App\Models\LmsAssignmentSubmission;
use App\Services\LmsProgressService;
use Illuminate\Http\Request;

class LmsAssignmentSubmissionController extends Controller
{
    public function store(Request $request, LmsCourse $course, LmsAssignment $assignment, LmsProgressService $progressService)
    {
        abort_if($request->user()->role !== 'mahasiswa', 403);

        $enrollment = $request->user()->lmsEnrollments()->where('course_id', $course->id)->firstOrFail();
        abort_if($assignment->chapter->course_id !== $course->id, 404);

        if ($assignment->deadline_at && now()->isAfter($assignment->deadline_at)) {
            return back()->withErrors(['message' => 'Tenggat waktu pengumpulan tugas telah berakhir.']);
        }

        $validated = $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $fileUrl = '/storage/' . $request->file('file')->store('lms/submissions', 'public');

        LmsAssignmentSubmission::updateOrCreate([
            'enrollment_id' => $enrollment->id,
            'assignment_id' => $assignment->id,
        ], [
            'file_url' => $fileUrl,
            'submitted_at' => now(),
        ]);

        $progressService->refreshChapterCompletion($enrollment, $assignment->chapter);

        return back()->with('success', 'Tugas berhasil diunggah.');
    }

    public function destroy(Request $request, LmsCourse $course, LmsAssignment $assignment, LmsAssignmentSubmission $submission, LmsProgressService $progressService)
    {
        abort_if($request->user()->role !== 'mahasiswa', 403);
        abort_if($submission->assignment_id !== $assignment->id, 404);
        abort_if($submission->enrollment->student_id !== $request->user()->id, 403);

        if ($assignment->deadline_at && now()->isAfter($assignment->deadline_at)) {
            return back()->withErrors(['message' => 'Tenggat waktu pengumpulan tugas telah berakhir sehingga data tidak bisa diubah.']);
        }

        $submission->delete();

        // Refresh completion (it might no longer be completed)
        $enrollment = $submission->enrollment;
        \App\Models\LmsChapterCompletion::where('enrollment_id', $enrollment->id)
            ->where('chapter_id', $assignment->chapter_id)
            ->delete();

        return back()->with('success', 'Tugas berhasil ditarik/dihapus.');
    }
}
