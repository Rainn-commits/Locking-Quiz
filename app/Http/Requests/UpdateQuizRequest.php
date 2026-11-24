<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateQuizRequest extends StoreQuizRequest
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
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'subject' => ['sometimes', 'required', 'string', 'max:100'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'in:draft'],
            'start_at' => ['sometimes', 'nullable', 'date'],
            'end_at' => [
                'sometimes',
                'nullable',
                'date',
                Rule::when($this->filled('start_at'), ['after:start_at']),
            ],
            'duration_minutes' => ['sometimes', 'nullable', 'integer', 'min:1'],
        ];
    }
}
