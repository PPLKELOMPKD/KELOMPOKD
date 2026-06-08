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
     * Helper to create a dummy image file.
     */
    protected function createDummyImage(string $filename = 'logo.png'): string
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
        // 1x1 transparent PNG data
        $pngData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        file_put_contents($path, $pngData);
        return $path;
    }

    /**
     * Helper to create a dummy document file.
     */
    protected function createDummyDocument(string $filename = 'document.pdf'): string
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($path, '%PDF-1.4 mock content');
        return $path;
    }

    /**
     * TC-01: Menampilkan halaman profil perusahaan
     */
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

    /**
     * TC-02: Mengisi profil perusahaan pertama kali
     */
    public function test_tc02_mengisi_profil_perusahaan_pertama_kali(): void
    {
        $company = User::factory()->create([
            'role' => User::ROLE_PERUSAHAAN,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // No profile model created yet.
        $this->browse(function (Browser $browser) use ($company) {
            $browser->loginAs($company)
                ->visit('/perusahaan/profile')
                ->waitFor('button[title="Edit profil perusahaan"]', 15)
                // 1. Fill Description via About section modal
                ->click('button[title="Edit tentang perusahaan"]')
                ->waitFor('form', 10)
                ->type('form textarea', 'Deskripsi perusahaan pertama kali.')
                ->press('Batal')
                ->waitUntilMissing('form', 10)
                // 2. Fill Office Address via Contact section modal
                ->click('button[title="Edit kontak dan lokasi"]')
                ->waitFor('form', 10)
                ->type('form textarea', 'Alamat kantor baru pertama kali.')
                ->press('Batal')
                ->waitUntilMissing('form', 10);

            // Use Javascript click to bypass sticky header overlay click interception
            $browser->script("document.querySelector('button[title=\"Edit profil perusahaan\"]').click();");

            $browser->waitFor('form', 10)
                ->type('form div.grid > div:nth-child(2) input', 'E-commerce') // Industry field
                ->type('form div.grid > div:nth-child(3) input', 'Jakarta') // Location field
                ->type('form div.grid > div:nth-child(4) input', 'https://example-company.com') // Website field
                // 4. Save (submits all data including description and office_address)
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

    /**
     * TC-03: Memperbarui profil perusahaan
     */
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
                ->type('form div.grid > div:nth-child(2) input', 'Fintech Baru') // Industry field
                ->type('form div.grid > div:nth-child(3) input', 'Malang') // Location field
                ->press('Simpan Profil')
                ->waitUntilMissing('form', 15);
        });

        $this->assertDatabaseHas('perusahaan_profiles', [
            'user_id' => $company->id,
            'industry' => 'Fintech Baru',
            'location' => 'Malang',
        ]);
    }

    /**
     * TC-04: Validasi field wajib
     */
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
                // Use key combination to clear the input value so Vue binding updates properly
                ->keys('form div.grid > div:nth-child(1) input', ['{control}', 'a'], '{backspace}')
                ->press('Simpan Profil')
                ->waitForText('The name field is required.', 15);
        });
    }

    /**
     * TC-05: Upload logo perusahaan berhasil
     */
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
                // Logo file input is the first input[type="file"] in identity section
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

    /**
     * TC-06: Upload logo dengan format tidak valid
     */
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

    /**
     * TC-07: Role selain perusahaan mengakses profil perusahaan
     */
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

    /**
     * TC-08: Data profil gagal disimpan
     */
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
                    ->type('form div.grid > div:nth-child(2) input', 'Industri Gagal'); // Industry field

                // Sabotage the database table by renaming it temporarily to cause query failure
                Schema::rename('perusahaan_profiles', 'perusahaan_profiles_sabotaged');

                $browser->press('Simpan Profil')
                    ->pause(3000); // Wait for the request to attempt and fail
            });
        } finally {
            // Restore the table
            if (Schema::hasTable('perusahaan_profiles_sabotaged')) {
                Schema::rename('perusahaan_profiles_sabotaged', 'perusahaan_profiles');
            }
        }

        // Verify that the database record was NOT updated (remains "Industri Stabil")
        $this->assertDatabaseHas('perusahaan_profiles', [
            'id' => $profile->id,
            'industry' => 'Industri Stabil',
        ]);
    }

    /**
     * TC-09: Profil perusahaan tampil pada halaman publik/detail perusahaan
     */
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
                // The edit buttons must NOT be present
                ->assertNotPresent('button[title="Edit profil perusahaan"]');
        });
    }
}
