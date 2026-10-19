<?php

namespace App\Support;

/**
 * مساعد صغير لقراءة بيانات الشركة من config/site.php مع تمييز الـ placeholders.
 * الهدف: الـ schema ميطبعش قيم وهمية (تليفون/إيميل placeholder).
 */
class Site
{
    /** رقم الاتصال الدولي لو حقيقي، وإلا null. */
    public static function phoneTel(): ?string
    {
        $value = (string) site('phone_tel');

        return self::looksReal($value) ? $value : null;
    }

    /** الإيميل لو متحدد، وإلا null. */
    public static function email(): ?string
    {
        $value = trim((string) site('email'));

        return $value !== '' ? $value : null;
    }

    /**
     * رقم placeholder بيبقى فيه سلسلة أصفار طويلة (مثال: 01000000000).
     * بنعتبره "حقيقي" طالما مفيش 7 أصفار متتالية أو أكتر.
     */
    protected static function looksReal(string $value): bool
    {
        $digits = preg_replace('/\D/', '', $value);

        if ($digits === null || $digits === '') {
            return false;
        }

        return ! preg_match('/0{7,}/', $digits);
    }
}
