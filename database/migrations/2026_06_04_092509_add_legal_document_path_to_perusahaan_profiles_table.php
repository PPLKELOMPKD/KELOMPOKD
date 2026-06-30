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
        if (!Schema::hasTable('perusahaan_profiles')) {
            return;
        }

        if (!Schema::hasColumn('perusahaan_profiles', 'legal_document_path')) {
            Schema::table('perusahaan_profiles', function (Blueprint $table) {
                $table->text('legal_document_path')->nullable()->after('website');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('perusahaan_profiles')) {
            return;
        }

        if (Schema::hasColumn('perusahaan_profiles', 'legal_document_path')) {
            Schema::table('perusahaan_profiles', function (Blueprint $table) {
                $table->dropColumn('legal_document_path');
            });
        }
    }
};
