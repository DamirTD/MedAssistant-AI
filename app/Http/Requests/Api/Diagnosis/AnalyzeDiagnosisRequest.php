<?php

namespace App\Http\Requests\Api\Diagnosis;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AnalyzeDiagnosisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => ['nullable', 'string', 'max:4000'],
            'image' => ['nullable', 'image', 'max:5120'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'age' => ['nullable', 'integer', 'min:0', 'max:120'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $description = trim((string) $this->input('description', ''));
            $image = $this->file('image');

            if ($description === '' && ! $image) {
                $validator->errors()->add('description', 'Нужно добавить текст симптомов или изображение.');
            }
        });
    }
}
