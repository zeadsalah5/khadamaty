<?php

namespace App\Support;

/**
 * تطبيع أرقام الموبايل المصرية في مصدر واحد (M6) — يُستخدم في:
 * إنشاء الطلب، وتتبّع الطلب، وتسجيل/دخول العميل. توحيد الصيغة بيضمن إن
 * نفس الرقم بصيغ مختلفة يطابق بعضه (الطلب vs التتبّع vs الحساب).
 */
class PhoneNumber
{
    /**
     * تطبيع رقم موبايل مصري لصيغة محلية موحّدة (01xxxxxxxxx):
     *  - بيشيل المسافات وأي رموز غير أرقام (بما فيها +).
     *  - بيحوّل كود الدولة (+20 / 20 + 1XXXXXXXXX) لصيغة محلية بصفر (01...).
     * بيرجّع null لو القيمة فاضية.
     */
    public static function normalize(?string $phone): ?string
    {
        if (blank($phone)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', trim($phone)) ?? '';

        if ($digits === '') {
            return null;
        }

        // كود مصر الدولي (20) قبل رقم موبايل محلي (1XXXXXXXXX) => نحوّله لصفر محلي.
        if (str_starts_with($digits, '20') && strlen($digits) >= 12) {
            $digits = '0' . substr($digits, 2);
        }

        return $digits;
    }
}
