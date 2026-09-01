<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class ChatVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => 'required|string|min:2|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'الرسالة مطلوبة',
            'message.string'   => 'الرسالة يجب أن تكون نصاً',
            'message.min'      => 'الرسالة يجب ألا تقل عن حرفين',
            'message.max'      => 'الرسالة يجب ألا تتجاوز 500 حرف',
        ];
    }
}
