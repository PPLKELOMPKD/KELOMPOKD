<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Setting;
use App\Models\ActivityLog;
use App\Models\Internship;
use App\Models\PerusahaanProfile;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * SystemSettingsTest
 *
 * Test suite untuk fitur Pengaturan Sistem & Logging (SKR-140).
 * Menggunakan database dev yang sudah berjalan (MySQL).
 * Setiap test membersihkan data yang dibuat setelah selesai.
 */
class SystemSettingsTest extends DuskTestCase
{
    /** Daftar ID user yang dibuat selama test. */
    protected array $createdUserIds = [];
    protected array $createdInternshipIds = [];
    protected array $createdLogIds = [];

    /**
     * Cleanup setelah test selesai.
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
     * Helper: buat admin user untuk testing.
     */
    protected function createAdmin(string $name = 'Admin Budi'): User
    {
        $email = 'dusk_admin_settings_' . time() . '@example.com';
        User::withTrashed()->where('email', $email)->forceDelete();

        $admin = User::factory()->admin()->create([
            'name'              => $name,
            'email'             => $email,
            'password'          => Hash::make('AdminPass123!'),
            'email_verified_at' => now(),
            'status'            => 'active',
        ]);
        $this->createdUserIds[] = $admin->id;
        return $admin;
    }

