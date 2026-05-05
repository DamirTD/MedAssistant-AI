<?php

namespace App\Http\Requests\Api\Portal;

use Illuminate\Foundation\Http\FormRequest;

class PortalShowRequest extends FormRequest
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
