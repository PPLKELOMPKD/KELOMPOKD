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
        Schema::create('lms_courses', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('provider');
            $table->string('level');
            $table->string('status')->default('in_progress');
            $table->unsignedTinyInteger('progress')->default(0);
            $table->date('started_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->text('image_url');
            $table->text('image_alt')->nullable();
            $table->json('chapters')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_courses');
    }
};
