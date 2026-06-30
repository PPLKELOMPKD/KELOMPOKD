<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * LoginTest
 *
 * Test suite untuk fitur Login Pengguna & Proteksi Rute Admin (SKR-26).
 * Menggunakan database dev yang sudah berjalan (MySQL).
 * Setiap test membersihkan data yang dibuat setelah selesai.
 */
class LoginTest extends DuskTestCase
{
    /** Email user yang dibuat selama testing, untuk cleanup. */
    protected array $createdEmails = [];

    /**
     * Cleanup: hapus user yang dibuat selama test (termasuk soft-deleted).
     */
    protected function tearDown(): void
    {
        if (!empty($this->createdEmails)) {
            User::withTrashed()->whereIn('email', $this->createdEmails)->forceDelete();
        }
        parent::tearDown();
    }

    /**
     * TC-ADMIN-001: Login dengan kredensial admin yang valid dan status aktif
     */
    #[TestDox('TC-ADMIN-001 Login dengan kredensial admin yang valid dan status aktif')]
    public function test_tc_admin_001_login_dengan_kredensial_admin_yang_valid_dan_status_aktif(): void
    {
        User::withTrashed()->where('email', 'admin@sikara.test')->forceDelete();

        $admin = User::factory()->admin()->create([
            'email'             => 'admin@sikara.test',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'status'            => 'active',
        ]);
        $this->createdEmails[] = 'admin@sikara.test';

        $this->printHeader("TC-ADMIN-001 Login dengan kredensial admin yang valid dan status aktif");

        $this->browse(function (Browser $browser) {
            $this->runStep("1. Masuk ke halaman login SIKARA", function () use ($browser) {
                $browser->visit('/login')
                        ->waitForLocation('/login', 15)
                        ->assertPathIs('/login');
            });

            $this->runStep("2. Pilih role 'Admin' pada role selector", function () use ($browser) {
                $browser->click('div.grid-cols-3 button:nth-child(3)')
                        ->pause(500);
            });

            $this->runStep("3. Masukkan email admin valid", function () use ($browser) {
                $browser->type('#email', 'admin@sikara.test');
            });

            $this->runStep("4. Masukkan password admin valid", function () use ($browser) {
                $browser->type('#password', 'password')
                        ->screenshot('tc_admin_001_credentials');
            });

            $this->runStep("5. Klik tombol 'Masuk ke SIKARA'", function () use ($browser) {
                $browser->press('Masuk ke SIKARA')
                        ->pause(4000)
                        ->screenshot('tc_admin_001_success');

                $currentUrl = $browser->driver->getCurrentURL();
                $this->assertTrue(
                    str_contains($currentUrl, 'admin') ||
                    str_contains($currentUrl, 'dashboard') ||
                    !str_contains($currentUrl, '/login'),
                    "Admin seharusnya diarahkan ke dashboard admin. URL: $currentUrl"
                );
            });

            $browser->visit('/');
        });
    }

    /**
     * TC-ADMIN-002: Mengakses halaman admin tanpa login / login sebagai mahasiswa
     */
    #[TestDox('TC-ADMIN-002 Mengakses halaman admin tanpa login / login sebagai mahasiswa')]
    public function test_tc_admin_002_mengakses_halaman_admin_tanpa_login_login_sebagai_mahasiswa(): void
    {
        $email = 'dusk_mhs_tc2_' . time() . '@example.com';
        User::withTrashed()->where('email', $email)->forceDelete();

        $user = User::factory()->mahasiswa()->create([
            'email'             => $email,
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'status'            => 'active',
        ]);
        $this->createdEmails[] = $email;

        $this->printHeader("TC-ADMIN-002 Mengakses halaman admin tanpa login / login sebagai mahasiswa");

        $this->browse(function (Browser $browser) use ($user) {
            $this->runStep("1. Pengguna membuka browser", function () use ($browser) {
                $browser->visit('/')
                        ->pause(1000);
            });

            $this->runStep("2. Ketik langsung URL '/admin/dashboard' pada address bar browser", function () use ($browser) {
                $browser->visit('/admin/dashboard')
                        ->pause(2000);
            });

            $this->runStep("3. Tekan Enter", function () use ($browser, $user) {
                // If not logged in, it should redirect to login or show 403
                $currentUrl = $browser->driver->getCurrentURL();
                $this->assertTrue(
                    str_contains($currentUrl, '/login') || str_contains($currentUrl, '403') || $browser->see('403') || $browser->see('FORBIDDEN'),
                    "Seharusnya dialihkan ke login atau 403. URL: $currentUrl"
                );

                // Now test as Mahasiswa
                $browser->visit('/login')
                        ->waitForLocation('/login', 15)
                        ->click('div.grid-cols-3 button:nth-child(1)') // Select mahasiswa
                        ->pause(500)
                        ->type('#email', $user->email)
                        ->type('#password', 'password')
                        ->press('Masuk ke SIKARA')
                        ->pause(4000);

                $browser->visit('/admin/dashboard')
                        ->pause(2000)
                        ->screenshot('tc_admin_002_forbidden')
                        ->assertSee('403');
            });

            $browser->visit('/');
        });
    }

