<?php

namespace Tests\Feature\Sikara;

use App\Models\ActivityLog;
use App\Models\LmsCourse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminLmsActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_users_cannot_access_lms_activity_monitoring(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);

        $this->actingAs($student)
            ->get('/admin/lms?tab=monitoring')
            ->assertForbidden();
    }

    public function test_admin_can_access_lms_activity_monitoring_and_see_inertia_view(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Clear existing courses to trigger seeder
        LmsCourse::query()->delete();
        ActivityLog::query()->delete();

        $this->actingAs($admin)
            ->get('/admin/lms?tab=monitoring')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Lms/Index')
                ->has('stats')
                ->has('activityLogs')
                ->has('filters')
            );
    }

    public function test_filters_and_search_can_be_applied_to_logs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $companyUser = User::factory()->create(['role' => 'perusahaan', 'name' => 'Tech Corp']);
        $studentUser = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Alex Student']);

        // Create specific logs
        ActivityLog::create([
            'user_id' => $companyUser->id,
            'role' => 'perusahaan',
            'category' => 'course',
            'action' => 'Membuat Course',
            'description' => 'Membuat course baru Laravel Avanzado',
        ]);

        ActivityLog::create([
            'user_id' => $studentUser->id,
            'role' => 'mahasiswa',
            'category' => 'lesson',
            'action' => 'Menyelesaikan Lesson',
            'description' => 'Menyelesaikan lesson Belajar Route',
        ]);

        // Filter by role = perusahaan
        $this->actingAs($admin)
            ->get('/admin/lms?tab=monitoring&role_activity=perusahaan')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->where('activityLogs.total', 1)
                ->where('activityLogs.data.0.action', 'Membuat Course')
            );

        // Search by query = Alex
        $this->actingAs($admin)
            ->get('/admin/lms?tab=monitoring&search_activity=Alex')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->where('activityLogs.total', 1)
                ->where('activityLogs.data.0.description', 'Menyelesaikan lesson Belajar Route')
            );
    }
}
