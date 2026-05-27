<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content'    => ['nullable', 'string'],
            'visibility' => ['nullable', 'string', 'in:Public,Private'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'attachments'              => ['nullable', 'array', 'max:10'],
            'attachments.*.file_url'   => ['required_with:attachments', 'string', 'url'],
            'attachments.*.file_type'  => ['required_with:attachments', 'string', 'in:Image,Video,Document'],
            'attachments.*.file_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
