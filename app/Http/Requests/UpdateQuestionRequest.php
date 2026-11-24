<?php

namespace App\Http\Requests;

class UpdateQuestionRequest extends StoreQuestionRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'prompt' => ['sometimes', 'required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'options' => ['sometimes', 'array', 'min:2'],
            'options.*.text' => ['required_with:options', 'string', 'max:255'],
            'options.*.is_correct' => ['required_with:options', 'boolean'],
        ];
    }
}
