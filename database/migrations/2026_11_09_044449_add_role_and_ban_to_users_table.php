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
            $table->string('role', 10)->default('siswa');
            $table->string('username', 50)->nullable()->unique(); 
            $table->boolean('is_banned')->default(false);
            $table->timestamp('banned_at')->nullable();
            $table->unsignedTinyInteger('jumlah_pelanggaran')->default(0);
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
