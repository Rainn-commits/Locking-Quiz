<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JurusanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'kode' => mb_strtoupper(trim((string) $this->kode)),
            'nama' => trim((string) $this->nama),
        ]);
    }

    public function rules(): array
    {
        $id = $this->route('jurusan')?->id;

        return [
            'kode' => ['required', 'string', 'max:10', 'alpha_dash', Rule::unique('jurusan', 'kode')->ignore($id)],
            'nama' => ['required', 'string', 'max:100', Rule::unique('jurusan', 'nama')->ignore($id)],
        ];
    }

    public function messages(): array
    {
        return [
            'kode.required'   => 'Kode jurusan wajib diisi.',
            'kode.max'        => 'Kode jurusan maksimal 10 karakter.',
            'kode.alpha_dash' => 'Kode jurusan hanya boleh huruf, angka, tanda - atau _.',
            'kode.unique'     => 'Kode jurusan sudah dipakai.',
            'nama.required'   => 'Nama jurusan wajib diisi.',
            'nama.max'        => 'Nama jurusan maksimal 100 karakter.',
            'nama.unique'     => 'Nama jurusan sudah ada.',
        ];
    }
}
