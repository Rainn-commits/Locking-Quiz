<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImporSiswaRequest;
use App\Models\AuditLog;
use App\Models\Jurusan;
use App\Services\ImporSiswa;

class ImporSiswaController extends Controller
{
    public function create()
    {
        return view('admin.siswa.impor', [
            'daftarJurusan' => Jurusan::orderBy('nama')->get(['kode', 'nama']),
            'maksBaris'     => ImporSiswa::MAKS_BARIS,
        ]);
    }

    public function store(ImporSiswaRequest $request, ImporSiswa $impor)
    {
        if (! $impor->jalankan($request->file('file')->getRealPath())) {
            return back()->with('imporErrors', $impor->errors);
        }

        AuditLog::record($request->user(), 'import_siswa', 'siswa', null, $request->ip());

        $pesan = "{$impor->jumlahSiswa} siswa berhasil diimpor.";
        if ($impor->jumlahKelasBaru > 0) {
            $pesan .= " {$impor->jumlahKelasBaru} kelas baru dibuat otomatis.";
        }

        return redirect()->route('admin.siswa.index')->with('success', $pesan);
    }

    public function template()
    {
        return response(ImporSiswa::templateCsv(), 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template-impor-siswa.csv"',
        ]);
    }
}
