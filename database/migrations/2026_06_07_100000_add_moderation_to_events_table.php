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
        Schema::table('events', function (Blueprint $table) {
            $table->string('moderation_status', 20)->default('pending')->after('status')->index();
            $table->text('rejection_reason')->nullable()->after('moderation_status');
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete()->after('rejection_reason');
            $table->timestamp('moderated_at')->nullable()->after('moderated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['moderated_by']);
            $table->dropIndex(['moderation_status']);
            $table->dropColumn(['moderation_status', 'rejection_reason', 'moderated_by', 'moderated_at']);
        });
    }
};
