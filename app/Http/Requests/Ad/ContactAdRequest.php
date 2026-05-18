<?php

namespace App\Http\Requests\Ad;

use Illuminate\Foundation\Http\FormRequest;

class ContactAdRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type'                 => 'required|in:whatsapp,phone,email,inquiry',
            'message'              => 'required_if:type,inquiry|nullable|string|max:500',
            'wants_whatsapp_reply' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'       => 'يجب تحديد نوع التواصل',
            'type.in'             => 'نوع التواصل غير صحيح',
            'message.required_if' => 'يجب كتابة تفاصيل الاستفسار',
            'message.max'         => 'الاستفسار يجب ألا يتجاوز 500 حرف',
        ];
    }
}
