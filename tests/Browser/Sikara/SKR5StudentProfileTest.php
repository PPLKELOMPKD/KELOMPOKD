<?php

namespace Tests\Browser\Sikara;

use App\Models\User;
use App\Models\MahasiswaProfile;
use App\Models\Skill;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SKR5StudentProfileTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected static bool $initialized = false;

    protected function setUp(): void
    {
        parent::setUp();

        // Settle delay for Laravel server boot and Dusk environment swap
        if (! self::$initialized) {
            sleep(8);
            self::$initialized = true;
        } else {
            sleep(1);
        }
    }

    protected function tearDown(): void
    {
        // Navigate to about:blank to prevent migration locks/contention on the database
        try {
            $this->browse(function (Browser $browser) {
                $browser->blank();
            });
        } catch (\Throwable $e) {
            // Ignore if browser already closed
        }

        parent::tearDown();
    }

    /**
     * Helper to open the edit profile drawer.
     */
    protected function openEditProfileDrawer(Browser $browser): Browser
    {
        $browser->waitForText('Edit Profil', 15);
        $browser->script("
            var buttons = document.querySelectorAll('button');
            var clicked = false;
            for (var i = 0; i < buttons.length; i++) {
                if (buttons[i].textContent.indexOf('Edit Profil') !== -1) {
                    buttons[i].click();
                    clicked = true;
                    break;
                }
            }
            if (!clicked) {
                throw new Error('Edit Profil button not found');
            }
        ");
        return $browser->waitFor('aside form', 10);
    }

    /**
     * Helper to save profile changes.
     */
    protected function saveProfile(Browser $browser): Browser
    {
        $browser->script("
            var buttons = document.querySelectorAll('aside button');
            var clicked = false;
            for (var i = 0; i < buttons.length; i++) {
                if (buttons[i].textContent.indexOf('Simpan Perubahan') !== -1) {
                    buttons[i].click();
                    clicked = true;
                    break;
                }
            }
            if (!clicked) {
                throw new Error('Simpan Perubahan button not found');
            }
        ");
        return $browser;
    }

    /**
     * TC-01: Tampil halaman profil
     */
    public function test_tc01_tampil_halaman_profil(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $user->mahasiswaProfile()->create([
            'nim' => '1234567890',
            'department' => 'Teknik Elektro',
            'study_program' => 'S1 Teknik Telekomunikasi',
            'gpa' => 3.82,
            'phone' => '081234567890',
            'university' => 'Institut Teknologi Bandung',
            'location' => 'Bandung',
            'bio' => 'Saya menyukai telekomunikasi.',
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/profile')
                ->waitForText('1234567890', 15) // Wait for the seeded profile content
                ->assertSee($user->name)
                ->assertSee('1234567890')
                ->assertSee('Teknik Elektro')
                ->assertSee('S1 Teknik Telekomunikasi')
                ->assertSee('3.82 / 4.00')
                ->assertSee('Institut Teknologi Bandung');
        });
    }

    /**
     * TC-02: Update profil berhasil
     */
    public function test_tc02_update_profil_berhasil(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $user->mahasiswaProfile()->create([
            'nim' => '1234567890',
            'department' => 'Lama',
            'study_program' => 'Lama',
            'gpa' => 3.00,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/profile')
                ->waitForText('1234567890', 15);

            $this->openEditProfileDrawer($browser);

            $browser->keys('aside form div.grid > div:nth-child(2) input', ['{control}', 'a'], '{backspace}')
                ->type('aside form div.grid > div:nth-child(2) input', '1122334455')
                ->keys('aside form div.grid > div:nth-child(3) input', ['{control}', 'a'], '{backspace}')
                ->type('aside form div.grid > div:nth-child(3) input', 'Informatika')
                ->keys('aside form div.grid > div:nth-child(4) input', ['{control}', 'a'], '{backspace}')
                ->type('aside form div.grid > div:nth-child(4) input', 'S1 Teknik Informatika')
                ->keys('aside form div.grid > div:nth-child(5) input', ['{control}', 'a'], '{backspace}')
                ->type('aside form div.grid > div:nth-child(5) input', '3.90');

            $this->saveProfile($browser);

            $browser->waitUntilMissing('aside form', 15)
                ->waitForText('Profil berhasil diperbarui.', 15);
        });

        $this->assertDatabaseHas('mahasiswa_profiles', [
            'user_id' => $user->id,
            'nim' => '1122334455',
            'department' => 'Informatika',
            'study_program' => 'S1 Teknik Informatika',
            'gpa' => 3.90,
        ]);
    }

    /**
     * TC-03: Nama lengkap kosong (API direct request test because name is read-only in UI)
     */
    public function test_tc03_nama_lengkap_kosong(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/profile')
                ->waitForText($user->name, 15);

            // Trigger direct PATCH request to verify backend name validation
            $browser->script("
                window.tc03Error = undefined;
                axios.patch('/profile', { name: '', email: '{$user->email}' })
                    .catch(error => {
                        window.tc03Error = error.response.data.errors.name[0];
                    });
            ");

            $browser->waitUntil('return window.tc03Error !== undefined;', 15);
            $browser->assertScript('return window.tc03Error;', 'The name field is required.');
        });
    }

    /**
     * TC-04: NIM kosong
     */
    public function test_tc04_nim_kosong(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $user->mahasiswaProfile()->create([
            'nim' => '1234567890',
            'department' => 'Lama',
            'study_program' => 'Lama',
            'gpa' => 3.00,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/profile')
                ->waitForText('1234567890', 15);

            $this->openEditProfileDrawer($browser);

            $browser->keys('aside form div.grid > div:nth-child(2) input', ['{control}', 'a'], '{backspace}');

            $this->saveProfile($browser);

            $browser->waitForText('The nim field is required.', 15);
        });
    }

    /**
     * TC-05: IPK tidak valid
     */
    public function test_tc05_ipk_tidak_valid(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $user->mahasiswaProfile()->create([
            'nim' => '1234567890',
            'department' => 'Lama',
            'study_program' => 'Lama',
            'gpa' => 3.00,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/profile')
                ->waitForText('1234567890', 15);

            $this->openEditProfileDrawer($browser);

            $browser->keys('aside form div.grid > div:nth-child(5) input', ['{control}', 'a'], '{backspace}')
                ->type('aside form div.grid > div:nth-child(5) input', '4.5')
                // trigger blur by clicking on study program input
                ->click('aside form div.grid > div:nth-child(4) input')
                ->waitForText('IPK harus berada di antara 0.00 - 4.00', 15)
                ->assertSee('IPK harus berada di antara 0.00 - 4.00');
        });
    }

    /**
     * TC-06: Update sebagian data
     */
    public function test_tc06_update_sebagian_data(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $user->mahasiswaProfile()->create([
            'nim' => '1234567890',
            'department' => 'Lama Jurusan',
            'study_program' => 'Lama Prodi',
            'gpa' => 3.00,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/profile')
                ->waitForText('1234567890', 15);

            $this->openEditProfileDrawer($browser);

            $browser->keys('aside form div.grid > div:nth-child(3) input', ['{control}', 'a'], '{backspace}')
                ->type('aside form div.grid > div:nth-child(3) input', 'Jurusan Baru')
                ->keys('aside form div.grid > div:nth-child(4) input', ['{control}', 'a'], '{backspace}')
                ->type('aside form div.grid > div:nth-child(4) input', 'Prodi Baru');

            $this->saveProfile($browser);

            $browser->waitUntilMissing('aside form', 15)
                ->waitForText('Profil berhasil diperbarui.', 15);
        });

        $this->assertDatabaseHas('mahasiswa_profiles', [
            'user_id' => $user->id,
            'department' => 'Jurusan Baru',
            'study_program' => 'Prodi Baru',
            'nim' => '1234567890',
        ]);
    }

    /**
     * TC-07: Tambah skill pada profil
     */
    public function test_tc07_tambah_skill_pada_profil(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $user->mahasiswaProfile()->create([
            'nim' => '1234567890',
            'department' => 'IT',
            'study_program' => 'Informatika',
            'gpa' => 3.00,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/profile')
                ->waitForText('1234567890', 15);

            // Click the "Tambah" button inside the Keterampilan Utama section
            $browser->script("
                var buttons = document.querySelectorAll('button');
                for (var i = 0; i < buttons.length; i++) {
                    if (buttons[i].textContent.trim() === 'Tambah') {
                        buttons[i].click();
                        break;
                    }
                }
            ");

            $browser->waitFor('input[placeholder="Contoh: Laravel"]', 10)
                ->type('input[placeholder="Contoh: Laravel"]', 'VueJS')
                ->press('Simpan Skill')
                ->waitForText('VueJS', 15)
                ->assertSee('VueJS');
        });

        $this->assertDatabaseHas('skills', [
            'user_id' => $user->id,
            'name' => 'VueJS',
        ]);
    }

    /**
     * TC-08: Akses tanpa login
     */
    public function test_tc08_akses_tanpa_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/profile')
                ->waitForLocation('/login', 15)
                ->assertPathIs('/login');
        });
    }

    /**
     * TC-09: Simpan data dengan format benar
     */
    public function test_tc09_simpan_data_dengan_format_benar(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $user->mahasiswaProfile()->create([
            'nim' => '1234567890',
            'department' => 'IT',
            'study_program' => 'Informatika',
            'gpa' => 3.00,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/profile')
                ->waitForText('1234567890', 15);

            $this->openEditProfileDrawer($browser);

            $browser->keys('aside form div.grid > div:nth-child(2) input', ['{control}', 'a'], '{backspace}')
                ->type('aside form div.grid > div:nth-child(2) input', 'NIM9999')
                ->keys('aside form div.grid > div:nth-child(3) input', ['{control}', 'a'], '{backspace}')
                ->type('aside form div.grid > div:nth-child(3) input', 'Sains')
                ->keys('aside form div.grid > div:nth-child(4) input', ['{control}', 'a'], '{backspace}')
                ->type('aside form div.grid > div:nth-child(4) input', 'Fisika')
                ->keys('aside form div.grid > div:nth-child(5) input', ['{control}', 'a'], '{backspace}')
                ->type('aside form div.grid > div:nth-child(5) input', '3.50')
                ->keys('aside form div.grid > div:nth-child(6) input', ['{control}', 'a'], '{backspace}')
                ->type('aside form div.grid > div:nth-child(6) input', '089999999')
                ->keys('aside form div.grid > div:nth-child(7) input', ['{control}', 'a'], '{backspace}')
                ->type('aside form div.grid > div:nth-child(7) input', 'Universitas Gadjah Mada')
                ->keys('aside form div.grid > div:nth-child(8) input', ['{control}', 'a'], '{backspace}')
                ->type('aside form div.grid > div:nth-child(8) input', 'Yogyakarta')
                ->keys('aside form div.grid > div:nth-child(9) textarea', ['{control}', 'a'], '{backspace}')
                ->type('aside form div.grid > div:nth-child(9) textarea', 'Saya suka fisika.');

            $this->saveProfile($browser);

            $browser->waitUntilMissing('aside form', 15)
                ->waitForText('Profil berhasil diperbarui.', 15);
        });

        $this->assertDatabaseHas('mahasiswa_profiles', [
            'user_id' => $user->id,
            'nim' => 'NIM9999',
            'department' => 'Sains',
            'study_program' => 'Fisika',
            'gpa' => 3.50,
            'phone' => '089999999',
            'university' => 'Universitas Gadjah Mada',
            'location' => 'Yogyakarta',
            'bio' => 'Saya suka fisika.',
        ]);
    }

    /**
     * TC-10: Notifikasi sukses update
     */
    public function test_tc10_notifikasi_sukses_update(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $user->mahasiswaProfile()->create([
            'nim' => '1234567890',
            'department' => 'IT',
            'study_program' => 'Informatika',
            'gpa' => 3.00,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/profile')
                ->waitForText('1234567890', 15);

            $this->openEditProfileDrawer($browser);

            $browser->keys('aside form div.grid > div:nth-child(2) input', ['{control}', 'a'], '{backspace}')
                ->type('aside form div.grid > div:nth-child(2) input', 'NIM8888');

            $this->saveProfile($browser);

            $browser->waitUntilMissing('aside form', 15)
                ->waitForText('Profil berhasil diperbarui.', 15)
                ->assertSee('Profil berhasil diperbarui.');
        });
    }
}
