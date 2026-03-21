<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ReviewLogbookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', 'string', 'in:ACCEPTED,REJECTED'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'reviewer_comment' => ['required', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'decision.required' => 'Keputusan wajib diisi.',
            'decision.in' => 'Keputusan harus ACCEPTED atau REJECTED.',
            'rating.required' => 'Rating wajib diisi.',
            'rating.integer' => 'Rating harus berupa bilangan bulat.',
            'rating.min' => 'Rating minimal 1.',
            'rating.max' => 'Rating maksimal 5.',
            'reviewer_comment.required' => 'Komentar reviewer wajib diisi.',
            'reviewer_comment.max' => 'Komentar reviewer maksimal 5000 karakter.',
        ];
    }
}
