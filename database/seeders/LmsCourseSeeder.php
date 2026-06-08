<?php

namespace Database\Seeders;

use App\Models\LmsCourse;
use App\Models\User;
use Illuminate\Database\Seeder;

class LmsCourseSeeder extends Seeder
{
    public function run(): void
    {
        $company = User::factory()->perusahaan()->create();

        $courses = [
            [
                'slug' => 'cloud-computing',
                'title' => 'KOMPUTASI AWAN SI-47-06',
                'provider' => 'SOUTH BANDUNG INFORMATION...',
                'level' => 'INTERMEDIATE',
                'status' => 'published',
                'moderation_status' => 'approved',
                'started_at' => '2023-09-12',
                'ends_at' => '2023-12-20',
                'image_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDyeBuA5EkEv7DkapzvBEBlqxY753D1gXobBAvHvdizlF1WJLP-8ecAbFfxJSd7KRvai6zbZMgaLqXKJ4zv9tHdNEQWn-NVs9NShwat-0FJO7QzljkAUxQZoMy09V5OfFNaJm71PagmFwINkLh1PYCUMvZ3aVQYlWFBKt7UjHHzagmt1cWH0MRGMIDOTFbg-gMoIpOYOyALOx7k66BLvWCUQqHSyJ6BEf9M6mnMP7P1p9uYzPTLdnZF_NPJaoZwA7LeaUC5rH7U5LLY',
                'image_alt' => 'Visualisasi abstrak cloud computing dengan jaringan data digital.',
            ],
            [
                'slug' => 'laravel-web-development',
                'title' => 'Pengembangan Web dengan Laravel',
                'provider' => 'Telkom University',
                'level' => 'BEGINNER',
                'status' => 'published',
                'moderation_status' => 'approved',
                'started_at' => '2023-10-01',
                'ends_at' => '2024-01-15',
                'image_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDmagRASKulLCvYorv5SRTXJqClZfNnVcWViQu22XL82CW8ZFyuU3e3XkAYjIIFRlM7Re-pQHBykmcn4V8Me1Vm0mlHLXeiVOEvEq5JhrQYkY0SQ9iexq88tDQeR3tKSfbQbqpUzhPhwe6Y9MuV4451mWCrSBQPaxpTMsIRtk4s32DGM6poGGBeHMIGtIAl4gYtU438dBxA6VrCztjVJCcDa505vyb-jay4XNQl7NIWCm_zzsJl7fb4lHLXXTDw83S87pakMLQmoeqY',
                'image_alt' => 'Tampilan kode di layar komputer pada lingkungan pengembangan modern.',
            ],
            [
                'slug' => 'machine-learning-fundamentals',
                'title' => 'Machine Learning Fundamentals',
                'provider' => 'Data Science Institute',
                'level' => 'ADVANCED',
                'status' => 'published',
                'moderation_status' => 'approved',
                'started_at' => '2023-08-10',
                'ends_at' => '2023-11-30',
                'image_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuA_-CVU-rEFOKVsXkn65aKSohQNRg0X-56VpqiC-d-8dnf62LOeipnQnSLz9zB7qbtJXej862WGvYM7Uv-ZWvBV9OBVRr22is5Nj9OAWuzSTXd_pkaUH_KJP2zdSBjoY4wn9UqD21S1DtAA2Exx9cT3s_td7dRwoajfPRr2D3omeYV3Y4FILw16j5pQPCABlgZfmTvxg2wV273iQyp__FE102kl284CUvZh8Ka2K_HFP4m5G-QdUp9yafoQkb23A4utIUSPfmrBMF7d',
                'image_alt' => 'Dashboard analitik data dengan grafik pada monitor resolusi tinggi.',
            ],
        ];

        foreach ($courses as $courseData) {
            $courseData['company_id'] = $company->id;
            $course = LmsCourse::query()->updateOrCreate(
                ['slug' => $courseData['slug']],
                $courseData
            );

            if ($course->slug === 'cloud-computing') {
                $course->chapters()->delete(); // Clean up existing to be deterministic

                $course->chapters()->create(['title' => 'Bab 1: Pendahuluan', 'position' => 1]);
                
                $chapter2 = $course->chapters()->create(['title' => 'Bab 2: Dasar Cloud', 'position' => 2]);
                $chapter2->lessons()->create(['type' => 'video', 'title' => 'Video: Pengenalan Cloud', 'position' => 1]);
                $chapter2->lessons()->create(['type' => 'article', 'title' => 'Materi: Arsitektur', 'position' => 2]);
                
                $chapter3 = $course->chapters()->create(['title' => 'Bab 3: Model Deployment', 'position' => 3]);
                $chapter3->lessons()->create([
                    'type' => 'video',
                    'title' => 'Video: Publik vs Privat',
                    'content' => 'Dalam modul ini, kita akan membahas perbedaan mendasar antara model deployment Public Cloud dan Private Cloud.',
                    'video_image_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuA8J00cT4k7aKPnEDPW2PIvwTQ5uGxcb8npxv2XbdyNPHSpjN0O-NxtOO_3C8J0m6wNEw6bUWCug6sLtDTNzOBCpfQCS3vvkGi44wedMI-owiEfGV8WZGy7IJZMV3rxGVP593Qb5xFCFSQ3OjlILFZ1y1g2v7ZE28eWcJ_BISZeN3aPpPa4Ew9WOKgDk6EKbTcNvubCI66jNRx1jCs7HmC9j1rXk8xZWvVWHCrJbHX_MIc97KuTM9EESpZh6NqdIlV3OlY-DGbtM1fX',
                    'position' => 1
                ]);
                $chapter3->lessons()->create(['type' => 'article', 'title' => 'Materi: Hybrid Cloud', 'position' => 2]);
                
                $quiz = $chapter3->quiz()->create([
                    'title' => 'Kuis: Model Deployment',
                    'passing_score' => 75
                ]);
                
                $q1 = $quiz->questions()->create(['question' => 'Apa itu cloud?', 'position' => 1]);
                $q1->options()->create(['option_text' => 'Layanan komputasi melalui internet', 'is_correct' => true, 'position' => 1]);
                $q1->options()->create(['option_text' => 'Penyimpanan lokal', 'is_correct' => false, 'position' => 2]);

                $q2 = $quiz->questions()->create(['question' => 'Apa itu private cloud?', 'position' => 2]);
                $q2->options()->create(['option_text' => 'Layanan eksklusif untuk satu organisasi', 'is_correct' => true, 'position' => 1]);
                $q2->options()->create(['option_text' => 'Layanan terbuka', 'is_correct' => false, 'position' => 2]);

                $course->chapters()->create(['title' => 'Bab 4: Layanan Lanjut', 'position' => 4]);
            }
        }
    }
}
