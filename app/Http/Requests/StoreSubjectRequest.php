<?php

namespace App\Http\Requests;

use App\Models\Subject;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-question') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $name = $this->input('name');

            if (! is_string($name) || $name === '') {
                return;
            }

            $query = Subject::query()->whereRaw('lower(name) = lower(?)', [$name]);
            $subject = $this->route('subject');

            if ($subject instanceof Subject) {
                $query->where('id', '!=', $subject->id);
            }

            if ($query->exists()) {
                $validator->errors()->add('name', 'Mata pelajaran sudah ada.');
            }
        });
    }
}
