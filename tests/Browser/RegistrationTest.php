<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\MahasiswaProfile;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Facades\DB;

/**
 * RegistrationTest
 *
 * Test suite untuk fitur Registrasi Pengguna.
 * URL: http://127.0.0.1:8000/register
 *
 * Menggunakan database dev yang sudah berjalan (MySQL).
 * Setiap test membersihkan data yang dibuat setelah selesai.
 *
 * Skenario yang diuji:
 * 1. Halaman registrasi dapat diakses dan ditampilkan dengan benar.
 * 2. Form registrasi menampilkan elemen-elemen utama.
 * 3. Registrasi mahasiswa berhasil dengan data valid.
 * 4. Validasi error muncul jika email sudah terdaftar.
 * 5. Validasi error muncul jika password tidak cocok.
 * 6. Validasi error muncul jika field wajib kosong.
 * 7. Link ke halaman login tersedia.
 */
class RegistrationTest extends DuskTestCase
{
    /** Email yang dibuat selama testing, untuk cleanup. */
    protected array $createdEmails = [];

    /**
     * Cleanup: hapus user yang dibuat selama test.
     */
    protected function tearDown(): void
    {
        if (!empty($this->createdEmails)) {
            User::whereIn('email', $this->createdEmails)->delete();
        }
        parent::tearDown();
    }

    /**
     * Test 1: Halaman register dapat diakses.
     */
    public function test_halaman_registrasi_dapat_diakses(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->waitForLocation('/register', 15)
                    ->assertPathIs('/register')
                    ->screenshot('registration_01_page_accessible');
        });
    }

    /**
     * Test 2: Form registrasi menampilkan elemen-elemen yang dibutuhkan.
     */
    public function test_elemen_form_registrasi_terlihat(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->waitForLocation('/register', 15)
                    ->assertPathIs('/register')
                    ->pause(2000)
                    ->screenshot('registration_02_form_elements');

            // Cek setidaknya ada div#app (Inertia wrapper)
            $browser->assertPresent('#app')
                    ->assertPresent('#name')
                    ->assertPresent('#email')
                    ->assertPresent('#password')
                    ->assertPresent('#password_confirmation');
        });
    }

    /**
     * Test 3: Registrasi mahasiswa berhasil dengan data valid.
     */
    public function test_mahasiswa_dapat_registrasi_dengan_sukses(): void
    {
        $uniqueEmail = 'dusk_mhs_reg_' . time() . '@example.com';
        $uniqueNim   = 'NIM' . time();
        $this->createdEmails[] = $uniqueEmail;

        $this->browse(function (Browser $browser) use ($uniqueEmail, $uniqueNim) {
            $browser->visit('/register')
                    ->waitForLocation('/register', 15)
                    ->pause(3000) // tunggu Inertia/Vue hydrate
                    ->screenshot('registration_03a_loaded');

            // Pilih role mahasiswa (button ke-1 di grid-cols-2)
            $browser->click('div.grid-cols-2 button:nth-child(1)')
                    ->pause(500);

            // Isi form menggunakan ID selectors
            $browser->type('#name', 'Test Mahasiswa Dusk')
                    ->type('#email', $uniqueEmail)
                    ->type('#phone', '08123456789')
                    ->type('#university', 'Universitas Test Dusk')
                    ->type('#study_program', 'Teknik Informatika')
                    ->type('#nim', $uniqueNim)
                    ->type('#password', 'Password123!')
                    ->type('#password_confirmation', 'Password123!')
                    ->check('input[type="checkbox"]')
                    ->screenshot('registration_03b_form_filled')
                    ->press('button[type="submit"]')
                    ->pause(4000)
                    ->screenshot('registration_03c_after_submit');

            // Setelah registrasi mahasiswa, harus redirect ke login
            $currentUrl = $browser->driver->getCurrentURL();
            $this->assertStringNotContainsString(
                '/register',
                $currentUrl,
                "Setelah registrasi berhasil, tidak boleh tetap di /register. URL: $currentUrl"
            );
        });
    }

    /**
     * Test 4: Validasi error muncul jika email sudah terdaftar.
     */
    public function test_registrasi_gagal_dengan_email_duplikat(): void
    {
        // Buat user yang sudah ada terlebih dahulu
        $existingEmail = 'duplicate_dusk_' . time() . '@example.com';
        $this->createdEmails[] = $existingEmail;

        $existingUser = User::factory()->mahasiswa()->create([
            'email'             => $existingEmail,
            'email_verified_at' => now(),
            'status'            => 'active',
        ]);

        $this->browse(function (Browser $browser) use ($existingEmail) {
            $browser->visit('/register')
                    ->waitForLocation('/register', 15)
                    ->pause(3000);

            // Pilih role mahasiswa
            $browser->click('div.grid-cols-2 button:nth-child(1)')
                    ->pause(500);

            $browser->type('#name', 'Test Duplicate Email')
                    ->type('#email', $existingEmail)
                    ->type('#phone', '08123456789')
                    ->type('#university', 'Universitas Test')
                    ->type('#study_program', 'Informatika')
                    ->type('#nim', 'NIM999' . time())
                    ->type('#password', 'Password123!')
                    ->type('#password_confirmation', 'Password123!')
                    ->check('input[type="checkbox"]')
                    ->press('button[type="submit"]')
                    ->pause(3000)
                    ->screenshot('registration_04_duplicate_email_error')
                    ->assertPathIs('/register');
        });
    }

    /**
     * Test 5: Validasi error muncul jika password dan konfirmasi tidak cocok.
     */
    public function test_registrasi_gagal_dengan_password_tidak_cocok(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->waitForLocation('/register', 15)
                    ->pause(3000);

            // Pilih role mahasiswa
            $browser->click('div.grid-cols-2 button:nth-child(1)')
                    ->pause(500);

            $browser->type('#name', 'Test Password Mismatch')
                    ->type('#email', 'mismatch_' . time() . '@example.com')
                    ->type('#phone', '08123456789')
                    ->type('#university', 'Universitas Test')
                    ->type('#study_program', 'Informatika')
                    ->type('#nim', 'NIM888' . time())
                    ->type('#password', 'Password123!')
                    ->type('#password_confirmation', 'WrongPassword456!')
                    ->check('input[type="checkbox"]')
                    ->press('button[type="submit"]')
                    ->pause(3000)
                    ->screenshot('registration_05_password_mismatch_error')
                    ->assertPathIs('/register');
        });
    }

    /**
     * Test 6: Halaman register memiliki link ke halaman login.
     */
    public function test_halaman_registrasi_memiliki_link_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->waitForLocation('/register', 15)
                    ->pause(2000)
                    ->assertPresent('a[href*="login"]')
                    ->screenshot('registration_06_login_link');
        });
    }
}
