<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Hapus kolom nim & jurusan dari users (sudah digantikan tabel `siswa`)
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'nim')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['nim']);
                $table->dropColumn('nim');
            });
        }

        if (Schema::hasColumn('users', 'jurusan')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('jurusan');
            });
        }
    }

    public function down(): void
    {
        //
    }
};
