<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\KelasRequest;
use App\Models\AuditLog;
use App\Models\Kelas;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'jurusan_id' => 'sometimes|integer',
            'tingkat_id' => 'sometimes|integer',
        ]);

        return Kelas::withJumlahSiswa()
            ->with(['jurusan:id,kode,nama', 'tingkat:id,angka,romawi'])
            ->when($request->jurusan_id, fn ($q, $id) => $q->where('jurusan_id', $id))
            ->when($request->tingkat_id, fn ($q, $id) => $q->where('tingkat_id', $id))
            ->orderBy('tingkat_id')->orderBy('jurusan_id')->orderBy('nomor')
            ->get();
    }

    public function store(KelasRequest $request)
    {
        $kelas = Kelas::simpan($request->validated());

        AuditLog::record($request->user(), 'create_kelas', 'kelas', $kelas->id, $request->ip());

        return response()->json($kelas->load(['jurusan:id,kode,nama', 'tingkat:id,angka,romawi']), 201);
    }

    public function update(KelasRequest $request, Kelas $kelas)
    {
        $data = $request->validated();

        if ($alasan = $kelas->alasanTidakBisaPindah((int) $data['jurusan_id'], (int) $data['tingkat_id'])) {
            return response()->json(['message' => $alasan], 422);
        }

        Kelas::simpan($data, $kelas);

        AuditLog::record($request->user(), 'update_kelas', 'kelas', $kelas->id, $request->ip());

        return response()->json($kelas->load(['jurusan:id,kode,nama', 'tingkat:id,angka,romawi']));
    }

    public function destroy(Kelas $kelas)
    {
        if ($alasan = $kelas->alasanTidakBisaDihapus()) {
            return response()->json(['message' => $alasan], 422);
        }

        $id = $kelas->id;
        $kelas->delete();

        AuditLog::record(request()->user(), 'delete_kelas', 'kelas', $id, request()->ip());

        return response()->json(['message' => 'Kelas berhasil dihapus.']);
    }
}
