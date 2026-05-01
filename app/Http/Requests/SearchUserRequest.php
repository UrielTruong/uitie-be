<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword'    => ['nullable', 'string', 'max:255'],
            'mssv'       => ['nullable', 'string', 'max:20'],
            'class_name' => ['nullable', 'string', 'max:100'],
            'faculty'    => ['nullable', 'string', 'max:255'],
            'per_page'   => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
