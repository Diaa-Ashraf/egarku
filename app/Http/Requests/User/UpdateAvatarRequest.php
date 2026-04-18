<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => 'الصورة مطلوبة',
            'avatar.image'    => 'الملف يجب أن يكون صورة',
            'avatar.max'      => 'الصورة يجب أن تكون أقل من 2MB',
        ];
    }

    protected function prepareForValidation()
    {
        // التأكد من استقبال الملف بشكل صحيح من multipart/form-data
        if (!$this->hasFile('avatar') && $this->file('avatar')) {
            $this->merge(['avatar' => $this->file('avatar')]);
        }
    }
}
