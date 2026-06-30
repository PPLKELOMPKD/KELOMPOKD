<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Internship;
use App\Models\Application;
use App\Models\Notification;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class StudentTrackApplicationTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * TC-01: Student sees empty state when no applications exist.
     */
    public function test_student_sees_empty_state_when_no_applications(): void
    {
        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Budi Santoso',
        ]);

        $this->browse(function (Browser $browser) use ($student) {
            $browser->loginAs($student)
                ->visit('/applications')
                ->waitForText('Belum Ada Lamaran')
                ->assertSee('Belum Ada Lamaran')
                ->assertSee('Mulai eksplorasi lowongan magang dan kirim lamaran pertama Anda sekarang!')
                ->assertSee('Cari Lowongan');
        });
    }

    /**
     * TC-02: Student can track applications in various statuses.
     */
    public function test_student_can_track_application_in_each_status(): void
    {
        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Budi Santoso',
        ]);

        $internshipA = Internship::query()->create([
            'company_id' => null,
            'title' => 'Backend Developer',
            'company_name' => 'PT A',
            'location' => 'Jakarta',
            'description' => 'Membangun REST API.',
            'requirements' => 'PHP',
            'work_type' => 'Magang',
            'deadline_at' => now()->addDays(5),
            'quota' => 2,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        $internshipB = Internship::query()->create([
            'company_id' => null,
            'title' => 'Frontend Developer',
            'company_name' => 'PT B',
            'location' => 'Jakarta',
            'description' => 'Membangun UI responsive.',
            'requirements' => 'Vue',
            'work_type' => 'Magang',
            'deadline_at' => now()->addDays(5),
            'quota' => 2,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        $internshipC = Internship::query()->create([
            'company_id' => null,
            'title' => 'Data Analyst',
            'company_name' => 'PT C',
            'location' => 'Jakarta',
            'description' => 'Menganalisis data pasar.',
            'requirements' => 'Python',
            'work_type' => 'Magang',
            'deadline_at' => now()->addDays(5),
            'quota' => 2,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        // Create applications with different statuses
        // 1. submitted -> displayed as 'Diajukan'
        Application::query()->create([
            'user_id' => $student->id,
            'internship_id' => $internshipA->id,
            'status' => 'submitted',
        ]);

        // 2. interview -> displayed as 'Wawancara'
        Application::query()->create([
            'user_id' => $student->id,
            'internship_id' => $internshipB->id,
            'status' => 'interview',
        ]);

        // 3. accepted -> displayed as 'Diterima'
        Application::query()->create([
            'user_id' => $student->id,
            'internship_id' => $internshipC->id,
            'status' => 'accepted',
        ]);

        $this->browse(function (Browser $browser) use ($student) {
            $browser->loginAs($student)
                ->visit('/applications')
                ->waitForText('Backend Developer')
                ->assertSee('Backend Developer')
                ->assertSee('PT A')
                ->assertSee('Diajukan')

                ->assertSee('Frontend Developer')
                ->assertSee('PT B')
                ->assertSee('Wawancara')

                ->assertSee('Data Analyst')
                ->assertSee('PT C')
                ->assertSee('Diterima');
        });
    }

    /**
     * TC-03: Student can click detail button from tracking page.
     */
    public function test_student_can_click_detail_from_tracking_page(): void
    {
        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Budi Santoso',
        ]);

        $internship = Internship::query()->create([
            'company_id' => null,
            'title' => 'Mobile Developer',
            'company_name' => 'PT Tech Indo',
            'location' => 'Jakarta',
            'description' => 'Membangun aplikasi Android/iOS.',
            'requirements' => 'Flutter',
            'work_type' => 'Magang',
            'deadline_at' => now()->addDays(5),
            'quota' => 2,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        Application::query()->create([
            'user_id' => $student->id,
            'internship_id' => $internship->id,
            'status' => 'submitted',
        ]);

        $this->browse(function (Browser $browser) use ($student, $internship) {
            $browser->loginAs($student)
                ->visit('/applications')
                ->waitForText('Mobile Developer')
                ->click('@track-detail-' . $internship->id)
                ->waitForText('Mobile Developer')
                ->assertSee('PT Tech Indo')
                ->assertSee('Sudah Melamar');
        });
    }

    /**
     * TC-04: Student dashboard updates active application count.
     */
    public function test_student_dashboard_updates_active_application_count(): void
    {
        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Budi Santoso',
        ]);

        $internship = Internship::query()->create([
            'company_id' => null,
            'title' => 'Mobile Developer',
            'company_name' => 'PT Tech Indo',
            'location' => 'Jakarta',
            'description' => 'Membangun aplikasi Android/iOS.',
            'requirements' => 'Flutter',
            'work_type' => 'Magang',
            'deadline_at' => now()->addDays(5),
            'quota' => 2,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        Application::query()->create([
            'user_id' => $student->id,
            'internship_id' => $internship->id,
            'status' => 'submitted',
        ]);

        $this->browse(function (Browser $browser) use ($student) {
            $browser->loginAs($student)
                ->visit('/dashboard') // Redirects to /peserta
                ->waitForText('LAMARAN AKTIF')
                ->assertSee('LAMARAN AKTIF')
                ->assertSee('1');
        });
    }

    /**
     * TC-05: Student can mark application notification as read.
     */
    public function test_student_can_mark_application_notification_as_read(): void
    {
        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Budi Santoso',
        ]);

        Notification::query()->create([
            'user_id' => $student->id,
            'title' => 'Application submitted successfully',
            'message' => 'Your application has been saved with the status: submitted.',
            'type' => 'application',
        ]);

        $this->browse(function (Browser $browser) use ($student) {
            $browser->loginAs($student)
                ->visit('/notifications')
                ->waitForText('Application submitted successfully')
                ->assertSee('Tandai Dibaca')
                ->click('@mark-read-button')
                ->waitForText('Sudah dibaca')
                ->assertSee('Sudah dibaca')
                ->assertDontSee('Tandai Dibaca');
        });
    }
}
