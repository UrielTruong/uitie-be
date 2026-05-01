<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminSearchUserRequest extends FormRequest
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
            'role'       => ['nullable', 'string', Rule::in([
                User::ROLE_STUDENT,
                User::ROLE_ADMIN,
                User::ROLE_SUPER_ADMIN,
            ])],
            'status'     => ['nullable', 'string', Rule::in([
                User::STATUS_ACTIVE,
                User::STATUS_INACTIVE,
                User::STATUS_LOCKED,
            ])],
            'per_page'   => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
