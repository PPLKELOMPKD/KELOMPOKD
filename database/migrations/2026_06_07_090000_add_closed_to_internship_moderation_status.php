<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambah nilai 'closed' ke enum moderation_status pada tabel internships.
     * 'closed' digunakan khusus untuk takedown — tidak bisa diedit ulang oleh perusahaan.
     */
    public function up(): void
    {
        // MySQL tidak mendukung ALTER COLUMN pada enum secara langsung,
        // sehingga kita gunakan raw SQL untuk memodifikasi kolom enum.
        DB::statement("ALTER TABLE internships MODIFY COLUMN moderation_status ENUM('pending', 'approved', 'rejected', 'closed') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Kembalikan record 'closed' ke 'rejected' sebelum menghapus nilainya dari enum
        DB::table('internships')->where('moderation_status', 'closed')->update(['moderation_status' => 'rejected']);
        DB::statement("ALTER TABLE internships MODIFY COLUMN moderation_status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
    }
};
