<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Jurusan extends Model
{
    protected $table = 'jurusan';

    protected $fillable = [
        'kode',
        'nama',
        'ikon',
    ];

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class);
    }

    public function scopeWithJumlah(Builder $query): Builder
    {
        return $query->select('jurusan.*')
            ->withCount('kelas')
            ->addSelect(['siswa_count' => DB::table('siswa')
                ->selectRaw('COUNT(*)')
                ->whereColumn('siswa.jurusan_id', 'jurusan.id')]);
    }

    public function jumlahSiswa(): int
    {
        return DB::table('siswa')->where('jurusan_id', $this->id)->count();
    }

    public function alasanTidakBisaDihapus(): ?string
    {
        $jumlahKelas = $this->kelas()->count();
        if ($jumlahKelas > 0) {
            return "Jurusan {$this->nama} masih memiliki {$jumlahKelas} kelas. Hapus kelasnya terlebih dahulu.";
        }

        $jumlahSiswa = $this->jumlahSiswa();
        if ($jumlahSiswa > 0) {
            return "Jurusan {$this->nama} masih dipilih oleh {$jumlahSiswa} siswa.";
        }

        return null;
    }

    public function sinkronkanNamaKelas(): void
    {
        $this->kelas()->with('tingkat')->get()->each(function (Kelas $kelas) {
            $kelas->update(['nama' => Kelas::namaUntuk($this, $kelas->tingkat, $kelas->nomor)]);
        });
    }
}
