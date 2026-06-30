<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Internship;
use App\Models\PerusahaanProfile;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * UserManagementTest
 *
 * Test suite untuk fitur Manajemen Pengguna (SKR-29).
 * Menggunakan database dev yang sudah berjalan (MySQL).
 * Setiap test membersihkan data yang dibuat setelah selesai.
 */
class UserManagementTest extends DuskTestCase
{
    /** Daftar ID user dan lowongan yang dibuat untuk cleanup. */
    protected array $createdUserIds = [];
    protected array $createdInternshipIds = [];
    protected array $createdLogIds = [];

    /**
     * Cleanup: hapus data yang dibuat selama test (termasuk soft-deleted).
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
        $email = 'dusk_admin_um_' . time() . '@example.com';
        User::withTrashed()->where('email', $email)->forceDelete();

        $admin = User::factory()->admin()->create([
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
    protected function createCompanyUser(string $email, string $status = 'active'): User
    {
        User::withTrashed()->where('email', $email)->forceDelete();

        $company = User::factory()->perusahaan()->create([
            'name'              => 'PT Mitra Test Dusk',
            'email'             => $email,
            'password'          => Hash::make('password123'),
            'email_verified_at' => now(),
            'status'            => $status,
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
     * TC-USERMGMT-001: Blokir Akun Pengguna (Banned) & Konfirmasi Pencegahan Akses (Multi-Portal)
     */
    #[TestDox('TC-USERMGMT-001 Blokir Akun Pengguna (Banned) & Konfirmasi Pencegahan Akses (Multi-Portal)')]
    public function test_tc_usermgmt_001_blokir_akun_pengguna_banned_dan_konfirmasi_pencegahan_akses(): void
    {
        $admin = $this->createAdmin();
        $companyEmail = 'mitra@company.com';
        $company = $this->createCompanyUser($companyEmail, 'active');

        $this->printHeader("TC-USERMGMT-001 Blokir Akun Pengguna (Banned) & Konfirmasi Pencegahan Akses (Multi-Portal)");

        $this->browse(function (Browser $adminBrowser, Browser $companyBrowser) use ($admin, $company) {
            // Establish an active session for the company first
            $companyBrowser->visit('/login')
                           ->waitForLocation('/login', 15)
                           ->waitForText('Selamat Datang')
                           ->click('div.grid-cols-3 button:nth-child(2)') // Perusahaan
                           ->pause(500)
                           ->type('#email', $company->email)
                           ->type('#password', 'password123')
                           ->press('Masuk ke SIKARA')
                           ->pause(4000)
                           ->assertPathIs('/perusahaan/dashboard');

            // Admin logs in and blocks the company
            $adminBrowser->loginAs($admin);

            $this->runStep("1. [Portal Admin] Admin mengakses dashboard utama.", function () use ($adminBrowser) {
                $adminBrowser->visit('/admin/dashboard')
                             ->waitForLocation('/admin/dashboard', 15)
                             ->assertPathIs('/admin/dashboard');
            });

            $this->runStep("2. Admin mengklik menu \"Manajemen Pengguna\" pada sidebar.", function () use ($adminBrowser) {
                $adminBrowser->visit('/admin/users')
                             ->waitForLocation('/admin/users', 15)
                             ->assertPathIs('/admin/users');
            });

            $this->runStep("3. Admin mencari email target 'mitra@company.com' pada kolom pencarian.", function () use ($adminBrowser, $company) {
                $adminBrowser->type('input[placeholder="Cari nama atau email pengguna…"]', $company->email)
                             ->pause(2500)
                             ->assertSee($company->name);
            });

            $this->runStep("4. Admin menekan tombol \"Blokir Akun\".", function () use ($adminBrowser) {
                $adminBrowser->click('tbody tr:first-child button.border-red-200') // Banned button
                             ->pause(1500);
            });

            $this->runStep("5. Admin mengisi alasan pemblokiran pada modal popup lalu mengklik \"Ya, Blokir Akun\".", function () use ($adminBrowser) {
                $adminBrowser->type('textarea[placeholder="Catatan alasan perubahan status ini…"]', 'Melanggar Ketentuan')
                             ->press('Blokir (Banned) Akun')
                             ->pause(3000)
                             ->screenshot('tc_usermgmt_001_blocked');
            });

            $this->runStep("6. [Portal Perusahaan] Pengguna perusahaan mencoba melakukan refresh halaman pada sesi aktifnya di browser.", function () use ($companyBrowser) {
                $companyBrowser->refresh()
                               ->pause(2000)
                               ->screenshot('tc_usermgmt_001_company_force_logged_out');
                // Sesi aktif terputus seketika, diarah ke login
                $currentUrl = $companyBrowser->driver->getCurrentURL();
                $this->assertTrue(str_contains($currentUrl, '/login'), "Sesi aktif seharusnya terputus seketika. URL: $currentUrl");
            });

            $this->runStep("7. Pengguna mencoba masuk kembali melalui halaman login utama dengan email dan password yang benar.", function () use ($companyBrowser, $company) {
                $companyBrowser->assertPathIs('/login')
                               ->click('div.grid-cols-3 button:nth-child(2)') // Perusahaan
                               ->pause(1000)
                               ->type('#email', $company->email)
                               ->type('#password', 'password123');
            });

            $this->runStep("8. Pengguna menekan tombol \"Masuk ke SIKARA\".", function () use ($companyBrowser) {
                $companyBrowser->press('Masuk ke SIKARA')
                               ->pause(5000)
                               ->screenshot('tc_usermgmt_001_company_login_denied');

                $pageHtml = $companyBrowser->driver->getPageSource();
                file_put_contents(base_path('tc_usermgmt_001_page_source.html'), $pageHtml);
                file_put_contents(base_path('tc_usermgmt_001_console.log'), json_encode($companyBrowser->driver->manage()->getLog('browser'), JSON_PRETTY_PRINT));
                $this->assertTrue(
                    str_contains(strtolower($pageHtml), 'blocked') || 
                    str_contains(strtolower($pageHtml), 'blokir') ||
                    str_contains(strtolower($pageHtml), 'banned') ||
                    str_contains(strtolower($pageHtml), 'pelanggaran'),
                    "Tidak menemukan pesan akun diblokir (blocked/blokir/banned/pelanggaran) di halaman."
                );
            });
        });
    }

