<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * بيرجّع للفورم نفسه (#booking) لو فيه خطأ — من غيره الصفحة بتفتح من فوق
     * ورسايل الخطأ بتبقى تحت خالص، فالعميل بيفتكر إن الطلب اتبعت وهو ماتبعتش.
     */
    protected function getRedirectUrl()
    {
        $back = strtok((string) url()->previous(), '#');

        return $back . '#booking';
    }

    /**
     * تنضيف رقم الموبايل قبل الفحص: أرقام عربية، مسافات/شرطات، و+20 أو 0020.
     * مثال: «+20 101 234 5678» أو «٠١٠١٢٣٤٥٦٧٨» => 01012345678.
     */
    protected function prepareForValidation(): void
    {
        $phone = (string) $this->input('phone', '');
        $phone = strtr($phone, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]);
        $phone = preg_replace('/[\s\-().]/u', '', $phone);
        $phone = preg_replace('/^(\+20|0020)/', '0', $phone);
        if (preg_match('/^1[0-9]{9}$/', $phone)) {
            $phone = '0' . $phone;
        }

        $this->merge(['phone' => $phone]);
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'min:3', 'max:80'],
            'phone'          => ['required', 'string', 'regex:/^0?1[0-9]{9,10}$/'],
            'area'           => ['required', 'string', 'max:80'],
            'service'        => ['required', 'string', 'max:80'],
            'appliance'      => ['nullable', 'string', 'max:80'],
            'devices_count'  => ['nullable', 'integer', 'min:1', 'max:50'],
            'preferred_time' => ['nullable', 'string', 'max:120'],
            'notes'          => ['nullable', 'string', 'max:1000'],
            // حقول مخفية: الماركة (لو الحجز من صفحة ماركة) + رابط الصفحة المصدر
            'brand'          => ['nullable', 'string', 'max:80'],
            'source_url'     => ['nullable', 'string', 'max:2048'],
            // فخ السبام: لازم يفضل فاضي
            'website'        => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'          => 'من فضلك اكتب اسمك.',
            'name.min'               => 'الاسم قصير أوي، اكتبه كامل.',
            'name.max'               => 'الاسم طويل أوي.',
            'phone.required'         => 'من فضلك اكتب رقم الموبايل.',
            'phone.regex'            => 'رقم الموبايل مش مظبوط، اكتبه بصيغة زي 01xxxxxxxxx.',
            'area.required'          => 'من فضلك اختار المنطقة.',
            'service.required'       => 'من فضلك اختار نوع الخدمة.',
            'appliance.max'          => 'نوع الجهاز طويل أوي.',
            'devices_count.integer'  => 'عدد الأجهزة لازم يكون رقم.',
            'devices_count.min'      => 'عدد الأجهزة لازم يكون جهاز واحد على الأقل.',
            'devices_count.max'      => 'عدد الأجهزة كبير أوي، تواصل معانا مباشرة.',
            'preferred_time.max'     => 'الوقت المناسب طويل أوي.',
            'notes.max'              => 'الملاحظات طويلة أوي.',
            'website.prohibited'     => 'حصل خطأ في الإرسال، حاول تاني.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'           => 'الاسم',
            'phone'          => 'رقم الموبايل',
            'area'           => 'المنطقة',
            'service'        => 'نوع الخدمة',
            'appliance'      => 'نوع الجهاز',
            'devices_count'  => 'عدد الأجهزة',
            'preferred_time' => 'الوقت المناسب',
            'notes'          => 'الملاحظات',
        ];
    }
}
