<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Kelas extends Model
{
    protected $table = 'kelas';

    protected $fillable = [
        'jurusan_id',
        'tingkat_id',
        'nomor',
        'nama',
    ];

    protected function casts(): array
    {
        return [
            'jurusan_id' => 'integer',
            'tingkat_id' => 'integer',
            'nomor'      => 'integer',
        ];
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function tingkat(): BelongsTo
    {
        return $this->belongsTo(TingkatKelas::class, 'tingkat_id');
    }

    public static function namaUntuk(Jurusan $jurusan, TingkatKelas $tingkat, int $nomor): string
    {
        return "{$tingkat->romawi} {$jurusan->nama} {$nomor}";
    }

    public function scopeWithJumlahSiswa(Builder $query): Builder
    {
        return $query->select('kelas.*')
            ->addSelect(['siswa_count' => DB::table('siswa')
                ->selectRaw('COUNT(*)')
                ->whereColumn('siswa.kelas_id', 'kelas.id')]);
    }

    public function jumlahSiswa(): int
    {
        return DB::table('siswa')->where('kelas_id', $this->id)->count();
    }

    public function alasanTidakBisaDihapus(): ?string
    {
        $jumlah = $this->jumlahSiswa();

        return $jumlah > 0
            ? "Kelas {$this->nama} masih memiliki {$jumlah} siswa. Pindahkan siswanya terlebih dahulu."
            : null;
    }

    public function alasanTidakBisaPindah(int $jurusanId, int $tingkatId): ?string
    {
        $pindah = $jurusanId !== $this->jurusan_id || $tingkatId !== $this->tingkat_id;
        $jumlah = $pindah ? $this->jumlahSiswa() : 0;

        return $jumlah > 0
            ? "Jurusan/tingkat kelas {$this->nama} tidak bisa diubah karena sudah berisi {$jumlah} siswa."
            : null;
    }

    public static function simpan(array $data, ?self $kelas = null): self
    {
        $jurusan = Jurusan::findOrFail($data['jurusan_id']);
        $tingkat = TingkatKelas::findOrFail($data['tingkat_id']);

        $data['nama'] = static::namaUntuk($jurusan, $tingkat, (int) $data['nomor']);

        if ($kelas) {
            $kelas->update($data);

            return $kelas;
        }

        return static::create($data);
    }
}
