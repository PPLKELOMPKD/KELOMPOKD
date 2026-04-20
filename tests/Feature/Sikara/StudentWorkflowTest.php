<?php

namespace Tests\Feature\Sikara;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_update_profile_add_skill_apply_and_read_notifications(): void
    {
        $student = User::factory()->create([
            'role' => 'mahasiswa',
        ]);

        $this->actingAs($student)
            ->post('/profile', [
                'nim' => '1301213001',
                'department' => 'Teknik Informatika',
                'study_program' => 'S1 Informatika',
                'gpa' => '3.78',
                'phone' => '081234567890',
                'location' => 'Bandung',
                'bio' => 'Mahasiswa yang tertarik dengan pengembangan web.',
            ])
            ->assertRedirect(route('profile.show'));

        $this->assertDatabaseHas('mahasiswa_profiles', [
            'user_id' => $student->id,
            'nim' => '1301213001',
            'department' => 'Teknik Informatika',
        ]);

        $this->actingAs($student)
            ->post('/profile/skills', [
                'name' => 'Laravel',
                'proficiency' => 85,
            ])
            ->assertRedirect(route('profile.show'));

        $this->assertDatabaseHas('skills', [
            'name' => 'Laravel',
            'proficiency' => 85,
        ]);

        $internshipId = DB::table('internships')->insertGetId([
            'title' => 'Backend Engineer Intern',
            'company_name' => 'PT SIKARA Nusantara',
            'location' => 'Bandung',
            'requirements' => 'Menguasai dasar Laravel dan MySQL',
            'deadline_at' => now()->addDays(10),
            'is_published' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($student)
            ->post('/internship-apply', [
                'internship_id' => $internshipId,
            ])
            ->assertRedirect(route('internships.index'));

        $this->assertDatabaseHas('applications', [
            'internship_id' => $internshipId,
            'status' => 'submitted',
        ]);

        $notificationId = DB::table('notifications')->value('id');

        $this->actingAs($student)
            ->post("/notifications/{$notificationId}/read")
            ->assertRedirect(route('notifications.index'));

        $this->assertDatabaseMissing('notifications', [
            'id' => $notificationId,
            'read_at' => null,
        ]);
    }

    public function test_student_can_download_generated_cv_pdf(): void
    {
        $student = User::factory()->create([
            'role' => 'mahasiswa',
        ]);

        DB::table('mahasiswa_profiles')->insert([
            'user_id' => $student->id,
            'nim' => '1301213002',
            'department' => 'Sistem Informasi',
            'study_program' => 'S1 Sistem Informasi',
            'gpa' => '3.65',
            'phone' => '081298765432',
            'location' => 'Jakarta',
            'bio' => 'Mahasiswa aktif dan suka analisis data.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('skills')->insert([
            'user_id' => $student->id,
            'name' => 'Vue.js',
            'proficiency' => 80,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($student)->get('/cv/download');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
