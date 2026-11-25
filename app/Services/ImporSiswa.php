<?php

namespace App\Services;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\TingkatKelas;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Impor akun dan biodata siswa dari file CSV.
 *
 * Kolom wajib : nama, email, nis, jurusan (kode/nama), tingkat (X/XI/XII atau 10/11/12), rombel (nomor)
 * Kolom opsional: password (jika kosong, password = NIS)
 *
 * Semua baris dicek dulu. Jika ada satu saja yang salah, tidak ada data yang disimpan.
 * Kelas (rombel) yang belum ada dibuat otomatis, jurusan harus sudah terdaftar.
 */
class ImporSiswa
{
    public const MAKS_BARIS = 1000;

    public const KOLOM_WAJIB = ['nama', 'email', 'nis', 'jurusan', 'tingkat', 'rombel'];

    /** Nama kolom lain yang juga dikenali. */
    private const ALIAS = [
        'nama_lengkap' => 'nama', 'name' => 'nama', 'nama_siswa' => 'nama',
        'e_mail' => 'email',
        'nisn' => 'nis', 'nis_nisn' => 'nis',
        'kode_jurusan' => 'jurusan',
        'tingkat_kelas' => 'tingkat',
        'nomor_rombel' => 'rombel', 'nomor' => 'rombel', 'kelas' => 'rombel',
        'kata_sandi' => 'password',
    ];

    /** @var string[] */
    public array $errors = [];

    public int $jumlahSiswa = 0;

    public int $jumlahKelasBaru = 0;

    /** Isi file contoh untuk diunduh admin. */
    public static function templateCsv(): string
    {
        return "\xEF\xBB\xBF" . implode("\r\n", [
            'nama,email,nis,jurusan,tingkat,rombel,password',
            'Budi Santoso,budi@contoh.sch.id,1234567890,PBK,X,1,',
            'Siti Aminah,siti@contoh.sch.id,1234567891,Desain Komunikasi Visual,XI,2,rahasia123',
        ]) . "\r\n";
    }

    /** Jalankan impor. True jika berhasil, false jika ada kesalahan (lihat $errors). */
    public function jalankan(string $pathFile): bool
    {
        $this->errors = [];
        $this->jumlahSiswa = 0;
        $this->jumlahKelasBaru = 0;

        $baris = $this->bacaCsv($pathFile);
        if ($baris === null) {
            return false;
        }

        $data = $this->validasi($baris);
        if ($this->errors) {
            return false;
        }

        @set_time_limit(300);

        $this->simpan($data);

        return true;
    }

    /** @return array<int, array<string, string>>|null  nomor baris file => [kolom => nilai] */
    private function bacaCsv(string $pathFile): ?array
    {
        $isi = (string) file_get_contents($pathFile);
        $isi = preg_replace('/^\xEF\xBB\xBF/', '', $isi);

        if (! mb_check_encoding($isi, 'UTF-8')) {
            $isi = mb_convert_encoding($isi, 'UTF-8', 'Windows-1252');
        }

        if (trim($isi) === '') {
            $this->errors[] = 'File kosong.';

            return null;
        }

        $barisPertama = strtok($isi, "\r\n");
        $pemisah = collect([',', ';', "\t"])
            ->sortByDesc(fn ($p) => substr_count($barisPertama, $p))
            ->first();

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $isi);
        rewind($stream);

        $header = array_map(fn ($h) => $this->namaKolom((string) $h), fgetcsv($stream, null, $pemisah, '"', '') ?: []);

        $kurang = array_diff(self::KOLOM_WAJIB, $header);
        if ($kurang) {
            fclose($stream);
            $this->errors[] = 'Kolom wajib tidak ditemukan: ' . implode(', ', $kurang)
                . '. Baris pertama file harus berisi judul kolom: ' . implode(', ', self::KOLOM_WAJIB) . ', password (opsional).';

            return null;
        }

