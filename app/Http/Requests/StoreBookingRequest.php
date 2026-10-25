<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
