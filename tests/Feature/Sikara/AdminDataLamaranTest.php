<?php

namespace Tests\Feature\Sikara;

use App\Models\User;
use App\Models\Internship;
use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminDataLamaranTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TC-01 & TC-02: Render Tabel dan Fitur Pencarian Global
     */
    public function test_admin_can_view_and_search_applications(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@example.com',
        ]);
        
        $student->mahasiswaProfile()->create([
            'nim' => '12345678',
            'department' => 'Informatika',
            'study_program' => 'Teknik Informatika',
            'gpa' => 3.75,
        ]);

        $internship = Internship::query()->create([
            'company_id' => null,
            'title' => 'Software Engineer Intern',
            'company_name' => 'PT SIKARA',
            'location' => 'Jakarta',
            'description' => 'Membangun aplikasi backend.',
            'requirements' => 'Menguasai Laravel.',
            'work_type' => 'Magang',
            'deadline_at' => now()->addDays(5),
            'quota' => 3,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        Application::query()->create([
            'user_id' => $student->id,
            'internship_id' => $internship->id,
            'status' => 'submitted',
        ]);

        // Visit without search filter
        $response = $this->actingAs($admin)
            ->get('/admin/applications');

        $response->assertSuccessful();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Applications/Index')
            ->has('applications.data', 1)
            ->where('applications.data.0.student_name', 'Budi Santoso')
        );

        // Search with matching query
        $responseSearch = $this->actingAs($admin)
            ->get('/admin/applications?search=Budi');

        $responseSearch->assertSuccessful();
        $responseSearch->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Applications/Index')
            ->has('applications.data', 1)
            ->where('applications.data.0.student_name', 'Budi Santoso')
        );

        // Search with non-matching query
        $responseNoMatch = $this->actingAs($admin)
            ->get('/admin/applications?search=NonExistent');

        $responseNoMatch->assertSuccessful();
        $responseNoMatch->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Applications/Index')
            ->has('applications.data', 0)
        );
    }

    /**
     * TC-03: Ekspor Unduhan Dokumen Rekap
     */
    public function test_admin_can_export_csv_data(): void
    {
        $now = \Illuminate\Support\Carbon::create(2026, 6, 8, 12, 0, 0);
        \Illuminate\Support\Carbon::setTestNow($now);

        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->get('/admin/applications/export');

        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="data_lamaran_20260608_120000.csv"');

        \Illuminate\Support\Carbon::setTestNow();
    }

    /**
     * TC-04: Proteksi Keamanan Akses Rute (Forbidden)
     */
    public function test_mahasiswa_cannot_access_admin_application_data(): void
    {
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $response = $this->actingAs($mahasiswa)
            ->get('/admin/applications');

        $response->assertForbidden();
    }
}
