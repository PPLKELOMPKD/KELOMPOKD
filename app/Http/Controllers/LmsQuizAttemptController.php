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

        abort_if($quiz->status === 'takedown' || $course->moderation_status !== 'approved', 404);

        $enrollment = $request->user()->lmsEnrollments()->where('course_id', $course->id)->first();
        abort_if(!$enrollment, 403);

        $existingAttempts = $enrollment->quizAttempts()->where('quiz_id', $quiz->id)->count();
        if ($existingAttempts >= $quiz->max_attempts) {
            return redirect()->back()->with('flash', [
                'error' => 'You have reached the maximum attempt limit for this quiz (' . $quiz->max_attempts . ' times).',
            ]);
        }

        $result = $progressService->scoreQuiz($quiz, $validated['answers']);

        $enrollment->quizAttempts()->create([
            'quiz_id' => $quiz->id,
            'score' => $result['score'],
            'passed' => $result['passed'],
            'answers' => $validated['answers'],
            'submitted_at' => now(),
        ]);

        if ($result['passed']) {
            \App\Services\ActivityLogger::log(
                'Lulus Kuis',
                "Mahasiswa {$request->user()->name} lulus kuis '{$quiz->title}' dengan skor {$result['score']} pada course '{$course->title}'",
                'quiz'
            );
        } else {
            \App\Services\ActivityLogger::log(
                'Mengerjakan Kuis',
                "Mahasiswa {$request->user()->name} mengerjakan kuis '{$quiz->title}' dengan skor {$result['score']} pada course '{$course->title}'",
                'quiz'
            );
        }

        $progressService->refreshChapterCompletion($enrollment, $quiz->chapter);

        return redirect()->back()->with('flash', [
            'message' => $result['passed'] ? 'Congratulations, you passed the quiz!' : 'Sorry, you did not pass the quiz.',
        ]);
    }
}
