<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVendorRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'display_name' => 'sometimes|string|max:100',
            'company_name' => 'nullable|string|max:100',
            'work_phone'   => 'nullable|string|max:20',
            'whatsapp'     => 'nullable|string|max:20',
            'bio'          => 'nullable|string|max:1000',
            'website'      => 'nullable|url',
            'logo'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'website.url'   => 'الرابط غير صحيح',
            'logo.max'      => 'اللوجو يجب أن يكون أقل من 2MB',
        ];
    }
}
