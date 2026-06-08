<?php

namespace Tests\Feature\Sikara;

use App\Models\Internship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PBI38ValidasiDeadlineLowonganTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TC-01: Akses Lowongan Aktif Berhasil
     * Sistem menampilkan status Aktif dengan tombol lamar berwarna hijau yang bisa diklik.
     * Di Inertia prop: 'isExpired' => false.
     */
    public function test_tc01_akses_lowongan_aktif_berhasil(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);

        // Buat lowongan aktif dengan deadline di masa depan
        $internship = Internship::query()->create([
            'company_id' => null,
            'title' => 'Software Engineer Intern',
            'company_name' => 'PT SIKARA',
            'location' => 'Jakarta',
            'description' => 'Membangun aplikasi backend.',
            'requirements' => 'Menguasai Laravel.',
            'work_type' => 'Magang',
            'deadline_at' => Carbon::now()->addDays(5),
            'quota' => 3,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        $response = $this->actingAs($student)
            ->get(route('internships.show', $internship->id));

        $response->assertSuccessful();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Internships/Show')
            ->where('internship.id', $internship->id)
            ->where('isExpired', false)
        );
    }

    /**
     * TC-02: Proteksi Lowongan Kedaluwarsa
     * Sistem otomatis menampilkan status Expired dengan tombol lamar tidak aktif / disabled.
     * Di Inertia prop: 'isExpired' => true.
     */
    public function test_tc02_proteksi_lowongan_kedaluwarsa(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);

        // Buat lowongan kedaluwarsa dengan deadline di masa lalu
        $internship = Internship::query()->create([
            'company_id' => null,
            'title' => 'Product Designer Intern',
            'company_name' => 'PT SIKARA',
            'location' => 'Bandung',
            'description' => 'Mendesain UI/UX aplikasi.',
            'requirements' => 'Menguasai Figma.',
            'work_type' => 'Magang',
            'deadline_at' => Carbon::now()->subDays(2),
            'quota' => 2,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        $response = $this->actingAs($student)
            ->get(route('internships.show', $internship->id));

        $response->assertSuccessful();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Internships/Show')
            ->where('internship.id', $internship->id)
            ->where('isExpired', true)
        );
    }

    /**
     * TC-03: Blokir Akses URL Pendaftaran
     * Ketika mencoba mengirim lamaran ke lowongan expired via POST, sistem memblokir
     * dan memunculkan pesan error "Batas waktu pendaftaran lowongan ini telah berakhir. Anda tidak dapat melamar lagi."
     */
    public function test_tc03_blokir_akses_url_pendaftaran(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);

        // Buat lowongan expired
        $internship = Internship::query()->create([
            'company_id' => null,
            'title' => 'Data Analyst Intern',
            'company_name' => 'PT SIKARA',
            'location' => 'Jakarta',
            'description' => 'Menganalisis data performa.',
            'requirements' => 'Menguasai Python.',
            'work_type' => 'Magang',
            'deadline_at' => Carbon::now()->subDays(1),
            'quota' => 2,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        $response = $this->actingAs($student)
            ->from(route('internships.show', $internship->id))
            ->post(route('internships.apply'), [
                'internship_id' => $internship->id,
            ]);

        // Harus redirect back ke halaman show lowongan
        $response->assertRedirect(route('internships.show', $internship->id));
        $response->assertSessionHas('error', 'The application deadline for this job listing has passed. You can no longer apply.');

        // Pastikan lamaran tidak masuk ke database
        $this->assertDatabaseMissing('applications', [
            'user_id' => $student->id,
            'internship_id' => $internship->id,
        ]);
    }

    /**
     * TC-04: Validasi Input Tanggal Mundur
     * Mitra memasukkan tanggal deadline kemarin.
     * Sistem menolak simpan data dan memunculkan error validation pada kolom deadline_at.
     */
    public function test_tc04_validasi_input_tanggal_mundur(): void
    {
        $company = User::factory()->create(['role' => 'perusahaan']);

        // Skenario 1: Saat STORE (membuat lowongan baru)
        $responseStore = $this->actingAs($company)
            ->post(route('perusahaan.internships.store'), [
                'title' => 'QA Engineer Intern',
                'company_name' => 'PT SIKARA',
                'description' => 'Menguji aplikasi.',
                'requirements' => 'Paham SDLC.',
                'location' => 'Surabaya',
                'work_type' => 'Magang',
                'quota' => 2,
                'deadline_at' => Carbon::yesterday()->format('Y-m-d'),
            ]);

        $responseStore->assertSessionHasErrors([
            'deadline_at' => 'Application deadline cannot be earlier than today.'
        ]);

        // Skenario 2: Saat UPDATE (mengubah lowongan yang ada)
        $internship = Internship::query()->create([
            'company_id' => $company->id,
            'title' => 'QA Engineer Intern',
            'company_name' => 'PT SIKARA',
            'location' => 'Surabaya',
            'description' => 'Menguji aplikasi.',
            'requirements' => 'Paham SDLC.',
            'work_type' => 'Magang',
            'deadline_at' => Carbon::tomorrow(),
            'quota' => 2,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        $responseUpdate = $this->actingAs($company)
            ->put(route('perusahaan.internships.update', $internship->id), [
                'title' => 'QA Engineer Intern (Updated)',
                'company_name' => 'PT SIKARA',
                'description' => 'Menguji aplikasi.',
                'requirements' => 'Paham SDLC.',
                'location' => 'Surabaya',
                'work_type' => 'Magang',
                'quota' => 2,
                'deadline_at' => Carbon::yesterday()->format('Y-m-d'),
            ]);

        $responseUpdate->assertSessionHasErrors([
            'deadline_at' => 'Application deadline cannot be earlier than today.'
        ]);
        
        // Pastikan deadline_at di database TIDAK berubah menjadi kemarin
        $this->assertEquals(
            Carbon::tomorrow()->toDateString(), 
            $internship->fresh()->deadline_at->toDateString()
        );
    }

    /**
     * TC-05: Update Status Otomatis
     * Lowongan aktif melewati jam pergantian hari (waktu berpindah melewati deadline).
     * Sistem otomatis mengubah status menjadi Expired (isExpired => true) pada akses berikutnya.
     */
    public function test_tc05_update_status_otomatis(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);

        // Tentukan waktu sekarang: 8 Juni 2026 pukul 10:00:00
        Carbon::setTestNow(Carbon::create(2026, 6, 8, 10, 0, 0));

        // Buat lowongan dengan deadline hari ini pukul 23:59:59 (masih aktif)
        $internship = Internship::query()->create([
            'company_id' => null,
            'title' => 'DevOps Intern',
            'company_name' => 'PT SIKARA',
            'location' => 'Jakarta',
            'description' => 'Setup CI/CD pipeline.',
            'requirements' => 'Mengerti Docker.',
            'work_type' => 'Magang',
            'deadline_at' => Carbon::create(2026, 6, 8, 23, 59, 59),
            'quota' => 1,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        // 1. Cek pertama kali (waktu masih jam 10:00): harusnya isExpired => false
        $response1 = $this->actingAs($student)
            ->get(route('internships.show', $internship->id));
        $response1->assertSuccessful();
        $response1->assertInertia(fn (Assert $page) => $page
            ->component('Internships/Show')
            ->where('isExpired', false)
        );

        // 2. Majukan waktu sistem ke tanggal esok hari (9 Juni 2026 pukul 00:00:01)
        Carbon::setTestNow(Carbon::create(2026, 6, 9, 0, 0, 1));

        // 3. Akses kembali detail lowongan: harusnya isExpired => true
        $response2 = $this->actingAs($student)
            ->get(route('internships.show', $internship->id));
        $response2->assertSuccessful();
        $response2->assertInertia(fn (Assert $page) => $page
            ->component('Internships/Show')
            ->where('isExpired', true)
        );

        // Reset waktu testing Carbon agar tidak mengganggu test case lain
        Carbon::setTestNow();
    }
}
