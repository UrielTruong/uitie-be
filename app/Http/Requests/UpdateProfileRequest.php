<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name'     => 'sometimes|required|string|max:255',
            'phone_number'  => 'nullable|string|max:20',
            'faculty'       => 'nullable|string|max:255',
            'class_name'    => 'nullable|string|max:255',
            'academic_year' => 'nullable|string|max:50',
        ];
    }
}
