<?php

namespace Tests\Feature\Sikara;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia as Assert;
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
            'description' => 'Lowongan backend engineer intern.',
            'requirements' => 'Menguasai dasar Laravel dan MySQL',
            'work_type' => 'Magang',
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

    public function test_student_can_upload_profile_photo(): void
    {
        Storage::fake('public');

        $student = User::factory()->create([
            'role' => 'mahasiswa',
        ]);

        $photoPath = tempnam(sys_get_temp_dir(), 'avatar').'.png';
        file_put_contents($photoPath, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='
        ));

        $this->actingAs($student)
            ->post('/profile', [
                'nim' => '1301213099',
                'department' => 'Teknik Informatika',
                'study_program' => 'S1 Informatika',
                'gpa' => '3.81',
                'phone' => '081234567899',
                'location' => 'Jakarta',
                'bio' => 'Mahasiswa yang siap magang.',
                'photo' => new UploadedFile($photoPath, 'avatar.png', 'image/png', null, true),
            ])
            ->assertRedirect(route('profile.show'));

        $profile = $student->fresh()->mahasiswaProfile;

        $this->assertNotNull($profile->photo_path);
        $this->assertStringStartsWith('profile-photos/', $profile->photo_path);
        Storage::disk('public')->assertExists($profile->photo_path);
    }

    public function test_lowongan_filter_options_come_from_published_internship_database_values(): void
    {
        DB::table('internships')->insert([
            [
                'title' => 'Frontend Engineer Intern',
                'company_name' => 'PT SIKARA Nusantara',
                'location' => 'Kota Administrasi Jakarta Selatan',
                'work_type' => 'Magang WFH',
                'description' => 'Magang frontend dengan sistem kerja WFH.',
                'requirements' => 'Vue.js',
                'deadline_at' => now()->addDays(10),
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Backend Engineer Intern',
                'company_name' => 'PT SIKARA Nusantara',
                'location' => 'Bandung',
                'work_type' => 'Magang WFO',
                'description' => 'Magang backend dengan sistem kerja WFO.',
                'requirements' => 'Laravel',
                'deadline_at' => now()->addDays(12),
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Hidden Role',
                'company_name' => 'PT Tersembunyi',
                'location' => 'Kota Rahasia',
                'work_type' => 'Full-time',
                'description' => 'Lowongan tidak dipublikasikan.',
                'requirements' => 'Rahasia',
                'deadline_at' => now()->addDays(12),
                'is_published' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->get(route('lowongan'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Features/Lowongan')
                ->where('filterOptions.locations', [
                    'Kota Administrasi Jakarta Selatan',
                    'Bandung',
                ])
                ->where('filterOptions.workTypes', [
                    'Magang WFO',
                    'Magang WFH',
                ])
            );
    }
}
