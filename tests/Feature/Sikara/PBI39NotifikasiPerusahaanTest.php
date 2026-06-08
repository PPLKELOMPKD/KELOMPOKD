<?php

namespace Tests\Feature\Sikara;

use App\Models\Application;
use App\Models\Internship;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PBI39NotifikasiPerusahaanTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TC-01: Pemicu Notifikasi Sukses
     * Sistem sukses menyimpan pendaftaran dan memunculkan angka notifikasi baru di akun mitra.
     */
    public function test_tc01_pemicu_notifikasi_sukses(): void
    {
        // 1. Setup Mitra Perusahaan dan Mahasiswa
        $company = User::factory()->create([
            'role' => 'perusahaan',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // 2. Buat lowongan aktif milik mitra tersebut
        $internship = Internship::query()->create([
            'company_id' => $company->id,
            'title' => 'Software Engineer Intern',
            'company_name' => 'Mitra Perusahaan A',
            'location' => 'Jakarta',
            'description' => 'Membangun aplikasi web.',
            'requirements' => 'Paham PHP & Laravel.',
            'work_type' => 'Magang',
            'deadline_at' => Carbon::now()->addDays(10),
            'quota' => 2,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        // 3. Login sebagai mahasiswa dan kirim lamaran (Tombol Lamar)
        $response = $this->actingAs($student)
            ->post(route('internships.apply'), [
                'internship_id' => $internship->id,
            ]);

        // Pastikan lamaran berhasil disimpan
        $response->assertRedirect();
        $this->assertDatabaseHas('applications', [
            'user_id' => $student->id,
            'internship_id' => $internship->id,
            'status' => 'submitted',
        ]);

        // 4. Pastikan notifikasi baru tersimpan untuk mitra di database
        $this->assertDatabaseHas('notifications', [
            'user_id' => $company->id,
            'title' => 'New Application Received',
            'type' => 'application',
        ]);

        // 5. Cek akun mitra: memunculkan angka notifikasi baru (unreadCount => 1) di dashboard mitra
        $dashboardResponse = $this->actingAs($company)
            ->get(route('perusahaan.dashboard'));

        $dashboardResponse->assertSuccessful();
        $dashboardResponse->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('role', 'perusahaan')
            ->where('unreadCount', 1)
        );
    }

    /**
     * TC-02: Tampilan Isi Konten Notifikasi
     * Sistem memuat informasi lengkap berupa nama mahasiswa pelamar dan posisi lowongan secara akurat.
     */
    public function test_tc02_tampilan_isi_konten_notifikasi(): void
    {
        $company = User::factory()->create([
            'role' => 'perusahaan',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Budi Santoso',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $internship = Internship::query()->create([
            'company_id' => $company->id,
            'title' => 'UI/UX Designer',
            'company_name' => 'PT Design Kreatif',
            'location' => 'Bandung',
            'description' => 'Mendesain UI.',
            'requirements' => 'Paham Figma.',
            'work_type' => 'Magang',
            'deadline_at' => Carbon::now()->addDays(5),
            'quota' => 1,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        // Buat notifikasi secara eksplisit dengan format yang sesuai saat pelamar mendaftar
        $notification = Notification::create([
            'user_id' => $company->id,
            'title' => 'New Application Received',
            'message' => "{$student->name} has applied for the position {$internship->title}.",
            'type' => 'application',
            'link' => route('perusahaan.applicants.show', 1),
        ]);

        // Login sebagai mitra dan akses dashboard (Klik ikon lonceng)
        $response = $this->actingAs($company)
            ->get(route('perusahaan.dashboard'));

        $response->assertSuccessful();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('notifications', 1, fn (Assert $item) => $item
                ->where('id', $notification->id)
                ->where('title', 'New Application Received')
                ->where('message', 'Budi Santoso has applied for the position UI/UX Designer.')
                ->where('type', 'application')
                ->etc()
            )
        );

        // Akses menu notifikasi secara global
        $notifPageResponse = $this->actingAs($company)
            ->get(route('notifications.index'));

        $notifPageResponse->assertSuccessful();
        $notifPageResponse->assertInertia(fn (Assert $page) => $page
            ->component('Notifications/Index')
            ->has('items', 1, fn (Assert $item) => $item
                ->where('id', $notification->id)
                ->where('title', 'New Application Received')
                ->where('message', 'Budi Santoso has applied for the position UI/UX Designer.')
                ->where('type', 'application')
                ->etc()
            )
        );
    }

    /**
     * TC-03: Pengalihan Halaman Detail Pelamar
     * Sistem menghentikan tampilan panel dan mengarahkan mitra langsung ke halaman detail profil pelamar.
     */
    public function test_tc03_pengalihan_halaman_detail_pelamar(): void
    {
        $company = User::factory()->create([
            'role' => 'perusahaan',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $internship = Internship::query()->create([
            'company_id' => $company->id,
            'title' => 'Mobile Developer',
            'company_name' => 'PT Mobile Tech',
            'location' => 'Surabaya',
            'description' => 'Flutter/React Native.',
            'requirements' => 'Paham Git.',
            'work_type' => 'Magang',
            'deadline_at' => Carbon::now()->addDays(5),
            'quota' => 2,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        $application = Application::create([
            'user_id' => $student->id,
            'internship_id' => $internship->id,
            'status' => 'submitted',
        ]);

        // Buat notifikasi dengan field 'link' mengarah ke detail pelamar
        $notification = Notification::create([
            'user_id' => $company->id,
            'title' => 'New Application Received',
            'message' => "{$student->name} has applied for the position {$internship->title}.",
            'type' => 'application',
            'link' => route('perusahaan.applicants.show', $application->id),
        ]);

        // 1. Dapatkan daftar notifikasi di dashboard mitra, pastikan link mengarah ke detail pelamar secara akurat
        $dashboardResponse = $this->actingAs($company)
            ->get(route('perusahaan.dashboard'));

        $dashboardResponse->assertSuccessful();
        $dashboardResponse->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('notifications.0.link', route('perusahaan.applicants.show', $application->id))
        );

        // 2. Klik pada pesan tersebut (kirim GET ke URL redirect detail pelamar)
        $detailResponse = $this->actingAs($company)
            ->get($notification->link);

        // Pastikan langsung diarahkan/memuat halaman detail profil pelamar (render Company/Applicants/Show)
        $detailResponse->assertSuccessful();
        $detailResponse->assertInertia(fn (Assert $page) => $page
            ->component('Company/Applicants/Show')
            ->where('applicant.id', $application->id)
        );
    }

    /**
     * TC-04: Perubahan Status Notifikasi Dibaca
     * Sistem otomatis mengubah warna latar belakang notifikasi (read_at terisi) dan menurunkan jumlah angka merah pada ikon lonceng.
     */
    public function test_tc04_perubahan_status_notifikasi_dibaca(): void
    {
        $company = User::factory()->create([
            'role' => 'perusahaan',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $internship = Internship::query()->create([
            'company_id' => $company->id,
            'title' => 'Copywriter Intern',
            'company_name' => 'PT Media',
            'location' => 'Jakarta',
            'description' => 'Menulis artikel.',
            'requirements' => 'Paham SEO.',
            'work_type' => 'Magang',
            'deadline_at' => Carbon::now()->addDays(5),
            'quota' => 1,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        $notification = Notification::create([
            'user_id' => $company->id,
            'title' => 'New Application Received',
            'message' => "{$student->name} has applied for the position {$internship->title}.",
            'type' => 'application',
            'link' => route('perusahaan.applicants.show', 1),
            'read_at' => null,
        ]);

        // Cek dashboard pertama kali, unreadCount harus bernilai 1
        $response1 = $this->actingAs($company)
            ->get(route('perusahaan.dashboard'));
        $response1->assertInertia(fn (Assert $page) => $page
            ->where('unreadCount', 1)
            ->where('notifications.0.read_at', null)
        );

        // 1. Klik notifikasi belum dibaca (kirim POST ke route read)
        $responseRead = $this->actingAs($company)
            ->post(route('notifications.read', $notification->id));

        // Pastikan dialihkan kembali ke daftar notifikasi index
        $responseRead->assertRedirect(route('notifications.index'));

        // Pastikan di database read_at terisi
        $this->assertNotNull($notification->fresh()->read_at);

        // 2. Buka kembali dashboard (panel lonceng)
        $response2 = $this->actingAs($company)
            ->get(route('perusahaan.dashboard'));

        // 3. Pastikan unreadCount turun menjadi 0 dan read_at tidak null (mengubah warna latar belakang)
        $response2->assertInertia(fn (Assert $page) => $page
            ->where('unreadCount', 0)
            ->where('notifications.0.id', $notification->id)
            ->whereNot('notifications.0.read_at', null)
        );
    }

    /**
     * TC-05: Riwayat Notifikasi Kosong
     * Sistem menampilkan teks pemberitahuan bahwa belum ada notifikasi atau lamaran.
     */
    public function test_tc05_riwayat_notifikasi_kosong(): void
    {
        // Setup mitra baru yang belum memiliki pelamar atau notifikasi apapun
        $newCompany = User::factory()->create([
            'role' => 'perusahaan',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // 1. Login sebagai mitra
        // 2. Buka menu notifikasi (route notifications.index)
        $responseIndex = $this->actingAs($newCompany)
            ->get(route('notifications.index'));

        $responseIndex->assertSuccessful();
        $responseIndex->assertInertia(fn (Assert $page) => $page
            ->component('Notifications/Index')
            ->where('items', []) // Array items kosong
        );

        // Buka dashboard mitra baru
        $responseDashboard = $this->actingAs($newCompany)
            ->get(route('perusahaan.dashboard'));

        $responseDashboard->assertSuccessful();
        $responseDashboard->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('notifications', []) // Array notifications kosong
            ->where('unreadCount', 0)    // Jumlah unread 0
        );
    }
}
