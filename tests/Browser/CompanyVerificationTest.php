<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\PerusahaanProfile;
use App\Models\Internship;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * CompanyVerificationTest
 *
 * Test suite untuk fitur Verifikasi Perusahaan (SKR-88).
 * Menggunakan database dev yang sudah berjalan (MySQL).
 * Setiap test membersihkan data yang dibuat setelah selesai.
 */
class CompanyVerificationTest extends DuskTestCase
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
        $email = 'dusk_admin_verif_' . time() . '@example.com';
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
    protected function createCompanyUser(string $email, string $status = 'inactive', string $name = null): User
    {
        User::withTrashed()->where('email', $email)->forceDelete();

        $company = User::factory()->perusahaan()->create([
            'name'              => $name ?? 'PT Dusk Test Company ' . rand(100, 999),
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
     * TC-COMPVER-001: Verifikasi dan Persetujuan Akun Mitra Perusahaan Baru (Multi-Portal)
     */
    #[TestDox('TC-COMPVER-001 Verifikasi dan Persetujuan Akun Mitra Perusahaan Baru (Multi-Portal)')]
    public function test_tc_compver_001_verifikasi_dan_persetujuan_akun_mitra_perusahaan_baru(): void
    {
        $admin = $this->createAdmin();
        $companyEmail = 'baru@company.com';
        $company = $this->createCompanyUser($companyEmail, 'inactive', 'PT Sasaran Inactive Dusk');

        $this->printHeader("TC-COMPVER-001 Verifikasi dan Persetujuan Akun Mitra Perusahaan Baru (Multi-Portal)");

        $this->browse(function (Browser $adminBrowser, Browser $companyBrowser) use ($admin, $company) {
            $adminBrowser->loginAs($admin);

            $this->runStep("1. [Portal Admin] Admin mengakses dashboard utama.", function () use ($adminBrowser) {
                $adminBrowser->visit('/admin/dashboard')
                             ->waitForLocation('/admin/dashboard', 15)
                             ->assertPathIs('/admin/dashboard');
            });

            $this->runStep("2. Admin mengklik menu \"Verifikasi Perusahaan\" pada sidebar.", function () use ($adminBrowser) {
                $adminBrowser->visit('/admin/verifications')
                             ->waitForLocation('/admin/verifications', 15)
                             ->assertPathIs('/admin/verifications');
            });

            $this->runStep("3. Admin mencari nama perusahaan target 'baru@company.com'.", function () use ($adminBrowser, $company) {
                $adminBrowser->type('input[placeholder="Cari perusahaan atau email..."]', $company->email)
                             ->pause(2500)
                             ->assertSee($company->name);
            });

            $this->runStep("4. Admin mengklik tombol \"Tinjau Dokumen\".", function () use ($adminBrowser, $company) {
                // Click the first target row action
                $adminBrowser->click('tbody tr:first-child td:last-child a')
                             ->pause(3000)
                             ->assertPathIs('/admin/verifications/' . $company->id);
            });

            $this->runStep("5. Admin meninjau kesesuaian berkas PDF SIUP/NIB dengan profil perusahaan.", function () use ($adminBrowser) {
                // We screenshot and assert we are in the detail page
                $adminBrowser->screenshot('tc_compver_001_tinjau')
                             ->assertSee('Sasaran Inactive Dusk');
            });

            $this->runStep("6. Admin mengklik tombol \"Verifikasi & Setujui Mitra\".", function () use ($adminBrowser, $company) {
                $adminBrowser->press('Verifikasi & Setujui Mitra')
                             ->pause(1500)
                             ->press('Konfirmasi & Simpan')
                             ->pause(3000)
                             ->screenshot('tc_compver_001_approved');

                $company->refresh();
                $this->assertEquals('active', $company->status);
            });

            $this->runStep("7. [Portal Perusahaan] Perwakilan perusahaan membuka halaman login SIKARA.", function () use ($companyBrowser) {
                $companyBrowser->visit('/login')
                               ->waitForLocation('/login', 15)
                               ->assertPathIs('/login');
            });

            $this->runStep("8. Pengguna login menggunakan email 'baru@company.com' dan passwordnya.", function () use ($companyBrowser, $company) {
                $companyBrowser->click('div.grid-cols-3 button:nth-child(2)') // Perusahaan
                               ->pause(500)
                               ->type('#email', $company->email)
                               ->type('#password', 'password123');
            });

            $this->runStep("9. Pengguna menekan tombol \"Masuk ke SIKARA\".", function () use ($companyBrowser) {
                $companyBrowser->press('Masuk ke SIKARA')
                               ->pause(4000)
                               ->screenshot('tc_compver_001_company_logged_in')
                               ->assertPathIs('/perusahaan/dashboard');
            });
        });
    }

    /**
     * TC-COMPVER-002: Pencabutan Status Verifikasi Perusahaan Karena Dokumen Tidak Valid/Kadaluarsa (Multi-Portal)
     */
    #[TestDox('TC-COMPVER-002 Pencabutan Status Verifikasi Perusahaan Karena Dokumen Tidak Valid/Kadaluarsa (Multi-Portal)')]
    public function test_tc_compver_002_pencabutan_status_verifikasi_perusahaan(): void
    {
        $admin = $this->createAdmin();
        $companyEmail = 'lama@company.com';
        $company = $this->createCompanyUser($companyEmail, 'active', 'PT Sasaran Active Dusk');

        $this->printHeader("TC-COMPVER-002 Pencabutan Status Verifikasi Perusahaan Karena Dokumen Tidak Valid/Kadaluarsa (Multi-Portal)");

        $this->browse(function (Browser $companyBrowser, Browser $adminBrowser) use ($admin, $company) {
            // Company logins first
            $companyBrowser->visit('/login')
                           ->waitForLocation('/login', 15)
                           ->click('div.grid-cols-3 button:nth-child(2)') // Perusahaan
                           ->pause(500)
                           ->type('#email', $company->email)
                           ->type('#password', 'password123')
                           ->press('Masuk ke SIKARA')
                           ->pause(4000)
                           ->assertPathIs('/perusahaan/dashboard');

            $adminBrowser->loginAs($admin);

            $this->runStep("1. [Portal Admin] Admin mengakses menu \"Verifikasi Perusahaan\".", function () use ($adminBrowser) {
                $adminBrowser->visit('/admin/verifications')
                             ->waitForLocation('/admin/verifications', 15)
                             ->assertPathIs('/admin/verifications');
            });

            $this->runStep("2. Admin mencari akun perusahaan 'lama@company.com' dan mengklik tombol \"Cabut Verifikasi\".", function () use ($adminBrowser, $company) {
                $adminBrowser->visit('/admin/verifications/' . $company->id)
                             ->waitForLocation('/admin/verifications/' . $company->id, 15)
                             ->press('Cabut Verifikasi (Pending)')
                             ->pause(1500);
            });

            $this->runStep("3. Admin memasukkan alasan pencabutan (misal: \"Dokumen SIUP kadaluarsa\") pada modal popup dan menekan tombol \"Konfirmasi & Simpan\".", function () use ($adminBrowser, $company) {
                $adminBrowser->type('textarea', 'Dokumen SIUP kadaluarsa')
                             ->press('Konfirmasi & Simpan')
                             ->pause(3000)
                             ->screenshot('tc_compver_002_revoked');

                $company->refresh();
                $this->assertEquals('inactive', $company->status);
            });

            $this->runStep("4. [Portal Perusahaan] Pengguna perusahaan yang sedang aktif melakukan navigasi menu atau me-refresh browser.", function () use ($companyBrowser) {
                // Dashboard is accessible to all company users regardless of status,
                // so navigate to a protected route (behind company.verified middleware)
                // to trigger the redirect to pending-verification.
                $companyBrowser->visit('/perusahaan/internships')
                               ->pause(3000)
                               ->screenshot('tc_compver_002_company_redirected')
                               ->assertPathIs('/perusahaan/pending-verification');
            });
        });
    }

    /**
     * TC-COMPVER-003: Pemblokiran Akun Perusahaan Karena Terindikasi Penipuan/Perusahaan Fiktif (Multi-Portal)
     */
    #[TestDox('TC-COMPVER-003 Pemblokiran Akun Perusahaan Karena Terindikasi Penipuan/Perusahaan Fiktif (Multi-Portal)')]
    public function test_tc_compver_003_pemblokiran_akun_perusahaan(): void
    {
        $admin = $this->createAdmin();
        $companyEmail = 'fiktif@company.com';
        $company = $this->createCompanyUser($companyEmail, 'active', 'PT Sasaran Banned Dusk');

        $this->printHeader("TC-COMPVER-003 Pemblokiran Akun Perusahaan Karena Terindikasi Penipuan/Perusahaan Fiktif (Multi-Portal)");

        $this->browse(function (Browser $adminBrowser, Browser $companyBrowser) use ($admin, $company) {
            $adminBrowser->loginAs($admin);

            $this->runStep("1. [Portal Admin] Admin mengakses menu \"Verifikasi Perusahaan\".", function () use ($adminBrowser) {
                $adminBrowser->visit('/admin/verifications')
                             ->waitForLocation('/admin/verifications', 15)
                             ->assertPathIs('/admin/verifications');
            });

            $this->runStep("2. Admin mengklik tombol \"Tinjau Dokumen\" pada baris perusahaan 'fiktif@company.com'.", function () use ($adminBrowser, $company) {
                $adminBrowser->visit('/admin/verifications/' . $company->id)
                             ->waitForLocation('/admin/verifications/' . $company->id, 15);
            });

            $this->runStep("3. Admin mengidentifikasi dokumen tidak sah lalu menekan tombol \"Tolak & Blokir Akun (Banned)\".", function () use ($adminBrowser) {
                $adminBrowser->press('Tolak / Blokir Akun (Banned)')
                             ->pause(1500);
            });

            $this->runStep("4. Admin memasukkan alasan pemblokiran lalu mengonfirmasi.", function () use ($adminBrowser, $company) {
                $adminBrowser->type('textarea', 'Terindikasi penipuan/perusahaan fiktif')
                             ->press('Konfirmasi & Simpan')
                             ->pause(3000)
                             ->screenshot('tc_compver_003_banned');

                $company->refresh();
                $this->assertEquals('banned', $company->status);
            });

            $this->runStep("5. [Portal Perusahaan] Pengguna perusahaan yang terblokir mencoba melakukan login kembali ke portal SIKARA menggunakan kredensial mereka.", function () use ($companyBrowser, $company) {
                $companyBrowser->visit('/login')
                               ->waitForLocation('/login', 15)
                               ->click('div.grid-cols-3 button:nth-child(2)') // Perusahaan
                               ->pause(500)
                               ->type('#email', $company->email)
                               ->type('#password', 'password123')
                               ->press('Masuk ke SIKARA')
                               ->pause(3000)
                               ->screenshot('tc_compver_003_company_login_blocked')
                               ->assertSee('blocked'); // blocked message
            });
        });
    }

    /**
     * TC-COMPVER-004: Cegah Akses Fitur Pembuatan Lowongan untuk Akun Perusahaan Belum Terverifikasi (Bypass URL)
     */
    #[TestDox('TC-COMPVER-004 Cegah Akses Fitur Pembuatan Lowongan untuk Akun Perusahaan Belum Terverifikasi (Bypass URL)')]
    public function test_tc_compver_004_cegah_akses_fitur_belum_terverifikasi_bypass_url(): void
    {
        $companyEmail = 'belum@company.com';
        $company = $this->createCompanyUser($companyEmail, 'inactive');

        $this->printHeader("TC-COMPVER-004 Cegah Akses Fitur Pembuatan Lowongan untuk Akun Perusahaan Belum Terverifikasi (Bypass URL)");

        $this->browse(function (Browser $browser) use ($company) {
            $browser->visit('/login')
                    ->waitForLocation('/login', 15)
                    ->click('div.grid-cols-3 button:nth-child(2)') // Perusahaan
                    ->pause(500)
                    ->type('#email', $company->email)
                    ->type('#password', 'password123')
                    ->press('Masuk ke SIKARA')
                    ->pause(4000);

            $this->runStep("1. [Portal Perusahaan] Pengguna mengetik langsung URL pembuatan lowongan magang '/perusahaan/internships/create' pada address bar browser.", function () use ($browser) {
                $browser->visit('/perusahaan/internships/create')
                        ->pause(2000);
            });

            $this->runStep("2. Pengguna menekan Enter.", function () use ($browser) {
                $browser->screenshot('tc_compver_004_redirected')
                        ->assertPathIs('/perusahaan/pending-verification');
            });

            $this->runStep("3. Pengguna mencoba mengirimkan POST request manual ke endpoint '/perusahaan/internships' menggunakan API client.", function () use ($browser) {
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
     * TC-COMPVER-005: Otomatisasi Penonaktifan Lowongan Kerja Aktif di Publik Saat Status Perusahaan Berubah Menjadi Inactive/Banned (Multi-Portal)
     */
    #[TestDox('TC-COMPVER-005 Otomatisasi Penonaktifan Lowongan Kerja Aktif di Publik Saat Status Perusahaan Berubah Menjadi Inactive/Banned (Multi-Portal)')]
    public function test_tc_compver_005_otomatisasi_penonaktifan_lowongan(): void
    {
        $admin = $this->createAdmin();
        $companyEmail = 'lama_verif@company.com';
        $company = $this->createCompanyUser($companyEmail, 'active', 'PT Sasaran Cascading Dusk');

        // Create 2 active internships
        for ($i = 0; $i < 2; $i++) {
            $internship = Internship::create([
                'company_id'         => $company->id,
                'title'              => 'Dusk Cascading Test #' . ($i + 1),
                'description'        => 'Testing cascading hide on block',
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

        $this->printHeader("TC-COMPVER-005 Otomatisasi Penonaktifan Lowongan Kerja Aktif di Publik Saat Status Perusahaan Berubah Menjadi Inactive/Banned (Multi-Portal)");

        $this->browse(function (Browser $mhsBrowser, Browser $adminBrowser) use ($admin, $company) {
            $this->runStep("1. [Portal Publik] Mahasiswa membuka halaman pencarian lowongan magang SIKARA dan melihat lowongan dari perusahaan 'lama@company.com' terdaftar aktif.", function () use ($mhsBrowser) {
                $mhsBrowser->visit('/lowongan')
                           ->pause(3000)
                           ->screenshot('tc_compver_005_mhs_sees_lowongan')
                           ->assertSee('Dusk Cascading Test #1');
            });

            $this->runStep("2. [Portal Admin] Admin masuk ke menu \"Verifikasi Perusahaan\" dan mencabut status verifikasi ('inactive') atau memblokir ('banned') perusahaan 'lama@company.com'.", function () use ($adminBrowser, $admin, $company) {
                $adminBrowser->loginAs($admin)
                             ->visit('/admin/verifications/' . $company->id)
                             ->waitForLocation('/admin/verifications/' . $company->id, 15)
                             ->press('Cabut Verifikasi (Pending)')
                             ->pause(1500)
                             ->type('textarea', 'Pencabutan otomatis test.')
                             ->press('Konfirmasi & Simpan')
                             ->pause(3000)
                             ->screenshot('tc_compver_005_admin_revoked');
            });

            $this->runStep("3. [Portal Publik] Mahasiswa me-refresh halaman pencarian lowongan magang SIKARA.", function () use ($mhsBrowser) {
                $mhsBrowser->visit('/lowongan')
                           ->pause(3000)
                           ->screenshot('tc_compver_005_mhs_refreshed')
                           ->assertDontSee('Dusk Cascading Test #1');
            });
        });
    }
}
