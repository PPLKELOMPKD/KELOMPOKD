<?php

namespace Tests\Feature\Sikara;

use App\Models\MahasiswaProfile;
use App\Models\PerusahaanProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Test Suite: Fitur Registrasi
 * URL: /register
 *
 * Mencakup:
 * - Tampilan halaman registrasi
 * - Registrasi mahasiswa (sukses & validasi)
 * - Registrasi perusahaan (sukses & validasi dokumen)
 * - Validasi field umum (duplikat email, password tidak cocok, dll.)
 * - Guard: user yang sudah login tidak bisa akses halaman registrasi
 */
class RegistrasiTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────────────────
    // Tampilan Halaman
    // ─────────────────────────────────────────────────────────────────────────

    public function test_halaman_registrasi_dapat_diakses_oleh_tamu(): void
    {
        $response = $this->get('/register');

        $response->assertSuccessful();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Auth/Register')
            ->has('roles', 2)
        );
    }

    public function test_user_yang_sudah_login_diredirect_dari_halaman_registrasi(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);

        $this->actingAs($user)
            ->get('/register')
            ->assertRedirect('/dashboard');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Registrasi Mahasiswa
    // ─────────────────────────────────────────────────────────────────────────

    public function test_registrasi_mahasiswa_berhasil_membuat_user_dan_profil(): void
    {
        $response = $this->post('/register', [
            'role'          => 'mahasiswa',
            'name'          => 'Budi Santoso',
            'email'         => 'budi@sikara.test',
            'phone'         => '081234567890',
            'nim'           => '2021001001',
            'university'    => 'Universitas Test',
            'study_program' => 'Teknik Informatika',
            'password'      => 'password123',
            'password_confirmation' => 'password123',
            'terms'         => true,
        ]);

        // Mahasiswa diarahkan ke halaman login setelah registrasi
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');

        // User terbuat dengan role mahasiswa dan status active
        $user = User::where('email', 'budi@sikara.test')->first();
        $this->assertNotNull($user);
        $this->assertEquals('mahasiswa', $user->role);
        $this->assertEquals('active', $user->status);

        // Profil mahasiswa terbuat
        $profile = MahasiswaProfile::where('user_id', $user->id)->first();
        $this->assertNotNull($profile);
        $this->assertEquals('2021001001', $profile->nim);
        $this->assertEquals('Universitas Test', $profile->university);
        $this->assertEquals('Teknik Informatika', $profile->study_program);
    }

    public function test_registrasi_mahasiswa_gagal_tanpa_nim(): void
    {
        $response = $this->post('/register', [
            'role'          => 'mahasiswa',
            'name'          => 'Budi Santoso',
            'email'         => 'budi@sikara.test',
            'university'    => 'Universitas Test',
            'study_program' => 'Teknik Informatika',
            // 'nim' => null, // sengaja dikosongkan
            'password'      => 'password123',
            'password_confirmation' => 'password123',
            'terms'         => true,
        ]);

        $response->assertSessionHasErrors(['nim']);
        $this->assertDatabaseMissing('users', ['email' => 'budi@sikara.test']);
    }

    public function test_registrasi_mahasiswa_gagal_tanpa_university(): void
    {
        $response = $this->post('/register', [
            'role'          => 'mahasiswa',
            'name'          => 'Andi Wijaya',
            'email'         => 'andi@sikara.test',
            'nim'           => '2021002002',
            'study_program' => 'Sistem Informasi',
            // 'university' => null, // dikosongkan
            'password'      => 'password123',
            'password_confirmation' => 'password123',
            'terms'         => true,
        ]);

        $response->assertSessionHasErrors(['university']);
        $this->assertDatabaseMissing('users', ['email' => 'andi@sikara.test']);
    }

    public function test_registrasi_mahasiswa_gagal_dengan_nim_duplikat(): void
    {
        // Buat profil mahasiswa dengan NIM yang sama lebih dulu
        $existingUser = User::factory()->create(['role' => 'mahasiswa']);
        MahasiswaProfile::create([
            'user_id'       => $existingUser->id,
            'nim'           => '2021999999',
            'study_program' => 'Teknik Informatika',
            'department'    => 'Teknik Informatika',
            'university'    => 'Universitas A',
            'gpa'           => 0,
        ]);

        $response = $this->post('/register', [
            'role'          => 'mahasiswa',
            'name'          => 'Mahasiswa Baru',
            'email'         => 'baru@sikara.test',
            'nim'           => '2021999999', // NIM duplikat
            'university'    => 'Universitas B',
            'study_program' => 'Teknik Informatika',
            'password'      => 'password123',
            'password_confirmation' => 'password123',
            'terms'         => true,
        ]);

        $response->assertSessionHasErrors(['nim']);
        $this->assertDatabaseMissing('users', ['email' => 'baru@sikara.test']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Registrasi Perusahaan
    // ─────────────────────────────────────────────────────────────────────────

    public function test_registrasi_perusahaan_berhasil_dengan_dokumen_legal(): void
    {
        Storage::fake('public');

        $document = UploadedFile::fake()->create('siup.pdf', 200, 'application/pdf');

        $response = $this->post('/register', [
            'role'          => 'perusahaan',
            'name'          => 'PT Maju Jaya',
            'email'         => 'maju@sikara.test',
            'phone'         => '02112345678',
            'legal_document' => $document,
            'password'      => 'password123',
            'password_confirmation' => 'password123',
            'terms'         => true,
        ]);

        // Perusahaan diarahkan ke halaman verifikasi email setelah registrasi + auto-login
        $response->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'maju@sikara.test')->first();
        $this->assertNotNull($user);
        $this->assertEquals('perusahaan', $user->role);
        $this->assertEquals('inactive', $user->status); // Menunggu verifikasi admin

        // Profil perusahaan terbuat dengan dokumen legal
        $profile = PerusahaanProfile::where('user_id', $user->id)->first();
        $this->assertNotNull($profile);
        $this->assertNotNull($profile->legal_document_path);
        Storage::disk('public')->assertExists($profile->legal_document_path);
    }

    public function test_registrasi_perusahaan_gagal_tanpa_dokumen_legal(): void
    {
        $response = $this->post('/register', [
            'role'          => 'perusahaan',
            'name'          => 'PT Tanpa Dokumen',
            'email'         => 'tanpadok@sikara.test',
            'password'      => 'password123',
            'password_confirmation' => 'password123',
            'terms'         => true,
            // 'legal_document' => null, // sengaja tidak diisi
        ]);

        $response->assertSessionHasErrors(['legal_document']);
        $this->assertDatabaseMissing('users', ['email' => 'tanpadok@sikara.test']);
    }

    public function test_registrasi_perusahaan_gagal_dengan_dokumen_bukan_pdf(): void
    {
        Storage::fake('public');

        $document = UploadedFile::fake()->create('logo.jpg', 100, 'image/jpeg');

        $response = $this->post('/register', [
            'role'          => 'perusahaan',
            'name'          => 'PT Format Salah',
            'email'         => 'formatsalah@sikara.test',
            'legal_document' => $document,
            'password'      => 'password123',
            'password_confirmation' => 'password123',
            'terms'         => true,
        ]);

        $response->assertSessionHasErrors(['legal_document']);
        $this->assertDatabaseMissing('users', ['email' => 'formatsalah@sikara.test']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Validasi Field Umum
    // ─────────────────────────────────────────────────────────────────────────

    public function test_registrasi_gagal_dengan_email_duplikat(): void
    {
        User::factory()->create(['email' => 'existing@sikara.test']);

        $response = $this->post('/register', [
            'role'          => 'mahasiswa',
            'name'          => 'User Baru',
            'email'         => 'existing@sikara.test', // email sudah ada
            'nim'           => '2021005005',
            'university'    => 'Universitas Test',
            'study_program' => 'Teknik Informatika',
            'password'      => 'password123',
            'password_confirmation' => 'password123',
            'terms'         => true,
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_registrasi_gagal_dengan_password_tidak_cocok(): void
    {
        $response = $this->post('/register', [
            'role'          => 'mahasiswa',
            'name'          => 'User Test',
            'email'         => 'usertest@sikara.test',
            'nim'           => '2021006006',
            'university'    => 'Universitas Test',
            'study_program' => 'Teknik Informatika',
            'password'      => 'password123',
            'password_confirmation' => 'password456', // tidak cocok
            'terms'         => true,
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertDatabaseMissing('users', ['email' => 'usertest@sikara.test']);
    }

    public function test_registrasi_gagal_tanpa_persetujuan_syarat(): void
    {
        $response = $this->post('/register', [
            'role'          => 'mahasiswa',
            'name'          => 'User Test',
            'email'         => 'tanpasyarat@sikara.test',
            'nim'           => '2021007007',
            'university'    => 'Universitas Test',
            'study_program' => 'Teknik Informatika',
            'password'      => 'password123',
            'password_confirmation' => 'password123',
            // 'terms' => null, // tidak diisi
        ]);

        $response->assertSessionHasErrors(['terms']);
        $this->assertDatabaseMissing('users', ['email' => 'tanpasyarat@sikara.test']);
    }

    public function test_registrasi_gagal_dengan_role_tidak_valid(): void
    {
        $response = $this->post('/register', [
            'role'          => 'admin', // role tidak diperbolehkan
            'name'          => 'Hacker',
            'email'         => 'hacker@sikara.test',
            'password'      => 'password123',
            'password_confirmation' => 'password123',
            'terms'         => true,
        ]);

        $response->assertSessionHasErrors(['role']);
        $this->assertDatabaseMissing('users', ['email' => 'hacker@sikara.test']);
    }

    public function test_registrasi_gagal_tanpa_nama(): void
    {
        $response = $this->post('/register', [
            'role'          => 'mahasiswa',
            'name'          => '', // kosong
            'email'         => 'tanpanama@sikara.test',
            'nim'           => '2021008008',
            'university'    => 'Universitas Test',
            'study_program' => 'Teknik Informatika',
            'password'      => 'password123',
            'password_confirmation' => 'password123',
            'terms'         => true,
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_registrasi_gagal_dengan_email_tidak_valid(): void
    {
        $response = $this->post('/register', [
            'role'          => 'mahasiswa',
            'name'          => 'User Test',
            'email'         => 'email-tidak-valid', // format salah
            'nim'           => '2021009009',
            'university'    => 'Universitas Test',
            'study_program' => 'Teknik Informatika',
            'password'      => 'password123',
            'password_confirmation' => 'password123',
            'terms'         => true,
        ]);

        $response->assertSessionHasErrors(['email']);
    }
}
