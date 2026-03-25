<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['sometimes', 'required', 'string', 'max:255'],
            'npp' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('users', 'npp')->ignore($this->route('user'))],
            'role' => ['sometimes', 'required', Rule::in(['SuperAdmin', 'Admin', 'Staff', 'ADMIN', 'STAFF'])],
            'manager_id' => ['nullable', 'uuid', 'exists:users,id'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'foto' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'tempat_lahir' => ['sometimes', 'nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['sometimes', 'nullable', 'date'],
            'nik' => ['sometimes', 'nullable', 'string', 'max:32'],
            'npwp' => ['sometimes', 'nullable', 'string', 'max:32'],
            'alamat' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'status_kawin' => ['sometimes', 'nullable', 'string', 'max:32'],
            'riwayat_pendidikan' => ['sometimes', 'nullable', 'array'],
            'riwayat_karir' => ['sometimes', 'nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama wajib diisi.',
            'nama.max' => 'Nama maksimal 255 karakter.',
            'npp.required' => 'NPP wajib diisi.',
            'npp.unique' => 'NPP sudah digunakan.',
            'role.required' => 'Role wajib diisi.',
            'role.in' => 'Role tidak valid.',
            'manager_id.uuid' => 'ID manager harus berupa UUID.',
            'manager_id.exists' => 'Manager tidak ditemukan.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Email harus berformat valid.',
            'email.unique' => 'Email sudah digunakan.',
            'email.max' => 'Email maksimal 255 karakter.',
            'foto.image' => 'Foto harus berupa file gambar.',
            'foto.mimes' => 'Foto harus berformat jpg, jpeg, png, atau webp.',
            'foto.max' => 'Ukuran foto maksimal 5 MB.',
            'tempat_lahir.max' => 'Tempat lahir maksimal 255 karakter.',
            'tanggal_lahir.date' => 'Tanggal lahir harus berupa tanggal yang valid.',
            'nik.max' => 'NIK maksimal 32 karakter.',
            'npwp.max' => 'NPWP maksimal 32 karakter.',
            'alamat.max' => 'Alamat maksimal 2000 karakter.',
            'status_kawin.max' => 'Status kawin maksimal 32 karakter.',
            'riwayat_pendidikan.array' => 'Riwayat pendidikan harus berupa array.',
            'riwayat_karir.array' => 'Riwayat karir harus berupa array.',
        ];
    }
}