    /**
     * TC-ADMIN-003: Login admin dengan password yang salah
     */
    #[TestDox('TC-ADMIN-003 Login admin dengan password yang salah')]
    public function test_tc_admin_003_login_admin_dengan_password_yang_salah(): void
    {
        User::withTrashed()->where('email', 'admin@sikara.id')->forceDelete();

        $admin = User::factory()->admin()->create([
            'email'             => 'admin@sikara.id',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'status'            => 'active',
        ]);
        $this->createdEmails[] = 'admin@sikara.id';

        $this->printHeader("TC-ADMIN-003 Login admin dengan password yang salah");

        $this->browse(function (Browser $browser) {
            $this->runStep("1. Masuk ke halaman login SIKARA", function () use ($browser) {
                $browser->visit('/login')
                        ->waitForLocation('/login', 15)
                        ->assertPathIs('/login');
            });

            $this->runStep("2. Pilih role 'Admin' pada role selector", function () use ($browser) {
                $browser->click('div.grid-cols-3 button:nth-child(3)')
                        ->pause(500);
            });

            $this->runStep("3. Masukkan email admin valid ('admin@sikara.id')", function () use ($browser) {
                $browser->type('#email', 'admin@sikara.id');
            });

            $this->runStep("4. Masukkan password salah", function () use ($browser) {
                $browser->type('#password', 'salahpassword123');
            });

            $this->runStep("5. Klik tombol 'Masuk ke SIKARA'", function () use ($browser) {
                $browser->press('Masuk ke SIKARA')
                        ->pause(3000)
                        ->screenshot('tc_admin_003_wrong_password')
                        ->assertPathIs('/login');
            });
        });
    }

