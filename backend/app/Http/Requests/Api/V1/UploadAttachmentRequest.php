<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UploadAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lampiran_file' => ['required', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,doc,docx'],
        ];
    }

    public function messages(): array
    {
        return [
            'lampiran_file.required' => 'File lampiran wajib diunggah.',
            'lampiran_file.file' => 'Lampiran harus berupa file.',
            'lampiran_file.max' => 'Ukuran file maksimal 5 MB.',
            'lampiran_file.mimes' => 'File harus berformat jpg, jpeg, png, pdf, doc, atau docx.',
        ];
    }
}
