<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ExportUserPdfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // middleware auth.role đã kiểm tra quyền
    }

    public function rules(): array
    {
        return [
            'keyword'    => ['nullable', 'string', 'max:100'],
            'mssv'       => ['nullable', 'string', 'max:20'],
            'class_name' => ['nullable', 'string', 'max:50'],
            'faculty'    => ['nullable', 'string', 'max:100'],
            'status'     => ['nullable', 'string', 'in:' . implode(',', [
                User::STATUS_ACTIVE,
                User::STATUS_INACTIVE,
                User::STATUS_LOCKED,
            ])],
            'role'       => ['nullable', 'string', 'in:' . implode(',', [
                User::ROLE_STUDENT,
                User::ROLE_ADMIN,
                User::ROLE_SUPER_ADMIN,
            ])],
        ];
    }
}
