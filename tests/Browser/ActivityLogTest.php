<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Internship;
use App\Models\PerusahaanProfile;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * ActivityLogTest
 *
 * Test suite untuk fitur Log Aktivitas (SKR-99).
 * Menggunakan database dev yang sudah berjalan (MySQL).
 * Setiap test membersihkan data yang dibuat setelah selesai.
 */
class ActivityLogTest extends DuskTestCase
{
    /** Daftar ID user dan log yang dibuat untuk cleanup. */
    protected array $createdUserIds = [];
    protected array $createdLogIds = [];
    protected array $createdInternshipIds = [];

    /**
     * Cleanup: hapus data yang dibuat selama test.
     */
    protected function tearDown(): void
    {
        if (!empty($this->createdLogIds)) {
            ActivityLog::whereIn('id', $this->createdLogIds)->delete();
        }
        if (!empty($this->createdInternshipIds)) {
            Internship::whereIn('id', $this->createdInternshipIds)->delete();
        }
        if (!empty($this->createdUserIds)) {
            User::withTrashed()->whereIn('id', $this->createdUserIds)->forceDelete();
        }
        parent::tearDown();
    }

    /**
     * Helper: Buat admin untuk testing.
     */
    protected function createAdmin(): User
    {
        $email = 'dusk_admin_log_' . time() . '@example.com';
        User::withTrashed()->where('email', $email)->forceDelete();

        $admin = User::factory()->admin()->create([
            'name'              => 'Admin Budi',
            'email'             => $email,
            'password'          => Hash::make('AdminPass123!'),
            'email_verified_at' => now(),
            'status'            => 'active',
        ]);
        $this->createdUserIds[] = $admin->id;
        return $admin;
    }

    /**
     * Helper: Buat perusahaan untuk testing.
     */
    protected function createCompanyUser(string $email): User
    {
        User::withTrashed()->where('email', $email)->forceDelete();

        $company = User::factory()->perusahaan()->create([
            'name'              => 'PT Logger Dusk',
            'email'             => $email,
            'password'          => Hash::make('Perusahaan123!'),
            'email_verified_at' => now(),
            'status'            => 'active',
        ]);
        $this->createdUserIds[] = $company->id;

        PerusahaanProfile::create([
            'user_id'             => $company->id,
            'industry'            => 'Teknologi Informasi',
            'location'            => 'Jakarta',
            'website'             => 'https://dusk-test.example.com',
            'description'         => 'Perusahaan testing Dusk automation.',
            'legal_document_path' => null,
        ]);

        return $company;
    }

    /**
     * TC-ACTLOG-001: Memuat Halaman Log Aktivitas Sistem Secara Dinamis dan Terurut
     */
    #[TestDox('TC-ACTLOG-001 Memuat Halaman Log Aktivitas Sistem Secara Dinamis dan Terurut')]
    public function test_tc_actlog_001_memuat_halaman_log_aktivitas_secara_dinamis_dan_terurut(): void
    {
        $admin = $this->createAdmin();

        $this->printHeader("TC-ACTLOG-001 Memuat Halaman Log Aktivitas Sistem Secara Dinamis dan Terurut");

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin);

            $this->runStep("1. [Portal Admin] Admin mengakses dashboard utama.", function () use ($browser) {
                $browser->visit('/admin/dashboard')
                        ->waitForLocation('/admin/dashboard', 15)
                        ->assertPathIs('/admin/dashboard');
            });

