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
        ])->assertRedirect(route('peserta'));

        $this->actingAs($student)
            ->get('/dashboard')
            ->assertRedirect(route('peserta'));

        auth()->logout();

        $this->post('/login', [
            'email' => $company->email,
            'password' => 'password',
            'role' => 'perusahaan',
        ])->assertRedirect(route('perusahaan.dashboard'));

        $this->actingAs($company)
            ->get('/dashboard')
            ->assertRedirect(route('perusahaan.dashboard'));

        $this->actingAs($company)
            ->get(route('perusahaan.dashboard'))
            ->assertSuccessful()
            ->assertSee('Dashboard Perusahaan');

        auth()->logout();

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
            'role' => 'admin',
        ])->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Dashboard')
            );
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

    public function test_student_dashboard_route_redirects_to_student_landing_page(): void
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
            'description' => 'Lowongan frontend engineer intern.',
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
            ->assertRedirect(route('peserta'));
    }

    public function test_shared_auth_user_includes_profile_photo_url_for_navbar(): void
    {
        $student = User::factory()->create([
            'role' => 'mahasiswa',
        ]);

        DB::table('mahasiswa_profiles')->insert([
            'user_id' => $student->id,
            'nim' => '1301213888',
            'department' => 'Teknik Informatika',
            'study_program' => 'S1 Informatika',
            'gpa' => '3.85',
            'phone' => '081234567890',
            'location' => 'Jakarta',
            'bio' => 'Mahasiswa aktif.',
            'photo_path' => 'profile-photos/avatar.png',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($student)
            ->get('/profile')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->where('auth.user.profile_photo_url', '/storage/profile-photos/avatar.png')
            );
    }
}
