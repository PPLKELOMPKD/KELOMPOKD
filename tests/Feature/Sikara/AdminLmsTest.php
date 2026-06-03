<?php

namespace Tests\Feature\Sikara;

use App\Models\LmsCourse;
use App\Models\LmsEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminLmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_users_cannot_access_lms_monitoring(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);

        $this->actingAs($student)
            ->get('/admin/lms')
            ->assertForbidden();
    }

    public function test_admin_can_access_lms_monitoring_and_see_inertia_view(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Seeder will run automatically on first index visit because of our controller check
        $this->actingAs($admin)
            ->get('/admin/lms')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Lms/Index'));
    }

    public function test_admin_can_suspend_and_activate_lms_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'mahasiswa', 'status' => 'active']);

        // Suspend
        $this->actingAs($admin)
            ->patch(route('admin.lms.users.suspend', $student->id))
            ->assertRedirect();

        $this->assertEquals('banned', $student->fresh()->status);

        // Activate
        $this->actingAs($admin)
            ->patch(route('admin.lms.users.activate', $student->id))
            ->assertRedirect();

        $this->assertEquals('active', $student->fresh()->status);
    }

    public function test_admin_can_soft_delete_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'mahasiswa']);

        $this->actingAs($admin)
            ->delete(route('admin.lms.users.destroy', $student->id))
            ->assertRedirect();

        $this->assertSoftDeleted('users', ['id' => $student->id]);
    }
}
