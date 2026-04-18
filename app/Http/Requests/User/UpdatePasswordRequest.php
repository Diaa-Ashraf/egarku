<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password'      => 'required|string|min:1',
            'password'              => 'required|string|min:8',
            'password_confirmation' => 'required|string|same:password',
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required'      => 'كلمة المرور الحالية مطلوبة',
            'password.required'              => 'كلمة المرور الجديدة مطلوبة',
            'password.min'                   => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'password_confirmation.required' => 'تأكيد كلمة المرور مطلوب',
            'password_confirmation.same'     => 'كلمة المرور غير متطابقة',
        ];
    }
}
