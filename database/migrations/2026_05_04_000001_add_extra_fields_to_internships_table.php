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
        Schema::table('internships', function (Blueprint $table) {
            $table->string('work_type')->default('Magang')->after('location');
            $table->string('duration')->nullable()->after('work_type');
            $table->text('benefits')->nullable()->after('requirements');
            $table->string('salary')->nullable()->after('benefits');
            $table->string('company_logo')->nullable()->after('company_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internships', function (Blueprint $table) {
            $table->dropColumn(['work_type', 'duration', 'benefits', 'salary', 'company_logo']);
        });
    }
};
