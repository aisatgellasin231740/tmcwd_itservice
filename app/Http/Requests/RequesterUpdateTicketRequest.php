<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequesterUpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Fine-grained authorization is done in the controller via policy
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:10'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'Please provide a title for your request.',
            'description.required' => 'Please describe the issue in detail.',
            'description.min'      => 'Description must be at least 10 characters.',
            'category_id.required' => 'Please select a category.',
        ];
    }
}
