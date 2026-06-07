<?php

namespace App\Http\Controllers;

use App\Models\LmsChapter;
use App\Models\LmsCourse;
use App\Models\LmsLesson;
use App\Models\LmsQuiz;
use App\Models\LmsQuizOption;
use App\Models\LmsQuizQuestion;
use App\Models\LmsAssignment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyLmsContentController extends Controller
{
    public function builder(Request $request, LmsCourse $course)
    {
        abort_if($course->company_id !== $request->user()->id, 403);

        $course->load('chapters.lessons', 'chapters.assignments', 'chapters.quiz.questions.options');

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
        $chapter = $course->chapters()->create(array_merge($validated, ['position' => $position]));

        \App\Services\ActivityLogger::log(
            'Mengubah Materi',
            "Perusahaan {$request->user()->name} menambahkan bab baru '{$chapter->title}' pada course '{$course->title}'",
            'course'
        );

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
        $lesson = $chapter->lessons()->create(array_merge($validated, ['position' => $position]));

        \App\Services\ActivityLogger::log(
            'Mengubah Materi',
            "Perusahaan {$request->user()->name} menambahkan lesson baru '{$lesson->title}' pada bab '{$chapter->title}'",
            'lesson'
        );

        return back();
    }

    public function storeQuiz(Request $request, LmsChapter $chapter)
    {
        abort_if($chapter->course->company_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'passing_score' => 'required|integer|min:0|max:100',
            'time_limit' => 'nullable|integer|min:0',
            'max_attempts' => 'required|integer|min:1',
        ]);

        if ($chapter->quiz) {
            $chapter->quiz()->update($validated);
        } else {
            $chapter->quiz()->create($validated);
        }

        \App\Services\ActivityLogger::log(
            'Membuat Kuis',
            "Perusahaan {$request->user()->name} mengonfigurasi kuis pada chapter '{$chapter->title}'",
            'quiz'
        );

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

    public function updateChapter(Request $request, LmsChapter $chapter)
    {
        abort_if($chapter->course->company_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $chapter->update($validated);

        \App\Services\ActivityLogger::log(
            'Mengubah Materi',
            "Perusahaan {$request->user()->name} memperbarui bab '{$chapter->title}'",
            'course'
        );

        return back();
    }

    public function destroyChapter(Request $request, LmsChapter $chapter)
    {
        abort_if($chapter->course->company_id !== $request->user()->id, 403);
        $chapter->delete();
        return back();
    }

    public function updateLesson(Request $request, LmsLesson $lesson)
    {
        abort_if($lesson->chapter->course->company_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:article,video',
            'content' => 'nullable|string',
            'video_url' => 'nullable|url',
            'video_image_url' => 'nullable|url',
        ]);

        $lesson->update($validated);

        \App\Services\ActivityLogger::log(
            'Mengubah Materi',
            "Perusahaan {$request->user()->name} memperbarui lesson '{$lesson->title}'",
            'lesson'
        );

        return back();
    }

    public function destroyLesson(Request $request, LmsLesson $lesson)
    {
        abort_if($lesson->chapter->course->company_id !== $request->user()->id, 403);
        $lesson->delete();
        return back();
    }

    public function updateQuiz(Request $request, LmsQuiz $quiz)
    {
        abort_if($quiz->chapter->course->company_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'passing_score' => 'required|integer|min:0|max:100',
            'time_limit' => 'nullable|integer|min:0',
            'max_attempts' => 'required|integer|min:1',
        ]);

        $quiz->update($validated);

        \App\Services\ActivityLogger::log(
            'Membuat Kuis',
            "Perusahaan {$request->user()->name} memperbarui kuis '{$quiz->title}'",
            'quiz'
        );

        return back();
    }

    public function destroyQuiz(Request $request, LmsQuiz $quiz)
    {
        abort_if($quiz->chapter->course->company_id !== $request->user()->id, 403);
        $quiz->delete();
        return back();
    }

    public function updateQuestion(Request $request, LmsQuizQuestion $question)
    {
        abort_if($question->quiz->chapter->course->company_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'question' => 'required|string',
        ]);

        $question->update($validated);

        return back();
    }

    public function destroyQuestion(Request $request, LmsQuizQuestion $question)
    {
        abort_if($question->quiz->chapter->course->company_id !== $request->user()->id, 403);
        $question->delete();
        return back();
    }

    public function updateOption(Request $request, LmsQuizOption $option)
    {
        abort_if($option->question->quiz->chapter->course->company_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'option_text' => 'required|string',
            'is_correct' => 'required|boolean',
        ]);

        $option->update($validated);

        return back();
    }

    public function destroyOption(Request $request, LmsQuizOption $option)
    {
        abort_if($option->question->quiz->chapter->course->company_id !== $request->user()->id, 403);
        $option->delete();
        return back();
    }

    public function storeAssignment(Request $request, LmsChapter $chapter)
    {
        abort_if($chapter->course->company_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'file' => 'nullable|file|max:10240',
            'deadline_at' => 'nullable|date',
            'allowed_formats' => 'nullable|string|max:255',
        ]);

        $data = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'deadline_at' => $validated['deadline_at'] ?? null,
            'allowed_formats' => $validated['allowed_formats'] ?? 'pdf,doc,docx,zip',
            'position' => $chapter->assignments()->max('position') + 1,
        ];

        if ($request->hasFile('file')) {
            $data['file_url'] = '/storage/' . $request->file('file')->store('lms/assignments', 'public');
        }

        $assignment = $chapter->assignments()->create($data);

        \App\Services\ActivityLogger::log(
            'Mengubah Materi',
            "Perusahaan {$request->user()->name} menambahkan tugas baru '{$assignment->title}' pada bab '{$chapter->title}'",
            'assignment'
        );

        return back()->with('success', 'Tugas berhasil ditambahkan.');
    }

    public function updateAssignment(Request $request, LmsAssignment $assignment)
    {
        abort_if($assignment->chapter->course->company_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'file' => 'nullable|file|max:10240',
            'deadline_at' => 'nullable|date',
            'allowed_formats' => 'nullable|string|max:255',
        ]);

        $data = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'deadline_at' => $validated['deadline_at'] ?? null,
            'allowed_formats' => $validated['allowed_formats'] ?? 'pdf,doc,docx,zip',
        ];

        if ($request->hasFile('file')) {
            $data['file_url'] = '/storage/' . $request->file('file')->store('lms/assignments', 'public');
        }

        $assignment->update($data);

        \App\Services\ActivityLogger::log(
            'Mengubah Materi',
            "Perusahaan {$request->user()->name} memperbarui tugas '{$assignment->title}'",
            'assignment'
        );

        return back()->with('success', 'Tugas berhasil diperbarui.');
    }

    public function destroyAssignment(Request $request, LmsAssignment $assignment)
    {
        abort_if($assignment->chapter->course->company_id !== $request->user()->id, 403);
        $assignment->delete();
        return back()->with('success', 'Tugas berhasil dihapus.');
    }
}
