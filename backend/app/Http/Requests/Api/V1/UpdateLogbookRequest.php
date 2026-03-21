<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLogbookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal' => ['sometimes', 'date'],
            'start_kerja' => ['sometimes', 'date_format:H:i'],
            'end_kerja' => ['sometimes', 'nullable', 'date_format:H:i'],
            'lokasi' => ['sometimes', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.date' => 'Tanggal harus berupa tanggal yang valid.',
            'start_kerja.date_format' => 'Jam mulai harus berformat HH:MM.',
            'end_kerja.date_format' => 'Jam selesai harus berformat HH:MM.',
            'lokasi.max' => 'Lokasi maksimal 1000 karakter.',
        ];
    }
}
