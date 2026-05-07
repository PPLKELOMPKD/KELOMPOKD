<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lms_courses', function (Blueprint $table) {
            $table->string('location')->nullable()->after('level');
            $table->unsignedInteger('quota')->nullable()->after('location');
            $table->time('start_time')->nullable()->after('started_at');
        });

        Schema::table('lms_quizzes', function (Blueprint $table) {
            $table->unsignedInteger('time_limit')->nullable()->after('passing_score'); // in minutes
        });

        Schema::table('lms_assignments', function (Blueprint $table) {
            $table->string('allowed_formats')->default('pdf,doc,docx,zip')->after('deadline_at');
        });

        Schema::table('lms_enrollments', function (Blueprint $table) {
            $table->string('status')->default('accepted')->after('student_id'); // pending, accepted, rejected
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lms_courses', function (Blueprint $table) {
            $table->dropColumn(['location', 'quota', 'start_time']);
        });

        Schema::table('lms_quizzes', function (Blueprint $table) {
            $table->dropColumn('time_limit');
        });

        Schema::table('lms_assignments', function (Blueprint $table) {
            $table->dropColumn('allowed_formats');
        });

        Schema::table('lms_enrollments', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