    /**
     * TC-ADMIN-004: Sesi admin berakhir secara otomatis setelah masa idle timeout (30 menit)
     */
    #[TestDox('TC-ADMIN-004 Sesi admin berakhir secara otomatis setelah masa idle timeout (30 menit)')]
    public function test_tc_admin_004_sesi_admin_berakhir_secara_otomatis_setelah_masa_idle_timeout_30_menit(): void
    {
        User::withTrashed()->where('email', 'admin@sikara.test')->forceDelete();

        $admin = User::factory()->admin()->create([
            'email'             => 'admin@sikara.test',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'status'            => 'active',
        ]);
        $this->createdEmails[] = 'admin@sikara.test';

        $this->printHeader("TC-ADMIN-004 Sesi admin berakhir secara otomatis setelah masa idle timeout (30 menit)");

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->waitForLocation('/login', 15)
                    ->click('div.grid-cols-3 button:nth-child(3)')
                    ->pause(500)
                    ->type('#email', 'admin@sikara.test')
                    ->type('#password', 'password')
                    ->press('Masuk ke SIKARA')
                    ->pause(4000);

            $this->runStep("1. Admin membiarkan browser terbuka tanpa melakukan aktivitas apapun selama 30 menit", function () use ($browser) {
                // Hapus cookie session untuk mensimulasikan timeout / habisnya sesi
                $browser->driver->manage()->deleteAllCookies();
                $browser->pause(1000);
            });

            $this->runStep("2. Admin melakukan refresh halaman atau mencoba mengklik menu di dashboard", function () use ($browser) {
                $browser->refresh()
                        ->pause(2000)
                        ->screenshot('tc_admin_004_after_refresh')
                        ->assertPathIs('/login');
            });
        });
    }

    /**
     * TC-ADMIN-005: Mengakses halaman root '/dashboard' saat session admin masih aktif
     */
    #[TestDox("TC-ADMIN-005 Mengakses halaman root '/dashboard' saat session admin masih aktif")]
    public function test_tc_admin_005_mengakses_halaman_root_dashboard_saat_session_admin_masih_aktif(): void
    {
        User::withTrashed()->where('email', 'admin@sikara.test')->forceDelete();

        $admin = User::factory()->admin()->create([
            'email'             => 'admin@sikara.test',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'status'            => 'active',
        ]);
        $this->createdEmails[] = 'admin@sikara.test';

        $this->printHeader("TC-ADMIN-005 Mengakses halaman root '/dashboard' saat session admin masih aktif");

        $this->browse(function (Browser $browser) {
            // Login admin terlebih dahulu jika belum login
            $browser->visit('/login')
                    ->waitForLocation('/login', 15)
                    ->click('div.grid-cols-3 button:nth-child(3)')
                    ->pause(500)
                    ->type('#email', 'admin@sikara.test')
                    ->type('#password', 'password')
                    ->press('Masuk ke SIKARA')
                    ->pause(4000);

            $this->runStep("1. Admin mengetik URL root '/dashboard' di address bar browser", function () use ($browser) {
                $browser->visit('/dashboard')
                        ->pause(3000);
            });

            $this->runStep("2. Tekan Enter", function () use ($browser) {
                $currentUrl = $browser->driver->getCurrentURL();
                $this->assertTrue(
                    str_contains($currentUrl, 'admin') ||
                    str_contains($currentUrl, 'dashboard') ||
                    !str_contains($currentUrl, '/login'),
                    "Admin seharusnya diarahkan kembali ke dashboard admin. URL: $currentUrl"
                );
            });

            $browser->visit('/');
        });
    }

    /**
     * TC-ADMIN-006: Login admin dengan email yang tidak terdaftar di sistem
     */
    #[TestDox('TC-ADMIN-006 Login admin dengan email yang tidak terdaftar di sistem')]
    public function test_tc_admin_006_login_admin_dengan_email_yang_tidak_terdaftar_di_sistem(): void
    {
        User::withTrashed()->where('email', 'bukanadmin@sikara.id')->forceDelete();

        $this->printHeader("TC-ADMIN-006 Login admin dengan email yang tidak terdaftar di sistem");

        $this->browse(function (Browser $browser) {
            $this->runStep("1. Masuk ke halaman login SIKARA", function () use ($browser) {
                $browser->visit('/login')
                        ->waitForLocation('/login', 15)
                        ->assertPathIs('/login');
            });

            $this->runStep("2. Pilih role 'Admin' pada role selector", function () use ($browser) {
                $browser->click('div.grid-cols-3 button:nth-child(3)')
                        ->pause(500);
            });

            $this->runStep("3. Masukkan email tidak terdaftar ('bukanadmin@sikara.id')", function () use ($browser) {
                $browser->type('#email', 'bukanadmin@sikara.id');
            });

            $this->runStep("4. Masukkan password sembarang", function () use ($browser) {
                $browser->type('#password', 'password123');
            });

            $this->runStep("5. Klik tombol 'Masuk ke SIKARA'", function () use ($browser) {
                $browser->press('Masuk ke SIKARA')
                        ->pause(3000)
                        ->screenshot('tc_admin_006_unregistered')
                        ->assertPathIs('/login');
            });
        });
    }

    /**
     * TC-ADMIN-007: Login admin dengan akun yang berstatus banned atau inactive
     */
    #[TestDox('TC-ADMIN-007 Login admin dengan akun yang berstatus banned atau inactive')]
    public function test_tc_admin_007_login_admin_dengan_akun_yang_berstatus_banned_atau_inactive(): void
    {
        $emailBanned = 'bannedadmin@sikara.test';
        User::withTrashed()->where('email', $emailBanned)->forceDelete();

        $bannedAdmin = User::factory()->admin()->create([
            'email'             => $emailBanned,
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'status'            => 'banned',
        ]);
        $this->createdEmails[] = $emailBanned;

        $this->printHeader("TC-ADMIN-007 Login admin dengan akun yang berstatus banned atau inactive");

        $this->browse(function (Browser $browser) use ($emailBanned) {
            $this->runStep("1. Masuk ke halaman login SIKARA", function () use ($browser) {
                $browser->visit('/login')
                        ->waitForLocation('/login', 15)
                        ->assertPathIs('/login');
            });

            $this->runStep("2. Pilih role 'Admin' pada role selector", function () use ($browser) {
                $browser->click('div.grid-cols-3 button:nth-child(3)')
                        ->pause(500);
            });

            $this->runStep("3. Masukkan email admin yang berstatus banned/inactive", function () use ($browser) {
                $browser->type('#email', 'bannedadmin@sikara.test');
            });

            $this->runStep("4. Masukkan password valid", function () use ($browser) {
                $browser->type('#password', 'password');
            });

            $this->runStep("5. Klik tombol 'Masuk ke SIKARA'", function () use ($browser) {
                $browser->press('Masuk ke SIKARA')
                        ->pause(3000)
                        ->screenshot('tc_admin_007_banned')
                        ->assertPathIs('/login');
            });
        });
    }
}
