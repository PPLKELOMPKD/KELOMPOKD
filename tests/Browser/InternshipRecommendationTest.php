<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Internship;
use App\Models\Application;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class InternshipRecommendationTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * TC-01: Tampil Rekomendasi Berhasil
     * Precondition: Mahasiswa memiliki 2 skill (misal: Vue.js, Laravel). Ada lowongan yang mensyaratkan skill tersebut.
     * Expected: Sistem memunculkan section "Rekomendasi Teratas Untukmu" berisi lowongan yang relevan di urutan teratas.
     */
    public function test_tc01_tampil_rekomendasi_berhasil()
    {
        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Budi Santoso',
        ]);

        // Tambahkan 2 skill ke mahasiswa
        $student->skills()->create(['name' => 'Vue.js', 'proficiency' => 4]);
        $student->skills()->create(['name' => 'Laravel', 'proficiency' => 4]);

        // Buat lowongan magang yang cocok
        $internship = Internship::query()->create([
            'company_id' => null,
            'title' => 'Frontend Engineer Vue & Laravel',
            'company_name' => 'PT SIKARA',
            'location' => 'Jakarta',
            'description' => 'Membangun aplikasi web dengan Vue.js dan Laravel.',
            'requirements' => 'Keahlian: Vue.js, Laravel.',
            'work_type' => 'Magang',
            'deadline_at' => now()->addDays(5),
            'quota' => 2,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        $this->browse(function (Browser $browser) use ($student) {
            $browser->loginAs($student)
                    ->visit('/lowongan')
                    ->waitForText('Rekomendasi Teratas Untukmu')
                    ->assertSee('Rekomendasi Teratas Untukmu')
                    ->assertSee('COCOK (2 SKILL)')
                    ->assertSee('Frontend Engineer Vue & Laravel');
        });
    }

    /**
     * TC-02: State Profil Skill Kosong
     * Precondition: Akun mahasiswa active, namun belum pernah menambahkan skill di halaman profil.
     * Expected: Sistem tidak menampilkan daftar rekomendasi, melainkan memunculkan alert/banner ajakan untuk melengkapi skill.
     */
    public function test_tc02_state_profil_skill_kosong()
    {
        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Budi Santoso',
        ]);

        // Mahasiswa tidak memiliki skill apapun

        // Buat lowongan magang acak
        $internship = Internship::query()->create([
            'company_id' => null,
            'title' => 'Frontend Developer React',
            'company_name' => 'PT SIKARA',
            'location' => 'Jakarta',
            'description' => 'Membangun frontend aplikasi.',
            'requirements' => 'Keahlian: React.',
            'work_type' => 'Magang',
            'deadline_at' => now()->addDays(5),
            'quota' => 2,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        $this->browse(function (Browser $browser) use ($student) {
            $browser->loginAs($student)
                    ->visit('/lowongan')
                    ->waitForText('Dapatkan Rekomendasi Akurat!')
                    ->assertSee('Dapatkan Rekomendasi Akurat!')
                    ->assertSee('Lengkapi profil skill Anda agar sistem dapat merekomendasikan lowongan yang paling cocok dengan keahlian Anda.')
                    ->assertDontSee('Rekomendasi Teratas Untukmu');
        });
    }

    /**
     * TC-03: Validasi Sorting Akurasi
     * Precondition: Mahasiswa memiliki skill A, B, C. Ada Lowongan X (Butuh A, B, C) dan Lowongan Y (Butuh A saja).
     * Expected: Lowongan X muncul di urutan lebih tinggi daripada Lowongan Y pada daftar rekomendasi.
     */
    public function test_tc03_validasi_sorting_akurasi()
    {
        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Budi Santoso',
        ]);

        // Tambahkan 3 skill (Vue.js, Laravel, PHP) ke mahasiswa
        $student->skills()->create(['name' => 'Vue.js', 'proficiency' => 4]); // Skill A
        $student->skills()->create(['name' => 'Laravel', 'proficiency' => 4]); // Skill B
        $student->skills()->create(['name' => 'PHP', 'proficiency' => 4]); // Skill C

        // Buat Lowongan X (butuh A, B, C) -> match 3
        $internshipX = Internship::query()->create([
            'company_id' => null,
            'title' => 'Fullstack Developer Web (Vue.js, Laravel, PHP)',
            'company_name' => 'PT SIKARA X',
            'location' => 'Jakarta',
            'description' => 'Membangun aplikasi backend dan frontend.',
            'requirements' => 'Mampu menggunakan Vue.js, Laravel, PHP secara bersinergi.',
            'work_type' => 'Magang',
            'deadline_at' => now()->addDays(5),
            'quota' => 2,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        // Buat Lowongan Y (butuh A saja) -> match 1
        $internshipY = Internship::query()->create([
            'company_id' => null,
            'title' => 'Frontend Junior (Vue.js)',
            'company_name' => 'PT SIKARA Y',
            'location' => 'Jakarta',
            'description' => 'Mendesain tampilan dengan Vue.',
            'requirements' => 'Keahlian dasar: Vue.js saja.',
            'work_type' => 'Magang',
            'deadline_at' => now()->addDays(5),
            'quota' => 2,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        $this->browse(function (Browser $browser) use ($student) {
            $browser->loginAs($student)
                    ->visit('/lowongan')
                    ->waitForText('Rekomendasi Teratas Untukmu')
                    // Asersi Lowongan X (kecocokan 3 skill) berada di urutan pertama (anak pertama dari grid)
                    ->assertSeeIn('[dusk="recommendation-grid"] > div:nth-child(1) h3', 'Fullstack Developer Web (Vue.js, Laravel, PHP)')
                    // Asersi Lowongan Y (kecocokan 1 skill) berada di urutan kedua (anak kedua dari grid)
                    ->assertSeeIn('[dusk="recommendation-grid"] > div:nth-child(2) h3', 'Frontend Junior (Vue.js)');
        });
    }

    /**
     * TC-04: Sembunyikan Lowongan yang Sudah Dilamar
     */
    public function test_tc04_recommendations_exclude_applied()
    {
        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Budi Santoso',
        ]);

        $student->skills()->create(['name' => 'PHP', 'proficiency' => 4]);

        $internshipA = Internship::query()->create([
            'company_id' => null,
            'title' => 'Backend Developer PHP A',
            'company_name' => 'PT A',
            'location' => 'Jakarta',
            'description' => 'Membangun REST API.',
            'requirements' => 'Keahlian: PHP.',
            'work_type' => 'Magang',
            'deadline_at' => now()->addDays(5),
            'quota' => 2,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        $internshipB = Internship::query()->create([
            'company_id' => null,
            'title' => 'Backend Developer PHP B',
            'company_name' => 'PT B',
            'location' => 'Jakarta',
            'description' => 'Membangun REST API.',
            'requirements' => 'Keahlian: PHP.',
            'work_type' => 'Magang',
            'deadline_at' => now()->addDays(5),
            'quota' => 2,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        // Student has already applied for Internship A
        Application::query()->create([
            'user_id' => $student->id,
            'internship_id' => $internshipA->id,
            'status' => 'submitted',
        ]);

        $this->browse(function (Browser $browser) use ($student) {
            $browser->loginAs($student)
                    ->visit('/lowongan')
                    ->waitForText('Rekomendasi Teratas Untukmu')
                    // Backend PHP B should appear in recommendations (not applied)
                    ->assertSeeIn('[dusk="recommendation-grid"]', 'Backend Developer PHP B')
                    // Backend PHP A should NOT appear in recommendations (already applied)
                    ->assertDontSeeIn('[dusk="recommendation-grid"]', 'Backend Developer PHP A');
        });
    }

    /**
     * TC-05: Sembunyikan Rekomendasi dari Role Non-Mahasiswa
     */
    public function test_tc05_recommendations_hidden_for_non_mahasiswa()
    {
        $perusahaan = User::factory()->create([
            'role' => 'perusahaan',
            'name' => 'PT Makmur',
        ]);

        // Add an internship so catalog shows
        Internship::query()->create([
            'company_id' => null,
            'title' => 'PHP Developer Intern',
            'company_name' => 'PT Nusantara',
            'location' => 'Jakarta',
            'description' => 'Membangun REST API.',
            'requirements' => 'PHP.',
            'work_type' => 'Magang',
            'deadline_at' => now()->addDays(5),
            'quota' => 2,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        $this->browse(function (Browser $browser) use ($perusahaan) {
            $browser->loginAs($perusahaan)
                    ->visit('/lowongan')
                    ->waitForText('Temukan Pekerjaan Impianmu') // Main page heading always visible
                    // For non-mahasiswa, the recommendations results section ('Rekomendasi Teratas Untukmu')
                    // should NOT appear since backend only builds recommendations for mahasiswa role
                    ->assertDontSee('Rekomendasi Teratas Untukmu');
        });
    }

    /**
     * TC-06: Klik Kartu Rekomendasi Mengarah ke Detail Lowongan
     */
    public function test_tc06_recommendation_click_redirects_to_detail()
    {
        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Budi Santoso',
        ]);

        $student->skills()->create(['name' => 'PHP', 'proficiency' => 4]);

        $internship = Internship::query()->create([
            'company_id' => null,
            'title' => 'Backend Developer PHP',
            'company_name' => 'PT SIKARA',
            'location' => 'Jakarta',
            'description' => 'Membangun REST API.',
            'requirements' => 'PHP.',
            'work_type' => 'Magang',
            'deadline_at' => now()->addDays(5),
            'quota' => 2,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        $this->browse(function (Browser $browser) use ($student, $internship) {
            $browser->loginAs($student)
                    ->visit('/lowongan')
                    ->waitForText('Rekomendasi Teratas Untukmu')
                    ->click('@rec-detail-' . $internship->id)
                    ->waitForText('Deskripsi Pekerjaan')
                    ->assertPathIs('/internships/' . $internship->id)
                    ->assertSee('Backend Developer PHP');
        });
    }

    /**
     * TC-07: Redirect Button Lengkapi Skill ke Halaman Profil
     */
    public function test_tc07_empty_skills_banner_redirects_to_profile()
    {
        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Budi Santoso',
        ]);

        $this->browse(function (Browser $browser) use ($student) {
            $browser->loginAs($student)
                    ->visit('/lowongan')
                    ->waitForText('Dapatkan Rekomendasi Akurat!')
                    ->assertPresent('[dusk="lengkapi-skill-button"]')
                    ->scrollTo('[dusk="lengkapi-skill-button"]')
                    ->waitFor('[dusk="lengkapi-skill-button"]')
                    // Use tap() to run JS click inline without breaking chain
                    ->tap(function ($browser) {
                        $browser->script('document.querySelector("[dusk=\'lengkapi-skill-button\']").click()');
                    })
                    ->waitForLocation('/profile')
                    ->assertPathIs('/profile');
        });
    }
}
