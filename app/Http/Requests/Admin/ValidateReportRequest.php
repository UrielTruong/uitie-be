<?php

namespace App\Http\Requests\Admin;

use App\Models\Report;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValidateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in([
                Report::STATUS_RESOLVED,
                Report::STATUS_DISMISSED,
            ])],
        ];
    }
}
