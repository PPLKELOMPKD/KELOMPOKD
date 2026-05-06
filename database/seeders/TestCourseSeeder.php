<?php

namespace Database\Seeders;

use App\Models\LmsCourse;
use App\Models\LmsChapter;
use App\Models\LmsLesson;
use App\Models\LmsQuiz;
use App\Models\LmsQuizQuestion;
use App\Models\LmsQuizOption;
use App\Models\LmsAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestCourseSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create a company user
        $company = User::firstOrCreate(
            ['email' => 'company@sikara.test'],
            [
                'name' => 'Tech Academy',
                'password' => bcrypt('password'),
                'role' => 'perusahaan',
                'status' => 'active'
            ]
        );

        // Ensure company profile exists
        $company->perusahaanProfile()->firstOrCreate([], [
            'description' => 'A leading tech education provider.',
            'industry' => 'Education',
            'website' => 'https://techacademy.test'
        ]);

        // Create the course
        $course = LmsCourse::create([
            'company_id' => $company->id,
            'slug' => 'introduction-to-cloud-computing',
            'title' => 'Introduction to Cloud Computing',
            'provider' => 'Tech Academy',
            'description' => 'Learn the basics of cloud computing, deployment models, and service models.',
            'level' => 'Beginner',
            'status' => LmsCourse::STATUS_PUBLISHED,
            'started_at' => now(),
            'ends_at' => now()->addMonths(3),
            'image_url' => 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?q=80&w=800&auto=format&fit=crop',
            'image_alt' => 'Cloud Computing Illustration',
        ]);

        // Create Chapter 1
        $chapter = LmsChapter::create([
            'course_id' => $course->id,
            'title' => 'Cloud Basics',
            'position' => 1,
        ]);

        // Create Lesson 1
        LmsLesson::create([
            'chapter_id' => $chapter->id,
            'title' => 'What is Cloud Computing?',
            'type' => 'video',
            'content' => 'Cloud computing is the on-demand availability of computer system resources, especially data storage and computing power, without direct active management by the user.',
            'position' => 1,
        ]);

        // Create Assignment
        LmsAssignment::create([
            'chapter_id' => $chapter->id,
            'title' => 'Compare Deployment Models',
            'description' => 'Write a short essay comparing Public, Private, and Hybrid cloud models.',
            'position' => 2,
        ]);

        // Create Quiz
        $quiz = LmsQuiz::create([
            'chapter_id' => $chapter->id,
            'title' => 'Cloud Foundations Quiz',
            'description' => 'Test your knowledge on cloud basics.',
            'passing_score' => 70,
        ]);

        // Create Quiz Question
        $question = LmsQuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question' => 'Which of these is a Cloud deployment model?',
            'position' => 1,
        ]);

        LmsQuizOption::create([
            'question_id' => $question->id,
            'option_text' => 'Public Cloud',
            'is_correct' => true,
        ]);

        LmsQuizOption::create([
            'question_id' => $question->id,
            'option_text' => 'Local Cloud',
            'is_correct' => false,
        ]);
    }
}
