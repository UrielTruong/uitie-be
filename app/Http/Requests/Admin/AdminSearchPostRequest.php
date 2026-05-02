<?php

namespace App\Http\Requests\Admin;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminSearchPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword'     => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'status'      => ['nullable', 'string', Rule::in([
                Post::STATUS_PENDING,
                Post::STATUS_ACCEPTED,
                Post::STATUS_REJECTED,
            ])],
            'visibility'  => ['nullable', 'string', Rule::in([
                Post::VISIBILITY_PUBLIC,
                Post::VISIBILITY_PRIVATE,
            ])],
            'user_id'     => ['nullable', 'integer', 'exists:users,id'],
            'from_date'   => ['nullable', 'date', 'date_format:Y-m-d'],
            'to_date'     => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            'per_page'    => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
