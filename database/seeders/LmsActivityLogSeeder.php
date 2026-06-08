<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LmsActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure we have our test users
        $company = User::query()->firstOrCreate(
            ['email' => 'telkom@sikara.test'],
            [
                'name' => 'PT Telkom Indonesia',
                'password' => Hash::make('password'),
                'role' => User::ROLE_PERUSAHAAN,
                'status' => User::STATUS_ACTIVE,
            ]
        );

        $student = User::query()->firstOrCreate(
            ['email' => 'putvi@student.test'],
            [
                'name' => 'Putvi Ilyasyah',
                'password' => Hash::make('password'),
                'role' => User::ROLE_MAHASISWA,
                'status' => User::STATUS_ACTIVE,
            ]
        );

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@sikara.test'],
            [
                'name' => 'Admin SIKARA',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
            ]
        );

        // 2. Define the sample activities
        $logs = [
            [
                'user_id' => $company->id,
                'role' => 'perusahaan',
                'category' => 'course',
                'action' => 'Membuat Course',
                'description' => "Perusahaan {$company->name} membuat course 'Fundamental UI/UX Design'",
                'created_at' => now()->subDays(10),
            ],
            [
                'user_id' => $company->id,
                'role' => 'perusahaan',
                'category' => 'course',
                'action' => 'Mengubah Materi',
                'description' => "Perusahaan {$company->name} menambahkan bab baru 'Bab 1: Pengenalan UI/UX' pada course 'Fundamental UI/UX Design'",
                'created_at' => now()->subDays(9)->addHours(2),
            ],
            [
                'user_id' => $company->id,
                'role' => 'perusahaan',
                'category' => 'lesson',
                'action' => 'Mengubah Materi',
                'description' => "Perusahaan {$company->name} menambahkan lesson baru 'Apa itu UI dan UX?' pada bab 'Bab 1: Pengenalan UI/UX'",
                'created_at' => now()->subDays(9)->addHours(3),
            ],
            [
                'user_id' => $company->id,
                'role' => 'perusahaan',
                'category' => 'quiz',
                'action' => 'Membuat Kuis',
                'description' => "Perusahaan {$company->name} mengonfigurasi kuis pada chapter 'Bab 1: Pengenalan UI/UX'",
                'created_at' => now()->subDays(9)->addHours(4),
            ],
            [
                'user_id' => $admin->id,
                'role' => 'admin',
                'category' => 'moderasi',
                'action' => 'Menyetujui Course',
                'description' => "Admin {$admin->name} menyetujui course 'Fundamental UI/UX Design' untuk dipublikasikan",
                'created_at' => now()->subDays(8),
            ],
            [
                'user_id' => $student->id,
                'role' => 'mahasiswa',
                'category' => 'enrollment',
                'action' => 'Mendaftar Kursus',
                'description' => "Mahasiswa {$student->name} mendaftar pada kursus LMS: Fundamental UI/UX Design",
                'created_at' => now()->subDays(7),
            ],
            [
                'user_id' => $student->id,
                'role' => 'mahasiswa',
                'category' => 'lesson',
                'action' => 'Menyelesaikan Lesson',
                'description' => "Mahasiswa {$student->name} menyelesaikan lesson 'Apa itu UI dan UX?' pada course 'Fundamental UI/UX Design'",
                'created_at' => now()->subDays(6),
            ],
            [
                'user_id' => $student->id,
                'role' => 'mahasiswa',
                'category' => 'quiz',
                'action' => 'Lulus Kuis',
                'description' => "Mahasiswa {$student->name} lulus kuis 'Kuis Dasar UI/UX' dengan skor 100 pada course 'Fundamental UI/UX Design'",
                'created_at' => now()->subDays(5),
            ],
            [
                'user_id' => $student->id,
                'role' => 'mahasiswa',
                'category' => 'assignment',
                'action' => 'Submit Assignment',
                'description' => "Mahasiswa {$student->name} mengumpulkan tugas 'Latihan Wireframing' pada course 'Fundamental UI/UX Design'",
                'created_at' => now()->subDays(4),
            ],
            [
                'user_id' => $company->id,
                'role' => 'perusahaan',
                'category' => 'enrollment',
                'action' => 'Meluluskan Mahasiswa',
                'description' => "Perusahaan {$company->name} meluluskan mahasiswa '{$student->name}' pada course 'Fundamental UI/UX Design'",
                'created_at' => now()->subDays(3),
            ],
            [
                'user_id' => $student->id,
                'role' => 'mahasiswa',
                'category' => 'course',
                'action' => 'Menyelesaikan Course',
                'description' => "Mahasiswa {$student->name} menyelesaikan course 'Fundamental UI/UX Design' dan mendapatkan sertifikat kelulusan",
                'created_at' => now()->subDays(3)->addHours(1),
            ],
            [
                'user_id' => $company->id,
                'role' => 'perusahaan',
                'category' => 'moderasi',
                'action' => 'Reset Enrollment',
                'description' => "Perusahaan {$company->name} mereset progress belajar '{$student->name}' pada course 'Fundamental UI/UX Design'",
                'created_at' => now()->subDays(2),
            ],
            [
                'user_id' => $admin->id,
                'role' => 'admin',
                'category' => 'moderasi',
                'action' => 'Menolak Course',
                'description' => "Admin {$admin->name} menolak course draft 'Hacking for Beginners' dari PT Telkom Indonesia karena konten tidak sesuai standar",
                'created_at' => now()->subDays(1),
            ],
            [
                'user_id' => $student->id,
                'role' => 'mahasiswa',
                'category' => 'enrollment',
                'action' => 'Mendaftar Kursus',
                'description' => "Mahasiswa {$student->name} mendaftar pada kursus LMS: Backend Laravel Development",
                'created_at' => now(),
            ]
        ];

        // 3. Insert into the database
        foreach ($logs as $logData) {
            ActivityLog::create([
                'user_id' => $logData['user_id'],
                'role' => $logData['role'],
                'category' => $logData['category'],
                'action' => $logData['action'],
                'description' => $logData['description'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'created_at' => $logData['created_at'],
                'updated_at' => $logData['created_at'],
            ]);
        }
    }
}