    /**
     * Helper: buat perusahaan user untuk testing.
     */
    protected function createCompanyUser(string $email): User
    {
        User::withTrashed()->where('email', $email)->forceDelete();

        $company = User::factory()->perusahaan()->create([
            'name'              => 'PT Sinar Jaya',
            'email'             => $email,
            'password'          => Hash::make('password123'),
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
     * Helper: memastikan setting default ada di database.
     */
    protected function ensureSettingsExistInDatabase(): void
    {
        $defaults = [
            ['key' => 'app_name', 'value' => 'SIKARA', 'type' => 'string', 'group' => 'general', 'label' => 'Nama Aplikasi'],
            ['key' => 'app_description', 'value' => 'Sistem Informasi Kampus Merdeka', 'type' => 'string', 'group' => 'general', 'label' => 'Deskripsi Aplikasi'],
            ['key' => 'contact_email', 'value' => 'admin@sikara.ac.id', 'type' => 'string', 'group' => 'general', 'label' => 'Email Kontak'],
            ['key' => 'footer_text', 'value' => '© 2026 SIKARA. Hak cipta dilindungi undang-undang.', 'type' => 'string', 'group' => 'tampilan', 'label' => 'Teks Footer'],
            ['key' => 'social_instagram', 'value' => 'https://instagram.com/sikara', 'type' => 'string', 'group' => 'tampilan', 'label' => 'Link Instagram'],
            ['key' => 'maintenance_mode', 'value' => 'false', 'type' => 'boolean', 'group' => 'system', 'label' => 'Mode Pemeliharaan (Maintenance)'],
            ['key' => 'session_lifetime', 'value' => '120', 'type' => 'integer', 'group' => 'system', 'label' => 'Durasi Sesi (Menit)'],
            ['key' => 'registration_enabled', 'value' => 'true', 'type' => 'boolean', 'group' => 'system', 'label' => 'Buka Pendaftaran Akun Baru'],
        ];

        foreach ($defaults as $default) {
            Setting::firstOrCreate(
                ['key' => $default['key']],
                $default
            );
        }
    }

    /**
     * TC-SYSSET-001: Perubahan Nama Aplikasi Secara Dinamis dari Portal Admin dan Dampaknya di Halaman Publik/Portal Mahasiswa (Multi-Portal)
     */
    #[TestDox('TC-SYSSET-001 Perubahan Nama Aplikasi Secara Dinamis dari Portal Admin dan Dampaknya di Halaman Publik/Portal Mahasiswa (Multi-Portal)')]
    public function test_tc_sysset_001_perubahan_nama_aplikasi_secara_dinamis(): void
    {
        $admin = $this->createAdmin();
        $this->ensureSettingsExistInDatabase();

        // Reset settings app_name to 'SIKARA' before test starts
        Setting::where('key', 'app_name')->update(['value' => 'SIKARA']);

        // Create a student user for step 5
        $mhsEmail = 'mhs_settings_' . time() . '@sikara.test';
        $student = User::factory()->mahasiswa()->create([
            'email'             => $mhsEmail,
            'password'          => Hash::make('password123'),
            'email_verified_at' => now(),
            'status'            => 'active',
        ]);
        $this->createdUserIds[] = $student->id;

        $this->printHeader("TC-SYSSET-001 Perubahan Nama Aplikasi Secara Dinamis dari Portal Admin (Multi-Portal)");
        $this->browse(function (Browser $browser) use ($admin, $student) {
            $browser->loginAs($admin);

            $this->runStep("1. [Portal Admin] Admin mengakses dashboard utama.", function () use ($browser) {
                $browser->visit('/admin/dashboard')
                             ->waitForLocation('/admin/dashboard', 15)
                             ->assertPathIs('/admin/dashboard');
            });

            $this->runStep("2. Admin mengklik menu \"Pengaturan Sistem\" pada sidebar.", function () use ($browser) {
                $browser->visit('/admin/settings')
                             ->waitForLocation('/admin/settings', 15)
                             ->assertPathIs('/admin/settings');
            });

            $this->runStep("3. Admin mengubah nilai pada input field \"Nama Aplikasi\" dari \"SIKARA\" menjadi \"SIKARA Portal\".", function () use ($browser) {
                $browser->waitFor('input[placeholder="Masukkan Nama Aplikasi"]', 5)
                             ->clear('input[placeholder="Masukkan Nama Aplikasi"]')
                             ->type('input[placeholder="Masukkan Nama Aplikasi"]', 'SIKARA Portal')
                             ->screenshot('tc_sysset_001_typed');
            });

            $this->runStep("4. Admin mengklik tombol \"Simpan Perubahan\".", function () use ($browser) {
                $browser->press('Simpan Perubahan')
                             ->pause(3000)
                             ->screenshot('tc_sysset_001_saved')
                             ->assertSee('SIKARA Portal');
            });

            $this->runStep("5. [Portal Mahasiswa] Pengguna mahasiswa melakukan login ke akun mereka.", function () use ($browser, $student) {
                $browser->loginAs($student)
                           ->visit('/peserta')
                           ->pause(3000)
                           ->assertPathIs('/peserta');
            });

            $this->runStep("6. Mahasiswa meninjau bagian header/logo di dashboard mahasiswa.", function () use ($browser) {
                $browser->screenshot('tc_sysset_001_mhs_dashboard')
                           ->assertSee('SIKARA Portal');
            });

            $this->runStep("7. [Portal Publik] Pengguna eksternal mengakses halaman login SIKARA (tanpa login).", function () use ($browser) {
                $browser->driver->manage()->deleteAllCookies();
                $browser->visit('/login')
                               ->waitForLocation('/login', 15)
                               ->screenshot('tc_sysset_001_public_login')
                               ->assertSee('SIKARA Portal'); // Checking dynamic setting output
            });
        });
    }

    /**
     * TC-SYSSET-002: Pencatatan Log Aktivitas Pengguna (Mahasiswa/Perusahaan) dan Pemantauannya di Portal Admin (Multi-Portal)
     */
    #[TestDox('TC-SYSSET-002 Pencatatan Log Aktivitas Pengguna (Mahasiswa/Perusahaan) dan Pemantauannya di Portal Admin (Multi-Portal)')]
    public function test_tc_sysset_002_pencatatan_log_aktivitas_pengguna(): void
    {
        $admin = $this->createAdmin();
        $companyEmail = 'mitra@company.com';
        $company = $this->createCompanyUser($companyEmail);
        $internshipTitle = 'Junior Web Developer';

        $this->printHeader("TC-SYSSET-002 Pencatatan Log Aktivitas Pengguna (Mahasiswa/Perusahaan) dan Pemantauannya di Portal Admin (Multi-Portal)");

        $this->browse(function (Browser $browser) use ($admin, $company, $internshipTitle) {
            // Establish login first
            $browser->visit('/login')
                    ->waitForLocation('/login', 15)
                    ->click('div.grid-cols-3 button:nth-child(2)') // Perusahaan
                    ->pause(500)
                    ->type('#email', $company->email)
                    ->type('#password', 'password123')
                    ->press('Masuk ke SIKARA')
                    ->pause(4000)
                    ->assertPathIs('/perusahaan/dashboard');

            $this->runStep("1. [Portal Perusahaan] Pengguna perusahaan login ke dashboard perusahaan.", function () {
                // Verified in the login establishment above
            });

            $this->runStep("2. Pengguna membuat dan mempublikasikan lowongan magang baru berjudul \"Junior Web Developer\".", function () use ($browser, $internshipTitle) {
                $deadline = now()->addDays(15)->format('Y-m-d');

                $browser->visit('/perusahaan/internships/create')
                        ->waitFor('#title', 10)
                        ->pause(2000);

                // Use JavaScript to fill ALL fields, ensuring Vue v-model reactivity is triggered.
                // Native type() does not reliably update Vue's reactive form state in headless Chrome.
                $browser->script("
                    (function() {
                        function setField(id, value) {
                            var el = document.getElementById(id);
                            if (!el) { return; }
                            el.value = value;
                            el.dispatchEvent(new Event('input', { bubbles: true }));
                            el.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                        setField('title', '" . addslashes($internshipTitle) . "');
                        setField('company_name', 'PT Sinar Jaya');
                        setField('description', 'Lowongan untuk Junior Web Developer.');
                        setField('requirements', 'Mengerti HTML, CSS, dan Javascript.');
                        setField('salary', 'Rp 4.000.000');
                        setField('quota', '3');
                        setField('deadline_at', '$deadline');
                        setField('location', 'Jakarta');

                        // Force-close the location autocomplete dropdown that was opened by the input event
                        // Remove dropdown from DOM immediately
                        var dropdown = document.querySelector('.absolute.z-10.mt-1');
                        if (dropdown) { dropdown.remove(); }
                    })();
                ");

                $browser->pause(1000)
                        ->screenshot('tc_sysset_002_form');

                // Click the submit button using JS to bypass any potential overlay
                $browser->script("document.querySelector('button[type=submit]').click();");

                $browser->waitForLocation('/perusahaan/internships', 15)
                        ->assertPathIs('/perusahaan/internships');

                $internship = Internship::where('title', $internshipTitle)->first();
                if ($internship) {
                    $this->createdInternshipIds[] = $internship->id;
                }
            });

            $this->runStep("3. [Portal Admin] Admin mengakses dashboard utama.", function () use ($browser, $admin) {
                $browser->loginAs($admin)
                        ->visit('/admin/dashboard')
                        ->waitForLocation('/admin/dashboard', 15)
                        ->assertPathIs('/admin/dashboard');
            });

            $this->runStep("4. Admin mengklik menu \"Log Aktivitas\" pada sidebar.", function () use ($browser) {
                $browser->visit('/admin/activity-logs')
                        ->waitForLocation('/admin/activity-logs', 15)
                        ->assertPathIs('/admin/activity-logs');
            });

            $this->runStep("5. Admin memeriksa entri log terbaru pada tabel log.", function () use ($browser, $company, $internshipTitle) {
                $browser->waitFor('input[placeholder="Cari nama, aksi, atau keterangan..."]', 10)
                        ->type('input[placeholder="Cari nama, aksi, atau keterangan..."]', $internshipTitle)
                        ->pause(3000)
                        ->screenshot('tc_sysset_002_log_checked');

                // Verify the page contains the internship title in search results
                $browser->assertSee($internshipTitle);

                // Verify the activity log shows the company identifier (email or name)
                $pageSource = $browser->driver->getPageSource();
                $this->assertTrue(
                    str_contains($pageSource, $company->email) || str_contains($pageSource, $company->name),
                    "Activity log should display the company identifier (email: {$company->email} or name: {$company->name})."
                );
            });
        });
    }

    /**
     * TC-SYSSET-003: Cegah Akses Non-Admin ke Halaman Pengaturan Sistem
     */
    #[TestDox('TC-SYSSET-003 Cegah Akses Non-Admin ke Halaman Pengaturan Sistem')]
    public function test_tc_sysset_003_cegah_akses_non_admin_ke_settings(): void
    {
        $mhsEmail = 'terdaftar@mahasiswa.com';
        User::withTrashed()->where('email', $mhsEmail)->forceDelete();

        $student = User::factory()->mahasiswa()->create([
            'email'             => $mhsEmail,
            'password'          => Hash::make('password123'),
            'email_verified_at' => now(),
            'status'            => 'active',
        ]);
        $this->createdUserIds[] = $student->id;

        $this->printHeader("TC-SYSSET-003 Cegah Akses Non-Admin ke Halaman Pengaturan Sistem");

        $this->browse(function (Browser $browser) use ($student) {
            $browser->loginAs($student);

            $this->runStep("1. [Portal Mahasiswa] Pengguna membuka browser.", function () use ($browser) {
                $browser->visit('/peserta')
                        ->pause(1000);
            });

            $this->runStep("2. Pengguna mengetik langsung URL endpoint pengaturan admin ('/admin/settings') di address bar browser.", function () use ($browser) {
                $browser->visit('/admin/settings')
                        ->pause(2000);
            });

            $this->runStep("3. Pengguna menekan Enter.", function () use ($browser) {
                $browser->screenshot('tc_sysset_003_forbidden')
                        ->assertSee('403');
            });

            $this->runStep("4. Pengguna mencoba menembak POST request manual (menggunakan API Client) ke endpoint '/admin/settings' dengan payload data modifikasi pengaturan.", function () use ($browser) {
                $browser->script("
                    var csrfMeta = document.querySelector('meta[name=\"csrf-token\"]');
                    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
                    fetch('/admin/settings', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ settings: [{ key: 'app_name', value: 'Hacker SIKARA' }] })
                    }).then(r => window.duskSettingsPostStatus = r.status)
                      .catch(e => window.duskSettingsPostStatus = 0);
                ");
                $browser->pause(2000);
                $status = $browser->script("return window.duskSettingsPostStatus;")[0];
                $this->assertTrue(
                    in_array($status, [403, 419, 302, 0]),
                    "POST request should be forbidden or rejected. Got status: " . $status
                );
            });
        });
    }
}
