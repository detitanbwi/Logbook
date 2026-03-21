<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'npp' => ['required', 'string', 'max:255', 'unique:users'],
            'role' => ['required', Rule::in(['SuperAdmin', 'Admin', 'Staff', 'ADMIN', 'STAFF'])],
            'password' => ['required', 'string', 'min:8'],
            'manager_id' => ['nullable', 'uuid', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama wajib diisi.',
            'nama.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'npp.required' => 'NPP wajib diisi.',
            'npp.unique' => 'NPP sudah digunakan.',
            'role.required' => 'Role wajib diisi.',
            'role.in' => 'Role tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'manager_id.uuid' => 'ID manager harus berupa UUID.',
            'manager_id.exists' => 'Manager tidak ditemukan.',
        ];
    }
}
