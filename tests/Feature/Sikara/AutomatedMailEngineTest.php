<?php

namespace Tests\Feature\Sikara;

use App\Models\Application;
use App\Models\Internship;
use App\Models\User;
use App\Notifications\ApplicationStatusUpdatedNotification;
use App\Notifications\ApplicationSubmittedToCompanyNotification;
use App\Services\AutomatedMailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\TestCase;

class AutomatedMailEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_receives_email_when_student_submits_application(): void
    {
        Notification::fake();

        $student = User::factory()->create(['role' => User::ROLE_MAHASISWA]);
        $company = User::factory()->create(['role' => User::ROLE_PERUSAHAAN]);
        $internship = Internship::query()->create([
            'company_id' => $company->id,
            'title' => 'Backend Engineer Intern',
            'company_name' => $company->name,
            'location' => 'Bandung',
            'description' => 'Lowongan backend engineer intern.',
            'requirements' => 'Laravel dan MySQL',
            'deadline_at' => now()->addDays(10),
            'is_published' => true,
        ]);

        $this->actingAs($student)
            ->post(route('internships.apply'), ['internship_id' => $internship->id])
            ->assertRedirect();

        Notification::assertSentTo($company, ApplicationSubmittedToCompanyNotification::class);
    }

    public function test_student_receives_email_when_application_status_changes(): void
    {
        Notification::fake();

        $student = User::factory()->create(['role' => User::ROLE_MAHASISWA]);
        $company = User::factory()->create(['role' => User::ROLE_PERUSAHAAN]);
        $internship = Internship::query()->create([
            'company_id' => $company->id,
            'title' => 'Frontend Engineer Intern',
            'company_name' => $company->name,
            'location' => 'Jakarta',
            'description' => 'Lowongan frontend engineer intern.',
            'requirements' => 'Vue dan Tailwind',
            'deadline_at' => now()->addDays(10),
            'is_published' => true,
        ]);
        $application = Application::query()->create([
            'user_id' => $student->id,
            'internship_id' => $internship->id,
            'status' => 'submitted',
        ]);

        $this->actingAs($company)
            ->patch(route('perusahaan.applicants.updateStatus', $application), [
                'status' => 'wawancara',
            ])
            ->assertRedirect();

        Notification::assertSentTo($student, ApplicationStatusUpdatedNotification::class);
    }

    public function test_application_submission_still_succeeds_when_automated_mail_fails(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_MAHASISWA]);
        $company = User::factory()->create(['role' => User::ROLE_PERUSAHAAN]);
        $internship = Internship::query()->create([
            'company_id' => $company->id,
            'title' => 'QA Engineer Intern',
            'company_name' => $company->name,
            'location' => 'Bandung',
            'description' => 'Lowongan QA engineer intern.',
            'requirements' => 'Testing dasar',
            'deadline_at' => now()->addDays(10),
            'is_published' => true,
        ]);

        $this->app->instance(AutomatedMailService::class, new class extends AutomatedMailService {
            public function sendApplicationSubmittedToCompany(Application $application): void
            {
                throw new RuntimeException('Provider down');
            }
        });

        $this->actingAs($student)
            ->post(route('internships.apply'), ['internship_id' => $internship->id])
            ->assertRedirect();

        $this->assertDatabaseHas('applications', [
            'user_id' => $student->id,
            'internship_id' => $internship->id,
            'status' => 'submitted',
        ]);
    }
}
