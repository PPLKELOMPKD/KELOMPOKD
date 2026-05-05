<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_courses', function (Blueprint $table) {
            if (! Schema::hasColumn('lms_courses', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('lms_courses', 'description')) {
                $table->text('description')->nullable()->after('provider');
            }
            if (Schema::hasColumn('lms_courses', 'chapters')) {
                $table->dropColumn('chapters');
            }
        });

        Schema::create('lms_chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();
        });

        Schema::create('lms_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained('lms_chapters')->cascadeOnDelete();
            $table->string('title');
            $table->string('type')->default('article');
            $table->longText('content')->nullable();
            $table->text('video_url')->nullable();
            $table->text('video_image_url')->nullable();
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();
        });

        Schema::create('lms_quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->unique()->constrained('lms_chapters')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('passing_score')->default(70);
            $table->timestamps();
        });

        Schema::create('lms_quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('lms_quizzes')->cascadeOnDelete();
            $table->text('question');
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();
        });

        Schema::create('lms_quiz_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('lms_quiz_questions')->cascadeOnDelete();
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();
        });

        Schema::create('lms_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['course_id', 'student_id']);
        });

        Schema::create('lms_lesson_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('lms_enrollments')->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained('lms_lessons')->cascadeOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['enrollment_id', 'lesson_id']);
        });

        Schema::create('lms_quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('lms_enrollments')->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained('lms_quizzes')->cascadeOnDelete();
            $table->unsignedTinyInteger('score')->default(0);
            $table->boolean('passed')->default(false);
            $table->json('answers');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lms_chapter_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('lms_enrollments')->cascadeOnDelete();
            $table->foreignId('chapter_id')->constrained('lms_chapters')->cascadeOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['enrollment_id', 'chapter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_chapter_completions');
        Schema::dropIfExists('lms_quiz_attempts');
        Schema::dropIfExists('lms_lesson_completions');
        Schema::dropIfExists('lms_enrollments');
        Schema::dropIfExists('lms_quiz_options');
        Schema::dropIfExists('lms_quiz_questions');
        Schema::dropIfExists('lms_quizzes');
        Schema::dropIfExists('lms_lessons');
        Schema::dropIfExists('lms_chapters');
    }
};
