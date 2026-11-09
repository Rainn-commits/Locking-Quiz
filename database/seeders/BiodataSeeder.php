<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Data pilihan di layar biodata.
 * Taruh di database/seeders/, lalu panggil dari DatabaseSeeder:
 $this->call(BiodataSeeder::class);
 */
class BiodataSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('jurusan')->upsert([
            ['kode' => 'PBK', 'nama' => 'Perbankan',                'ikon' => 'images/jurusan/perbankan.png', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => 'DKV', 'nama' => 'Desain Komunikasi Visual', 'ikon' => 'images/jurusan/dkv.png',       'created_at' => $now, 'updated_at' => $now],
        ], ['kode'], ['nama', 'ikon', 'updated_at']);

        DB::table('tingkat_kelas')->upsert([
            ['angka' => 10, 'romawi' => 'X',   'ikon' => 'images/kelas/x.png'],
            ['angka' => 11, 'romawi' => 'XI',  'ikon' => 'images/kelas/xi.png'],
            ['angka' => 12, 'romawi' => 'XII', 'ikon' => 'images/kelas/xii.png'],
        ], ['angka'], ['romawi', 'ikon']);

        // 3 rombel per jurusan per tingkat: X Perbankan 1..3, XI DKV 1..3, dst.
        $rows = [];
        foreach (DB::table('jurusan')->get() as $j) {
            foreach (DB::table('tingkat_kelas')->orderBy('angka')->get() as $t) {
                foreach ([1, 2, 3] as $n) {
                    $rows[] = [
                        'jurusan_id' => $j->id,
                        'tingkat_id' => $t->id,
                        'nomor'      => $n,
                        'nama'       => "{$t->romawi} {$j->nama} {$n}",
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }
        DB::table('kelas')->upsert($rows, ['jurusan_id', 'tingkat_id', 'nomor'], ['nama', 'updated_at']);
    }
}