        $hasil = [];
        $nomor = 1;
        while (($kolom = fgetcsv($stream, null, $pemisah, '"', '')) !== false) {
            $nomor++;
            if (count(array_filter($kolom, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue; 
            }

            $row = [];
            foreach ($header as $i => $nama) {
                if ($nama !== '' && ! isset($row[$nama])) {
                    $row[$nama] = trim((string) ($kolom[$i] ?? ''));
                }
            }
            $hasil[$nomor] = $row;

            if (count($hasil) > self::MAKS_BARIS) {
                fclose($stream);
                $this->errors[] = 'Maksimal ' . self::MAKS_BARIS . ' siswa per file. Bagi file menjadi beberapa bagian.';

                return null;
            }
        }
        fclose($stream);

        if (! $hasil) {
            $this->errors[] = 'File tidak berisi data siswa (hanya judul kolom).';

            return null;
        }

        return $hasil;
    }

    private function namaKolom(string $judul): string
    {
        $k = trim(mb_strtolower($judul));
        $k = preg_replace('/[^a-z0-9]+/', '_', $k);
        $k = trim($k, '_');

        return self::ALIAS[$k] ?? $k;
    }

    /** @return array<int, array<string, mixed>> */
    private function validasi(array $baris): array
    {
        $jurusan = Jurusan::all();
        $jurusanByKey = [];
        foreach ($jurusan as $j) {
            $jurusanByKey[mb_strtolower($j->kode)] = $j;
            $jurusanByKey[mb_strtolower($j->nama)] = $j;
        }

        $tingkatByKey = [];
        foreach (TingkatKelas::all() as $t) {
            $tingkatByKey[mb_strtoupper($t->romawi)] = $t;
            $tingkatByKey[(string) $t->angka] = $t;
        }

        $emailDiFile = array_map(fn ($r) => mb_strtolower($r['email'] ?? ''), $baris);
        $nisDiFile = array_map(fn ($r) => $r['nis'] ?? '', $baris);

        $emailTerdaftar = User::whereIn(DB::raw('LOWER(email)'), array_filter($emailDiFile))
            ->pluck('email')->map(fn ($e) => mb_strtolower($e))->flip();
        $nisTerdaftar = DB::table('siswa')->whereIn('nis', array_filter($nisDiFile))->pluck('nis')->flip();

        $sudahEmail = [];
        $sudahNis = [];
        $data = [];

        foreach ($baris as $no => $r) {
            $salah = [];

            $nama = $r['nama'] ?? '';
            if ($nama === '') {
                $salah[] = 'nama wajib diisi';
            } elseif (mb_strlen($nama) > 150) {
                $salah[] = 'nama maksimal 150 karakter';
            }

            $email = mb_strtolower($r['email'] ?? '');
            if ($email === '') {
                $salah[] = 'email wajib diisi';
            } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 255) {
                $salah[] = "email \"{$email}\" tidak valid";
            } elseif (isset($emailTerdaftar[$email])) {
                $salah[] = "email {$email} sudah terdaftar";
            } elseif (isset($sudahEmail[$email])) {
                $salah[] = "email {$email} sama dengan baris {$sudahEmail[$email]}";
            } else {
                $sudahEmail[$email] = $no;
            }

            $nis = $r['nis'] ?? '';
            if ($nis === '') {
                $salah[] = 'NIS wajib diisi';
            } elseif (! preg_match('/^[0-9]{4,20}$/', $nis)) {
                $salah[] = "NIS \"{$nis}\" harus berupa 4–20 digit angka";
            } elseif (isset($nisTerdaftar[$nis])) {
                $salah[] = "NIS {$nis} sudah terdaftar";
            } elseif (isset($sudahNis[$nis])) {
                $salah[] = "NIS {$nis} sama dengan baris {$sudahNis[$nis]}";
            } else {
                $sudahNis[$nis] = $no;
            }

            $j = $jurusanByKey[mb_strtolower($r['jurusan'] ?? '')] ?? null;
            if (! $j) {
                $salah[] = ($r['jurusan'] ?? '') === ''
                    ? 'jurusan wajib diisi'
                    : "jurusan \"{$r['jurusan']}\" tidak ditemukan (pakai kode atau nama jurusan yang terdaftar)";
            }

            $t = $tingkatByKey[mb_strtoupper($r['tingkat'] ?? '')] ?? null;
            if (! $t) {
                $salah[] = 'tingkat harus X, XI, XII (atau 10, 11, 12)';
            }

            $rombel = $r['rombel'] ?? '';
            if (! ctype_digit($rombel) || (int) $rombel < 1 || (int) $rombel > 99) {
                $salah[] = 'rombel harus angka 1–99';
            }

            $password = $r['password'] ?? '';
            if ($password !== '' && mb_strlen($password) < 6) {
                $salah[] = 'password minimal 6 karakter (kosongkan agar sama dengan NIS)';
            }

            if ($salah) {
                $this->errors[] = "Baris {$no}: " . implode('; ', $salah) . '.';

                continue;
            }

            $data[] = [
                'nama'     => $nama,
                'email'    => $email,
                'nis'      => $nis,
                'jurusan'  => $j,
                'tingkat'  => $t,
                'rombel'   => (int) $rombel,
                'password' => $password !== '' ? $password : $nis,
            ];
        }

        return $data;
    }

    private function simpan(array $data): void
    {
        DB::transaction(function () use ($data) {
            $kelasCache = [];
            $sekarang = now();

            foreach ($data as $d) {
                $kunci = "{$d['jurusan']->id}-{$d['tingkat']->id}-{$d['rombel']}";

                if (! isset($kelasCache[$kunci])) {
                    $kelas = Kelas::where('jurusan_id', $d['jurusan']->id)
                        ->where('tingkat_id', $d['tingkat']->id)
                        ->where('nomor', $d['rombel'])
                        ->first();

                    if (! $kelas) {
                        $kelas = Kelas::simpan([
                            'jurusan_id' => $d['jurusan']->id,
                            'tingkat_id' => $d['tingkat']->id,
                            'nomor'      => $d['rombel'],
                        ]);
                        $this->jumlahKelasBaru++;
                    }

                    $kelasCache[$kunci] = $kelas->id;
                }

                $user = User::create([
                    'name'     => $d['nama'],
                    'email'    => $d['email'],
                    'password' => $d['password'],
                    'role'     => 'siswa',
                    'status'   => 'active',
                ]);

                DB::table('siswa')->insert([
                    'user_id'         => $user->id,
                    'nama_lengkap'    => $d['nama'],
                    'nis'             => $d['nis'],
                    'jurusan_id'      => $d['jurusan']->id,
                    'tingkat_id'      => $d['tingkat']->id,
                    'kelas_id'        => $kelasCache[$kunci],
                    'langkah'         => 5,
                    'biodata_lengkap' => true,
                    'selesai_at'      => $sekarang,
                    'created_at'      => $sekarang,
                    'updated_at'      => $sekarang,
                ]);

                $this->jumlahSiswa++;
            }
        });
    }
}