            $this->runStep("2. Admin mengklik menu \"Log Aktivitas\" pada sidebar Admin.", function () use ($browser) {
                $browser->visit('/admin/activity-logs')
                        ->waitForLocation('/admin/activity-logs', 15)
                        ->assertPathIs('/admin/activity-logs')
                        ->pause(3000)
                        ->screenshot('tc_actlog_001_loaded')
                        ->assertPresent('#app');
            });
        });
    }

    /**
     * TC-ACTLOG-002: Menyaring Data Log Aktivitas Berdasarkan Peran Pengguna
     */
    #[TestDox('TC-ACTLOG-002 Menyaring Data Log Aktivitas Berdasarkan Peran Pengguna')]
    public function test_tc_actlog_002_menyaring_data_log_aktivitas_berdasarkan_peran_pengguna(): void
    {
        $admin = $this->createAdmin();

        // Ensure we have at least one log with role 'perusahaan'
        $log = ActivityLog::create([
            'action'      => 'Test Perusahaan Action',
            'description' => 'Aktivitas oleh mitra perusahaan.',
            'category'    => 'lowongan',
            'user_id'     => null,
            'role'        => 'perusahaan',
            'ip_address'  => '127.0.0.1',
            'user_agent'  => 'Dusk Test',
        ]);
        $this->createdLogIds[] = $log->id;

        $this->printHeader("TC-ACTLOG-002 Menyaring Data Log Aktivitas Berdasarkan Peran Pengguna");

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/admin/activity-logs')
                    ->waitForLocation('/admin/activity-logs', 15);

            $this->runStep("1. [Portal Admin] Admin mengklik dropdown filter \"Pilih Peran\".", function () use ($browser) {
                $browser->assertPresent('select:first-of-type');
            });

            $this->runStep("2. Admin memilih opsi \"Perusahaan\".", function () use ($browser) {
                $browser->select('select:first-of-type', 'perusahaan')
                        ->pause(2000)
                        ->screenshot('tc_actlog_002_filtered_perusahaan')
                        ->assertSee('Perusahaan');
            });
        });
    }

    /**
     * TC-ACTLOG-003: Menyaring Log Aktivitas Menggunakan Kolom Pencarian Kata Kunci
     */
    #[TestDox('TC-ACTLOG-003 Menyaring Log Aktivitas Menggunakan Kolom Pencarian Kata Kunci')]
    public function test_tc_actlog_003_menyaring_log_aktivitas_menggunakan_kolom_pencarian(): void
    {
        $admin = $this->createAdmin();

        // Create a unique log to search for
        $uniqueKeyword = 'DuskSearchKey_' . time();
        $log = ActivityLog::create([
            'action'      => 'Verifikasi Khusus',
            'description' => 'Mencari kata kunci ' . $uniqueKeyword,
            'category'    => 'admin',
            'user_id'     => null,
            'role'        => 'admin',
            'ip_address'  => '127.0.0.1',
            'user_agent'  => 'Dusk Test',
        ]);
        $this->createdLogIds[] = $log->id;

        $this->printHeader("TC-ACTLOG-003 Menyaring Log Aktivitas Menggunakan Kolom Pencarian Kata Kunci");

        $this->browse(function (Browser $browser) use ($admin, $uniqueKeyword) {
            $browser->loginAs($admin)
                    ->visit('/admin/activity-logs')
                    ->waitForLocation('/admin/activity-logs', 15);

            $this->runStep("1. [Portal Admin] Admin memposisikan kursor pada kolom pencarian (search box).", function () use ($browser) {
                $browser->waitFor('input[placeholder="Cari nama, aksi, atau keterangan..."]', 5);
            });

            $this->runStep("2. Admin mengetikkan kata kunci pencarian (misal: \"Verifikasi\").", function () use ($browser, $uniqueKeyword) {
                $browser->type('input[placeholder="Cari nama, aksi, atau keterangan..."]', $uniqueKeyword)
                        ->pause(2500)
                        ->screenshot('tc_actlog_003_search_results')
                        ->assertSee($uniqueKeyword);
            });
        });
    }

    /**
     * TC-ACTLOG-004: Pencatatan Otomatis Aksi Penting Pengguna di Portal Perusahaan dan Penampilannya di Portal Admin (Multi-Portal)
     */
    #[TestDox('TC-ACTLOG-004 Pencatatan Otomatis Aksi Penting Pengguna di Portal Perusahaan dan Penampilannya di Portal Admin (Multi-Portal)')]
    public function test_tc_actlog_004_pencatatan_otomatis_aksi_penting_pengguna(): void
    {
        $admin = $this->createAdmin();
        $companyEmail = 'mitra@company.com';
        $company = $this->createCompanyUser($companyEmail);
        $internshipTitle = 'Android Dev';

        $this->printHeader("TC-ACTLOG-004 Pencatatan Otomatis Aksi Penting Pengguna di Portal Perusahaan (Multi-Portal)");

        $this->browse(function (Browser $companyBrowser, Browser $adminBrowser) use ($admin, $company, $internshipTitle) {
            // Establish login first
            $companyBrowser->visit('/login')
                           ->waitForLocation('/login', 15)
                           ->click('div.grid-cols-3 button:nth-child(2)') // Perusahaan
                           ->pause(500)
                           ->type('#email', $company->email)
                           ->type('#password', 'Perusahaan123!')
                           ->press('Masuk ke SIKARA')
                           ->pause(4000)
                           ->assertPathIs('/perusahaan/dashboard');

            $this->runStep("1. [Portal Perusahaan] Pengguna perusahaan membuat lowongan magang baru dengan judul \"Android Dev\".", function () use ($companyBrowser, $internshipTitle) {
                $companyBrowser->visit('/perusahaan/internships/create')
                               ->waitForLocation('/perusahaan/internships/create', 15)
                               ->type('#title', $internshipTitle)
                               ->type('#company_name', 'PT Logger Dusk')
                               ->type('#description', 'Lowongan magang Android.')
                               ->type('#requirements', 'Mengerti Kotlin.')
                               ->type('#salary', 'Rp 3.500.000')
                               ->type('#location', 'Jakarta')
                               ->clear('#quota')
                               ->type('#quota', '2');

                $companyBrowser->script([
                    "document.getElementById('deadline_at').value = '" . now()->addDays(10)->format('Y-m-d') . "'",
                    "document.getElementById('deadline_at').dispatchEvent(new Event('input'))"
                ]);
            });

            $this->runStep("2. Pengguna perusahaan mengklik tombol \"Terbitkan Lowongan\".", function () use ($companyBrowser, $internshipTitle) {
                $companyBrowser->screenshot('tc_actlog_004_company_form')
                               ->press('Simpan Lowongan') // Action button to publish/save lowongan
                               ->pause(4000);

                $internship = Internship::where('title', $internshipTitle)->first();
                if ($internship) {
                    $this->createdInternshipIds[] = $internship->id;
                }
            });

            $this->runStep("3. [Portal Admin] Admin mengakses menu \"Log Aktivitas\" pada sidebar Admin.", function () use ($adminBrowser, $admin, $company, $internshipTitle) {
                $adminBrowser->loginAs($admin)
                             ->visit('/admin/activity-logs')
                             ->waitForLocation('/admin/activity-logs', 15)
                             ->type('input[placeholder="Cari nama, aksi, atau keterangan..."]', $internshipTitle)
                             ->pause(2500)
                             ->screenshot('tc_actlog_004_log_recorded')
                             ->assertSee($company->email)
                             ->assertSee($internshipTitle);
            });
        });
    }

    /**
     * TC-ACTLOG-005: Pencegahan Akses Halaman Log Aktivitas untuk Akun Pengguna Non-Admin (Bypass URL)
     */
    #[TestDox('TC-ACTLOG-005 Pencegahan Akses Halaman Log Aktivitas untuk Akun Pengguna Non-Admin (Bypass URL)')]
    public function test_tc_actlog_005_pencegahan_akses_halaman_log_aktivitas(): void
    {
        $mhsEmail = 'mahasiswa@gmail.com';
        User::withTrashed()->where('email', $mhsEmail)->forceDelete();

        $student = User::factory()->mahasiswa()->create([
            'email'             => $mhsEmail,
            'password'          => Hash::make('password123'),
            'email_verified_at' => now(),
            'status'            => 'active',
        ]);
        $this->createdUserIds[] = $student->id;

        $this->printHeader("TC-ACTLOG-005 Pencegahan Akses Halaman Log Aktivitas untuk Akun Pengguna Non-Admin (Bypass URL)");

        $this->browse(function (Browser $browser) use ($student) {
            $browser->loginAs($student);

            $this->runStep("1. [Portal Mahasiswa] Pengguna membuka browser.", function () use ($browser) {
                $browser->visit('/dashboard')
                        ->pause(1000);
            });

            $this->runStep("2. Pengguna mengetik secara manual URL rute log admin ('/admin/activity-logs') pada address bar browser.", function () use ($browser) {
                $browser->visit('/admin/activity-logs')
                        ->pause(2000);
            });

            $this->runStep("3. Pengguna menekan Enter.", function () use ($browser) {
                $browser->screenshot('tc_actlog_005_forbidden')
                        ->assertSee('403');
            });

            $this->runStep("4. Pengguna mencoba mengirimkan GET request manual ke endpoint '/admin/activity-logs' via API client.", function () use ($browser) {
                $browser->script("
                    fetch('/admin/activity-logs', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json'
                        }
                    }).then(r => window.duskLogGetStatus = r.status);
                ");
                $browser->pause(1500);
                $status = $browser->script("return window.duskLogGetStatus;")[0];
                $this->assertEquals(403, $status, "GET request should be forbidden (403).");
            });
        });
    }
}
