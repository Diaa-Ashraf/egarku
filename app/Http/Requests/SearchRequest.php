<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q'    => 'required|string|min:2|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'q.required' => 'يرجى إدخال كلمة البحث',
            'q.string'   => 'كلمة البحث يجب أن تكون نصاً',
            'q.min'      => 'كلمة البحث يجب ألا تقل عن حرفين',
            'q.max'      => 'كلمة البحث يجب ألا تتجاوز 100 حرف',
            'page.integer' => 'رقم الصفحة يجب أن يكون رقماً صحيحاً',
            'page.min'     => 'رقم الصفحة يجب أن يكون 1 على الأقل',
        ];
    }
}
