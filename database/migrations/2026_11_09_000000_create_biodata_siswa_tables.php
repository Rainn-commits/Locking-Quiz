<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel biodata siswa Locking Quiz (PostgreSQL).
 * Alur layar: nama -> NIS/NISN -> jurusan -> tingkat kelas -> rombel.
 * Taruh di database/migrations/, lalu: php artisan migrate --seed
 */
return new class extends Migration
{
    public function up(): void
    {
        // jurusan_screen: Perbankan, Desain Komunikasi Visual
        Schema::create('jurusan', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique();
            $table->string('nama', 100)->unique();
            $table->string('ikon')->nullable();
            $table->timestamps();
        });

        // kelas_screen: kelas X, XI, XII
        Schema::create('tingkat_kelas', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->smallInteger('angka')->unique();
            $table->string('romawi', 5)->unique();
            $table->string('ikon')->nullable();
        });

        // pilih rombel: X Perbankan 1, 2, 3
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jurusan_id')->constrained('jurusan')->cascadeOnDelete();
            $table->unsignedSmallInteger('tingkat_id');
            $table->foreign('tingkat_id')->references('id')->on('tingkat_kelas')->cascadeOnDelete();
            $table->smallInteger('nomor');
            $table->string('nama', 100);
            $table->timestamps();
            $table->unique(['jurusan_id', 'tingkat_id', 'nomor'], 'kelas_unik');
            $table->index(['jurusan_id', 'tingkat_id']);
        });

        // biodata siswa, diisi bertahap per layar
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nama_lengkap', 150)->nullable();
            $table->string('nis', 20)->nullable()->unique();
            $table->foreignId('jurusan_id')->nullable()->constrained('jurusan')->nullOnDelete();
            $table->unsignedSmallInteger('tingkat_id')->nullable();
            $table->foreign('tingkat_id')->references('id')->on('tingkat_kelas')->nullOnDelete();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->smallInteger('langkah')->default(0); // 0..5 = 0%..100%
            $table->boolean('biodata_lengkap')->default(false);
            $table->timestamp('selesai_at')->nullable();
            $table->timestamps();
        });

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE tingkat_kelas ADD CONSTRAINT tingkat_angka_chk CHECK (angka BETWEEN 10 AND 12)');
        DB::statement('ALTER TABLE kelas ADD CONSTRAINT kelas_nomor_chk CHECK (nomor > 0)');
        DB::statement("ALTER TABLE siswa ADD CONSTRAINT siswa_nis_chk CHECK (nis ~ '^[0-9]{4,20}$')");
        DB::statement('ALTER TABLE siswa ADD CONSTRAINT siswa_langkah_chk CHECK (langkah BETWEEN 0 AND 5)');

        // Kelas harus cocok dengan jurusan & tingkat; tandai biodata lengkap otomatis
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION cek_kelas_siswa() RETURNS TRIGGER AS $$
            DECLARE k kelas%ROWTYPE;
            BEGIN
                IF NEW.kelas_id IS NOT NULL THEN
                    SELECT * INTO k FROM kelas WHERE id = NEW.kelas_id;
                    IF NEW.jurusan_id IS DISTINCT FROM k.jurusan_id
                       OR NEW.tingkat_id IS DISTINCT FROM k.tingkat_id THEN
                        RAISE EXCEPTION 'Kelas "%" tidak sesuai dengan jurusan/tingkat yang dipilih', k.nama;
                    END IF;
                END IF;

                NEW.biodata_lengkap := NEW.nama_lengkap IS NOT NULL AND NEW.nis IS NOT NULL
                                   AND NEW.jurusan_id IS NOT NULL AND NEW.tingkat_id IS NOT NULL
                                   AND NEW.kelas_id IS NOT NULL;
                IF NEW.biodata_lengkap THEN
                    NEW.langkah    := 5;
                    NEW.selesai_at := COALESCE(NEW.selesai_at, CURRENT_TIMESTAMP);
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER trg_cek_kelas_siswa
                BEFORE INSERT OR UPDATE ON siswa
                FOR EACH ROW EXECUTE FUNCTION cek_kelas_siswa();

            CREATE OR REPLACE VIEW v_biodata_siswa AS
            SELECT s.id, s.user_id, s.nama_lengkap, s.nis,
                   j.nama AS jurusan, t.romawi AS tingkat, k.nama AS kelas,
                   s.langkah * 20 AS progres_persen, s.biodata_lengkap, s.selesai_at
            FROM siswa s
            LEFT JOIN jurusan       j ON j.id = s.jurusan_id
            LEFT JOIN tingkat_kelas t ON t.id = s.tingkat_id
            LEFT JOIN kelas         k ON k.id = s.kelas_id;
        SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared('DROP VIEW IF EXISTS v_biodata_siswa; DROP TRIGGER IF EXISTS trg_cek_kelas_siswa ON siswa; DROP FUNCTION IF EXISTS cek_kelas_siswa();');
        }
        Schema::dropIfExists('siswa');
        Schema::dropIfExists('kelas');
        Schema::dropIfExists('tingkat_kelas');
        Schema::dropIfExists('jurusan');
    }
};
