<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lms_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained('lms_chapters')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('file_url')->nullable();
            $table->dateTime('deadline_at')->nullable();
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_assignments');
    }
};
