<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Internship;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminInternshipModerationTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Helper: Login via form login sebagai admin
     */
    private function loginAsAdminViaForm(Browser $browser, User $admin): void
    {
        $browser->visit('/login')
                ->pause(1500)
                // Pilih role "Admin"
                ->click('button[dusk="role-admin"]')
                ->pause(500)
                // Isi email admin@sikara.id
                ->type('#email', $admin->email)
                ->pause(500)
                // Isi password
                ->type('#password', 'password')
                ->pause(500)
                // Klik tombol submit
                ->press('Masuk ke SIKARA')
                ->pause(2000);
    }

    /**
     * Buat user admin dengan kredensial tetap admin@sikara.id
     */
    private function createAdmin(): User
    {
        return User::factory()->create([
            'name'               => 'Admin Sikara',
            'email'              => 'admin@sikara.id',
            'role'               => 'admin',
            'status'             => 'active',
            'email_verified_at'  => now(),
        ]);
    }

    /**
     * TC-01: Melihat daftar moderasi & detail moderasi
     * Admin dapat melihat daftar lowongan magang yang membutuhkan moderasi
     * dan detail informasinya.
     */
    public function test_admin_can_view_moderation_index_and_detail()
    {
        $admin = $this->createAdmin();

        $internship = Internship::query()->create([
            'company_id'        => null,
            'title'             => 'Android Engineer Intern',
            'company_name'      => 'PT SIKARA',
            'location'          => 'Jakarta',
            'description'       => 'Membangun aplikasi Android.',
            'requirements'      => 'Menguasai Kotlin/Java.',
            'work_type'         => 'Magang',
            'deadline_at'       => now()->addDays(10),
            'quota'             => 2,
            'is_published'      => false,
            'moderation_status' => 'pending',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $internship) {
            $browser->driver->manage()->deleteAllCookies();
            // Step 1: Login sebagai Admin via form login
            $this->loginAsAdminViaForm($browser, $admin);
            // Step 2: Kunjungi /admin/internships
            $browser->visit('/admin/internships')
                    ->pause(1500)
                    ->waitForText('Android Engineer Intern')
                    ->pause(1500)
                    ->assertSee('Android Engineer Intern')
                    ->pause(1500)
                    // Step 3: Klik tombol detail pada lowongan
                    ->clickLink('Detail')
                    ->pause(1500)
                    ->waitForText('Garis Waktu Alur Lowongan')
                    ->pause(1500)
                    ->assertPathIs('/admin/internships/' . $internship->id)
                    ->pause(1500)
                    ->assertSee('Android Engineer Intern')
                    ->pause(1500)
                    // Expected: Halaman detail termuat dan menampilkan status "Menunggu Review"
                    ->assertSee('Menunggu Review')
                    ->pause(1500);
        });
    }

    /**
     * TC-02: Approve Lowongan Berhasil
     * Admin membuka detail lowongan yang berstatus pending, menyetujuinya,
     * lalu perusahaan melihat status berubah menjadi Aktif.
     *
     * Catatan: Tombol "Setujui & Tayangkan" pada Show.vue langsung mengirim
     * request tanpa konfirmasi modal tambahan.
     */
    public function test_admin_can_approve_internship()
    {
        $admin     = $this->createAdmin();
        $perusahaan = User::factory()->create(['role' => 'perusahaan', 'status' => 'active']);

        $internship = Internship::query()->create([
            'company_id'        => $perusahaan->id,
            'title'             => 'Data Analyst Intern',
            'company_name'      => 'PT SIKARA',
            'location'          => 'Jakarta',
            'description'       => 'Menganalisis data performa.',
            'requirements'      => 'Menguasai SQL/Python.',
            'work_type'         => 'Magang',
            'deadline_at'       => now()->addDays(10),
            'quota'             => 3,
            'is_published'      => false,
            'moderation_status' => 'pending',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $perusahaan, $internship) {
            $browser->driver->manage()->deleteAllCookies();
            // Step 1: Login sebagai admin via form login
            $this->loginAsAdminViaForm($browser, $admin);
            // Step 2: Buka detail lowongan berstatus pending
            $browser->visit('/admin/internships/' . $internship->id)
                    ->pause(1500)
                    ->waitForText('Setujui & Tayangkan')
                    ->pause(1500)
                    // Step 3: Klik tombol "Setujui & Tayangkan" (langsung approve, tanpa modal konfirmasi)
                    ->press('Setujui & Tayangkan')
                    ->pause(1500)
                    // Expected (sisi admin): Status lowongan berubah menjadi "Disetujui & Tayang"
                    ->waitForText('Disetujui & Tayang')
                    ->pause(1500)
                    ->assertSee('Disetujui & Tayang')
                    ->pause(1500)
                    // Step 4: Logout dari akun Admin
                    ->logout()
                    ->pause(1500)
                    // Step 5: Login ke platform menggunakan akun Perusahaan Mitra via form login
                    ->visit('/login')
                    ->pause(1500)
                    ->click('button[dusk="role-perusahaan"]')
                    ->pause(500)
                    ->type('#email', $perusahaan->email)
                    ->pause(500)
                    ->type('#password', 'password')
                    ->pause(500)
                    ->press('Masuk ke SIKARA')
                    ->pause(2000)
                    // Step 6: Buka Dashboard Perusahaan / Riwayat Lowongan
                    ->visit('/perusahaan/internships')
                    ->pause(1500)
                    ->waitForText('Data Analyst Intern')
                    ->pause(1500)
                    // Expected: Lowongan kini berstatus "Aktif" di sisi perusahaan
                    ->assertSee('Data Analyst Intern')
                    ->pause(1500)
                    ->assertSee('Aktif')
                    ->pause(1500);
        });
    }

    /**
     * TC-03: Reject Lowongan Berhasil
     * Admin membuka detail lowongan yang berstatus pending, menolaknya dengan
     * alasan yang valid, lalu perusahaan melihat status berubah menjadi Ditolak.
     */
    public function test_admin_can_reject_internship()
    {
        $admin     = $this->createAdmin();
        $perusahaan = User::factory()->create(['role' => 'perusahaan', 'status' => 'active']);

        $internship = Internship::query()->create([
            'company_id'        => $perusahaan->id,
            'title'             => 'UI/UX Designer Intern',
            'company_name'      => 'PT SIKARA',
            'location'          => 'Bandung',
            'description'       => 'Mendesain antarmuka aplikasi.',
            'requirements'      => 'Menguasai Figma.',
            'work_type'         => 'Magang',
            'deadline_at'       => now()->addDays(10),
            'quota'             => 2,
            'is_published'      => false,
            'moderation_status' => 'pending',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $perusahaan, $internship) {
            $browser->driver->manage()->deleteAllCookies();
            // Step 1: Login sebagai admin (lowongan berstatus pending) via form login
            $this->loginAsAdminViaForm($browser, $admin);
            // Step 2: Buka detail lowongan berstatus pending
            $browser->visit('/admin/internships/' . $internship->id)
                    ->pause(1500)
                    ->waitForText('Tolak Lowongan')
                    ->pause(1500)
                    // Step 3: Klik tombol "Tolak"
                    ->press('Tolak Lowongan')
                    ->pause(1500)
                    // Step 4: Isi kolom teks di modal alasan
                    // Input: "Deskripsi tugas tidak relevan dengan prodi terkait."
                    ->waitForText('Tolak Lowongan')
                    ->pause(1500)
                    ->type('textarea', 'Deskripsi tugas tidak relevan dengan prodi terkait.')
                    ->pause(1500)
                    // Step 5: Klik "Ya, Tolak Lowongan"
                    ->press('Ya, Tolak Lowongan')
                    ->pause(1500)
                    // Expected (sisi admin): Status lowongan berubah menjadi Ditolak
                    ->waitForText('Ditolak')
                    ->pause(1500)
                    ->assertSee('Ditolak')
                    ->pause(1500)
                    // Step 6: Logout dari akun Admin
                    ->logout()
                    ->pause(1500)
                    // Step 7: Login ke platform menggunakan akun Perusahaan Mitra via form login
                    ->visit('/login')
                    ->pause(1500)
                    ->click('button[dusk="role-perusahaan"]')
                    ->pause(500)
                    ->type('#email', $perusahaan->email)
                    ->pause(500)
                    ->type('#password', 'password')
                    ->pause(500)
                    ->press('Masuk ke SIKARA')
                    ->pause(2000)
                    // Step 8: Buka Dashboard Perusahaan / Riwayat Lowongan
                    ->visit('/perusahaan/internships')
                    ->pause(1500)
                    ->waitForText('Daftar Lowongan')
                    ->pause(1500)
                    // Expected: Status lowongan di sisi perusahaan berubah menjadi "Ditolak"
                    ->assertSee('Ditolak')
                    ->pause(1500);
        });
    }

    /**
     * TC-04: Proteksi Akses (Non-Admin)
     * User Mahasiswa atau Perusahaan yang sedang login tidak bisa mengakses
     * rute moderasi admin dan akan diarahkan ke halaman 403 Forbidden.
     */
    public function test_non_admin_cannot_access_moderation()
    {
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $this->browse(function (Browser $browser) use ($mahasiswa) {
            $browser->driver->manage()->deleteAllCookies();
            // Step 1: Login sebagai Mahasiswa via form login
            $browser->visit('/login')
                    ->pause(1500)
                    ->click('button[dusk="role-mahasiswa"]')
                    ->pause(500)
                    ->type('#email', $mahasiswa->email)
                    ->pause(500)
                    ->type('#password', 'password')
                    ->pause(500)
                    ->press('Masuk ke SIKARA')
                    ->pause(2000)
                    // Step 2: Akses paksa URL rute moderasi admin
                    ->visit('/admin/internships')
                    ->pause(1500)
                    ->waitForText('403')
                    ->pause(1500)
                    // Expected: Sistem menolak akses dan mengarahkan ke halaman 403 Forbidden
                    ->assertSee('403')
                    ->pause(1500)
                    ->assertSee('FORBIDDEN')
                    ->pause(1500)
                    ->assertPathIs('/admin/internships')
                    ->pause(1500);
        });
    }

    /**
     * TC-05: Takedown Lowongan Berhasil & Cek Status Mitra
     * Admin men-takedown lowongan yang sudah Approved/Active,
     * lalu perusahaan melihat status berubah menjadi Ditutup (Takedown).
     *
     * Catatan: Setelah takedown, moderation_status di DB menjadi 'closed'
     * dan tampil sebagai "Ditutup (Takedown)" di detail admin.
     */
    public function test_admin_can_takedown_approved_internship()
    {
        $admin     = $this->createAdmin();
        $perusahaan = User::factory()->create(['role' => 'perusahaan', 'status' => 'active']);

        $internship = Internship::query()->create([
            'company_id'        => $perusahaan->id,
            'title'             => 'Backend Dev Intern',
            'company_name'      => 'PT SIKARA',
            'location'          => 'Surabaya',
            'description'       => 'Membangun API Laravel.',
            'requirements'      => 'Menguasai PHP.',
            'work_type'         => 'Magang',
            'deadline_at'       => now()->addDays(10),
            'quota'             => 3,
            'is_published'      => true,
            'moderation_status' => 'approved',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $perusahaan, $internship) {
            $browser->driver->manage()->deleteAllCookies();
            // Step 1: Login sebagai admin via form login
            $this->loginAsAdminViaForm($browser, $admin);
            // Step 2: Buka detail lowongan aktif tersebut
            $browser->visit('/admin/internships/' . $internship->id)
                    ->pause(1500)
                    ->waitForText('Takedown Lowongan')
                    ->pause(1500)
                    // Step 3: Klik tombol "Takedown Lowongan"
                    ->press('Takedown Lowongan')
                    ->pause(1500)
                    // Step 4: Isi alasan pencabutan lowongan (>10 karakter)
                    // Input: "Lowongan ini ditarik kembali karena alasan kepatuhan internal."
                    ->waitForText('Takedown Lowongan')
                    ->pause(1500)
                    ->type('textarea', 'Lowongan ini ditarik kembali karena alasan kepatuhan internal.')
                    ->pause(1500)
                    // Step 5: Klik "Ya, Cabut Lowongan"
                    ->press('Ya, Cabut Lowongan')
                    ->pause(3000)
                    // Expected (sisi admin): Muncul status "Ditutup (Takedown)" atau info pencabutan
                    ->waitForText('Ditutup (Takedown)', 15)
                    ->pause(1500)
                    ->assertSee('Ditutup (Takedown)')
                    ->pause(1500)
                    // Step 6: Logout dari akun Admin
                    ->logout()
                    ->pause(1500)
                    // Step 7: Login menggunakan akun Perusahaan Mitra via form login
                    ->visit('/login')
                    ->pause(1500)
                    ->click('button[dusk="role-perusahaan"]')
                    ->pause(500)
                    ->type('#email', $perusahaan->email)
                    ->pause(500)
                    ->type('#password', 'password')
                    ->pause(500)
                    ->press('Masuk ke SIKARA')
                    ->pause(2000)
                    // Step 8: Buka halaman Lowongan Saya / Riwayat Aktivitas
                    ->visit('/perusahaan/internships')
                    ->pause(1500)
                    ->waitForText('Daftar Lowongan')
                    ->pause(1500)
                    // Expected: Status lowongan di sisi perusahaan berubah menjadi "Ditutup"
                    ->assertSee('Ditutup')
                    ->pause(1500);
        });
    }

    /**
     * TC-06: Validasi Alasan Penolakan Kosong/Kurang
     * Ketika admin mengisi alasan penolakan kurang dari 10 karakter,
     * sistem menampilkan pesan validasi error.
     */
    public function test_admin_rejection_validation_error()
    {
        $admin = $this->createAdmin();

        $internship = Internship::query()->create([
            'company_id'        => null,
            'title'             => 'React Developer Intern',
            'company_name'      => 'PT SIKARA',
            'location'          => 'Jakarta',
            'description'       => 'Frontend Web.',
            'requirements'      => 'React.',
            'work_type'         => 'Magang',
            'deadline_at'       => now()->addDays(10),
            'quota'             => 1,
            'is_published'      => false,
            'moderation_status' => 'pending',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $internship) {
            $browser->driver->manage()->deleteAllCookies();
            // Step 1: Login sebagai admin via form login
            $this->loginAsAdminViaForm($browser, $admin);
            // Step 2: Buka detail lowongan pending
            $browser->visit('/admin/internships/' . $internship->id)
                    ->pause(1500)
                    ->waitForText('Tolak Lowongan')
                    ->pause(1500)
                    // Step 3: Klik "Tolak Lowongan"
                    ->press('Tolak Lowongan')
                    ->pause(1500)
                    ->waitForText('Tolak Lowongan')
                    ->pause(1500)
                    // Step 4: Ketik kurang dari 10 karakter di modal
                    // Input: "Salah" (5 karakter, < 10)
                    ->type('textarea', 'Salah')
                    ->pause(1500)
                    // Step 5: Klik "Ya, Tolak Lowongan"
                    ->press('Ya, Tolak Lowongan')
                    ->pause(1500)
                    // Expected: Terdapat alert error validasi dan status tidak berubah
                    ->waitForText('must be at least 10 characters', 15)
                    ->pause(1500)
                    ->assertSee('must be at least 10 characters')
                    ->pause(1500);
        });
    }

    /**
     * TC-07: Validasi Alasan Takedown Kosong/Kurang
     * Ketika admin mengisi alasan takedown kurang dari 10 karakter,
     * sistem menampilkan pesan validasi error.
     */
    public function test_admin_takedown_validation_error()
    {
        $admin = $this->createAdmin();

        $internship = Internship::query()->create([
            'company_id'        => null,
            'title'             => 'Vue Developer Intern',
            'company_name'      => 'PT SIKARA',
            'location'          => 'Jakarta',
            'description'       => 'Frontend Web Vue.',
            'requirements'      => 'Vue.',
            'work_type'         => 'Magang',
            'deadline_at'       => now()->addDays(10),
            'quota'             => 1,
            'is_published'      => true,
            'moderation_status' => 'approved',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $internship) {
            $browser->driver->manage()->deleteAllCookies();
            // Step 1: Login sebagai admin via form login
            $this->loginAsAdminViaForm($browser, $admin);
            // Step 2: Buka detail lowongan approved
            $browser->visit('/admin/internships/' . $internship->id)
                    ->pause(1500)
                    ->waitForText('Takedown Lowongan')
                    ->pause(1500)
                    // Step 3: Klik "Takedown Lowongan"
                    ->press('Takedown Lowongan')
                    ->pause(1500)
                    ->waitForText('Takedown Lowongan')
                    ->pause(1500)
                    // Step 4: Ketik kurang dari 10 karakter
                    // Input: "Salah" (5 karakter, < 10)
                    ->type('textarea', 'Salah')
                    ->pause(1500)
                    // Step 5: Klik "Ya, Cabut Lowongan"
                    ->press('Ya, Cabut Lowongan')
                    ->pause(1500)
                    // Expected: Terdapat alert error validasi dan status tidak berubah
                    ->waitForText('must be at least 10 characters')
                    ->pause(1500)
                    ->assertSee('must be at least 10 characters')
                    ->pause(1500);
        });
    }

    /**
     * TC-08: Filter Daftar Moderasi Berdasarkan Status
     * Admin dapat memfilter daftar lowongan berdasarkan tab status:
     * Approved (Disetujui), Pending, Ditolak (Rejected), dan Ditutup (Closed).
     *
     * Catatan: Tab key di Index.vue adalah 'all', 'pending', 'approved', 'rejected', 'closed'.
     * Status setelah takedown di DB adalah 'closed' (bukan 'takedown').
     * Dusk attribute: @moderation-tab-{key}
     */
    public function test_admin_can_filter_moderation_by_status()
    {
        $admin = $this->createAdmin();

        // Internship A: Approved / Active
        Internship::query()->create([
            'company_id'        => null,
            'title'             => 'Approved Position',
            'company_name'      => 'PT Approved',
            'location'          => 'Jakarta',
            'description'       => 'Deskripsi pekerjaan approved.',
            'requirements'      => 'PHP.',
            'work_type'         => 'Magang',
            'deadline_at'       => now()->addDays(10),
            'quota'             => 1,
            'is_published'      => true,
            'moderation_status' => 'approved',
        ]);

        // Internship B: Pending
        Internship::query()->create([
            'company_id'        => null,
            'title'             => 'Pending Position',
            'company_name'      => 'PT Pending',
            'location'          => 'Jakarta',
            'description'       => 'Deskripsi pekerjaan pending.',
            'requirements'      => 'PHP.',
            'work_type'         => 'Magang',
            'deadline_at'       => now()->addDays(10),
            'quota'             => 1,
            'is_published'      => false,
            'moderation_status' => 'pending',
        ]);

        // Internship C: Rejected
        Internship::query()->create([
            'company_id'        => null,
            'title'             => 'Rejected Position',
            'company_name'      => 'PT Rejected',
            'location'          => 'Jakarta',
            'description'       => 'Deskripsi pekerjaan ditolak.',
            'requirements'      => 'PHP.',
            'work_type'         => 'Magang',
            'deadline_at'       => now()->addDays(10),
            'quota'             => 1,
            'is_published'      => false,
            'moderation_status' => 'rejected',
        ]);

        // Internship D: Closed (Takedown) — status DB pakai 'closed' sesuai sistem
        Internship::query()->create([
            'company_id'        => null,
            'title'             => 'Closed Position',
            'company_name'      => 'PT Closed',
            'location'          => 'Jakarta',
            'description'       => 'Deskripsi pekerjaan ditutup.',
            'requirements'      => 'PHP.',
            'work_type'         => 'Magang',
            'deadline_at'       => now()->addDays(10),
            'quota'             => 1,
            'is_published'      => false,
            'moderation_status' => 'closed',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->driver->manage()->deleteAllCookies();
            // Step 1: Login sebagai admin via form login
            $this->loginAsAdminViaForm($browser, $admin);
            // Step 2: Buka halaman moderasi lowongan (default: tampil semua)
            $browser->visit('/admin/internships')
                    ->pause(1500)
                    ->waitForText('Pending Position')
                    ->pause(1500)
                    ->assertSee('Pending Position')
                    ->pause(1500)
                    ->assertSee('Approved Position')
                    ->pause(1500)

                    // Step 3: Klik tab filter "Disetujui" (key: 'approved')
                    // Input: Navigasi URL /admin/internships, klik tab "Approved"
                    ->click('@moderation-tab-approved')
                    ->pause(2000)
                    ->waitForText('Approved Position', 15)
                    ->pause(1500)
                    // Expected: Hanya lowongan berstatus "Approved" yang ditampilkan
                    ->assertSee('Approved Position')
                    ->pause(1500)
                    ->assertDontSee('Pending Position')
                    ->pause(1500)

                    // Step 4: Klik tab filter "Pending" (key: 'pending')
                    ->click('@moderation-tab-pending')
                    ->pause(2000)
                    ->waitForText('Pending Position', 15)
                    ->pause(1500)
                    // Expected: Hanya lowongan berstatus "Pending" yang ditampilkan
                    ->assertSee('Pending Position')
                    ->pause(1500)
                    ->assertDontSee('Approved Position')
                    ->pause(1500)

                    // Step 5: Klik tab filter "Ditolak" (key: 'rejected')
                    ->click('@moderation-tab-rejected')
                    ->pause(2000)
                    ->waitForText('Rejected Position', 15)
                    ->pause(1500)
                    // Expected: Hanya lowongan berstatus "Ditolak" yang ditampilkan
                    ->assertSee('Rejected Position')
                    ->pause(1500)
                    ->assertDontSee('Pending Position')
                    ->pause(1500)

                    // Step 6: Klik tab filter "Ditutup" (key: 'closed', bukan 'takedown')
                    ->click('@moderation-tab-closed')
                    ->pause(2000)
                    ->waitForText('Closed Position', 15)
                    ->pause(1500)
                    // Expected: Hanya lowongan berstatus "Ditutup" yang ditampilkan
                    ->assertSee('Closed Position')
                    ->pause(1500)
                    ->assertDontSee('Pending Position')
                    ->pause(1500);
        });
    }
}
