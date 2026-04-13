<?php

namespace App\Http\Requests\Ad;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'marketplace_id' => 'required|exists:marketplaces,id',
            'category_id'    => 'required|exists:categories,id',
            'area_id'        => 'required|exists:areas,id',
            'title'          => 'required|string|max:255',
            'description'    => 'required|string|max:5000',
            'price'          => 'required|numeric|min:0',
            'price_unit'     => 'nullable|in:daily,weekly,monthly,yearly',
            'is_for_expats'  => 'boolean',
            'latitude'       => 'nullable|numeric|between:-90,90',
            'longitude'      => 'nullable|numeric|between:-180,180',
            'address'        => 'nullable|string|max:255',
            'fields'         => 'nullable|array',
            'fields.*'       => 'string|max:500',
            'amenities'      => 'nullable|array',
            'amenities.*'    => 'exists:amenities,id',
            'images'         => 'nullable|array|max:10',
            'images.*'       => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'marketplace_id.required' => 'يجب اختيار السوق',
            'category_id.required'    => 'يجب اختيار الكاتيجوري',
            'area_id.required'        => 'يجب اختيار المنطقة',
            'title.required'          => 'العنوان مطلوب',
            'description.required'    => 'الوصف مطلوب',
            'price.required'          => 'السعر مطلوب',
            'images.*.max'            => 'الصورة يجب أن تكون أقل من 5MB',
        ];
    }
}
