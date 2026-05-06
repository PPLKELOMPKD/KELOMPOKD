<?php

namespace App\Http\Controllers;

use App\Models\LmsChapter;
use App\Models\LmsCourse;
use App\Models\LmsLesson;
use App\Models\LmsQuiz;
use App\Models\LmsQuizQuestion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyLmsContentController extends Controller
{
    public function builder(Request $request, LmsCourse $course)
    {
        abort_if($course->company_id !== $request->user()->id, 403);

        $course->load('chapters.lessons', 'chapters.quiz.questions.options');

        return Inertia::render('Perusahaan/Lms/Builder', [
            'course' => $course,
        ]);
    }

    public function storeChapter(Request $request, LmsCourse $course)
    {
        abort_if($course->company_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $position = $course->chapters()->max('position') + 1;
        $course->chapters()->create(array_merge($validated, ['position' => $position]));

        return back();
    }

    public function storeLesson(Request $request, LmsChapter $chapter)
    {
        abort_if($chapter->course->company_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:article,video',
            'content' => 'nullable|string',
            'video_url' => 'nullable|url',
            'video_image_url' => 'nullable|url',
        ]);

        $position = $chapter->lessons()->max('position') + 1;
        $chapter->lessons()->create(array_merge($validated, ['position' => $position]));

        return back();
    }

    public function storeQuiz(Request $request, LmsChapter $chapter)
    {
        abort_if($chapter->course->company_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'passing_score' => 'required|integer|min:0|max:100',
        ]);

        if ($chapter->quiz) {
            $chapter->quiz()->update($validated);
        } else {
            $chapter->quiz()->create($validated);
        }

        return back();
    }

    public function storeQuestion(Request $request, LmsQuiz $quiz)
    {
        abort_if($quiz->chapter->course->company_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'question' => 'required|string',
        ]);

        $position = $quiz->questions()->max('position') + 1;
        $quiz->questions()->create(array_merge($validated, ['position' => $position]));

        return back();
    }

    public function storeOption(Request $request, LmsQuizQuestion $question)
    {
        abort_if($question->quiz->chapter->course->company_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'option_text' => 'required|string',
            'is_correct' => 'required|boolean',
        ]);

        $position = $question->options()->max('position') + 1;
        $question->options()->create(array_merge($validated, ['position' => $position]));

        return back();
    }
}
