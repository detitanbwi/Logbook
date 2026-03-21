<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreMasterKpiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'target_angka' => ['nullable', 'numeric', 'min:0'],
            'satuan' => ['nullable', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string'],
            'status_aktif' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama KPI wajib diisi.',
            'nama.max' => 'Nama KPI maksimal 255 karakter.',
            'target_angka.numeric' => 'Target angka harus berupa angka.',
            'target_angka.min' => 'Target angka tidak boleh kurang dari 0.',
            'satuan.max' => 'Satuan maksimal 100 karakter.',
            'status_aktif.boolean' => 'Status aktif harus berupa boolean.',
        ];
    }
}
