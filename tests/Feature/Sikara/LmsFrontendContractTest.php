<?php

namespace Tests\Feature\Sikara;

use Database\Seeders\LmsCourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LmsFrontendContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_lms_page_lists_seeded_learning_courses(): void
    {
        $this->seed(LmsCourseSeeder::class);

        $this->get('/lms')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Features/Lms')
                ->has('courses', 3)
                ->where('courses.0.slug', 'cloud-computing')
                ->where('courses.0.progress', 0)
                ->where('courses.0.is_enrolled', false)
            );
    }

    public function test_lms_module_page_shows_seeded_course_content(): void
    {
        $this->seed(LmsCourseSeeder::class);

        $this->get('/lms/module/cloud-computing')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Features/LmsModule')
                ->where('course.slug', 'cloud-computing')
                ->has('course.chapters', 4)
                ->where('course.chapters.2.quiz.passing_score', 75)
                ->where('course.chapters.2.lessons.0.title', 'Video: Publik vs Privat')
            );
    }

    public function test_lms_vue_page_keeps_hero_and_adds_learning_section_links(): void
    {
        $source = file_get_contents(resource_path('js/Pages/Features/Lms.vue'));

        $this->assertStringContainsString('Tingkatkan <strong>Skill</strong>', $source);
        $this->assertStringContainsString('Pembelajaran Saya', $source);
        $this->assertStringContainsString("route('lms.module.show', course.slug)", $source);
    }

    public function test_lms_module_sidebar_has_interactive_navigation_state(): void
    {
        $source = file_get_contents(resource_path('js/Pages/Features/LmsModule.vue'));

        $this->assertStringContainsString('selectedLessonKey', $source);
        $this->assertStringContainsString('selectLesson', $source);
        $this->assertStringContainsString('toggleChapter', $source);
        $this->assertStringContainsString('@click.prevent="selectLesson', $source);
        $this->assertStringContainsString('@click="toggleChapter', $source);
        $this->assertStringContainsString('isLessonSelected', $source);
    }

    public function test_company_lms_frontend_pages_expose_authoring_forms(): void
    {
        $index = @file_get_contents(resource_path('js/Pages/Perusahaan/Lms/Index.vue')) ?: '';
        $form = @file_get_contents(resource_path('js/Pages/Perusahaan/Lms/Form.vue')) ?: '';
        $builder = @file_get_contents(resource_path('js/Pages/Perusahaan/Lms/Builder.vue')) ?: '';

        $this->assertStringContainsString("route('perusahaan.lms.create')", $index);
        $this->assertStringContainsString('form.title', $form);
        $this->assertStringContainsString('form.passing_score', $builder);
        $this->assertStringContainsString("route('perusahaan.lms.chapters.store'", $builder);
        $this->assertStringContainsString("route('perusahaan.lms.options.store'", $builder);
    }

    public function test_student_lms_pages_expose_enrollment_completion_and_quiz_actions(): void
    {
        $lms = @file_get_contents(resource_path('js/Pages/Features/Lms.vue')) ?: '';
        $module = @file_get_contents(resource_path('js/Pages/Features/LmsModule.vue')) ?: '';

        $this->assertStringContainsString("route('lms.enrollments.store'", $lms);
        $this->assertStringContainsString("route('lms.lessons.complete'", $module);
        $this->assertStringContainsString("route('lms.quizzes.submit'", $module);
        $this->assertStringContainsString('selectedQuizAnswers', $module);
        $this->assertStringContainsString('course.progress', $module);
    }
}
