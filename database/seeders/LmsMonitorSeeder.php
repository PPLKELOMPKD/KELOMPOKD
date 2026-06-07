<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\LmsAssignment;
use App\Models\LmsAssignmentSubmission;
use App\Models\LmsChapter;
use App\Models\LmsChapterCompletion;
use App\Models\LmsCourse;
use App\Models\LmsEnrollment;
use App\Models\LmsLesson;
use App\Models\LmsLessonCompletion;
use App\Models\LmsQuiz;
use App\Models\LmsQuizAttempt;
use App\Models\MahasiswaProfile;
use App\Models\PerusahaanProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LmsMonitorSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Create Perusahaan Users & Profiles ────────────────────────
        $perusahaanData = [
            [
                'name' => 'PT Telkom Indonesia',
                'email' => 'telkom@sikara.test',
                'industry' => 'Telekomunikasi & Digital',
                'location' => 'Bandung, Indonesia',
                'website' => 'https://telkom.co.id',
                'desc' => 'Perusahaan BUMN telekomunikasi terbesar di Indonesia.',
            ],
            [
                'name' => 'PT Bank Mandiri',
                'email' => 'mandiri@sikara.test',
                'industry' => 'Perbankan & Keuangan',
                'location' => 'Jakarta, Indonesia',
                'website' => 'https://bankmandiri.co.id',
                'desc' => 'Salah satu bank terbesar di Indonesia dengan layanan perbankan digital terdepan.',
            ],
            [
                'name' => 'PT Astra International',
                'email' => 'astra@sikara.test',
                'industry' => 'Otomotif & Konglomerasi',
                'location' => 'Jakarta, Indonesia',
                'website' => 'https://astra.co.id',
                'desc' => 'Perusahaan multinasional Indonesia yang beroperasi di berbagai sektor.',
            ],
        ];

        $companies = [];
        foreach ($perusahaanData as $p) {
            $user = User::query()->updateOrCreate(
                ['email' => $p['email']],
                [
                    'name' => $p['name'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_PERUSAHAAN,
                    'status' => User::STATUS_ACTIVE,
                ]
            );

            PerusahaanProfile::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'industry' => $p['industry'],
                    'location' => $p['location'],
                    'website' => $p['website'],
                    'description' => $p['desc'],
                ]
            );

            $companies[$p['name']] = $user;
        }

        // ── 2. Create Mahasiswa Users & Profiles ─────────────────────────
        $mahasiswaData = [
            [
                'name' => 'Putvi Ilyasyah',
                'email' => 'putvi@student.test',
                'nim' => '1301213002',
                'dept' => 'S1 Informatika',
                'gpa' => 3.92,
                'status' => User::STATUS_ACTIVE,
            ],
            [
                'name' => 'Bintang Pratama',
                'email' => 'bintang@student.test',
                'nim' => '1301213003',
                'dept' => 'S1 Sistem Informasi',
                'gpa' => 3.65,
                'status' => User::STATUS_INACTIVE,
            ],
            [
                'name' => 'Farah Nabila',
                'email' => 'farah@student.test',
                'nim' => '1301213004',
                'dept' => 'S1 Desain Komunikasi Visual',
                'gpa' => 3.78,
                'status' => User::STATUS_BANNED,
            ],
        ];

        $students = [];
        foreach ($mahasiswaData as $m) {
            $user = User::query()->updateOrCreate(
                ['email' => $m['email']],
                [
                    'name' => $m['name'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_MAHASISWA,
                    'status' => $m['status'],
                ]
            );

            MahasiswaProfile::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nim' => $m['nim'],
                    'department' => $m['dept'],
                    'study_program' => $m['dept'],
                    'gpa' => $m['gpa'],
                    'phone' => '0812' . rand(10000000, 99999999),
                    'location' => 'Bandung, Indonesia',
                    'bio' => 'Mahasiswa berprestasi yang berfokus pada pengembangan karir.',
                ]
            );

            $students[$m['name']] = $user;
        }

        // ── 3. Create Courses ────────────────────────────────────────────
        // Course 1: Fundamental UI/UX Design (PT Telkom Indonesia)
        $courseUiUx = LmsCourse::query()->updateOrCreate(
            ['slug' => 'fundamental-ui-ux-design'],
            [
                'company_id' => $companies['PT Telkom Indonesia']->id,
                'title' => 'Fundamental UI/UX Design',
                'provider' => 'PT Telkom Indonesia',
                'description' => 'Pelajari prinsip dasar desain antarmuka dan pengalaman pengguna yang modern.',
                'level' => 'Beginner',
                'status' => LmsCourse::STATUS_PUBLISHED,
                'started_at' => now()->subDays(10),
                'ends_at' => now()->addMonths(2),
                'image_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDyeBuA5EkEv7DkapzvBEBlqxY753D1gXobBAvHvdizlF1WJLP-8ecAbFfxJSd7KRvai6zbZMgaLqXKJ4zv9tHdNEQWn-NVs9NShwat-0FJO7QzljkAUxQZoMy09V5OfFNaJm71PagmFwINkLh1PYCUMvZ3aVQYlWFBKt7UjHHzagmt1cWH0MRGMIDOTFbg-gMoIpOYOyALOx7k66BLvWCUQqHSyJ6BEf9M6mnMP7P1p9uYzPTLdnZF_NPJaoZwA7LeaUC5rH7U5LLY',
                'image_alt' => 'Visualisasi abstrak cloud computing dengan jaringan data digital.',
            ]
        );

        $ch1UiUx = LmsChapter::query()->firstOrCreate(
            ['course_id' => $courseUiUx->id, 'title' => 'Bab 1: Pengenalan UI/UX'],
            ['position' => 1]
        );

        $les1UiUx = LmsLesson::query()->firstOrCreate(
            ['chapter_id' => $ch1UiUx->id, 'title' => 'Apa itu UI dan UX?'],
            ['type' => 'video', 'content' => 'Penjelasan dasar perbedaan UI dan UX.', 'position' => 1]
        );

        $quizUiUx = LmsQuiz::query()->firstOrCreate(
            ['chapter_id' => $ch1UiUx->id],
            ['title' => 'Kuis Dasar UI/UX', 'passing_score' => 75]
        );

        $qUiUx1 = $quizUiUx->questions()->firstOrCreate(
            ['question' => 'Apakah kepanjangan dari UX?'],
            ['position' => 1]
        );
        $qUiUx1->options()->firstOrCreate(['option_text' => 'User Experience', 'is_correct' => true, 'position' => 1]);
        $qUiUx1->options()->firstOrCreate(['option_text' => 'User Example', 'is_correct' => false, 'position' => 2]);

        $ch2UiUx = LmsChapter::query()->firstOrCreate(
            ['course_id' => $courseUiUx->id, 'title' => 'Bab 2: Figma Basics'],
            ['position' => 2]
        );

        $les2UiUx = LmsLesson::query()->firstOrCreate(
            ['chapter_id' => $ch2UiUx->id, 'title' => 'Mengenal Tool Figma'],
            ['type' => 'article', 'content' => 'Cara menggunakan frames, shapes, dan prototyping di Figma.', 'position' => 1]
        );

        $assignUiUx = LmsAssignment::query()->firstOrCreate(
            ['chapter_id' => $ch2UiUx->id, 'title' => 'Latihan Wireframing'],
            [
                'description' => 'Buat low-fidelity wireframe untuk halaman login aplikasi mobile.',
                'deadline_at' => now()->addDays(5),
            ]
        );

        // Course 2: Backend Laravel Development (PT Bank Mandiri)
        $courseLaravel = LmsCourse::query()->updateOrCreate(
            ['slug' => 'backend-laravel-development'],
            [
                'company_id' => $companies['PT Bank Mandiri']->id,
                'title' => 'Backend Laravel Development',
                'provider' => 'PT Bank Mandiri',
                'description' => 'Membangun API handal dan aplikasi web menggunakan framework Laravel.',
                'level' => 'Intermediate',
                'status' => LmsCourse::STATUS_PUBLISHED,
                'started_at' => now()->subDays(5),
                'ends_at' => now()->addMonths(3),
                'image_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDmagRASKulLCvYorv5SRTXJqClZfNnVcWViQu22XL82CW8ZFyuU3e3XkAYjIIFRlM7Re-pQHBykmcn4V8Me1Vm0mlHLXeiVOEvEq5JhrQYkY0SQ9iexq88tDQeR3tKSfbQbqpUzhPhwe6Y9MuV4451mWCrSBQPaxpTMsIRtk4s32DGM6poGGBeHMIGtIAl4gYtU438dBxA6VrCztjVJCcDa505vyb-jay4XNQl7NIWCm_zzsJl7fb4lHLXXTDw83S87pakMLQmoeqY',
                'image_alt' => 'Tampilan kode di layar komputer pada lingkungan pengembangan modern.',
            ]
        );

        $ch1Laravel = LmsChapter::query()->firstOrCreate(
            ['course_id' => $courseLaravel->id, 'title' => 'Bab 1: MVC Architecture'],
            ['position' => 1]
        );

        $les1Laravel = LmsLesson::query()->firstOrCreate(
            ['chapter_id' => $ch1Laravel->id, 'title' => 'Laravel MVC'],
            ['type' => 'video', 'content' => 'Penjelasan Model-View-Controller.', 'position' => 1]
        );

        $quizLaravel = LmsQuiz::query()->firstOrCreate(
            ['chapter_id' => $ch1Laravel->id],
            ['title' => 'Kuis Laravel MVC', 'passing_score' => 70]
        );
        $qLaravel1 = $quizLaravel->questions()->firstOrCreate(
            ['question' => 'M di MVC singkatan dari?'],
            ['position' => 1]
        );
        $qLaravel1->options()->firstOrCreate(['option_text' => 'Model', 'is_correct' => true, 'position' => 1]);
        $qLaravel1->options()->firstOrCreate(['option_text' => 'Middleware', 'is_correct' => false, 'position' => 2]);

        // Course 3: Data Analyst Bootcamp (PT Astra International)
        $courseDataAnalyst = LmsCourse::query()->updateOrCreate(
            ['slug' => 'data-analyst-bootcamp'],
            [
                'company_id' => $companies['PT Astra International']->id,
                'title' => 'Data Analyst Bootcamp',
                'provider' => 'PT Astra International',
                'description' => 'Pondasi analisis data praktis dengan Python, Pandas, dan visualisasi data.',
                'level' => 'Intermediate',
                'status' => LmsCourse::STATUS_PUBLISHED,
                'started_at' => now()->subDays(2),
                'ends_at' => now()->addMonths(4),
                'image_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuA_-CVU-rEFOKVsXkn65aKSohQNRg0X-56VpqiC-d-8dnf62LOeipnQnSLz9zB7qbtJXej862WGvYM7Uv-ZWvBV9OBVRr22is5Nj9OAWuzSTXd_pkaUH_KJP2zdSBjoY4wn9UqD21S1DtAA2Exx9cT3s_td7dRwoajfPRr2D3omeYV3Y4FILw16j5pQPCABlgZfmTvxg2wV273iQyp__FE102kl284CUvZh8Ka2K_HFP4m5G-QdUp9yafoQkb23A4utIUSPfmrBMF7d',
                'image_alt' => 'Dashboard analitik data dengan grafik pada monitor resolusi tinggi.',
            ]
        );

        $ch1Analyst = LmsChapter::query()->firstOrCreate(
            ['course_id' => $courseDataAnalyst->id, 'title' => 'Bab 1: Python and Pandas'],
            ['position' => 1]
        );

        $les1Analyst = LmsLesson::query()->firstOrCreate(
            ['chapter_id' => $ch1Analyst->id, 'title' => 'Dasar Python'],
            ['type' => 'article', 'content' => 'Variabel, lists, dan fungsi python.', 'position' => 1]
        );

        // Course 4: Cloud Computing Essentials (PT Telkom Indonesia)
        $courseCloud = LmsCourse::query()->updateOrCreate(
            ['slug' => 'cloud-computing-essentials'],
            [
                'company_id' => $companies['PT Telkom Indonesia']->id,
                'title' => 'Cloud Computing Essentials',
                'provider' => 'PT Telkom Indonesia',
                'description' => 'Pengenalan infrastruktur awan, AWS, dan model deployment cloud.',
                'level' => 'Beginner',
                'status' => LmsCourse::STATUS_PUBLISHED,
                'started_at' => now()->subDays(15),
                'ends_at' => now()->addMonths(1),
                'image_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDyeBuA5EkEv7DkapzvBEBlqxY753D1gXobBAvHvdizlF1WJLP-8ecAbFfxJSd7KRvai6zbZMgaLqXKJ4zv9tHdNEQWn-NVs9NShwat-0FJO7QzljkAUxQZoMy09V5OfFNaJm71PagmFwINkLh1PYCUMvZ3aVQYlWFBKt7UjHHzagmt1cWH0MRGMIDOTFbg-gMoIpOYOyALOx7k66BLvWCUQqHSyJ6BEf9M6mnMP7P1p9uYzPTLdnZF_NPJaoZwA7LeaUC5rH7U5LLY',
                'image_alt' => 'Visualisasi abstrak cloud computing dengan jaringan data digital.',
            ]
        );

        $ch1Cloud = LmsChapter::query()->firstOrCreate(
            ['course_id' => $courseCloud->id, 'title' => 'Bab 1: Cloud Service Models'],
            ['position' => 1]
        );

        $les1Cloud = LmsLesson::query()->firstOrCreate(
            ['chapter_id' => $ch1Cloud->id, 'title' => 'IaaS, PaaS, SaaS'],
            ['type' => 'video', 'content' => 'Model layanan cloud komputasi.', 'position' => 1]
        );
    }
}
