<?php

namespace App\Http\Requests\Admin;

use App\Models\Report;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminSearchReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'      => ['nullable', 'string', Rule::in([
                Report::STATUS_PENDING,
                Report::STATUS_RESOLVED,
                Report::STATUS_DISMISSED,
            ])],
            'target_type' => ['nullable', 'string', Rule::in([
                Report::TARGET_USER,
                Report::TARGET_POST,
            ])],
            'keyword'     => ['nullable', 'string', 'max:255'], // searches reporter name/email
            'per_page'    => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
