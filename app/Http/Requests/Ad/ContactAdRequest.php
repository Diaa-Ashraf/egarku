<?php

namespace App\Http\Requests\Ad;

use Illuminate\Foundation\Http\FormRequest;

class ContactAdRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type' => 'required|in:whatsapp,phone,email',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'يجب تحديد نوع التواصل',
            'type.in'       => 'نوع التواصل غير صحيح',
        ];
    }
}
