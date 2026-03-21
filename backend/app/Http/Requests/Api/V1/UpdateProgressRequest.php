<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'capaian_angka' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'capaian_angka.required' => 'Capaian angka wajib diisi.',
            'capaian_angka.numeric' => 'Capaian angka harus berupa angka.',
            'capaian_angka.min' => 'Capaian angka tidak boleh kurang dari 0.',
        ];
    }
}
