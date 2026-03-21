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
            'lokasi_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lokasi_lng' => ['nullable', 'numeric', 'between:-180,180'],
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
            'lokasi_lat.numeric' => 'Latitude harus berupa angka.',
            'lokasi_lat.between' => 'Latitude harus antara -90 dan 90.',
            'lokasi_lng.numeric' => 'Longitude harus berupa angka.',
            'lokasi_lng.between' => 'Longitude harus antara -180 dan 180.',
        ];
    }
}
