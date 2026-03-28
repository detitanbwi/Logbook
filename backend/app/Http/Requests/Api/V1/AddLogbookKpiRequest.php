<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class AddLogbookKpiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kpi_id' => ['required', 'exists:kpi_masters,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'kpi_id.required' => 'KPI wajib dipilih.',
            'kpi_id.exists' => 'KPI tidak ditemukan.',
        ];
    }
}
