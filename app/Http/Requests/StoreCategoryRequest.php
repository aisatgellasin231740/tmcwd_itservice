<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('it_head');
    }

    public function rules(): array
    {
        $catId = $this->route('category')?->id;

        return [
            'name'      => [
                'required', 'string', 'max:100',
                Rule::unique('categories', 'name')->ignore($catId),
            ],
            'is_active' => ['boolean'],
        ];
    }
}
