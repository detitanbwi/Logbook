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
            'lokasi_lat' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'lokasi_lng' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.date' => 'Tanggal harus berupa tanggal yang valid.',
            'start_kerja.date_format' => 'Jam mulai harus berformat HH:MM.',
            'end_kerja.date_format' => 'Jam selesai harus berformat HH:MM.',
            'lokasi.max' => 'Lokasi maksimal 1000 karakter.',
            'lokasi_lat.numeric' => 'Latitude harus berupa angka.',
            'lokasi_lat.between' => 'Latitude harus antara -90 dan 90.',
            'lokasi_lng.numeric' => 'Longitude harus berupa angka.',
            'lokasi_lng.between' => 'Longitude harus antara -180 dan 180.',
        ];
    }
}
