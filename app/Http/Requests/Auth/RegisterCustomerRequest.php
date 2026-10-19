<?php

namespace App\Http\Requests\Auth;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

/**
 * تسجيل حساب عميل (المرحلة 6D).
 * - الموبايل أو الإيميل — واحد منهم على الأقل إلزامي.
 * - الموبايل مصري (01 + 9 أرقام) ويتخزّن بعد تطبيع.
 */
class RegisterCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => Customer::normalizePhone($this->input('phone')),
            'email' => filled($this->input('email')) ? mb_strtolower(trim((string) $this->input('email'))) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'min:3', 'max:80'],
            'phone'    => ['nullable', 'required_without:email', 'regex:/^01[0-9]{9}$/', 'unique:customers,phone'],
            'email'    => ['nullable', 'required_without:phone', 'email', 'max:190', 'unique:customers,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            // فخ السبام.
            'website'  => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'          => 'من فضلك اكتب اسمك.',
            'name.min'               => 'الاسم قصير أوي.',
            'phone.required_without' => 'اكتب الموبايل أو الإيميل.',
            'phone.regex'            => 'رقم الموبايل مش مظبوط، اكتبه بصيغة 01xxxxxxxxx (11 رقم).',
            'phone.unique'           => 'الموبايل ده مسجّل قبل كده. تقدر تسجّل دخول.',
            'email.required_without' => 'اكتب الإيميل أو الموبايل.',
            'email.email'            => 'الإيميل مش مظبوط.',
            'email.unique'           => 'الإيميل ده مسجّل قبل كده. تقدر تسجّل دخول.',
            'password.required'      => 'من فضلك اكتب كلمة السر.',
            'password.min'           => 'كلمة السر لازم تكون 8 حروف على الأقل.',
            'password.confirmed'     => 'تأكيد كلمة السر مش مطابق.',
            'website.prohibited'     => 'حصل خطأ في الإرسال، حاول تاني.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'     => 'الاسم',
            'phone'    => 'رقم الموبايل',
            'email'    => 'الإيميل',
            'password' => 'كلمة السر',
        ];
    }
}
