<?php

namespace App\Http\Requests;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * التحقق من بيانات الـ checkout (الدفع عند الاستلام) — المرحلة 6C.
 * - موبايل مصري: 01 + 9 أرقام (11 رقم).
 * - المنطقة لازم تكون منشورة وداخل نطاق التوصيل (delivers=true).
 * - honeypot (website) لازم يفضل فاضي.
 */
class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => PhoneNumber::normalize((string) $this->input('phone')),
        ]);
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'min:3', 'max:80'],
            'phone'   => ['required', 'string', 'regex:/^01[0-9]{9}$/'],
            'address' => ['required', 'string', 'min:5', 'max:255'],
            'area'    => [
                'required',
                Rule::exists('areas', 'id')->where(fn ($q) => $q->where('delivers', true)->where('is_published', true)),
            ],
            'notes'   => ['nullable', 'string', 'max:1000'],
            'payment' => ['required', 'string', 'max:30'],
            // فخ السبام: لازم يفضل فاضي.
            'website' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'من فضلك اكتب اسمك.',
            'name.min'         => 'الاسم قصير أوي، اكتبه كامل.',
            'phone.required'   => 'من فضلك اكتب رقم الموبايل.',
            'phone.regex'      => 'رقم الموبايل مش مظبوط، اكتبه بصيغة 01xxxxxxxxx (11 رقم).',
            'address.required' => 'من فضلك اكتب العنوان بالتفصيل.',
            'address.min'      => 'العنوان قصير أوي، اكتبه بالتفصيل.',
            'area.required'    => 'من فضلك اختار المنطقة.',
            'area.exists'      => 'المنطقة دي غير متاحة للتوصيل حالياً، اختار منطقة تانية أو كلّمنا.',
            'payment.required' => 'من فضلك اختار طريقة الدفع.',
            'website.prohibited' => 'حصل خطأ في الإرسال، حاول تاني.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'    => 'الاسم',
            'phone'   => 'رقم الموبايل',
            'address' => 'العنوان',
            'area'    => 'المنطقة',
            'notes'   => 'الملاحظات',
            'payment' => 'طريقة الدفع',
        ];
    }
}
