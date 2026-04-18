<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'        => 'sometimes|string|max:100',
            'email'       => 'nullable|email|unique:users,email,' . auth()->id(),
            'is_expat'    => 'boolean',
            'nationality' => 'nullable|string|max:3',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'الإيميل مسجل مسبقاً',
        ];
    }
}
