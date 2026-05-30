<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content'                  => ['nullable', 'string'],
            'attachments'              => ['nullable', 'array', 'max:10'],
            'attachments.*.file_url'   => ['required_with:attachments', 'string'],
            'attachments.*.file_type'  => ['required_with:attachments', 'string', 'in:Image,Video,Document'],
            'attachments.*.file_name'  => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $hasContent     = filled($this->input('content'));
            $hasAttachments = ! empty($this->input('attachments'));
            if (! $hasContent && ! $hasAttachments) {
                $v->errors()->add('content', 'A message must have content or at least one attachment.');
            }
        });
    }
}
