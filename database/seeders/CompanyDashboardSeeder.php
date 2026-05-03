<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Internship;
use App\Models\MahasiswaProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompanyDashboardSeeder extends Seeder
{
    /**
     * Seed sample data for the company dashboard.
     */
    public function run(): void
    {
        // ── 1. Create a Company User ─────────────────────────────────────
        $company = User::firstOrCreate(
            ['email' => 'perusahaan@sikara.test'],
            [
                'name'     => 'PT SIKARA Digital',
                'password' => Hash::make('password'),
                'role'     => 'perusahaan',
                'status'   => 'active',
            ]
        );

        // ── 2. Create Mahasiswa Users ────────────────────────────────────
        $students = [];
        $studentData = [
            ['name' => 'Budi Santoso',      'email' => 'budi.santoso@student.test',    'dept' => 'Ilmu Komputer',              'univ' => 'Universitas Indonesia'],
            ['name' => 'Ani Wijaya',         'email' => 'ani.wijaya@student.test',      'dept' => 'Desain Komunikasi Visual',   'univ' => 'Institut Teknologi Bandung'],
            ['name' => 'Dian Pratama',       'email' => 'dian.pratama@student.test',    'dept' => 'Teknik Informatika',         'univ' => 'Universitas Gadjah Mada'],
            ['name' => 'Rina Kumala',        'email' => 'rina.kumala@student.test',     'dept' => 'Statistika',                 'univ' => 'Universitas Brawijaya'],
            ['name' => 'Fajar Ardiansyah',   'email' => 'fajar.ardi@student.test',      'dept' => 'Sistem Informasi',           'univ' => 'Binus University'],
            ['name' => 'Siti Nurhaliza',     'email' => 'siti.nur@student.test',        'dept' => 'Manajemen Informatika',      'univ' => 'Universitas Diponegoro'],
            ['name' => 'Eko Prasetyo',       'email' => 'eko.prasetyo@student.test',    'dept' => 'Teknik Elektro',             'univ' => 'Institut Teknologi Sepuluh Nopember'],
            ['name' => 'Dewi Anggraini',     'email' => 'dewi.anggraini@student.test',  'dept' => 'Akuntansi',                  'univ' => 'Universitas Airlangga'],
        ];

        foreach ($studentData as $sd) {
            $user = User::firstOrCreate(
                ['email' => $sd['email']],
                [
                    'name'     => $sd['name'],
                    'password' => Hash::make('password'),
                    'role'     => 'mahasiswa',
                    'status'   => 'active',
                ]
            );

            MahasiswaProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nim'           => '20260' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                    'department'    => $sd['dept'],
                    'study_program' => $sd['dept'],
                    'university'    => $sd['univ'],
                    'gpa'           => number_format(rand(300, 400) / 100, 2),
                    'phone'         => '08' . rand(1000000000, 9999999999),
                    'location'      => 'Indonesia',
                    'bio'           => 'Mahasiswa aktif yang bersemangat dalam karier teknologi.',
                ]
            );

            $students[] = $user;
        }

        // ── 3. Create Internships owned by the Company ──────────────────
        $internshipData = [
            ['title' => 'Frontend Engineer Intern',     'desc' => 'Membangun antarmuka pengguna modern dengan Vue.js dan React.', 'req' => 'HTML, CSS, JavaScript, Vue.js / React', 'loc' => 'Jakarta'],
            ['title' => 'Backend Engineer Intern',      'desc' => 'Mengembangkan API dan layanan backend dengan Laravel.', 'req' => 'PHP, Laravel, MySQL, REST API',             'loc' => 'Bandung'],
            ['title' => 'UI/UX Designer Intern',        'desc' => 'Merancang pengalaman pengguna yang intuitif.', 'req' => 'Figma, Adobe XD, Prototyping, User Research',        'loc' => 'Remote'],
            ['title' => 'Data Analyst Intern',           'desc' => 'Menganalisis data bisnis dan membuat dashboard analitik.', 'req' => 'Python, SQL, Tableau, Excel',             'loc' => 'Surabaya'],
            ['title' => 'Product Manager Intern',        'desc' => 'Mengelola roadmap produk dan koordinasi tim.', 'req' => 'Communication, Jira, Agile, Product Thinking',       'loc' => 'Jakarta'],
            ['title' => 'DevOps Engineer Intern',        'desc' => 'Mengelola infrastruktur cloud dan CI/CD pipeline.', 'req' => 'Docker, AWS, GitHub Actions, Linux',             'loc' => 'Remote'],
        ];

        $internships = [];
        foreach ($internshipData as $i => $id) {
            $internship = Internship::firstOrCreate(
                ['title' => $id['title'], 'company_id' => $company->id],
                [
                    'company_name'  => $company->name,
                    'description'   => $id['desc'],
                    'requirements'  => $id['req'],
                    'location'      => $id['loc'],
                    'deadline_at'   => now()->addDays(30 + ($i * 7)),
                    'quota'         => rand(3, 10),
                    'is_published'  => true,
                ]
            );
            $internships[] = $internship;
        }

        // ── 4. Create Applications (mix of statuses) ───────────────────
        $statuses = ['menunggu ulasan', 'wawancara', 'lolos', 'tidak lolos'];
        $statusWeights = [
            'menunggu ulasan' => 4,
            'wawancara'       => 3,
            'lolos'           => 2,
            'tidak lolos'     => 3,
        ];
        $weightedStatuses = [];
        foreach ($statusWeights as $status => $weight) {
            for ($w = 0; $w < $weight; $w++) {
                $weightedStatuses[] = $status;
            }
        }

        foreach ($students as $student) {
            // Each student applies to 2-3 random internships
            $applied = collect($internships)->shuffle()->take(rand(2, 3));
            foreach ($applied as $internship) {
                Application::firstOrCreate(
                    ['user_id' => $student->id, 'internship_id' => $internship->id],
                    ['status' => $weightedStatuses[array_rand($weightedStatuses)]]
                );
            }
        }

        // ── 5. Create Events owned by the Company ──────────────────────
        $eventsData = [
            ['title' => 'Tech Career Fair 2026',        'desc' => 'Pameran karier teknologi terbesar di kampus.', 'date' => now()->addDays(14)->toDateString(), 'start' => '09:00', 'end' => '15:00', 'loc' => 'Online',   'max' => 200],
            ['title' => 'Workshop: Resume Building',     'desc' => 'Belajar membuat CV yang menarik bagi recruiter.', 'date' => now()->addDays(28)->toDateString(), 'start' => '13:00', 'end' => '16:00', 'loc' => 'Jakarta', 'max' => 50],
            ['title' => 'Seminar: Karir di Startup',     'desc' => 'Memahami dinamika bekerja di startup teknologi.', 'date' => now()->addDays(45)->toDateString(), 'start' => '10:00', 'end' => '12:00', 'loc' => 'Online',   'max' => 100],
        ];

        $events = [];
        foreach ($eventsData as $ed) {
            $event = Event::firstOrCreate(
                ['title' => $ed['title'], 'company_id' => $company->id],
                [
                    'description'      => $ed['desc'],
                    'date'             => $ed['date'],
                    'start_time'       => $ed['start'],
                    'end_time'         => $ed['end'],
                    'location'         => $ed['loc'],
                    'type'             => $ed['loc'] === 'Online' ? 'online' : 'offline',
                    'status'           => 'published',
                    'max_participants' => $ed['max'],
                ]
            );
            $events[] = $event;
        }

        // ── 6. Register some students to events ────────────────────────
        foreach ($events as $event) {
            $registrants = collect($students)->shuffle()->take(rand(3, 6));
            foreach ($registrants as $student) {
                EventRegistration::firstOrCreate(
                    ['event_id' => $event->id, 'user_id' => $student->id],
                    [
                        'status'        => 'registered',
                        'registered_at' => now()->subDays(rand(1, 10)),
                    ]
                );
            }
        }

        $this->command->info('✅ CompanyDashboardSeeder selesai! Login sebagai perusahaan@sikara.test / password');
    }
}
