<?php

namespace Tests\Feature\Sikara;

use App\Models\Internship;
use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CompanyDashboardIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_can_access_dashboard_and_has_required_data(): void
    {
        $company = User::factory()->create(['role' => 'perusahaan']);

        $this->actingAs($company)
            ->get('/perusahaan/dashboard')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('role', 'perusahaan')
                ->has('stats')
                ->has('pipeline')
                ->has('recentApplicants')
                ->has('upcomingEvents')
                ->has('notifications')
            );
    }

    public function test_update_applicant_status_redirects_correctly(): void
    {
        $company = User::factory()->create(['role' => 'perusahaan']);

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
        ]);

        $student = User::factory()->create(['role' => 'mahasiswa']);

        $application = Application::create([
            'user_id' => $student->id,
            'internship_id' => $internship->id,
            'status' => 'submitted',
        ]);

        // Test redirect to dashboard
        $this->actingAs($company)
            ->patch(route('perusahaan.applicants.updateStatus', $application->id), [
                'status' => 'lolos',
                'redirect' => 'dashboard',
            ])
            ->assertRedirect(route('perusahaan.dashboard'))
            ->assertSessionHas('success');

        $this->assertEquals('lolos', $application->fresh()->status);

        // Reset status
        $application->update(['status' => 'submitted']);

        // Test redirect to show
        $this->actingAs($company)
            ->patch(route('perusahaan.applicants.updateStatus', $application->id), [
                'status' => 'wawancara',
                'redirect' => 'show',
            ])
            ->assertRedirect(route('perusahaan.applicants.show', $application->id))
            ->assertSessionHas('success');

        $this->assertEquals('wawancara', $application->fresh()->status);

        // Reset status
        $application->update(['status' => 'submitted']);

        // Test redirect to index (default/fallback/index parameter)
        $this->actingAs($company)
            ->patch(route('perusahaan.applicants.updateStatus', $application->id), [
                'status' => 'tidak lolos',
                'redirect' => 'index',
            ])
            ->assertRedirect(route('perusahaan.applicants.index'))
            ->assertSessionHas('success');

        $this->assertEquals('tidak lolos', $application->fresh()->status);
    }
}
