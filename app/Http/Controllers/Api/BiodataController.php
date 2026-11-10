<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * API untuk alur layar biodata:
 * identity_screen (nama) -> nomor_induk_screen (NIS) -> jurusan_screen
 * -> kelas_screen (tingkat X/XI/XII) -> pilih rombel -> selesai (100%).
 */
class BiodataController extends Controller
{
    // Urutan langkah; progres = jumlah langkah terisi berurutan x 20%.
    private const LANGKAH = ['nama_lengkap', 'nis', 'jurusan_id', 'tingkat_id', 'kelas_id'];

    /* ---------- Data pilihan untuk layar ---------- */

    // jurusan_screen
    public function jurusan(): JsonResponse
    {
        $data = DB::table('jurusan')->orderBy('id')->get(['id', 'kode', 'nama', 'ikon'])
            ->map(fn ($j) => $this->denganIkonUrl($j));

        return response()->json(['data' => $data]);
    }

    // kelas_screen (kelas X, XI, XII)
    public function tingkat(): JsonResponse
    {
        $data = DB::table('tingkat_kelas')->orderBy('angka')->get(['id', 'angka', 'romawi', 'ikon'])
            ->map(fn ($t) => $this->denganIkonUrl($t));

        return response()->json(['data' => $data]);
    }

    // layar pilih rombel: X Perbankan 1, 2, 3
    // Pakai jurusan & tingkat yang sudah dipilih siswa ini.
    public function kelas(int $id): JsonResponse
    {
        $siswa = $this->siswa($id);

        $jurusanId = $siswa->jurusan_id;
        $tingkatId = $siswa->tingkat_id;

        if (! $jurusanId || ! $tingkatId) {
            return response()->json([
                'message' => 'Pilih jurusan dan tingkat kelas terlebih dahulu.',
            ], 422);
        }

        $data = DB::table('kelas')
            ->where('jurusan_id', $jurusanId)
            ->where('tingkat_id', $tingkatId)
            ->orderBy('nomor')
            ->get(['id', 'nomor', 'nama', 'jurusan_id', 'tingkat_id']);

        return response()->json(['data' => $data]);
    }

    /* ---------- Biodata siswa (tanpa login, pakai id siswa) ---------- */

    // start_screens: tombol "Mulai" -> buat data siswa baru, simpan "id" di aplikasi
    public function mulai(): JsonResponse
    {
        $now = now();
        $id  = DB::table('siswa')->insertGetId([
            'langkah' => 0, 'created_at' => $now, 'updated_at' => $now,
        ]);

        return $this->respon($this->siswa($id), 'Data siswa dibuat.', 201);
    }

    public function show(int $id): JsonResponse
    {
        return $this->respon($this->siswa($id));
    }

