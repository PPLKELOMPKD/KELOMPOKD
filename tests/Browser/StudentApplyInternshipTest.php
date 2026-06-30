<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Internship;
use App\Models\Application;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class StudentApplyInternshipTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * TC-01: Student can apply for internship successfully.
     */
    public function test_student_can_apply_for_internship_successfully(): void
    {
        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Adit Pratama',
        ]);

        $internship = Internship::query()->create([
            'company_id' => null,
            'title' => 'Software Engineer Intern',
            'company_name' => 'PT Makmur Sejahtera',
            'location' => 'Bandung',
            'description' => 'Membangun API dan modul menggunakan PHP & Laravel.',
            'requirements' => 'Menguasai PHP & Laravel.',
            'work_type' => 'Magang',
            'deadline_at' => now()->addDays(10),
            'quota' => 3,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        $this->browse(function (Browser $browser) use ($student, $internship) {
            $browser->loginAs($student)
                ->visit('/internships')
                ->waitForText('Software Engineer Intern')
                ->assertSee('PT MAKMUR SEJAHTERA')
                ->click('@detail-' . $internship->id)
                ->waitForText('Deskripsi Pekerjaan')
                ->assertSee('Lamar Sekarang')
                ->click('@apply-button')
                ->waitForLocation('/internships')
                ->waitForText('Application sent successfully!')
                ->assertSee('Application sent successfully!')
                // Verify the button on index is changed to "Sudah Dilamar"
                ->assertSee('Sudah Dilamar')
                // Revisit details page
                ->visit('/internships/' . $internship->id)
                ->waitForLocation('/internships/' . $internship->id)
                ->waitForText('Sudah Melamar')
                ->assertSee('Sudah Melamar')
                ->assertAttribute('@apply-button', 'disabled', 'true');
        });
    }

    /**
     * TC-02: Student cannot double apply.
     */
    public function test_student_cannot_double_apply(): void
    {
        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Budi Santoso',
        ]);

        $internship = Internship::query()->create([
            'company_id' => null,
            'title' => 'QA Engineer Intern',
            'company_name' => 'PT Sentosa Raya',
            'location' => 'Surabaya',
            'description' => 'Melakukan pengujian fitur aplikasi.',
            'requirements' => 'Menguasai QA Automation.',
            'work_type' => 'Magang',
            'deadline_at' => now()->addDays(5),
            'quota' => 2,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        // Create an existing application
        Application::query()->create([
            'user_id' => $student->id,
            'internship_id' => $internship->id,
            'status' => 'submitted',
        ]);

        $this->browse(function (Browser $browser) use ($student, $internship) {
            $browser->loginAs($student)
                ->visit('/internships/' . $internship->id)
                ->waitForLocation('/internships/' . $internship->id)
                ->waitForText('Sudah Melamar')
                ->assertSee('Sudah Melamar')
                ->assertAttribute('@apply-button', 'disabled', 'true');
        });
    }

    /**
     * TC-03: Student cannot apply for expired internship.
     */
    public function test_student_cannot_apply_for_expired_internship(): void
    {
        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Cahyo Hidayat',
        ]);

        $internship = Internship::query()->create([
            'company_id' => null,
            'title' => 'UI/UX Designer Intern',
            'company_name' => 'PT Kreatif Nusantara',
            'location' => 'Yogyakarta',
            'description' => 'Merancang user experience web dan mobile.',
            'requirements' => 'Figma, prototyping.',
            'work_type' => 'Magang',
            'deadline_at' => now()->subDays(2),
            'quota' => 2,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        $this->browse(function (Browser $browser) use ($student, $internship) {
            $browser->loginAs($student)
                ->visit('/internships/' . $internship->id)
                ->waitForLocation('/internships/' . $internship->id)
                ->waitForText('Batas waktu pendaftaran lowongan ini telah berakhir. Anda tidak dapat melamar.')
                ->assertSee('Batas waktu pendaftaran lowongan ini telah berakhir. Anda tidak dapat melamar.')
                ->assertSee('Pendaftaran Ditutup')
                ->assertAttribute('@apply-button', 'disabled', 'true');
        });
    }

    /**
     * TC-04: Guest user redirected to login when trying to access internship details.
     */
    public function test_guest_redirected_to_login_when_applying(): void
    {
        $internship = Internship::query()->create([
            'company_id' => null,
            'title' => 'DevOps Intern',
            'company_name' => 'PT Cloud Raya',
            'location' => 'Jakarta',
            'description' => 'Mengelola infrastruktur cloud.',
            'requirements' => 'Docker, Git.',
            'work_type' => 'Magang',
            'deadline_at' => now()->addDays(10),
            'quota' => 1,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        $this->browse(function (Browser $browser) use ($internship) {
            $browser->visit('/internships/' . $internship->id)
                ->waitForLocation('/login')
                ->assertPathIs('/login')
                ->assertSee('Email');
        });
    }

    /**
     * TC-05: Student receives notification after applying.
     */
    public function test_student_receives_notification_after_applying(): void
    {
        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Faisal Rahman',
        ]);

        $internship = Internship::query()->create([
            'company_id' => null,
            'title' => 'Database Engineer Intern',
            'company_name' => 'PT Data Solusindo',
            'location' => 'Bandung',
            'description' => 'Mengelola skema database.',
            'requirements' => 'SQL.',
            'work_type' => 'Magang',
            'deadline_at' => now()->addDays(10),
            'quota' => 1,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        $this->browse(function (Browser $browser) use ($student, $internship) {
            $browser->loginAs($student)
                ->visit('/internships/' . $internship->id)
                ->waitForText('Deskripsi Pekerjaan')
                ->click('@apply-button')
                ->waitForLocation('/internships')
                ->waitForText('Application sent successfully!')
                ->visit('/notifications')
                ->waitForText('Application submitted successfully')
                ->assertSee('Application submitted successfully')
                ->assertSee('Your application has been saved with the status: submitted.');
        });
    }
}
