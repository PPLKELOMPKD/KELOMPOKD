<?php

namespace App\Http\Controllers;

use App\Models\LmsQuiz;
use App\Services\LmsProgressService;
use Illuminate\Http\Request;

class LmsQuizAttemptController extends Controller
{
    public function store(Request $request, LmsQuiz $quiz, LmsProgressService $progressService)
    {
        $validated = $request->validate([
            'answers' => ['required', 'array'],
        ]);

        $quiz->load('chapter.course');
        $course = $quiz->chapter->course;

        $enrollment = $request->user()->lmsEnrollments()->where('course_id', $course->id)->first();
        abort_if(!$enrollment, 403);

        $result = $progressService->scoreQuiz($quiz, $validated['answers']);

        $enrollment->quizAttempts()->create([
            'quiz_id' => $quiz->id,
            'score' => $result['score'],
            'passed' => $result['passed'],
            'answers' => $validated['answers'],
            'submitted_at' => now(),
        ]);

        $progressService->refreshChapterCompletion($enrollment, $quiz->chapter);

        return redirect()->back()->with('flash', [
            'message' => $result['passed'] ? 'Selamat, Anda lulus kuis!' : 'Maaf, Anda belum lulus kuis.',
        ]);
    }
}
