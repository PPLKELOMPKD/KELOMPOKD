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
        Schema::table('perusahaan_profiles', function (Blueprint $table) {
            $table->string('contact_email')->nullable()->after('office_address');
            $table->string('instagram')->nullable()->after('contact_email');
            $table->string('linkedin')->nullable()->after('instagram');
            $table->json('gallery_photos')->nullable()->after('linkedin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perusahaan_profiles', function (Blueprint $table) {
            $table->dropColumn(['contact_email', 'instagram', 'linkedin', 'gallery_photos']);
        });
    }
};
