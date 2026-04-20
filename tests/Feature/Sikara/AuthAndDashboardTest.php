<?php

namespace Tests\Feature\Sikara;

use App\Models\Internship;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuthAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_are_redirected_to_their_role_dashboard_after_login(): void
    {
        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'email' => 'mahasiswa@example.com',
            'password' => 'password',
        ]);

        $company = User::factory()->create([
            'role' => 'perusahaan',
            'email' => 'perusahaan@example.com',
            'password' => 'password',
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $this->post('/login', [
            'email' => $student->email,
            'password' => 'password',
            'role' => 'mahasiswa',
        ])->assertRedirect(route('dashboard'));

        $this->actingAs($student)
            ->get('/dashboard')
            ->assertSuccessful()
            ->assertSee('Dashboard Mahasiswa');

        auth()->logout();

        $this->post('/login', [
            'email' => $company->email,
            'password' => 'password',
            'role' => 'perusahaan',
        ])->assertRedirect(route('dashboard'));

        $this->actingAs($company)
            ->get('/dashboard')
            ->assertSuccessful()
            ->assertSee('Dashboard Mitra');

        auth()->logout();

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
            'role' => 'admin',
        ])->assertRedirect(route('dashboard'));

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertSuccessful()
            ->assertSee('Dashboard Admin');
    }

    public function test_non_student_users_cannot_access_student_internship_pages(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get('/internships')
            ->assertForbidden();
    }

    public function test_student_dashboard_shows_profile_progress_latest_internships_and_notifications(): void
    {
        $student = User::factory()->create([
            'role' => 'mahasiswa',
        ]);

        DB::table('mahasiswa_profiles')->insert([
            'user_id' => $student->id,
            'nim' => '1301213999',
            'department' => 'Teknik Informatika',
            'study_program' => 'S1 Informatika',
            'gpa' => '3.85',
            'phone' => '081234567890',
            'university' => 'Universitas Indonesia',
            'location' => 'Jakarta',
            'bio' => 'Mahasiswa aktif yang fokus pada web development.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Internship::query()->create([
            'title' => 'Frontend Engineer Intern',
            'company_name' => 'PT SIKARA Nusantara',
            'location' => 'Jakarta',
            'requirements' => 'Vue, Tailwind, dan komunikasi yang baik.',
            'deadline_at' => now()->addDays(7),
            'is_published' => true,
        ]);

        Notification::query()->create([
            'user_id' => $student->id,
            'title' => 'Lamaran sedang ditinjau',
            'message' => 'Tim rekrutmen sedang meninjau lamaran Anda.',
            'type' => 'application',
        ]);

        $this->actingAs($student)
            ->get('/dashboard')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('profileSummary.department', 'Teknik Informatika')
                ->where('latestInternships.0.title', 'Frontend Engineer Intern')
                ->where('latestNotifications.0.title', 'Lamaran sedang ditinjau')
            );
    }
}
