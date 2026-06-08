<?php

namespace Tests\Feature\Sikara;

use App\Models\Application;
use App\Models\Internship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PBI41LaporanDanAnalitikTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TC-01: Pemuatan Ringkasan Statistik Berhasil
     * Sistem sukses menampilkan total lowongan aktif, total pelamar, dan pelamar diproses secara tepat.
     */
    public function test_tc01_pemuatan_ringkasan_statistik_berhasil(): void
    {
        // 1. Setup Mitra Perusahaan
        $company = User::factory()->create([
            'role' => 'perusahaan',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Setup 3 Mahasiswa berbeda untuk memenuhi unique constraint pelamar per lowongan
        $student1 = User::factory()->create(['role' => 'mahasiswa', 'status' => 'active', 'email_verified_at' => now()]);
        $student2 = User::factory()->create(['role' => 'mahasiswa', 'status' => 'active', 'email_verified_at' => now()]);
        $student3 = User::factory()->create(['role' => 'mahasiswa', 'status' => 'active', 'email_verified_at' => now()]);

        // 2. Buat lowongan aktif dan terbit
        $internship = Internship::query()->create([
            'company_id' => $company->id,
            'title' => 'Software Engineer Intern',
            'company_name' => 'Mitra Perusahaan A',
            'location' => 'Jakarta',
            'description' => 'Backend development.',
            'requirements' => 'Paham Laravel.',
            'work_type' => 'Magang',
            'deadline_at' => Carbon::now()->addDays(10),
            'quota' => 5,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        // 3. Buat beberapa lamaran dengan status berbeda oleh mahasiswa berbeda
        // Lamaran 1: status submitted (belum diproses)
        Application::create([
            'user_id' => $student1->id,
            'internship_id' => $internship->id,
            'status' => 'submitted',
        ]);

        // Lamaran 2: status wawancara (diproses)
        Application::create([
            'user_id' => $student2->id,
            'internship_id' => $internship->id,
            'status' => 'wawancara',
        ]);

        // Lamaran 3: status lolos (diproses)
        Application::create([
            'user_id' => $student3->id,
            'internship_id' => $internship->id,
            'status' => 'lolos',
        ]);

        // 4. Login sebagai mitra & akses halaman laporan (/perusahaan/reports)
        $response = $this->actingAs($company)
            ->get(route('perusahaan.reports.index'));

        // 5. Cek angka ringkasan statistik
        $response->assertSuccessful();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Company/Reports/Index')
            ->where('stats.active_internships', 1)
            ->where('stats.total_applicants', 3)
            ->where('stats.processed_applicants', 2) // wawancara + lolos
        );

        // Akses halaman dashboard untuk memverifikasi ringkasan stats utama di dashboard
        $dashboardResponse = $this->actingAs($company)
            ->get(route('perusahaan.dashboard'));

        $dashboardResponse->assertSuccessful();
        $dashboardResponse->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('role', 'perusahaan')
            ->where('stats.0.value', '1') // LOWONGAN AKTIF
            ->where('stats.1.value', '3') // TOTAL PELAMAR
        );
    }

    /**
     * TC-02: Akurasi Kalkulasi Persentase Kuota
     * Sistem menampilkan angka persentase keterisian kuota yang akurat hasil pembagian pelamar dengan kuota.
     */
    public function test_tc02_akurasi_kalkulasi_persentase_kuota(): void
    {
        $company = User::factory()->create([
            'role' => 'perusahaan',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        
        // Setup 2 Mahasiswa berbeda
        $student1 = User::factory()->create(['role' => 'mahasiswa', 'status' => 'active', 'email_verified_at' => now()]);
        $student2 = User::factory()->create(['role' => 'mahasiswa', 'status' => 'active', 'email_verified_at' => now()]);

        // Buat lowongan dengan kuota = 4
        $internship = Internship::query()->create([
            'company_id' => $company->id,
            'title' => 'Product Designer Intern',
            'company_name' => 'Mitra Perusahaan A',
            'location' => 'Jakarta',
            'description' => 'UI/UX design.',
            'requirements' => 'Paham Figma.',
            'work_type' => 'Magang',
            'deadline_at' => Carbon::now()->addDays(5),
            'quota' => 4,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        // Buat 2 pelamar yang mendaftar (2 / 4 = 50%) oleh mahasiswa berbeda
        Application::create([
            'user_id' => $student1->id,
            'internship_id' => $internship->id,
            'status' => 'submitted',
        ]);
        Application::create([
            'user_id' => $student2->id,
            'internship_id' => $internship->id,
            'status' => 'wawancara',
        ]);

        // Login dan buka tabel laporan lowongan
        $response = $this->actingAs($company)
            ->get(route('perusahaan.reports.index'));

        // Cek kolom persentase kuota di internships_data
        $response->assertSuccessful();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Company/Reports/Index')
            ->where('internships_data.0.id', $internship->id)
            ->where('internships_data.0.quota', 4)
            ->where('internships_data.0.applicants_count', 2)
            ->where('internships_data.0.quota_percentage', 50) // Harus tepat 50%
        );
    }

    /**
     * TC-03: Tampilan Grafik Perbandingan Performa
     * Sistem memuat grafik batang yang menunjukkan perbedaan tinggi volume pelamar antar tiap lowongan.
     */
    public function test_tc03_tampilan_grafik_perbandingan_performa(): void
    {
        $company = User::factory()->create([
            'role' => 'perusahaan',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        
        // Setup 4 Mahasiswa berbeda
        $students = User::factory()->count(4)->create([
            'role' => 'mahasiswa',
            'status' => 'active',
            'email_verified_at' => now()
        ]);

        // Lowongan A: 3 Pelamar
        $internshipA = Internship::query()->create([
            'company_id' => $company->id,
            'title' => 'Backend Developer',
            'company_name' => 'PT A',
            'location' => 'Bandung',
            'description' => 'Desc A',
            'requirements' => 'Req A',
            'work_type' => 'Magang',
            'deadline_at' => Carbon::now()->addDays(5),
            'quota' => 5,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        // Lowongan B: 1 Pelamar
        $internshipB = Internship::query()->create([
            'company_id' => $company->id,
            'title' => 'Frontend Developer',
            'company_name' => 'PT B',
            'location' => 'Bandung',
            'description' => 'Desc B',
            'requirements' => 'Req B',
            'work_type' => 'Magang',
            'deadline_at' => Carbon::now()->addDays(5),
            'quota' => 5,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        // Tambah 3 pelamar berbeda untuk Lowongan A
        for ($i = 0; $i < 3; $i++) {
            Application::create([
                'user_id' => $students[$i]->id,
                'internship_id' => $internshipA->id,
                'status' => 'submitted',
            ]);
        }

        // Tambah 1 pelamar (mahasiswa ke-4) untuk Lowongan B
        Application::create([
            'user_id' => $students[3]->id,
            'internship_id' => $internshipB->id,
            'status' => 'submitted',
        ]);

        // Akses menu analitik (laporan index)
        $response = $this->actingAs($company)
            ->get(route('perusahaan.reports.index'));

        // Pastikan data grafik memuat perbedaan volume pelamar dengan benar
        $response->assertSuccessful();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Company/Reports/Index')
            ->has('internships_data', 2)
            ->where('internships_data.0.applicants_count', 3)
            ->where('internships_data.1.applicants_count', 1)
        );
    }

    /**
     * TC-04: Respons Filter Waktu Laporan
     * Sistem otomatis memperbarui data angka dan visual grafik hanya untuk rekrutmen yang terjadi di bulan mei.
     */
    public function test_tc04_respons_filter_waktu_laporan(): void
    {
        $company = User::factory()->create([
            'role' => 'perusahaan',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        
        // Setup 2 Mahasiswa berbeda
        $student1 = User::factory()->create(['role' => 'mahasiswa', 'status' => 'active', 'email_verified_at' => now()]);
        $student2 = User::factory()->create(['role' => 'mahasiswa', 'status' => 'active', 'email_verified_at' => now()]);

        $internship = Internship::query()->create([
            'company_id' => $company->id,
            'title' => 'Data Scientist Intern',
            'company_name' => 'PT Data',
            'location' => 'Jakarta',
            'description' => 'Desc',
            'requirements' => 'Req',
            'work_type' => 'Magang',
            'deadline_at' => Carbon::now()->addDays(5),
            'quota' => 2,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        // Buat 1 lamaran di bulan Mei 2026 (misal tanggal 15 Mei 2026) oleh student1
        $appMei = new Application();
        $appMei->timestamps = false; // Bypass Eloquent auto timestamps
        $appMei->fill([
            'user_id' => $student1->id,
            'internship_id' => $internship->id,
            'status' => 'submitted',
        ]);
        $appMei->created_at = Carbon::create(2026, 5, 15, 10, 0, 0);
        $appMei->updated_at = Carbon::create(2026, 5, 15, 10, 0, 0);
        $appMei->save();

        // Buat 1 lamaran di bulan Juni 2026 (misal tanggal 5 Juni 2026) oleh student2
        $appJuni = new Application();
        $appJuni->timestamps = false;
        $appJuni->fill([
            'user_id' => $student2->id,
            'internship_id' => $internship->id,
            'status' => 'wawancara',
        ]);
        $appJuni->created_at = Carbon::create(2026, 6, 5, 10, 0, 0);
        $appJuni->updated_at = Carbon::create(2026, 6, 5, 10, 0, 0);
        $appJuni->save();

        // 1. Klik drop down filter waktu (kirim query filter: month = 5 (Mei), year = 2026)
        $responseMei = $this->actingAs($company)
            ->get(route('perusahaan.reports.index', ['month' => '5', 'year' => '2026']));

        // Cek perubahan data: hanya data rekrutmen bulan Mei yang masuk hitungan
        $responseMei->assertSuccessful();
        $responseMei->assertInertia(fn (Assert $page) => $page
            ->component('Company/Reports/Index')
            ->where('filters.month', '5')
            ->where('filters.year', '2026')
            ->where('stats.total_applicants', 1)
            ->where('stats.processed_applicants', 0) // Lamaran Mei bertipe 'submitted' (belum diproses)
            ->where('internships_data.0.applicants_count', 1)
        );

        // 2. Kirim query filter: month = 6 (Juni), year = 2026
        $responseJuni = $this->actingAs($company)
            ->get(route('perusahaan.reports.index', ['month' => '6', 'year' => '2026']));

        // Cek perubahan data: hanya data rekrutmen bulan Juni yang masuk hitungan
        $responseJuni->assertSuccessful();
        $responseJuni->assertInertia(fn (Assert $page) => $page
            ->component('Company/Reports/Index')
            ->where('filters.month', '6')
            ->where('filters.year', '2026')
            ->where('stats.total_applicants', 1)
            ->where('stats.processed_applicants', 1) // Lamaran Juni bertipe 'wawancara' (sudah diproses)
            ->where('internships_data.0.applicants_count', 1)
        );
    }

    /**
     * TC-05: Penanganan Laporan Akun Baru
     * Sistem menampilkan angka nol pada semua ringkasan statistik tanpa menyebabkan sistem crash atau error.
     */
    public function test_tc05_penanganan_laporan_akun_baru(): void
    {
        // Setup Mitra Perusahaan baru (belum memiliki lowongan & pendaftar)
        $newCompany = User::factory()->create([
            'role' => 'perusahaan',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // 1. Login dengan akun baru & masuk ke panel statistik dashboard
        $responseDashboard = $this->actingAs($newCompany)
            ->get(route('perusahaan.dashboard'));

        $responseDashboard->assertSuccessful();
        $responseDashboard->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('role', 'perusahaan')
            ->where('stats.0.value', '0') // LOWONGAN AKTIF: 0
            ->where('stats.1.value', '0') // TOTAL PELAMAR: 0
        );

        // 2. Masuk ke halaman laporan rekrutmen (/perusahaan/reports)
        $responseReports = $this->actingAs($newCompany)
            ->get(route('perusahaan.reports.index'));

        // Cek bahwa semua statistik bernilai 0 dan tidak terjadi crash
        $responseReports->assertSuccessful();
        $responseReports->assertInertia(fn (Assert $page) => $page
            ->component('Company/Reports/Index')
            ->where('stats.active_internships', 0)
            ->where('stats.total_applicants', 0)
            ->where('stats.processed_applicants', 0)
            ->where('internships_data', [])
        );
    }
}
