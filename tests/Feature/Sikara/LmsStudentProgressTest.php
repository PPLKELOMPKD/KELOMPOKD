<?php

namespace Tests\Feature\Sikara;

use App\Models\LmsCourse;
use App\Models\LmsEnrollment;
use App\Models\LmsLesson;
use App\Models\LmsQuiz;
use App\Models\LmsQuizOption;
use App\Models\User;
use Database\Seeders\LmsCourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LmsStudentProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_must_enroll_before_completing_lessons(): void
    {
        $this->seed(LmsCourseSeeder::class);
        $student = User::factory()->mahasiswa()->create();
        $lesson = LmsLesson::query()->firstOrFail();

        $this->actingAs($student)
            ->post(route('lms.lessons.complete', $lesson))
            ->assertForbidden();
    }

    public function test_student_progress_updates_after_lessons_and_passing_quiz(): void
    {
        $this->seed(LmsCourseSeeder::class);
        $student = User::factory()->mahasiswa()->create();
        $course = LmsCourse::query()->where('slug', 'cloud-computing')->firstOrFail();

        $this->actingAs($student)
            ->post(route('lms.enrollments.store', $course))
            ->assertRedirect(route('lms.module.show', $course));

        $enrollment = LmsEnrollment::query()->where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();
        $chapter = $course->chapters()->whereHas('quiz')->with('lessons', 'quiz.questions.options')->firstOrFail();

        foreach ($chapter->lessons as $lesson) {
            $this->actingAs($student)->post(route('lms.lessons.complete', $lesson))->assertRedirect();
        }

        $quiz = $chapter->quiz;
        $answers = $quiz->questions->mapWithKeys(fn ($question) => [
            $question->id => $question->options->firstWhere('is_correct', true)->id,
        ])->all();

        $this->actingAs($student)
            ->post(route('lms.quizzes.submit', $quiz), ['answers' => $answers])
            ->assertRedirect();

        $this->assertDatabaseHas('lms_quiz_attempts', [
            'enrollment_id' => $enrollment->id,
            'quiz_id' => $quiz->id,
            'score' => 100,
            'passed' => true,
        ]);

        $this->assertDatabaseHas('lms_chapter_completions', [
            'enrollment_id' => $enrollment->id,
            'chapter_id' => $chapter->id,
        ]);
    }
}
