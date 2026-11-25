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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role', 10)->default('siswa');
            }
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username', 50)->nullable()->unique();
            }
            if (! Schema::hasColumn('users', 'is_banned')) {
                $table->boolean('is_banned')->default(false);
            }
            if (! Schema::hasColumn('users', 'banned_at')) {
                $table->timestamp('banned_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'jumlah_pelanggaran')) {
                $table->unsignedTinyInteger('jumlah_pelanggaran')->default(0);
            }
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'username', 'is_banned', 'banned_at', 'jumlah_pelanggaran']);
        });
    }
};
