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

    /** رقم الاتصال الظاهر لو حقيقي، وإلا null (fallback لرقم الاتصال الدولي). */
    public static function phoneDisplay(): ?string
    {
        $value = trim((string) site('phone'));

        if (self::looksReal($value)) {
            return $value;
        }

        return self::phoneTel();
    }

    /** فيه رقم تليفون حقيقي نعرض زرار اتصال؟ (فاضي/placeholder => لأ). */
    public static function hasPhone(): bool
    {
        return self::phoneTel() !== null || self::phoneDisplay() !== null;
    }

    /** رقم الواتساب (دولي بدون +) لو حقيقي، وإلا null. */
    public static function whatsappNumber(): ?string
    {
        $value = trim((string) site('whatsapp'));

        return self::looksReal($value) ? $value : null;
    }

    /** فيه رقم واتساب حقيقي نعرض زراره؟ */
    public static function hasWhatsapp(): bool
    {
        return self::whatsappNumber() !== null;
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
