<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'         => 'sometimes|string|max:100',
            'email'        => 'nullable|email|unique:users,email,' . auth()->id(),
            'phone'        => 'sometimes|string|max:20|unique:users,phone,' . auth()->id(),
            'city_id'      => 'nullable|exists:cities,id',
            'account_type'   => 'sometimes|string|in:individual,company',
            'marketplace_id' => 'required_if:account_type,company|exists:marketplaces,id',
            'is_expat'     => 'boolean',
            'nationality'  => 'nullable|string|max:3',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'الإيميل مسجل مسبقاً',
        ];
    }
}
