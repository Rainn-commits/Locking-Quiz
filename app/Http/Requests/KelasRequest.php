<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KelasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('kelas')?->id;

        return [
            'jurusan_id' => ['required', 'integer', 'exists:jurusan,id'],
            'tingkat_id' => ['required', 'integer', 'exists:tingkat_kelas,id'],
            'nomor'      => [
                'required', 'integer', 'between:1,99',
                Rule::unique('kelas', 'nomor')
                    ->where('jurusan_id', (int) $this->jurusan_id)
                    ->where('tingkat_id', (int) $this->tingkat_id)
                    ->ignore($id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'jurusan_id.required' => 'Jurusan wajib dipilih.',
            'jurusan_id.exists'   => 'Jurusan tidak ditemukan.',
            'tingkat_id.required' => 'Tingkat kelas wajib dipilih.',
            'tingkat_id.exists'   => 'Tingkat kelas tidak ditemukan.',
            'nomor.required'      => 'Nomor rombel wajib diisi.',
            'nomor.integer'       => 'Nomor rombel harus berupa angka.',
            'nomor.between'       => 'Nomor rombel harus antara 1 dan 99.',
            'nomor.unique'        => 'Kelas dengan jurusan, tingkat, dan nomor ini sudah ada.',
        ];
    }
}
