<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content'     => ['nullable', 'string'],
            'visibility'  => ['nullable', 'string', 'in:Public,Private'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ];
    }
}
