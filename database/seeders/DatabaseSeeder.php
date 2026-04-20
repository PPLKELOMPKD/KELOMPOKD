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

        Internship::query()->create([
            'title' => 'Backend Engineer Intern',
            'company_name' => 'PT SIKARA Nusantara',
            'location' => 'Bandung',
            'requirements' => 'Memahami Laravel, MySQL, dan REST API.',
            'deadline_at' => now()->addDays(14),
            'is_published' => true,
        ]);

        Notification::query()->create([
            'user_id' => $student->id,
            'title' => 'Selamat datang di SIKARA',
            'message' => 'Lengkapi profil Anda untuk mulai melamar magang.',
            'type' => 'system',
        ]);

        User::factory()->perusahaan()->create([
            'name' => 'Mitra Perusahaan',
            'email' => 'perusahaan@sikara.test',
        ]);

        User::factory()->admin()->create([
            'name' => 'Admin Kampus',
            'email' => 'admin@sikara.test',
        ]);
    }
}
