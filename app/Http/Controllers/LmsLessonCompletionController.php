<?php

namespace App\Http\Controllers;

use App\Models\LmsLesson;
use App\Services\LmsProgressService;
use Illuminate\Http\Request;

class LmsLessonCompletionController extends Controller
{
    public function store(Request $request, LmsLesson $lesson, LmsProgressService $progressService)
    {
        $lesson->load('chapter.course');
        $course = $lesson->chapter->course;

        abort_if($lesson->status === 'takedown' || $course->moderation_status !== 'approved', 404);

        $enrollment = $request->user()->lmsEnrollments()->where('course_id', $course->id)->first();
        abort_if(!$enrollment, 403);

        $enrollment->lessonCompletions()->firstOrCreate([
            'lesson_id' => $lesson->id,
        ], [
            'completed_at' => now(),
        ]);

        \App\Services\ActivityLogger::log(
            'Menyelesaikan Lesson',
            "Mahasiswa {$request->user()->name} menyelesaikan lesson '{$lesson->title}' pada course '{$course->title}'",
            'lesson'
        );

        $progressService->refreshChapterCompletion($enrollment, $lesson->chapter);

        return redirect()->back();
    }
}
