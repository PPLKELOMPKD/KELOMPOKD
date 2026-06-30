<?php

namespace Tests\Browser\Sikara;

use App\Models\User;
use App\Models\MahasiswaProfile;
use App\Models\Skill;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SKR13InputSkillTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected static bool $initialized = false;

    protected function setUp(): void
    {
        parent::setUp();

        
        if (! self::$initialized) {
            sleep(15); 
            self::$initialized = true;
        } else {
            sleep(1);
        }
    }

    protected function tearDown(): void
    {
        
        try {
            $this->browse(function (Browser $browser) {
                $browser->blank();
            });
        } catch (\Throwable $e) {
            
        }

        parent::tearDown();
    }

    
    protected function clickTambahSkill(Browser $browser): Browser
    {
        $browser->script("
            var buttons = document.querySelectorAll('button');
            var clicked = false;
            for (var i = 0; i < buttons.length; i++) {
                if (buttons[i].textContent.trim() === 'Tambah') {
                    buttons[i].click();
                    clicked = true;
                    break;
                }
            }
            if (!clicked) {
                throw new Error('Tambah skill button not found');
            }
        ");
        return $browser;
    }

    
    protected function clickTutupSkill(Browser $browser): Browser
    {
        $browser->script("
            var buttons = document.querySelectorAll('button');
            var clicked = false;
            for (var i = 0; i < buttons.length; i++) {
                if (buttons[i].textContent.trim() === 'Tutup') {
                    buttons[i].click();
                    clicked = true;
                    break;
                }
            }
            if (!clicked) {
                throw new Error('Tutup skill button not found');
            }
        ");
        return $browser;
    }

    
    public function test_tc01_modal_tambah_skill_tampil(): void
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
                ->waitForText('1234567890', 45); 

            $this->clickTambahSkill($browser);

            $browser->waitFor('input[placeholder="Contoh: Laravel"]', 10)
                ->assertPresent('input[placeholder="Contoh: Laravel"]');
        });
    }

    
    public function test_tc02_tambah_skill_berhasil(): void
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
                ->waitForText('1234567890', 30);

            $this->clickTambahSkill($browser);

            $browser->waitFor('input[placeholder="Contoh: Laravel"]', 10)
                ->type('input[placeholder="Contoh: Laravel"]', 'Laravel')
                ->press('Simpan Skill')
                ->waitForText('Laravel', 15)
                ->assertSee('Laravel');
        });

        $this->assertDatabaseHas('skills', [
            'user_id' => $user->id,
            'name' => 'Laravel',
            'proficiency' => 50, 
        ]);
    }

    
    public function test_tc03_nama_skill_kosong(): void
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
                ->waitForText('1234567890', 30);

            $this->clickTambahSkill($browser);

            $browser->waitFor('input[placeholder="Contoh: Laravel"]', 10)
                ->press('Simpan Skill')
                ->waitForText('The name field is required.', 15);
        });
    }

    
    public function test_tc04_level_kosong(): void
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
                ->waitForText('1234567890', 30);

            
            $browser->script("
                window.tc04Error = undefined;
                axios.post('/profile/skills', { name: 'Laravel', proficiency: '' })
                    .catch(error => {
                        window.tc04Error = error.response.data.errors.proficiency[0];
                    });
            ");

            $browser->waitUntil('return window.tc04Error !== undefined;', 15);
            $browser->assertScript('return window.tc04Error;', 'The proficiency field is required.');
        });
    }

    
    public function test_tc05_skill_duplikat(): void
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

        
        $user->skills()->create([
            'name' => 'Laravel',
            'proficiency' => 80,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/profile')
                ->waitForText('1234567890', 30);

            $this->clickTambahSkill($browser);

            $browser->waitFor('input[placeholder="Contoh: Laravel"]', 10)
                ->type('input[placeholder="Contoh: Laravel"]', 'Laravel')
                ->press('Simpan Skill')
                ->waitForText('The name has already been taken.', 15);
        });
    }

    
    public function test_tc06_klik_batal(): void
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
                ->waitForText('1234567890', 30);

            $this->clickTambahSkill($browser);

            $browser->waitFor('input[placeholder="Contoh: Laravel"]', 10);

            $this->clickTutupSkill($browser);

            $browser->waitUntilMissing('input[placeholder="Contoh: Laravel"]', 10)
                ->assertMissing('input[placeholder="Contoh: Laravel"]');
        });
    }

    
    public function test_tc07_klik_close(): void
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
                ->waitForText('1234567890', 30);

            $this->clickTambahSkill($browser);

            $browser->waitFor('input[placeholder="Contoh: Laravel"]', 10);

            $this->clickTutupSkill($browser);

            $browser->waitUntilMissing('input[placeholder="Contoh: Laravel"]', 10)
                ->assertMissing('input[placeholder="Contoh: Laravel"]');
        });
    }

    
    public function test_tc08_belum_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/profile')
                ->waitForLocation('/login', 15)
                ->assertPathIs('/login');
        });
    }
}