    /**
     * TC-USERMGMT-002: Verifikasi Sukses Mitra Perusahaan (Multi-Portal)
     */
    #[TestDox('TC-USERMGMT-002 Verifikasi Sukses Mitra Perusahaan (Multi-Portal)')]
    public function test_tc_usermgmt_002_verifikasi_sukses_mitra_perusahaan(): void
    {
        $admin = $this->createAdmin();
        $companyEmail = 'baru@mitra.com';
        $company = $this->createCompanyUser($companyEmail, 'inactive');

        $this->printHeader("TC-USERMGMT-002 Verifikasi Sukses Mitra Perusahaan (Multi-Portal)");

        $this->browse(function (Browser $adminBrowser, Browser $companyBrowser) use ($admin, $company) {
            $adminBrowser->loginAs($admin);

            $this->runStep("1. [Portal Admin] Admin mengakses menu \"Verifikasi Perusahaan\" pada sidebar.", function () use ($adminBrowser) {
                $adminBrowser->visit('/admin/verifications')
                             ->waitForLocation('/admin/verifications', 15)
                             ->assertPathIs('/admin/verifications');
            });

            $this->runStep("2. Admin meninjau data profil dan mengunduh berkas SIUP/NIB perusahaan 'baru@mitra.com'.", function () use ($adminBrowser, $company) {
                $adminBrowser->visit('/admin/verifications/' . $company->id)
                             ->waitForLocation('/admin/verifications/' . $company->id, 15)
                             ->assertPathIs('/admin/verifications/' . $company->id);
            });

            $this->runStep("3. Admin mengklik tombol \"Verifikasi & Setujui Mitra\".", function () use ($adminBrowser) {
                $adminBrowser->press('Verifikasi & Setujui Mitra')
                             ->pause(1500)
                             ->press('Konfirmasi & Simpan')
                             ->pause(3000)
                             ->screenshot('tc_usermgmt_002_approved');
            });

            $this->runStep("4. [Portal Perusahaan] Perwakilan perusahaan membuka halaman login portal SIKARA.", function () use ($companyBrowser) {
                $companyBrowser->visit('/login')
                               ->waitForLocation('/login', 15)
                               ->assertPathIs('/login');
            });

            $this->runStep("5. Pengguna memasukkan kredensial login (email dan password) akun yang telah disetujui.", function () use ($companyBrowser, $company) {
                $companyBrowser->click('div.grid-cols-3 button:nth-child(2)') // Perusahaan
                               ->pause(500)
                               ->type('#email', $company->email)
                               ->type('#password', 'password123');
            });

            $this->runStep("6. Pengguna mengklik tombol \"Masuk ke SIKARA\".", function () use ($companyBrowser) {
                $companyBrowser->press('Masuk ke SIKARA')
                               ->pause(4000)
                               ->screenshot('tc_usermgmt_002_company_dashboard')
                               ->assertPathIs('/perusahaan/dashboard');
            });

            $this->runStep("7. Setelah berhasil diarahkan ke dashboard perusahaan, pengguna mencoba mengklik menu \"Buat Lowongan Magang\".", function () use ($companyBrowser) {
                $companyBrowser->visit('/perusahaan/internships/create')
                               ->waitForLocation('/perusahaan/internships/create', 15)
                               ->assertPathIs('/perusahaan/internships/create')
                               ->screenshot('tc_usermgmt_002_create_internship');
            });
        });
    }

