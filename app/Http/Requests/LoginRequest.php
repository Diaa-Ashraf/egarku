<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'login'    => 'required_without_all:email,phone|string',
            'email'    => 'required_without_all:login,phone|email',
            'phone'    => 'required_without_all:login,email|string',
            'password' => 'required|string',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('email') && !$this->has('login')) {
            $this->merge(['login' => $this->email]);
        } elseif ($this->has('phone') && !$this->has('login')) {
            $this->merge(['login' => $this->phone]);
        }
    }

    public function messages(): array
    {
        return [
            'login.required_without_all' => 'رقم الموبايل أو الإيميل مطلوب',
            'email.required_without_all' => 'الإيميل مطلوب إذا لم يتوفر رقم الهاتف أو اسم المستخدم',
            'phone.required_without_all' => 'رقم الموبايل مطلوب إذا لم يتوفر الإيميل أو اسم المستخدم',
            'password.required'          => 'كلمة المرور مطلوبة',
        ];
    }
}
