<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'plan_id' => 'required|exists:plans,id',
            'method'  => 'required|in:paymob,fawry,vodafone_cash,instapay,cash',
        ];
    }

    public function messages(): array
    {
        return [
            'plan_id.required' => 'يجب اختيار الباقة',
            'plan_id.exists'   => 'الباقة غير موجودة',
            'method.required'  => 'يجب اختيار وسيلة الدفع',
            'method.in'        => 'وسيلة الدفع غير صحيحة',
        ];
    }
}
