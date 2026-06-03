<?php

namespace Tests\Feature\Sikara;

use App\Models\Internship;
use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CompanyReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_and_students_cannot_access_company_reports(): void
    {
        // Guest
        $this->get('/perusahaan/reports')
            ->assertRedirect(route('login'));

        // Student
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $this->actingAs($student)
            ->get('/perusahaan/reports')
            ->assertForbidden();
    }

    public function test_empty_company_reports_gracefully_displays_zeroes(): void
    {
        $company = User::factory()->create(['role' => 'perusahaan']);

        $this->actingAs($company)
            ->get('/perusahaan/reports')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Company/Reports/Index')
                ->where('stats.active_internships', 0)
                ->where('stats.total_applicants', 0)
                ->where('stats.processed_applicants', 0)
                ->where('internships_data', [])
            );
    }

    public function test_company_reports_displays_correct_statistics_and_quota_calculations(): void
    {
        $company = User::factory()->create(['role' => 'perusahaan']);

        // Create internships
        $internshipActive = Internship::query()->create([
            'company_id' => $company->id,
            'title' => 'Software Engineer',
            'company_name' => 'PT SIKARA',
            'location' => 'Bandung',
            'description' => 'Desc',
            'requirements' => 'Req',
            'deadline_at' => now()->addDays(10),
            'quota' => 5,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        $internshipExpired = Internship::query()->create([
            'company_id' => $company->id,
            'title' => 'Product Manager',
            'company_name' => 'PT SIKARA',
            'location' => 'Bandung',
            'description' => 'Desc',
            'requirements' => 'Req',
            'deadline_at' => now()->subDays(5),
            'quota' => 2,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        // Create applicants (students)
        $student1 = User::factory()->create(['role' => 'mahasiswa']);
        $student2 = User::factory()->create(['role' => 'mahasiswa']);
        $student3 = User::factory()->create(['role' => 'mahasiswa']);

        // Applications for InternshipActive (total 3, quota 5) -> percentage 3/5 = 60%
        Application::create([
            'user_id' => $student1->id,
            'internship_id' => $internshipActive->id,
            'status' => 'submitted', // not processed
        ]);

        Application::create([
            'user_id' => $student2->id,
            'internship_id' => $internshipActive->id,
            'status' => 'wawancara', // processed
        ]);

        Application::create([
            'user_id' => $student3->id,
            'internship_id' => $internshipActive->id,
            'status' => 'lolos', // processed
        ]);

        $this->actingAs($company)
            ->get('/perusahaan/reports')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Company/Reports/Index')
                // Software Engineer (Active) count is 1. Product Manager (Expired) does not count as active.
                ->where('stats.active_internships', 1)
                ->where('stats.total_applicants', 3)
                ->where('stats.processed_applicants', 2) // wawancara and lolos
                ->has('internships_data', 2)
                ->where('internships_data.0.title', 'Software Engineer')
                ->where('internships_data.0.applicants_count', 3)
                ->where('internships_data.0.quota_percentage', 60)
            );
    }

    public function test_company_reports_filters_by_month_and_year(): void
    {
        $company = User::factory()->create(['role' => 'perusahaan']);

        $mayDate = Carbon::create(date('Y'), 5, 15, 12, 0, 0);
        $juneDate = Carbon::create(date('Y'), 6, 15, 12, 0, 0);

        $internship = Internship::query()->create([
            'company_id' => $company->id,
            'title' => 'Software Engineer',
            'company_name' => 'PT SIKARA',
            'location' => 'Bandung',
            'description' => 'Desc',
            'requirements' => 'Req',
            'deadline_at' => now()->addDays(10),
            'quota' => 5,
            'is_published' => true,
            'moderation_status' => 'approved',
            'created_at' => $mayDate,
        ]);

        $student1 = User::factory()->create(['role' => 'mahasiswa']);
        $student2 = User::factory()->create(['role' => 'mahasiswa']);

        // Application 1 created in May
        $appMay = Application::create([
            'user_id' => $student1->id,
            'internship_id' => $internship->id,
            'status' => 'submitted',
        ]);
        $appMay->created_at = $mayDate;
        $appMay->save();

        // Application 2 created in June
        $appJune = Application::create([
            'user_id' => $student2->id,
            'internship_id' => $internship->id,
            'status' => 'lolos',
        ]);
        $appJune->created_at = $juneDate;
        $appJune->save();

        // Query reports with May filter
        $this->actingAs($company)
            ->get('/perusahaan/reports?month=5')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Company/Reports/Index')
                ->where('stats.total_applicants', 1) // only May application
                ->where('stats.processed_applicants', 0) // May application is 'submitted'
                ->where('internships_data.0.applicants_count', 1)
            );

        // Query reports with June filter
        $this->actingAs($company)
            ->get('/perusahaan/reports?month=6')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Company/Reports/Index')
                ->where('stats.total_applicants', 1) // only June application
                ->where('stats.processed_applicants', 1) // June application is 'lolos'
                ->where('internships_data.0.applicants_count', 1)
            );
    }
}
