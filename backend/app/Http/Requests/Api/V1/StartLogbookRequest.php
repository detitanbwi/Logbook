<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StartLogbookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal' => ['required', 'date'],
            'start_kerja' => ['required', 'date_format:H:i'],
            'end_kerja' => ['nullable', 'date_format:H:i'],
            'lokasi' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date' => 'Tanggal harus berupa tanggal yang valid.',
            'start_kerja.required' => 'Jam mulai wajib diisi.',
            'start_kerja.date_format' => 'Jam mulai harus berformat HH:MM.',
            'end_kerja.date_format' => 'Jam selesai harus berformat HH:MM.',
            'lokasi.required' => 'Lokasi wajib diisi.',
            'lokasi.max' => 'Lokasi maksimal 1000 karakter.',
        ];
    }
}
