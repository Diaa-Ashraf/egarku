<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:100',
            'phone'          => 'required|string|max:20|unique:users,phone',
            'email'          => 'nullable|email|unique:users,email',
            'password'       => 'required|string|min:8|confirmed',
            'is_expat'       => 'boolean',
            'nationality'    => 'nullable|string|max:3',
            'is_vendor'      => 'boolean',
            'marketplace_id' => 'required_if:is_vendor,true|exists:marketplaces,id',
            'vendor_type'    => 'required_if:is_vendor,true|in:individual,company',
            'display_name'   => 'required_if:is_vendor,true|string|max:100',
            'whatsapp'       => 'nullable|string|max:20',
            'company_name'   => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'              => 'الاسم مطلوب',
            'phone.required'             => 'رقم الموبايل مطلوب',
            'phone.unique'               => 'رقم الموبايل مسجل مسبقاً',
            'email.unique'               => 'الإيميل مسجل مسبقاً',
            'password.required'          => 'كلمة المرور مطلوبة',
            'password.min'               => 'كلمة المرور 8 أحرف على الأقل',
            'password.confirmed'         => 'كلمة المرور غير متطابقة',
            'marketplace_id.required_if' => 'يجب اختيار السوق',
            'vendor_type.required_if'    => 'يجب اختيار نوع المعلن',
            'display_name.required_if'   => 'الاسم التجاري مطلوب',
        ];
    }
}
