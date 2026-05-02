<?php

namespace App\Http\Requests\Admin;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;

class ExportPostPdfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // middleware auth.role đã kiểm tra quyền
    }

    public function rules(): array
    {
        return [
            'keyword'     => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'status'      => ['nullable', 'string', 'in:' . implode(',', [
                Post::STATUS_PENDING,
                Post::STATUS_ACCEPTED,
                Post::STATUS_REJECTED,
            ])],
            'visibility'  => ['nullable', 'string', 'in:' . implode(',', [
                Post::VISIBILITY_PUBLIC,
                Post::VISIBILITY_PRIVATE,
            ])],
        ];
    }
}
