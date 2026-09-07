<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('it_head');
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:150'],
            'email'         => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'      => ['required', 'confirmed', Password::min(8)],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'role'          => ['required', 'string', 'in:it_head,it_staff,requester'],
            'is_active'     => ['boolean'],
        ];
    }
}
