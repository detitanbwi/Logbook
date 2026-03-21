<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class RevertLogbookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Alasan revert wajib diisi.',
            'reason.max' => 'Alasan revert maksimal 5000 karakter.',
        ];
    }
}
