<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Internship;
use App\Models\Application;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminDataLamaranTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_admin_can_view_and_search_applications()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@example.com',
        ]);
        
        // Buat profile mahasiswa karena dipanggil di index.vue dan controller
        $student->mahasiswaProfile()->create([
            'nim' => '12345678',
            'department' => 'Informatika',
            'study_program' => 'Teknik Informatika',
            'gpa' => 3.75,
            'phone' => '081234567890',
            'location' => 'Jakarta',
            'bio' => 'Halo, saya Budi.',
        ]);

        // Buat lowongan (internship)
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

        // Buat lamaran (application)
        Application::query()->create([
            'user_id' => $student->id,
            'internship_id' => $internship->id,
            'status' => 'submitted',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/admin/applications')
                    ->waitForText('Data Lamaran') 
                    
                    ->assertPresent('table') 
                    ->pause(3000)
                    ->type('@search-input', 'Budi') 
                    ->pause(1500)
                    
                    ->assertSee('Budi Santoso');
        });
    }

    /**
     * TC-03: Ekspor Unduhan Dokumen Rekap
     */
    public function test_admin_can_export_csv_data()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Budi Santoso',
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

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/admin/applications')
                    ->waitForText('Ekspor CSV')
                    ->assertSee('Ekspor CSV')
                    ->pause(3000)
                    ->clickLink('Ekspor CSV')
                    ->pause(3000);
        });
    }

    /**
     * TC-04: Proteksi Keamanan Akses Rute (Forbidden)
     */
    public function test_mahasiswa_cannot_access_admin_application_data()
    {
        // 1. Siapkan akun Mahasiswa
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $this->browse(function (Browser $browser) use ($mahasiswa) {
            $browser->loginAs($mahasiswa)
                    ->visit('/admin/applications')
                    ->waitForText('403') 

                    ->assertSee('403')
                    ->assertSee('FORBIDDEN')
                    ->assertPathIs('/admin/applications'); 
        });
    }

    /**
     * TC-05: Hasil Pencarian Kosong
     */
    public function test_admin_search_empty_results()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/admin/applications')
                    ->waitForText('Data Lamaran')
                    ->type('@search-input', 'Xyzabc123')
                    ->pause(1500)
                    ->assertSee('Tidak ada data lamaran')
                    ->assertSee('Tidak ada hasil untuk "Xyzabc123"');
        });
    }

    /**
     * TC-06: Filter Tab Status Lamaran
     */
    public function test_admin_can_filter_by_status_tab()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $studentA = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Agus Budiman']);
        $studentA->mahasiswaProfile()->create(['nim' => '11111111', 'department' => 'IF', 'study_program' => 'IF', 'gpa' => 3.5]);

        $studentB = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Beni Saputra']);
        $studentB->mahasiswaProfile()->create(['nim' => '22222222', 'department' => 'IF', 'study_program' => 'IF', 'gpa' => 3.5]);

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

        // Student A: Wawancara (wawancara)
        Application::query()->create([
            'user_id' => $studentA->id,
            'internship_id' => $internship->id,
            'status' => 'wawancara',
        ]);

        // Student B: Submitted (submitted)
        Application::query()->create([
            'user_id' => $studentB->id,
            'internship_id' => $internship->id,
            'status' => 'submitted',
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::where('role', 'admin')->first())
                    ->visit('/admin/applications')
                    ->waitForText('Agus Budiman')
                    ->assertSee('Agus Budiman')
                    ->assertSee('Beni Saputra')
                    
                    // Click Wawancara tab
                    ->click('@app-tab-wawancara')
                    ->pause(1500)
                    ->assertSee('Agus Budiman')
                    ->assertDontSee('Beni Saputra')

                    // Click Submitted tab
                    ->click('@app-tab-submitted')
                    ->pause(1500)
                    ->assertSee('Beni Saputra')
                    ->assertDontSee('Agus Budiman');
        });
    }

    /**
     * TC-07: Navigasi Halaman Tabel (Pagination)
     */
    public function test_admin_can_navigate_pagination()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $internship = Internship::query()->create([
            'company_id' => null,
            'title' => 'Software Engineer Intern',
            'company_name' => 'PT SIKARA',
            'location' => 'Jakarta',
            'description' => 'Membangun aplikasi backend.',
            'requirements' => 'Menguasai Laravel.',
            'work_type' => 'Magang',
            'deadline_at' => now()->addDays(5),
            'quota' => 25,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        // Create 21 students - paginate(20) means 21 records will create page 2
        for ($i = 1; $i <= 21; $i++) {
            $student = User::factory()->create([
                'role' => 'mahasiswa',
                'name' => 'Siswa Uji ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'email' => 'siswauji' . $i . '@example.com',
            ]);
            $student->mahasiswaProfile()->create([
                'nim' => '9000000' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'department' => 'Informatika',
                'study_program' => 'Teknik Informatika',
                'gpa' => 3.5,
            ]);
            Application::query()->create([
                'user_id' => $student->id,
                'internship_id' => $internship->id,
                'status' => 'submitted',
            ]);
        }

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::where('role', 'admin')->first())
                    ->visit('/admin/applications')
                    ->waitForText('Data Lamaran')
                    // Verify table is rendered with 'Siswa Uji' data
                    ->assertPresent('table')
                    ->waitForText('Siswa Uji')
                    // 21 records with 20 per page means page 2 link should exist
                    ->assertSeeLink('2')
                    // Navigate to page 2
                    ->clickLink('2')
                    ->pause(1500)
                    // Page 2 should still show the page heading and table
                    ->assertSee('Data Lamaran')
                    ->assertPresent('table');
        });
    }
}
