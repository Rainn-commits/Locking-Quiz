<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImporSiswaRequest;
use App\Models\AuditLog;
use App\Services\ImporSiswa;

class ImporSiswaController extends Controller
{
    public function store(ImporSiswaRequest $request, ImporSiswa $impor)
    {
        if (! $impor->jalankan($request->file('file')->getRealPath())) {
            return response()->json([
                'message' => 'Impor dibatalkan, tidak ada data yang disimpan. Perbaiki kesalahan berikut lalu unggah ulang.',
                'errors'  => $impor->errors,
            ], 422);
        }

        AuditLog::record($request->user(), 'import_siswa', 'siswa', null, $request->ip());

        return response()->json([
            'message'          => "{$impor->jumlahSiswa} siswa berhasil diimpor.",
            'jumlah_siswa'     => $impor->jumlahSiswa,
            'jumlah_kelas_baru' => $impor->jumlahKelasBaru,
        ], 201);
    }

    public function template()
    {
        return response(ImporSiswa::templateCsv(), 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template-impor-siswa.csv"',
        ]);
    }
}
