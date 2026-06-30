<?php

namespace Tests\Browser\Sikara;

use App\Models\User;
use App\Models\PerusahaanProfile;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Schema;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PBI54CompanyProfileTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected static bool $initialized = false;

    protected function setUp(): void
    {
        parent::setUp();


        if (! self::$initialized) {
            sleep(8);
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


    protected function createDummyImage(string $filename = 'logo.png'): string
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;

        $pngData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        file_put_contents($path, $pngData);
        return $path;
    }

    protected function createDummyDocument(string $filename = 'document.pdf'): string
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($path, '%PDF-1.4 mock content');
        return $path;
    }


    public function test_tc01_menampilkan_halaman_profil_perusahaan(): void
    {
        $company = User::factory()->create([
            'role' => User::ROLE_PERUSAHAAN,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        PerusahaanProfile::create([
            'user_id' => $company->id,
            'industry' => 'Teknologi Informasi',
            'location' => 'Bandung',
            'office_address' => 'Jl. Merdeka No. 10',
            'description' => 'Perusahaan IT yang inovatif.',
        ]);

        $this->browse(function (Browser $browser) use ($company) {
            $browser->loginAs($company)
                ->visit('/perusahaan/profile')
                ->waitForText($company->name, 15)
                ->assertPathIs('/perusahaan/profile')
                ->assertSee($company->name)
                ->assertSee('Teknologi Informasi')
                ->assertSee('Bandung')
                ->assertSee('Jl. Merdeka No. 10')
                ->assertSee('Perusahaan IT yang inovatif.')
                ->assertPresent('button[title="Edit profil perusahaan"]');
        });
    }


    public function test_tc02_mengisi_profil_perusahaan_pertama_kali(): void
    {
        $company = User::factory()->create([
            'role' => User::ROLE_PERUSAHAAN,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($company) {
            $browser->loginAs($company)
                ->visit('/perusahaan/profile')
                ->waitFor('button[title="Edit profil perusahaan"]', 15)
                
                ->click('button[title="Edit tentang perusahaan"]')
                ->waitFor('form', 10)
                ->type('form textarea', 'Deskripsi perusahaan pertama kali.')
                ->press('Batal')
                ->waitUntilMissing('form', 10)
                
                ->click('button[title="Edit kontak dan lokasi"]')
                ->waitFor('form', 10)
                ->type('form textarea', 'Alamat kantor baru pertama kali.')
                ->press('Batal')
                ->waitUntilMissing('form', 10);


            $browser->script("document.querySelector('button[title=\"Edit profil perusahaan\"]').click();");

            $browser->waitFor('form', 10)
                ->type('form div.grid > div:nth-child(2) input', 'E-commerce') 
                ->type('form div.grid > div:nth-child(3) input', 'Jakarta') 
                ->type('form div.grid > div:nth-child(4) input', 'https://example-company.com') 

                ->press('Simpan Profil')
                ->waitUntilMissing('form', 15);
        });

        $this->assertDatabaseHas('perusahaan_profiles', [
            'user_id' => $company->id,
            'industry' => 'E-commerce',
            'location' => 'Jakarta',
            'website' => 'https://example-company.com',
            'description' => 'Deskripsi perusahaan pertama kali.',
            'office_address' => 'Alamat kantor baru pertama kali.',
        ]);
    }


    public function test_tc03_memperbarui_profil_perusahaan(): void
    {
        $company = User::factory()->create([
            'role' => User::ROLE_PERUSAHAAN,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $profile = PerusahaanProfile::create([
            'user_id' => $company->id,
            'industry' => 'Fintech',
            'location' => 'Surabaya',
            'office_address' => 'Alamat lama',
            'description' => 'Deskripsi lama',
        ]);

        $this->browse(function (Browser $browser) use ($company) {
            $browser->loginAs($company)
                ->visit('/perusahaan/profile')
                ->waitFor('button[title="Edit profil perusahaan"]', 15)
                ->click('button[title="Edit profil perusahaan"]')
                ->waitFor('form', 10)
                ->type('form div.grid > div:nth-child(2) input', 'Fintech Baru') 
                ->type('form div.grid > div:nth-child(3) input', 'Malang') 
                ->press('Simpan Profil')
                ->waitUntilMissing('form', 15);
        });

        $this->assertDatabaseHas('perusahaan_profiles', [
            'user_id' => $company->id,
            'industry' => 'Fintech Baru',
            'location' => 'Malang',
        ]);
    }


    public function test_tc04_validasi_field_wajib(): void
    {
        $company = User::factory()->create([
            'role' => User::ROLE_PERUSAHAAN,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        PerusahaanProfile::create([
            'user_id' => $company->id,
            'industry' => 'Logistik',
            'location' => 'Semarang',
            'office_address' => 'Gedung A, Semarang',
            'description' => 'Perusahaan logistik terpadu.',
        ]);

        $this->browse(function (Browser $browser) use ($company) {
            $browser->loginAs($company)
                ->visit('/perusahaan/profile')
                ->waitFor('button[title="Edit profil perusahaan"]', 15)
                ->click('button[title="Edit profil perusahaan"]')
                ->waitFor('form', 10)
                ->keys('form div.grid > div:nth-child(1) input', ['{control}', 'a'], '{backspace}')
                ->press('Simpan Profil')
                ->waitForText('The name field is required.', 15);
        });
    }


    public function test_tc05_upload_logo_perusahaan_berhasil(): void
    {
        $company = User::factory()->create([
            'role' => User::ROLE_PERUSAHAAN,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        PerusahaanProfile::create([
            'user_id' => $company->id,
            'office_address' => 'Jl. Raya No. 5',
            'description' => 'Deskripsi profil.',
        ]);

        $dummyLogo = $this->createDummyImage('logo_valid.png');

        $this->browse(function (Browser $browser) use ($company, $dummyLogo) {
            $browser->loginAs($company)
                ->visit('/perusahaan/profile')
                ->waitFor('button[title="Edit profil perusahaan"]', 15)
                ->click('button[title="Edit profil perusahaan"]')
                ->waitFor('form', 10)

                ->attach('form input[type="file"]:nth-of-type(1)', $dummyLogo)
                ->press('Simpan Profil')
                ->waitUntilMissing('form', 15);
        });

        if (file_exists($dummyLogo)) {
            unlink($dummyLogo);
        }

        $profile = PerusahaanProfile::where('user_id', $company->id)->first();
        $this->assertNotNull($profile->logo_path);
        $this->assertStringContainsString('company-profiles/logos', $profile->logo_path);
    }


    public function test_tc06_upload_logo_dengan_format_tidak_valid(): void
    {
        $company = User::factory()->create([
            'role' => User::ROLE_PERUSAHAAN,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        PerusahaanProfile::create([
            'user_id' => $company->id,
            'office_address' => 'Jl. Raya No. 5',
            'description' => 'Deskripsi profil.',
        ]);

        $invalidFile = $this->createDummyDocument('logo_invalid.pdf');

        $this->browse(function (Browser $browser) use ($company, $invalidFile) {
            $browser->loginAs($company)
                ->visit('/perusahaan/profile')
                ->waitFor('button[title="Edit profil perusahaan"]', 15)
                ->click('button[title="Edit profil perusahaan"]')
                ->waitFor('form', 10)
                ->attach('form input[type="file"]:nth-of-type(1)', $invalidFile)
                ->press('Simpan Profil')
                ->waitForText('must be an image', 15);
        });

        if (file_exists($invalidFile)) {
            unlink($invalidFile);
        }
    }


    public function test_tc07_role_selain_perusahaan_mengakses_profil_perusahaan(): void
    {
        $student = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($student) {
            $browser->loginAs($student)
                ->visit('/perusahaan/profile')
                ->waitForText('403', 15)
                ->assertSee('FORBIDDEN');
        });
    }


    public function test_tc08_data_profil_gagal_disimpan(): void
    {
        $company = User::factory()->create([
            'role' => User::ROLE_PERUSAHAAN,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $profile = PerusahaanProfile::create([
            'user_id' => $company->id,
            'industry' => 'Industri Stabil',
            'office_address' => 'Jl. Raya No. 5',
            'description' => 'Deskripsi stabil.',
        ]);

        try {
            $this->browse(function (Browser $browser) use ($company) {
                $browser->loginAs($company)
                    ->visit('/perusahaan/profile')
                    ->waitFor('button[title="Edit profil perusahaan"]', 15)
                    ->click('button[title="Edit profil perusahaan"]')
                    ->waitFor('form', 10)
                    ->type('form div.grid > div:nth-child(2) input', 'Industri Gagal'); 


                Schema::rename('perusahaan_profiles', 'perusahaan_profiles_sabotaged');

                $browser->press('Simpan Profil')
                    ->pause(3000);
            });
        } finally {

            if (Schema::hasTable('perusahaan_profiles_sabotaged')) {
                Schema::rename('perusahaan_profiles_sabotaged', 'perusahaan_profiles');
            }
        }


        $this->assertDatabaseHas('perusahaan_profiles', [
            'id' => $profile->id,
            'industry' => 'Industri Stabil',
        ]);
    }


    public function test_tc09_profil_perusahaan_tampil_pada_halaman_publik(): void
    {
        $company = User::factory()->create([
            'role' => User::ROLE_PERUSAHAAN,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        PerusahaanProfile::create([
            'user_id' => $company->id,
            'industry' => 'E-Commerce Retail',
            'location' => 'Yogyakarta',
            'office_address' => 'Jl. Malioboro No. 20',
            'description' => 'Menjual barang retail secara online.',
        ]);

        $this->browse(function (Browser $browser) use ($company) {
            $browser->visit("/perusahaan-profile/{$company->id}")
                ->waitForText($company->name, 15)
                ->assertSee($company->name)
                ->assertSee('E-Commerce Retail')
                ->assertSee('Yogyakarta')
                ->assertSee('Jl. Malioboro No. 20')
                ->assertSee('Menjual barang retail secara online.')

                ->assertNotPresent('button[title="Edit profil perusahaan"]');
        });
    }
}
