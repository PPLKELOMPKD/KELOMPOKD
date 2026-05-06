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
                    'provider' => $course->provider ?? $course->company?->perusahaanProfile?->name ?? 'Sikara',
                    'level' => $course->level,
                    'status' => $enrollment ? ($progress === 100 ? 'completed' : 'in_progress') : 'available',
                    'progress' => $progress,
                    'is_enrolled' => (bool) $enrollment,
                    'enrollment_status' => $enrollment?->status ?? 'none',
                    'started_at' => $course->started_at?->format('d M Y'),
                    'ends_at' => $course->ends_at?->format('d M Y'),
                    'image_url' => $course->image_url,
                    'image_alt' => $course->image_alt,
                    'location' => $course->location,
                    'start_time' => $course->start_time,
                    'quota' => $course->quota,
                    'enrolled_count' => $course->enrollments()->count(),
                    'description' => $course->description,
                ];
            });

        return Inertia::render('Features/Lms', [
            'courses' => $courses,
            'isAuthenticated' => $request->user() !== null,
            'isMahasiswa' => $request->user()?->role === 'mahasiswa',
        ]);
    }

    public function show(Request $request, LmsCourse $course): Response
    {
        abort_if($course->status !== LmsCourse::STATUS_PUBLISHED, 404);

        $course->load([
            'company',
            'chapters.lessons',
            'chapters.assignments',
            'chapters.quiz.questions.options'
        ]);

        $enrollment = $request->user()?->role === 'mahasiswa'
            ? $course->enrollments()->where('student_id', $request->user()->id)->with(['lessonCompletions', 'chapterCompletions', 'quizAttempts', 'assignmentSubmissions'])->first()
            : null;

        if ($request->user()?->role === 'mahasiswa') {
            abort_if(!$enrollment, 403, 'Anda belum terdaftar di pelatihan ini.');
        }

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

            if ($chapter->assignments) {
                foreach ($chapter->assignments as $assignment) {
                    $submission = $enrollment ? $enrollment->assignmentSubmissions->where('assignment_id', $assignment->id)->first() : null;
                    $active = false;
                    if (!$submission && !$isFirstAvailableLessonFound) {
                        $active = true;
                        $isFirstAvailableLessonFound = true;
                    }

                    $lessonData = [
                        'id' => $assignment->id,
                        'type' => 'assignment',
                        'title' => 'Tugas: ' . $assignment->title,
                        'state' => $submission ? 'completed' : 'available',
                        'active' => $active,
                        'description_title' => $assignment->title,
                        'description' => $assignment->description,
                        'isAssignment' => true,
                        'assignment' => [
                            'id' => $assignment->id,
                            'title' => $assignment->title,
                            'description' => $assignment->description,
                            'deadline_at' => $assignment->deadline_at,
                            'file_url' => $assignment->file_url,
                        ],
                        'submission' => $submission,
                    ];
                    $lessons->push($lessonData);
                }
            }

            $quiz = null;
            if ($chapter->quiz) {
                $quizPassed = $enrollment ? $enrollment->quizAttempts->where('quiz_id', $chapter->quiz->id)->where('passed', true)->isNotEmpty() : false;
                $latestAttempt = $enrollment ? $enrollment->quizAttempts->where('quiz_id', $chapter->quiz->id)->sortByDesc('submitted_at')->first() : null;
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
                    'max_attempts' => $chapter->quiz->max_attempts,
                    'attempts_count' => $enrollment ? $enrollment->quizAttempts->where('quiz_id', $chapter->quiz->id)->count() : 0,
                    'latest_score' => $latestAttempt?->score,
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
                'provider' => $course->provider ?? $course->company?->perusahaanProfile?->name ?? 'Sikara',
                'level' => $course->level,
                'status' => $enrollment ? ($progress === 100 ? 'completed' : 'in_progress') : 'available',
                'progress' => $progress,
                'is_enrolled' => (bool) $enrollment,
                'is_graduated' => (bool) ($enrollment->is_graduated ?? false),
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
