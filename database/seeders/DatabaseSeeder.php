<?php

namespace Database\Seeders;

use App\Models\Internship;
use App\Models\MahasiswaProfile;
use App\Models\Notification;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $student = User::factory()->mahasiswa()->create([
            'name' => 'Budi Santoso',
            'email' => 'mahasiswa@sikara.test',
            'password' => "12345678",
        ]);

        MahasiswaProfile::query()->create([
            'user_id' => $student->id,
            'nim' => '1301213001',
            'department' => 'Teknik Informatika',
            'study_program' => 'S1 Informatika',
            'gpa' => 3.82,
            'phone' => '081234567890',
            'location' => 'Bandung',
            'bio' => 'Mahasiswa semester akhir yang berfokus pada pengembangan web dan backend.',
        ]);

        Skill::query()->create([
            'user_id' => $student->id,
            'name' => 'Laravel',
            'proficiency' => 86,
        ]);

        Skill::query()->create([
            'user_id' => $student->id,
            'name' => 'Vue.js',
            'proficiency' => 78,
        ]);

        // Internships will be assigned to specific companies below

        Notification::query()->create([
            'user_id' => $student->id,
            'title' => 'Selamat datang di SIKARA',
            'message' => 'Lengkapi profil Anda untuk mulai melamar magang.',
            'type' => 'system',
        ]);

        $company1 = User::factory()->perusahaan()->create([
            'name' => 'PT Telekomunikasi Terpadu',
            'email' => 'perusahaan@sikara.test',
            'password' => "12345678"
        ]);

        \App\Models\PerusahaanProfile::create([
            'user_id' => $company1->id,
            'industry' => 'Teknologi & Informasi',
            'location' => 'Jakarta, Indonesia',
            'website' => 'https://telkom.co.id',
            'description' => 'PT Telekomunikasi Terpadu (TELKOM) merupakan pilar utama infrastruktur digital Indonesia. Sejak berdiri, kami berkomitmen untuk menghubungkan seluruh pelosok nusantara melalui jaringan telekomunikasi yang handal, cepat, dan terjangkau. Kami percaya bahwa konektivitas adalah kunci pembangunan bangsa di era digital.',
            'vision' => 'Menjadi penyedia layanan telekomunikasi digital terdepan di Asia Tenggara yang mendorong pertumbuhan inklusif.',
            'mission' => 'Mempercepat pembangunan infrastruktur digital dan membina talenta masa depan melalui inovasi teknologi tanpa henti.',
            'founded_year' => 1995,
            'employee_count' => '5,000+',
            'specializations' => ['5G Network', 'IoT Solution', 'Cloud Comp.'],
            'office_address' => 'Jl. Gatot Subroto No.10, Jakarta Selatan, 12710',
            'logo_path' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBtOlym8xUAJfNqGmBBW8Y-ZEhZl72B9Uzb8PqqjGRVADpts_RMDuG8xbjwdTXbAECR3CnWejtit2--1vi_agE_JRJZ2-vEJWz0STb8V-F39wZKPlIMNx45AM4NcMlcz9KdO4sz9zxTBW3k9kXmdsUwxtL2-7bdq0iE3vsnGx8nFMhyK4ptkaWUGVCWuBDKb1j_EqIAp8Zxe8-81HxgHtaovd7IFJGLpavDHHAQeR6qdNe8nPr5LGV4dQd4MW__xZl5HeP0KTkrAUi3',
            'cover_path' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCLx7LBOWOcCDe4C_SewEYs2lCXY8_2moE-Q9uaruBb5w57ouThui60Z6vBAfMgbHSWDoqUOS5phem9y1-YloHXlMP7fmS8c98Fqufn_pQsf0MhHt_vTmJL78NN6k2glxn9m8RMclDC4ZdCoVODUYnDnUxzxSbr0B21Qcyd4jOGrQHzGEO15zTqaD0zPB_1ypsB6nBY-pKKFiSooOBulPzAYyM97ofm1LYCXxsxICtkjf-KsryIIpsEWdcvb4qFOeAaiJiQJuAZq4Pu',
        ]);

        Internship::query()->create([
            'company_id' => $company1->id,
            'title' => 'UI/UX Designer Intern',
            'description' => 'Mendesain UI/UX untuk produk digital.',
            'company_name' => 'PT Telekomunikasi Terpadu',
            'location' => 'Jakarta Selatan',
            'education_level' => 'S1',
            'requirements' => 'Memahami Figma dan UX Research.',
            'deadline_at' => now()->addDays(14),
            'salary_range' => 'Rp 1.000.000 - Rp 3.000.000',
            'quota' => 2,
            'is_published' => true,
        ]);

        Internship::query()->create([
            'company_id' => $company1->id,
            'title' => 'Backend Developer Intern',
            'description' => 'Mengatur Backend pada aplikasi perusahaan.',
            'company_name' => 'PT Telekomunikasi Terpadu',
            'location' => 'Remote',
            'education_level' => 'D4',
            'requirements' => 'Memahami Node.js atau Go.',
            'deadline_at' => now()->addDays(14),
            'salary_range' => 'Rp 3.000.000 - Rp 5.000.000',
            'quota' => 2,
            'is_published' => true,
        ]);

        $company2 = User::factory()->perusahaan()->create([
            'name' => 'PT Karya Anak Bangsa',
            'email' => 'go-tech@sikara.test',
            'password' => "12345678"
        ]);

        \App\Models\PerusahaanProfile::create([
            'user_id' => $company2->id,
            'industry' => 'Transportasi & Logistik Modern',
            'location' => 'Jakarta Pusat, Indonesia',
            'website' => 'https://karyaanakbangsa.id',
            'description' => 'PT Karya Anak Bangsa adalah perusahaan teknologi Indonesia yang melayani angkutan melalui jasa ojek, taksi, logistik, dan pembayaran digital.',
            'vision' => 'Meningkatkan taraf hidup masyarakat melalui teknologi.',
            'mission' => 'Memberikan solusi transportasi, logistik, dan pembayaran yang inovatif.',
            'founded_year' => 2010,
            'employee_count' => '10,000+',
            'specializations' => ['Ride Hailing', 'Logistics', 'Fintech'],
            'office_address' => 'Jl. Iskandarsyah Raya No.10, Jakarta Pusat',
        ]);

        Internship::query()->create([
            'company_id' => $company2->id,
            'title' => 'Data Analyst Intern',
            'description' => 'Menganalisis data transaksi harian.',
            'company_name' => 'PT Karya Anak Bangsa',
            'location' => 'Jakarta Pusat',
            'education_level' => 'S2',
            'requirements' => 'Memahami SQL dan Python.',
            'deadline_at' => now()->addDays(20),
            'salary_range' => 'Di atas Rp 5.000.000',
            'quota' => 5,
            'is_published' => true,
        ]);

        User::factory()->admin()->create([
            'name' => 'Admin Kampus',
            'email' => 'admin@sikara.test',
            'password' => "12345678"
        ]);
    }
}
