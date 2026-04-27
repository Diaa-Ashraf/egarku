<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseServiceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $rules = [
            'service_type'     => 'required|in:feature_ad,feature_company,add_banner',
            'service_price_id' => 'required|exists:service_prices,id',
            'method'           => 'required|in:paymob,fawry,vodafone_cash,instapay,cash',
        ];

        // تمييز إعلان → لازم يبعت ad_id
        if ($this->service_type === 'feature_ad') {
            $rules['ad_id'] = 'required|exists:ads,id';
        }

        // إضافة بانر → لازم يرفع الصورة وبيانات البانر
        if ($this->service_type === 'add_banner') {
            $rules['image']          = 'required|image|max:2048';
            $rules['link']           = 'nullable|url';
            $rules['position']       = 'required|in:homepage_top,homepage_bottom,marketplace_top,marketplace_bottom';
            $rules['marketplace_id'] = 'nullable|exists:marketplaces,id';
            $rules['city_id']        = 'nullable|exists:cities,id';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'service_type.required'     => 'يجب تحديد نوع الخدمة',
            'service_type.in'           => 'نوع الخدمة غير صحيح',
            'service_price_id.required' => 'يجب اختيار خطة السعر',
            'service_price_id.exists'   => 'خطة السعر غير موجودة',
            'method.required'           => 'يجب اختيار وسيلة الدفع',
            'method.in'                 => 'وسيلة الدفع غير صحيحة',
            'ad_id.required'            => 'يجب تحديد الإعلان المراد تمييزه',
            'ad_id.exists'              => 'الإعلان غير موجود',
            'image.required'            => 'يجب رفع صورة البانر',
            'image.image'               => 'الملف يجب أن يكون صورة',
            'image.max'                 => 'حجم الصورة يجب ألا يتجاوز 2 ميغا',
            'position.required'         => 'يجب تحديد موضع البانر',
            'position.in'               => 'موضع البانر غير صحيح',
        ];
    }
}
