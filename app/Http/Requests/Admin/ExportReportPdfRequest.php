<?php

namespace App\Http\Requests\Admin;

use App\Models\Report;
use Illuminate\Foundation\Http\FormRequest;

class ExportReportPdfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // middleware auth.role đã kiểm tra quyền
    }

    public function rules(): array
    {
        return [
            'status'      => ['nullable', 'string', 'in:' . implode(',', [
                Report::STATUS_PENDING,
                Report::STATUS_RESOLVED,
                Report::STATUS_DISMISSED,
            ])],
            'target_type' => ['nullable', 'string', 'in:' . implode(',', [
                Report::TARGET_USER,
                Report::TARGET_POST,
            ])],
        ];
    }
}
