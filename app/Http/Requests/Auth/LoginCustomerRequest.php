<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * دخول عميل (المرحلة 6D) — بالموبايل أو الإيميل في حقل واحد «identifier».
 */
class LoginCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string', 'max:190'],
            'password'   => ['required', 'string'],
            'remember'   => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'identifier.required' => 'اكتب الموبايل أو الإيميل.',
            'password.required'   => 'اكتب كلمة السر.',
        ];
    }
}
