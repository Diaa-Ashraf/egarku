<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class ChatSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message'        => 'required|string|min:2|max:500',
            'marketplace_id' => 'nullable|integer|exists:marketplaces,id',
            'city_id'        => 'nullable|integer|exists:cities,id',
        ];
    }

    public function messages(): array
    {
        return [
            'message.required'       => 'الرسالة مطلوبة',
            'message.string'         => 'الرسالة يجب أن تكون نصاً',
            'message.min'            => 'الرسالة يجب ألا تقل عن حرفين',
            'message.max'            => 'الرسالة يجب ألا تتجاوز 500 حرف',
            'marketplace_id.integer' => 'معرف السوق غير صالح',
            'marketplace_id.exists'  => 'السوق المختار غير موجود',
            'city_id.integer'        => 'معرف المدينة غير صالح',
            'city_id.exists'         => 'المدينة المختارة غير موجودة',
        ];
    }
}
