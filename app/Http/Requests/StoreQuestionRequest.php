<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('quiz')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'options' => ['required', 'array', 'min:2'],
            'options.*.text' => ['required', 'string', 'max:255'],
            'options.*.is_correct' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'options.min' => 'Minimal dua pilihan jawaban.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $options = $this->input('options');

            if (! is_array($options)) {
                return;
            }

            $correct = collect($options)->filter(
                fn (mixed $option): bool => is_array($option)
                    && filter_var($option['is_correct'] ?? false, FILTER_VALIDATE_BOOLEAN),
            )->count();

            if ($correct !== 1) {
                $validator->errors()->add('options', 'Harus ada tepat satu jawaban benar.');
            }
        });
    }
}
