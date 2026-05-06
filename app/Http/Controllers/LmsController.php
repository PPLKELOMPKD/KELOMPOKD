<?php

namespace App\Http\Controllers;

use App\Models\LmsCourse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LmsController extends Controller
{
    public function index(Request $request): Response
    {
        $courses = LmsCourse::query()
            ->with(['company'])
            ->where('status', LmsCourse::STATUS_PUBLISHED)
            ->latest()
            ->get()
            ->map(function (LmsCourse $course) use ($request) {
                $enrollment = $request->user()?->role === 'mahasiswa' 
                    ? $course->enrollments()->where('student_id', $request->user()->id)->first()
                    : null;
                
                $progress = $enrollment ? app(\App\Services\LmsProgressService::class)->courseProgress($enrollment) : 0;

                return [
                    'slug' => $course->slug,
                    'title' => $course->title,
                    'provider' => $course->provider ?? $course->company?->companyProfile?->name ?? 'Sikara',
                    'level' => $course->level,
                    'status' => $enrollment ? 'in_progress' : 'available',
                    'progress' => $progress,
                    'is_enrolled' => (bool) $enrollment,
                    'started_at' => $course->started_at?->format('d M Y'),
                    'ends_at' => $course->ends_at?->format('d M Y'),
                    'image_url' => $course->image_url,
                    'image_alt' => $course->image_alt,
                ];
            });

        return Inertia::render('Features/Lms', [
            'courses' => $courses,
        ]);
    }

    public function show(Request $request, LmsCourse $course): Response
    {
        abort_if($course->status !== LmsCourse::STATUS_PUBLISHED, 404);

        $course->load([
            'company',
            'chapters.lessons',
            'chapters.quiz.questions.options'
        ]);

        $enrollment = $request->user()?->role === 'mahasiswa' 
            ? $course->enrollments()->where('student_id', $request->user()->id)->with(['lessonCompletions', 'chapterCompletions', 'quizAttempts'])->first()
            : null;

        $progress = $enrollment ? app(\App\Services\LmsProgressService::class)->courseProgress($enrollment) : 0;
        
        $isFirstAvailableLessonFound = false;

        $chapters = $course->chapters->map(function ($chapter) use ($enrollment, &$isFirstAvailableLessonFound) {
            $chapterCompleted = $enrollment ? $enrollment->chapterCompletions->contains('chapter_id', $chapter->id) : false;
            
            $lessons = $chapter->lessons->map(function ($lesson) use ($enrollment, &$isFirstAvailableLessonFound) {
                $lessonCompleted = $enrollment ? $enrollment->lessonCompletions->contains('lesson_id', $lesson->id) : false;
                
                $active = false;
                if (!$lessonCompleted && !$isFirstAvailableLessonFound) {
                    $active = true;
                    $isFirstAvailableLessonFound = true;
                }

                return [
                    'id' => $lesson->id,
                    'type' => $lesson->type === 'video' ? 'play_circle' : 'article',
                    'title' => $lesson->title,
                    'state' => $lessonCompleted ? 'completed' : 'available',
                    'active' => $active,
                    'description_title' => $lesson->title,
                    'description' => $lesson->content,
                    'video_image_url' => $lesson->video_image_url,
                ];
            });

            $quiz = null;
            if ($chapter->quiz) {
                $quizPassed = $enrollment ? $enrollment->quizAttempts->where('quiz_id', $chapter->quiz->id)->where('passed', true)->isNotEmpty() : false;
                $allLessonsCompleted = $lessons->every(fn($l) => $l['state'] === 'completed');
                
                $active = false;
                if ($allLessonsCompleted && !$quizPassed && !$isFirstAvailableLessonFound) {
                    $active = true;
                    $isFirstAvailableLessonFound = true;
                }

                $quizData = [
                    'id' => $chapter->quiz->id,
                    'type' => 'quiz',
                    'title' => 'Kuis: ' . $chapter->quiz->title,
                    'state' => $quizPassed ? 'completed' : ($allLessonsCompleted ? 'available' : 'locked'),
                    'active' => $active,
                    'description_title' => $chapter->quiz->title,
                    'description' => $chapter->quiz->description ?? 'Kerjakan kuis ini untuk menguji pemahaman Anda.',
                    'video_image_url' => null,
                    'isQuiz' => true,
                    'passing_score' => $chapter->quiz->passing_score,
                    'questions' => $chapter->quiz->questions->map(function ($q) {
                        return [
                            'id' => $q->id,
                            'question' => $q->question,
                            'options' => $q->options->map(function ($o) {
                                return [
                                    'id' => $o->id,
                                    'option_text' => $o->option_text,
                                ];
                            }),
                        ];
                    }),
                ];
                
                $lessons->push($quizData);
                $quiz = $quizData;
            }

            return [
                'id' => $chapter->id,
                'title' => $chapter->title,
                'state' => $chapterCompleted ? 'completed' : 'active',
                'lessons' => $lessons,
                'quiz' => $quiz,
            ];
        });

        $activeLesson = null;
        foreach ($chapters as $chapter) {
            foreach ($chapter['lessons'] as $lesson) {
                if ($lesson['active']) {
                    $activeLesson = $lesson;
                    break 2;
                }
            }
        }

        return Inertia::render('Features/LmsModule', [
            'course' => [
                'slug' => $course->slug,
                'title' => $course->title,
                'provider' => $course->provider ?? $course->company?->companyProfile?->name ?? 'Sikara',
                'level' => $course->level,
                'status' => $enrollment ? 'in_progress' : 'available',
                'progress' => $progress,
                'is_enrolled' => (bool) $enrollment,
                'started_at' => $course->started_at?->format('d M Y'),
                'ends_at' => $course->ends_at?->format('d M Y'),
                'image_url' => $course->image_url,
                'image_alt' => $course->image_alt,
                'chapters' => $chapters,
            ],
            'activeLesson' => $activeLesson,
        ]);
    }
}
