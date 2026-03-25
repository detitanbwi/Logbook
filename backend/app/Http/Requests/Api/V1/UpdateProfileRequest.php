<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'foto' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'alamat' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'tempat_lahir' => ['sometimes', 'nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['sometimes', 'nullable', 'date'],
            'nik' => ['sometimes', 'nullable', 'string', 'max:32'],
            'npwp' => ['sometimes', 'nullable', 'string', 'max:32'],
            'status_kawin' => ['sometimes', 'nullable', 'string', 'max:32'],
            'riwayat_pendidikan' => ['sometimes', 'nullable', 'array'],
            'riwayat_karir' => ['sometimes', 'nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'foto.image' => 'Foto harus berupa file gambar.',
            'foto.mimes' => 'Foto harus berformat jpg, jpeg, png, atau webp.',
            'foto.max' => 'Ukuran foto maksimal 5 MB.',
            'alamat.max' => 'Alamat maksimal 2000 karakter.',
            'tempat_lahir.max' => 'Tempat lahir maksimal 255 karakter.',
            'tanggal_lahir.date' => 'Tanggal lahir harus berupa tanggal yang valid.',
            'nik.max' => 'NIK maksimal 32 karakter.',
            'npwp.max' => 'NPWP maksimal 32 karakter.',
            'status_kawin.max' => 'Status kawin maksimal 32 karakter.',
            'riwayat_pendidikan.array' => 'Riwayat pendidikan harus berupa array.',
            'riwayat_karir.array' => 'Riwayat karir harus berupa array.',
        ];
    }
}
