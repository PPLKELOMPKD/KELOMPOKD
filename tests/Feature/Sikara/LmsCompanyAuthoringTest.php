<?php

namespace Tests\Feature\Sikara;

use App\Models\LmsChapter;
use App\Models\LmsCourse;
use App\Models\LmsEnrollment;
use App\Models\LmsLesson;
use App\Models\LmsQuiz;
use App\Models\LmsQuizOption;
use App\Models\LmsQuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LmsCompanyAuthoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_lms_relational_models_can_store_company_course_content_and_student_enrollment(): void
    {
        $company = User::factory()->perusahaan()->create();
        $student = User::factory()->mahasiswa()->create();

        $course = LmsCourse::query()->create([
            'company_id' => $company->id,
            'slug' => 'cloud-computing',
            'title' => 'KOMPUTASI AWAN SI-47-06',
            'provider' => 'SOUTH BANDUNG INFORMATION...',
            'description' => 'Belajar cloud computing.',
            'level' => 'INTERMEDIATE',
            'status' => 'published',
            'image_url' => 'https://example.com/cloud.jpg',
            'image_alt' => 'Cloud computing',
        ]);

        $chapter = LmsChapter::query()->create([
            'course_id' => $course->id,
            'title' => 'Bab 1: Pendahuluan',
            'description' => 'Dasar pembelajaran.',
            'position' => 1,
        ]);

        $lesson = LmsLesson::query()->create([
            'chapter_id' => $chapter->id,
            'title' => 'Video: Pengenalan Cloud',
            'type' => 'video',
            'content' => 'Konten video.',
            'position' => 1,
        ]);

        $quiz = LmsQuiz::query()->create([
            'chapter_id' => $chapter->id,
            'title' => 'Kuis Pendahuluan',
            'description' => 'Kuis bab 1.',
            'passing_score' => 75,
        ]);

        $question = LmsQuizQuestion::query()->create([
            'quiz_id' => $quiz->id,
            'question' => 'Apa itu cloud?',
            'position' => 1,
        ]);

        LmsQuizOption::query()->create([
            'question_id' => $question->id,
            'option_text' => 'Layanan komputasi melalui internet',
            'is_correct' => true,
            'position' => 1,
        ]);

        $enrollment = LmsEnrollment::query()->create([
            'course_id' => $course->id,
            'student_id' => $student->id,
            'enrolled_at' => now(),
        ]);

        $this->assertTrue($course->company->is($company));
        $this->assertTrue($course->chapters->first()->is($chapter));
        $this->assertTrue($chapter->lessons->first()->is($lesson));
        $this->assertTrue($chapter->quiz->is($quiz));
        $this->assertTrue($quiz->questions->first()->is($question));
        $this->assertTrue($course->enrollments->first()->is($enrollment));
    }

    public function test_company_can_create_lms_course_with_draft_status(): void
    {
        $company = User::factory()->perusahaan()->create();

        $this->actingAs($company)
            ->post(route('perusahaan.lms.store'), [
                'title' => 'Cloud Computing',
                'provider' => 'Telkom University',
                'description' => 'Course cloud.',
                'level' => 'BEGINNER',
                'image_alt' => 'Cloud',
            ])
            ->assertRedirect(route('perusahaan.lms.index'));

        $this->assertDatabaseHas('lms_courses', [
            'company_id' => $company->id,
            'title' => 'Cloud Computing',
            'status' => 'draft',
        ]);
    }

    public function test_company_cannot_update_another_company_course(): void
    {
        $owner = User::factory()->perusahaan()->create();
        $other = User::factory()->perusahaan()->create();

        $course = LmsCourse::query()->create([
            'company_id' => $owner->id,
            'slug' => 'owner-course',
            'title' => 'Owner Course',
            'provider' => 'Owner',
            'level' => 'BEGINNER',
            'status' => 'draft',
            'image_url' => 'https://example.com/course.jpg',
        ]);

        $this->actingAs($other)
            ->put(route('perusahaan.lms.update', $course), ['title' => 'Hacked'])
            ->assertForbidden();
    }
}
