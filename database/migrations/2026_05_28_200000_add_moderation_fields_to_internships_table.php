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
            // Status moderasi: pending → menunggu review admin
            //                  approved → tayang di katalog
            //                  rejected → ditolak (termasuk takedown)
            $table->enum('moderation_status', ['pending', 'approved', 'rejected'])
                  ->default('pending')
                  ->after('is_published');

            // Alasan penolakan dari admin (wajib diisi saat reject/takedown)
            $table->text('rejection_reason')->nullable()->after('moderation_status');

            // Admin yang melakukan tindakan moderasi terakhir
            $table->foreignId('moderated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->after('rejection_reason');

            // Waktu moderasi terakhir dilakukan
            $table->timestamp('moderated_at')->nullable()->after('moderated_by');
        });

        // Sinkronisasi data existing: jika is_published = true → approved, false → pending
        DB::table('internships')->where('is_published', true)->update(['moderation_status' => 'approved']);
        DB::table('internships')->where('is_published', false)->update(['moderation_status' => 'pending']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internships', function (Blueprint $table) {
            $table->dropForeign(['moderated_by']);
            $table->dropColumn(['moderation_status', 'rejection_reason', 'moderated_by', 'moderated_at']);
        });
    }
};
