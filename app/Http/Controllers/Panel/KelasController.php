<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\KelasRequest;
use App\Models\AuditLog;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\TingkatKelas;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $filter = [
            'jurusan_id' => $request->integer('jurusan_id') ?: null,
            'tingkat_id' => $request->integer('tingkat_id') ?: null,
        ];

        $kelas = Kelas::withJumlahSiswa()
            ->with(['jurusan:id,nama', 'tingkat:id,romawi'])
            ->when($filter['jurusan_id'], fn ($q, $id) => $q->where('jurusan_id', $id))
            ->when($filter['tingkat_id'], fn ($q, $id) => $q->where('tingkat_id', $id))
            ->orderBy('tingkat_id')->orderBy('jurusan_id')->orderBy('nomor')
            ->paginate(20)
            ->withQueryString();

        return view('admin.kelas.index', array_merge($this->pilihan(), compact('kelas', 'filter')));
    }

    public function create(Request $request)
    {
        $kelas = new Kelas([
            'jurusan_id' => $request->integer('jurusan_id') ?: null,
            'tingkat_id' => $request->integer('tingkat_id') ?: null,
        ]);

        return view('admin.kelas.form', array_merge($this->pilihan(), compact('kelas')));
    }

    public function store(KelasRequest $request)
    {
        $kelas = Kelas::simpan($request->validated());

        AuditLog::record($request->user(), 'create_kelas', 'kelas', $kelas->id, $request->ip());

        return redirect()->route('admin.kelas.index')->with('success', "Kelas {$kelas->nama} berhasil ditambahkan.");
    }

    public function edit(Kelas $kelas)
    {
        return view('admin.kelas.form', array_merge($this->pilihan(), [
            'kelas'       => $kelas,
            'jumlahSiswa' => $kelas->jumlahSiswa(),
        ]));
    }

    public function update(KelasRequest $request, Kelas $kelas)
    {
        $data = $request->validated();

        if ($alasan = $kelas->alasanTidakBisaPindah((int) $data['jurusan_id'], (int) $data['tingkat_id'])) {
            return back()->withInput()->with('error', $alasan);
        }

        Kelas::simpan($data, $kelas);

        AuditLog::record($request->user(), 'update_kelas', 'kelas', $kelas->id, $request->ip());

        return redirect()->route('admin.kelas.index')->with('success', "Kelas {$kelas->nama} berhasil diperbarui.");
    }

    public function destroy(Request $request, Kelas $kelas)
    {
        if ($alasan = $kelas->alasanTidakBisaDihapus()) {
            return back()->with('error', $alasan);
        }

        $id = $kelas->id;
        $nama = $kelas->nama;
        $kelas->delete();

        AuditLog::record($request->user(), 'delete_kelas', 'kelas', $id, $request->ip());

        return back()->with('success', "Kelas {$nama} dihapus.");
    }

    private function pilihan(): array
    {
        return [
            'daftarJurusan' => Jurusan::orderBy('nama')->get(['id', 'nama']),
            'daftarTingkat' => TingkatKelas::orderBy('angka')->get(['id', 'romawi']),
        ];
    }
}
