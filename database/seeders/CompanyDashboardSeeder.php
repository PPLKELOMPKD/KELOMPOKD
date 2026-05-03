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
            ['title' => 'Frontend Engineer Intern',  'desc' => "Kami mencari Frontend Engineer Intern yang antusias untuk bergabung dengan tim pengembangan produk kami.\n\nTanggung Jawab:\n• Membangun antarmuka pengguna modern dan responsif menggunakan Vue.js dan React\n• Berkolaborasi dengan tim desain untuk mengimplementasikan UI/UX yang pixel-perfect\n• Mengoptimalkan performa aplikasi web untuk pengalaman pengguna terbaik\n• Menulis unit test dan melakukan code review bersama tim senior\n• Berkontribusi dalam sprint planning dan daily standup meeting", 'req' => "• Mahasiswa aktif jurusan Teknik Informatika / Ilmu Komputer / Sistem Informasi\n• Menguasai HTML5, CSS3, dan JavaScript (ES6+)\n• Familiar dengan framework Vue.js atau React\n• Memahami konsep responsive design dan cross-browser compatibility\n• Kemampuan komunikasi yang baik dan mampu bekerja dalam tim\n• Memiliki portfolio atau project pribadi menjadi nilai tambah", 'loc' => 'Jakarta', 'type' => 'Magang', 'dur' => '3 Bulan', 'ben' => "• Uang saku bulanan\n• Sertifikat magang\n• Mentoring dari senior engineer\n• Akses ke learning platform premium\n• Kesempatan menjadi karyawan tetap", 'sal' => 'Rp 2.000.000 - 3.000.000/bulan'],
            ['title' => 'Backend Engineer Intern',   'desc' => "Bergabunglah sebagai Backend Engineer Intern dan kembangkan kemampuanmu di sisi server.\n\nTanggung Jawab:\n• Mengembangkan RESTful API dan microservices menggunakan Laravel\n• Mendesain dan mengelola skema database MySQL/PostgreSQL\n• Menulis query yang efisien dan mengoptimalkan performa database\n• Mengimplementasikan autentikasi, otorisasi, dan keamanan API\n• Membuat dokumentasi API menggunakan Swagger/OpenAPI", 'req' => "• Mahasiswa aktif jurusan Teknik Informatika / Ilmu Komputer\n• Menguasai PHP dan framework Laravel\n• Memahami konsep REST API dan database relasional\n• Familiar dengan version control (Git)\n• Memahami konsep OOP dan design patterns\n• Pengalaman dengan Docker menjadi nilai tambah", 'loc' => 'Bandung', 'type' => 'Magang', 'dur' => '6 Bulan', 'ben' => "• Uang saku kompetitif\n• Sertifikat magang resmi\n• Mentoring 1-on-1 dengan Tech Lead\n• Free lunch setiap hari kerja\n• Flexible working hours", 'sal' => 'Rp 2.500.000 - 3.500.000/bulan'],
            ['title' => 'UI/UX Designer Intern',     'desc' => "Kami mencari UI/UX Designer Intern kreatif untuk merancang pengalaman pengguna yang luar biasa.\n\nTanggung Jawab:\n• Melakukan user research dan usability testing\n• Membuat wireframe, mockup, dan prototype interaktif\n• Mendesain user interface yang konsisten dengan design system\n• Berkolaborasi erat dengan tim engineering untuk implementasi desain\n• Membuat dan memelihara design system perusahaan", 'req' => "• Mahasiswa aktif jurusan DKV / Informatika / Desain Produk\n• Menguasai Figma dan/atau Adobe XD\n• Memahami prinsip-prinsip UI/UX design\n• Memiliki portfolio desain yang kuat\n• Kemampuan visual yang baik dan perhatian terhadap detail\n• Familiar dengan design system dan atomic design", 'loc' => 'Remote', 'type' => 'Magang', 'dur' => '3 Bulan', 'ben' => "• Fully remote working\n• Uang saku bulanan\n• Akses ke tools desain premium\n• Portfolio review dengan Senior Designer\n• Sertifikat dan letter of recommendation", 'sal' => 'Rp 1.500.000 - 2.500.000/bulan'],
            ['title' => 'Data Analyst Intern',        'desc' => "Jadilah Data Analyst Intern dan bantu kami mengubah data menjadi insight bisnis yang berharga.\n\nTanggung Jawab:\n• Mengumpulkan, membersihkan, dan menganalisis dataset besar\n• Membuat dashboard analitik interaktif menggunakan Tableau/Power BI\n• Melakukan statistical analysis untuk mendukung keputusan bisnis\n• Menyajikan hasil analisis dalam bentuk laporan yang mudah dipahami\n• Berkolaborasi dengan tim bisnis untuk memahami kebutuhan data", 'req' => "• Mahasiswa aktif jurusan Statistika / Matematika / Informatika\n• Menguasai Python (Pandas, NumPy) atau R\n• Terampil dalam SQL dan database querying\n• Familiar dengan tools visualisasi (Tableau, Power BI, atau Looker)\n• Kemampuan analitis yang kuat dan detail-oriented\n• Pengetahuan dasar tentang machine learning menjadi nilai tambah", 'loc' => 'Surabaya', 'type' => 'Magang', 'dur' => '4 Bulan', 'ben' => "• Uang saku bulanan\n• Sertifikat resmi\n• Akses ke cloud computing resources\n• Training data science tools\n• Networking dengan profesional data", 'sal' => 'Rp 2.000.000 - 3.000.000/bulan'],
            ['title' => 'Product Manager Intern',     'desc' => "Bergabunglah sebagai Product Manager Intern dan pelajari cara membangun produk yang dicintai pengguna.\n\nTanggung Jawab:\n• Membantu mengelola product backlog dan roadmap produk\n• Melakukan riset pasar dan kompetitor analysis\n• Menulis user stories dan product requirements document\n• Berkoordinasi dengan tim engineering dan design\n• Menganalisis metrics produk dan membuat product report", 'req' => "• Mahasiswa aktif jurusan Manajemen / Informatika / Bisnis\n• Memahami metodologi Agile/Scrum\n• Familiar dengan tools seperti Jira, Trello, atau Notion\n• Kemampuan komunikasi dan presentasi yang excellent\n• Analytical thinking dan problem-solving skills\n• Pengalaman organisasi atau leadership menjadi nilai tambah", 'loc' => 'Jakarta', 'type' => 'Magang', 'dur' => '3 Bulan', 'ben' => "• Uang saku bulanan\n• Mentoring dari Senior PM\n• Sertifikat dan rekomendasi\n• Exposure ke stakeholder level C-suite\n• Kesempatan full-time setelah lulus", 'sal' => 'Rp 2.500.000 - 4.000.000/bulan'],
            ['title' => 'DevOps Engineer Intern',     'desc' => "Kami mencari DevOps Engineer Intern untuk membantu mengelola infrastruktur cloud kami.\n\nTanggung Jawab:\n• Membantu setup dan maintenance CI/CD pipeline\n• Mengelola containerization menggunakan Docker dan Kubernetes\n• Monitoring dan troubleshooting infrastruktur cloud (AWS/GCP)\n• Mengimplementasikan Infrastructure as Code (IaC)\n• Menulis automation scripts untuk deployment", 'req' => "• Mahasiswa aktif jurusan Informatika / Teknik Komputer\n• Familiar dengan Linux dan command line\n• Mengenal Docker dan containerization\n• Dasar-dasar networking dan cloud computing\n• Familiar dengan Git dan GitHub Actions\n• Pengalaman dengan AWS/GCP menjadi nilai tambah", 'loc' => 'Remote', 'type' => 'Magang', 'dur' => '6 Bulan', 'ben' => "• Fully remote working\n• Uang saku kompetitif\n• AWS/GCP credits untuk belajar\n• Sertifikasi cloud gratis\n• Mentoring dari Senior DevOps", 'sal' => 'Rp 3.000.000 - 4.500.000/bulan'],
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
                    'work_type'     => $id['type'],
                    'duration'      => $id['dur'],
                    'benefits'      => $id['ben'],
                    'salary'        => $id['sal'],
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
