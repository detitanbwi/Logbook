<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'kpi_id' => ['required', 'exists:kpi_masters,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'User wajib dipilih.',
            'user_id.exists' => 'User tidak ditemukan.',
            'kpi_id.required' => 'KPI wajib dipilih.',
            'kpi_id.exists' => 'KPI tidak ditemukan.',
        ];
    }
}
