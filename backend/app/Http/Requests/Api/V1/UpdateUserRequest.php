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
        ];
    }
}
