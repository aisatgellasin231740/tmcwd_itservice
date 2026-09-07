<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body'        => ['required', 'string', 'min:5'],
            'is_internal' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'Please enter a message.',
            'body.min'      => 'Message must be at least 5 characters.',
        ];
    }
}
