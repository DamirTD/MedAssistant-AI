<?php

namespace App\Http\Requests\Api\DiagnosisHistory;

use Illuminate\Foundation\Http\FormRequest;

class DiagnosisHistoryShowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
