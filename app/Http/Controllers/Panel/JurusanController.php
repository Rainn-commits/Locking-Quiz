<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\JurusanRequest;
use App\Models\AuditLog;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JurusanController extends Controller
{
    public function index()
    {
        $jurusan = Jurusan::withJumlah()->orderBy('nama')->get();

        return view('admin.jurusan.index', compact('jurusan'));
    }

    public function create()
    {
        return view('admin.jurusan.form', ['jurusan' => new Jurusan()]);
    }

    public function store(JurusanRequest $request)
    {
        $jurusan = Jurusan::create($request->validated());

        AuditLog::record($request->user(), 'create_jurusan', 'jurusan', $jurusan->id, $request->ip());

        return redirect()->route('admin.jurusan.index')->with('success', "Jurusan {$jurusan->nama} berhasil ditambahkan.");
    }

    public function edit(Jurusan $jurusan)
    {
        return view('admin.jurusan.form', compact('jurusan'));
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

        return redirect()->route('admin.jurusan.index')->with('success', "Jurusan {$jurusan->nama} berhasil diperbarui.");
    }

    public function destroy(Request $request, Jurusan $jurusan)
    {
        if ($alasan = $jurusan->alasanTidakBisaDihapus()) {
            return back()->with('error', $alasan);
        }

        $id = $jurusan->id;
        $nama = $jurusan->nama;
        $jurusan->delete();

        AuditLog::record($request->user(), 'delete_jurusan', 'jurusan', $id, $request->ip());

        return redirect()->route('admin.jurusan.index')->with('success', "Jurusan {$nama} dihapus.");
    }
}
