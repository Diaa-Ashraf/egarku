<?php

namespace App\Http\Requests\Ad;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'         => 'sometimes|string|max:255',
            'description'   => 'sometimes|string|max:5000',
            'price'         => 'sometimes|numeric|min:0',
            'price_unit'    => 'nullable|in:daily,weekly,monthly,yearly',
            'area_id'       => 'sometimes|exists:areas,id',
            'is_for_expats' => 'boolean',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
            'address'       => 'nullable|string|max:255',
            'fields'        => 'nullable|array',
            'amenities'     => 'nullable|array',
        ];
    }
}
