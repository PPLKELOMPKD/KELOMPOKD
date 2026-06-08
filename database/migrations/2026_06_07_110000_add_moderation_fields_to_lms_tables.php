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
            if (!Schema::hasColumn('lms_courses', 'moderation_status')) {
                $table->enum('moderation_status', ['pending', 'approved', 'rejected', 'takedown'])
                    ->default('pending')
                    ->after('status');
            }
            if (!Schema::hasColumn('lms_courses', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('moderation_status');
            }
        });

        Schema::table('lms_lessons', function (Blueprint $table) {
            if (!Schema::hasColumn('lms_lessons', 'status')) {
                $table->string('status')->default('active')->after('title');
            }
        });

        Schema::table('lms_quizzes', function (Blueprint $table) {
            if (!Schema::hasColumn('lms_quizzes', 'status')) {
                $table->string('status')->default('active')->after('title');
            }
        });

        Schema::table('lms_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('lms_assignments', 'status')) {
                $table->string('status')->default('active')->after('title');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lms_courses', function (Blueprint $table) {
            $table->dropColumn(['moderation_status', 'rejection_reason']);
        });

        Schema::table('lms_lessons', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('lms_quizzes', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('lms_assignments', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
