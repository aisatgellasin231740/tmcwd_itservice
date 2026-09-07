<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('it_head');
    }

    public function rules(): array
    {
        $deptId = $this->route('department')?->id;

        return [
            'name'      => [
                'required', 'string', 'max:100',
                Rule::unique('departments', 'name')->ignore($deptId),
            ],
            'is_active' => ['boolean'],
        ];
    }
}
