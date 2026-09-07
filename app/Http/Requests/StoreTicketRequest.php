<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authenticated users; policy checked in controller
    }

    public function rules(): array
    {
        return [
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['required', 'string', 'min:10'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'category_id'   => ['required', 'integer', 'exists:categories,id'],
            'priority_id'   => ['required', 'integer', 'exists:priorities,id'],
            'attachments'   => ['nullable', 'array', 'max:5'],
            'attachments.*' => [
                'file',
                'max:10240', // 10 MB
                'mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt,zip',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'         => 'Please provide a title for your request.',
            'description.required'   => 'Please describe the issue in detail.',
            'description.min'        => 'Description must be at least 10 characters.',
            'department_id.required' => 'Please select your department.',
            'category_id.required'   => 'Please select a category.',
            'priority_id.required'   => 'Please select a priority level.',
            'attachments.*.max'      => 'Each attachment must not exceed 10 MB.',
            'attachments.*.mimes'    => 'Allowed file types: JPG, PNG, PDF, DOC, DOCX, XLS, XLSX, TXT, ZIP.',
        ];
    }
}
