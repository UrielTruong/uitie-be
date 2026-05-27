<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PresignAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'files_meta'        => ['required', 'array', 'min:1', 'max:10'],
            'files_meta.*.name' => ['required', 'string'],
            'files_meta.*.mime' => ['required', 'string', 'in:image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/x-msvideo,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation'],
        ];
    }
}