    // identity_screen
    public function simpanNama(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'min:2', 'max:150'],
        ]);

        return $this->simpan($id, ['nama_lengkap' => trim($data['nama_lengkap'])], 'Nama tersimpan.');
    }

    // nomor_induk_screen
    public function simpanNis(Request $request, int $id): JsonResponse
    {
        $siswa = $this->siswa($id);
        $this->wajibSudah($siswa, 'nama_lengkap', 'Isi nama lengkap terlebih dahulu.');

        $data = $request->validate([
            'nis' => [
                'required', 'string', 'regex:/^[0-9]{4,20}$/',
                Rule::unique('siswa', 'nis')->ignore($siswa->id),
            ],
        ], [
            'nis.regex'  => 'NIS/NISN hanya boleh angka, 4 sampai 20 digit.',
            'nis.unique' => 'NIS/NISN ini sudah dipakai siswa lain.',
        ]);

        return $this->simpan($id, ['nis' => $data['nis']], 'NIS/NISN tersimpan.');
    }

    // jurusan_screen
    public function simpanJurusan(Request $request, int $id): JsonResponse
    {
        $siswa = $this->siswa($id);
        $this->wajibSudah($siswa, 'nis', 'Isi NIS/NISN terlebih dahulu.');

        $data = $request->validate([
            'jurusan_id' => ['required', 'integer', 'exists:jurusan,id'],
        ]);

        $ubah = ['jurusan_id' => $data['jurusan_id']];
        if ((int) $siswa->jurusan_id !== (int) $data['jurusan_id']) {
            $ubah['kelas_id'] = null; // rombel lama tidak berlaku lagi
        }

        return $this->simpan($id, $ubah, 'Jurusan tersimpan.');
    }

    // kelas_screen (X / XI / XII)
    public function simpanTingkat(Request $request, int $id): JsonResponse
    {
        $siswa = $this->siswa($id);
        $this->wajibSudah($siswa, 'jurusan_id', 'Pilih jurusan terlebih dahulu.');

        $data = $request->validate([
            'tingkat_id' => ['required', 'integer', 'exists:tingkat_kelas,id'],
        ]);

        $ubah = ['tingkat_id' => $data['tingkat_id']];
        if ((int) $siswa->tingkat_id !== (int) $data['tingkat_id']) {
            $ubah['kelas_id'] = null;
        }

        return $this->simpan($id, $ubah, 'Tingkat kelas tersimpan.');
    }

    // layar pilih rombel
    public function simpanKelas(Request $request, int $id): JsonResponse
    {
        $siswa = $this->siswa($id);
        $this->wajibSudah($siswa, 'tingkat_id', 'Pilih tingkat kelas terlebih dahulu.');

        $data = $request->validate([
            'kelas_id' => [
                'required', 'integer',
                Rule::exists('kelas', 'id')
                    ->where('jurusan_id', $siswa->jurusan_id)
                    ->where('tingkat_id', $siswa->tingkat_id),
            ],
        ], [
            'kelas_id.exists' => 'Kelas tidak sesuai dengan jurusan/tingkat yang dipilih.',
        ]);

        return $this->simpan($id, ['kelas_id' => $data['kelas_id']], 'Biodata lengkap!');
    }

    /* ---------- Helper ---------- */

    // Ambil data siswa berdasarkan id; 404 kalau tidak ada.
    private function siswa(int $id): object
    {
        $siswa = DB::table('siswa')->where('id', $id)->first();

        abort_if(! $siswa, 404, 'Data siswa tidak ditemukan.');

        return $siswa;
    }

    private function wajibSudah(object $siswa, string $kolom, string $pesan): void
    {
        if ($siswa->{$kolom} === null) {
            throw ValidationException::withMessages(['langkah' => [$pesan]]);
        }
    }

    private function simpan(int $id, array $ubah, string $pesan): JsonResponse
    {
        $siswa = $this->siswa($id);
        $baru  = array_merge((array) $siswa, $ubah);

        $langkah = 0;
        foreach (self::LANGKAH as $kolom) {
            if ($baru[$kolom] === null) {
                break;
            }
            $langkah++;
        }

        $lengkap = $langkah === count(self::LANGKAH);

        DB::table('siswa')->where('id', $siswa->id)->update($ubah + [
            'langkah'         => $langkah,
            'biodata_lengkap' => $lengkap,
            'selesai_at'      => $lengkap ? ($siswa->selesai_at ?? now()) : null,
            'updated_at'      => now(),
        ]);

        return $this->respon(DB::table('siswa')->where('id', $siswa->id)->first(), $pesan);
    }

    private function respon(object $siswa, ?string $pesan = null, int $status = 200): JsonResponse
    {
        $jurusan = $siswa->jurusan_id ? DB::table('jurusan')->find($siswa->jurusan_id) : null;
        $tingkat = $siswa->tingkat_id ? DB::table('tingkat_kelas')->find($siswa->tingkat_id) : null;
        $kelas   = $siswa->kelas_id ? DB::table('kelas')->find($siswa->kelas_id) : null;

        $body = [
            'data' => [
                'id'              => $siswa->id,
                'nama_lengkap'    => $siswa->nama_lengkap,
                'nis'             => $siswa->nis,
                'jurusan'         => $jurusan ? ['id' => $jurusan->id, 'nama' => $jurusan->nama] : null,
                'tingkat'         => $tingkat ? ['id' => $tingkat->id, 'romawi' => $tingkat->romawi] : null,
                'kelas'           => $kelas ? ['id' => $kelas->id, 'nama' => $kelas->nama] : null,
                'langkah'         => (int) $siswa->langkah,
                'progres_persen'  => (int) $siswa->langkah * 20,
                'biodata_lengkap' => (bool) $siswa->biodata_lengkap,
                'selesai_at'      => $siswa->selesai_at,
            ],
        ];

        return response()->json($pesan ? ['message' => $pesan] + $body : $body, $status);
    }

    private function denganIkonUrl(object $row): object
    {
        $row->ikon_url = $row->ikon ? asset($row->ikon) : null;

        return $row;
    }
}