    /**
     * TC-USERMGMT-003: Cegah Posting Lowongan Perusahaan Belum Terverifikasi (Bypass URL)
     */
    #[TestDox('TC-USERMGMT-003 Cegah Posting Lowongan Perusahaan Belum Terverifikasi (Bypass URL)')]
    public function test_tc_usermgmt_003_cegah_posting_lowongan_perusahaan_belum_terverifikasi_bypass_url(): void
    {
        $companyEmail = 'pending@mitra.com';
        $company = $this->createCompanyUser($companyEmail, 'inactive');

        $this->printHeader("TC-USERMGMT-003 Cegah Posting Lowongan Perusahaan Belum Terverifikasi (Bypass URL)");

        $this->browse(function (Browser $browser) use ($company) {
            $browser->visit('/login')
                    ->waitForLocation('/login', 15)
                    ->click('div.grid-cols-3 button:nth-child(2)') // Perusahaan
                    ->pause(500)
                    ->type('#email', $company->email)
                    ->type('#password', 'password123')
                    ->press('Masuk ke SIKARA')
                    ->pause(4000);

            $this->runStep("1. [Portal Perusahaan] Pengguna perusahaan mengetikkan URL pembuatan lowongan ('/perusahaan/internships/create') secara manual pada address bar browser.", function () use ($browser) {
                $browser->visit('/perusahaan/internships/create')
                        ->pause(2000);
            });

            $this->runStep("2. Pengguna menekan Enter.", function () use ($browser) {
                // Should redirect to pending-verification
                $browser->screenshot('tc_usermgmt_003_redirected')
                        ->assertPathIs('/perusahaan/pending-verification');
            });

            $this->runStep("3. Pengguna mencoba menembak API store lowongan magang secara langsung menggunakan POST request (misal lewat REST Client) ke endpoint '/perusahaan/internships'.", function () use ($browser) {
                $browser->script("
                    fetch('/perusahaan/internships', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            title: 'Android Developer Hack',
                            company_name: 'PT Hack',
                            description: 'Hack description',
                            requirements: 'Hack requirements',
                            salary: 'Rp 5.000.000',
                            location: 'Jakarta',
                            quota: 5,
                            deadline_at: '2026-12-31'
                        })
                    }).then(r => window.duskPostStatus = r.status);
                ");
                $browser->pause(1500);
                $status = $browser->script("return window.duskPostStatus;")[0];
                $this->assertEquals(403, $status, "POST request should be forbidden (403).");
            });
        });
    }

    /**
     * TC-USERMGMT-004: Penyembunyian Otomatis Lowongan Kerja Saat Mitra Dibanned (Multi-Portal)
     */
    #[TestDox('TC-USERMGMT-004 Penyembunyian Otomatis Lowongan Kerja Saat Mitra Dibanned (Multi-Portal)')]
    public function test_tc_usermgmt_004_penyembunyian_otomatis_lowongan_kerja_saat_mitra_dibanned(): void
    {
        $admin = $this->createAdmin();
        $companyEmail = 'mitra_banned@company.com';
        $company = $this->createCompanyUser($companyEmail, 'active');

        // Create 3 active internships for the company
        for ($i = 0; $i < 3; $i++) {
            $internship = Internship::create([
                'company_id'         => $company->id,
                'title'              => 'Junior Developer Magang #' . ($i + 1),
                'description'        => 'Testing cascading hide on banned',
                'company_name'       => $company->name,
                'location'           => 'Jakarta',
                'education_level'    => 'S1',
                'requirements'       => 'None',
                'deadline_at'        => now()->addDays(10),
                'quota'              => 5,
                'is_published'       => true,
                'moderation_status'  => 'approved',
            ]);
            $this->createdInternshipIds[] = $internship->id;
        }

        $this->printHeader("TC-USERMGMT-004 Penyembunyian Otomatis Lowongan Kerja Saat Mitra Dibanned (Multi-Portal)");

        $this->browse(function (Browser $mhsBrowser, Browser $adminBrowser) use ($admin, $company) {
            $this->runStep("1. [Portal Publik] Mahasiswa membuka halaman cari lowongan magang dan memastikan lowongan milik perusahaan tersebut terlihat di daftar.", function () use ($mhsBrowser) {
                $mhsBrowser->visit('/lowongan')
                           ->pause(2000)
                           ->screenshot('tc_usermgmt_004_mhs_sees_internship')
                           ->assertSee('Junior Developer Magang #1');
            });

            $this->runStep("2. [Portal Admin] Admin masuk ke halaman \"Manajemen Pengguna\".", function () use ($adminBrowser, $admin) {
                $adminBrowser->loginAs($admin)
                             ->visit('/admin/users')
                             ->waitForLocation('/admin/users', 15)
                             ->assertPathIs('/admin/users');
            });

            $this->runStep("3. Admin mencari profil perusahaan tersebut dan mengklik \"Blokir Akun\".", function () use ($adminBrowser, $company) {
                $adminBrowser->type('input[placeholder="Cari nama atau email pengguna…"]', $company->email)
                             ->pause(2500)
                             ->click('tbody tr:first-child button.border-red-200') // Banned button
                             ->pause(1500)
                             ->type('textarea[placeholder="Catatan alasan perubahan status ini…"]', 'Blokir akun karena pelanggaran.')
                             ->press('Blokir (Banned) Akun')
                             ->pause(3000)
                             ->screenshot('tc_usermgmt_004_blocked_company');
            });

            $this->runStep("4. [Portal Publik] Mahasiswa melakukan refresh halaman pencarian lowongan magang.", function () use ($mhsBrowser) {
                $mhsBrowser->refresh()
                           ->pause(2000)
                           ->screenshot('tc_usermgmt_004_mhs_refreshed')
                           ->assertDontSee('Junior Developer Magang #1');
            });
        });
    }

    /**
     * TC-USERMGMT-005: Mengaktifkan Kembali Akun Terblokir (Unbanned) (Multi-Portal)
     */
    #[TestDox('TC-USERMGMT-005 Mengaktifkan Kembali Akun Terblokir (Unbanned) (Multi-Portal)')]
    public function test_tc_usermgmt_005_mengaktifkan_kembali_akun_terblokir_unbanned_multi_portal(): void
    {
        $admin = $this->createAdmin();

        $email = 'terblokir@mahasiswa.com';
        User::withTrashed()->where('email', $email)->forceDelete();

        $mahasiswa = User::factory()->mahasiswa()->create([
            'name'              => 'Mahasiswa Terblokir',
            'email'             => $email,
            'password'          => Hash::make('password123'),
            'email_verified_at' => now(),
            'status'            => 'banned',
        ]);
        $this->createdUserIds[] = $mahasiswa->id;

        $this->printHeader("TC-USERMGMT-005 Mengaktifkan Kembali Akun Terblokir (Unbanned) (Multi-Portal)");

        $this->browse(function (Browser $adminBrowser, Browser $mhsBrowser) use ($admin, $mahasiswa) {
            $adminBrowser->loginAs($admin);

            $this->runStep("1. [Portal Admin] Admin mengakses halaman \"Manajemen Pengguna\".", function () use ($adminBrowser) {
                $adminBrowser->visit('/admin/users')
                             ->waitForLocation('/admin/users', 15)
                             ->assertPathIs('/admin/users');
            });

            $this->runStep("2. Admin memfilter daftar pengguna bersatus \"Banned\".", function () use ($adminBrowser) {
                $adminBrowser->select('select:last-of-type', 'banned') // Filter status Banned
                             ->pause(2000);
            });

            $this->runStep("3. Admin mencari email 'terblokir@mahasiswa.com' lalu mengklik tombol \"Buka Blokir (Unbanned)\".", function () use ($adminBrowser, $mahasiswa) {
                $adminBrowser->type('input[placeholder="Cari nama atau email pengguna…"]', $mahasiswa->email)
                             ->pause(2000)
                             ->press('Aktifkan') // Buka Blokir (Unbanned) / Aktifkan button
                             ->pause(1500)
                             ->press('Aktifkan Akun') // Confirm in modal
                             ->pause(3000)
                             ->screenshot('tc_usermgmt_005_unbanned');
            });

            $this->runStep("4. [Portal Mahasiswa] Mahasiswa membuka halaman login portal SIKARA.", function () use ($mhsBrowser) {
                $mhsBrowser->visit('/login')
                           ->waitForLocation('/login', 15)
                           ->assertPathIs('/login');
            });

            $this->runStep("5. Mahasiswa memasukkan email dan passwordnya.", function () use ($mhsBrowser, $mahasiswa) {
                $mhsBrowser->click('div.grid-cols-3 button:nth-child(1)') // Mahasiswa
                           ->pause(500)
                           ->type('#email', $mahasiswa->email)
                           ->type('#password', 'password123');
            });

            $this->runStep("6. Mahasiswa menekan tombol \"Masuk ke SIKARA\".", function () use ($mhsBrowser) {
                $mhsBrowser->press('Masuk ke SIKARA')
                           ->pause(4000)
                           ->screenshot('tc_usermgmt_005_mhs_logged_in')
                           ->assertPathIs('/peserta');
            });
        });
    }
}
