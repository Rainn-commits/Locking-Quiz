<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\JurusanRequest;
use App\Models\AuditLog;
use App\Models\Jurusan;
use Illuminate\Support\Facades\DB;

class JurusanController extends Controller
{
    public function index()
    {
        return Jurusan::withJumlah()->orderBy('nama')->get();
    }

    public function store(JurusanRequest $request)
    {
        $jurusan = Jurusan::create($request->validated());

        AuditLog::record($request->user(), 'create_jurusan', 'jurusan', $jurusan->id, $request->ip());

        return response()->json($jurusan, 201);
    }

    public function update(JurusanRequest $request, Jurusan $jurusan)
    {
        DB::transaction(function () use ($request, $jurusan) {
            $jurusan->update($request->validated());

            if ($jurusan->wasChanged('nama')) {
                $jurusan->sinkronkanNamaKelas();
            }
        });

        AuditLog::record($request->user(), 'update_jurusan', 'jurusan', $jurusan->id, $request->ip());

        return response()->json($jurusan);
    }

    public function destroy(Jurusan $jurusan)
    {
        if ($alasan = $jurusan->alasanTidakBisaDihapus()) {
            return response()->json(['message' => $alasan], 422);
        }

        $id = $jurusan->id;
        $jurusan->delete();

        AuditLog::record(request()->user(), 'delete_jurusan', 'jurusan', $id, request()->ip());

        return response()->json(['message' => 'Jurusan berhasil dihapus.']);
    }
}
