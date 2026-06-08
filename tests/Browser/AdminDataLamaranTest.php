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
    use DatabaseMigrations; // Runs migrations in the isolated test database

    /**
     * TC-01 & TC-02: Render Tabel dan Fitur Pencarian Global
     */
    public function test_admin_can_view_and_search_applications()
    {
        // 1. Siapkan data Admin dan Mahasiswa target (Budi Santoso)
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
                    ->visit('/admin/applications') // Corrected URL
                    ->waitForText('Data Lamaran') // Wait for Vue to mount
                    
                    // Validasi TC-01: Tabel berhasil dimuat
                    ->assertPresent('table') 
                    
                    // Validasi TC-02: Input kata kunci "Budi" di kolom filter
                    ->type('@search-input', 'Budi') 
                    ->pause(1500) // Tunggu Inertia/Vue melakukan filter request
                    
                    // Memastikan sistem menampilkan entri atas nama Budi Santoso
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
                    ->waitForText('Ekspor CSV') // Wait for Vue to mount
                    // Memastikan tombol "Ekspor CSV" ada di layar
                    ->assertSee('Ekspor CSV')
                    // Melakukan klik pada tombol tersebut
                    ->clickLink('Ekspor CSV');
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
                    ->waitForText('403') // Wait for 403 page
                    
                    // Memastikan Middleware mencegat dan mengembalikan halaman 403
                    ->assertSee('403')
                    ->assertSee('FORBIDDEN') // Matches uppercase visible text
                    ->assertPathIs('/admin/applications'); // Path remains unchanged
        });
    }
}
