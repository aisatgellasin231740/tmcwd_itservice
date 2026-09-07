<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'      => ['sometimes', Rule::in(['open', 'in_progress', 'on_hold', 'resolved', 'closed'])],
            'assigned_to' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'priority_id' => ['sometimes', 'integer', 'exists:priorities,id'],
        ];
    }
}
