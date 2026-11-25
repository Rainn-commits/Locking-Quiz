<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImporSiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:2048', 'mimes:csv,txt'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Pilih file CSV terlebih dahulu.',
            'file.max'      => 'Ukuran file maksimal 2 MB.',
            'file.mimes'    => 'File harus berformat CSV. Dari Excel, pilih Save As → CSV.',
        ];
    }
}
