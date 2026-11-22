<?php

use App\Support\Settings;

if (! function_exists('site')) {
    /**
     * قراءة إعداد من لوحة التحكم (جدول settings) مع fallback تلقائي لـ config/site.php.
     * مثال: site('phone') أو site('announcement_enabled', false)
     */
    function site(string $key, mixed $default = null): mixed
    {
        return Settings::get($key, $default);
    }
}

if (! function_exists('public_note')) {
    /**
     * ملاحظة اختيارية (سعر/مدة/زمن وصول/توصيل) جاهزة للعرض على الزائر.
     * الحقول دي مسموح يفضل فيها «TODO» كتذكير لصاحب الموقع من غير ما يمنع النشر،
     * فلازم تتفلتر عند العرض: فاضية أو فيها TODO => null (القسم كله بيتخفى).
     */
    function public_note(?string $value): ?string
    {
        $value = trim((string) $value);

        return ($value === '' || stripos($value, 'TODO') !== false) ? null : $value;
    }
}

if (! function_exists('price_from_note')) {
    /**
     * أول سعر رقمي مكتوب في ملاحظة السعر (رقم متبوع بـ «ج.م» أو «جنيه») — للـ schema بس.
     * الملاحظة نص حر من الداشبورد؛ لو مفيهاش رقم واضح بترجع null (مفيش offers).
     * مبيألّفش سعر: الرقم لازم يكون ظاهر للزائر في نفس النص.
     */
    function price_from_note(?string $note): ?float
    {
        $note = public_note($note);

        if ($note === null) {
            return null;
        }

        // أرقام عربية/هندية => لاتينية
        $note = strtr($note, ['٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9']);

        if (! preg_match('/(\d[\d,]*(?:\.\d+)?)\s*(?:ج\.?\s?م|جنيه)/u', $note, $m)) {
            return null;
        }

        $price = (float) str_replace(',', '', $m[1]);

        return $price > 0 ? $price : null;
    }
}

if (! function_exists('can_preview_drafts')) {
    /**
     * هل المستخدم الحالي مسموح له يشوف المسوّدات (الصفحات غير المنشورة)؟
     * الأدمن والمحرّر فقط — عشان يراجعوا المحتوى قبل النشر. الزائر العادي => 404.
     */
    function can_preview_drafts(): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        return (method_exists($user, 'isAdmin') && $user->isAdmin())
            || (method_exists($user, 'isEditor') && $user->isEditor());
    }
}

if (! function_exists('call_tel')) {
    /** رقم الاتصال من الإعدادات (لزرار «اتصل الآن»)، أو null لو مش متسجّل. */
    function call_tel(): ?string
    {
        $tel = \App\Support\Site::phoneTel() ?? \App\Support\Site::phoneDisplay();

        return filled($tel) ? $tel : null;
    }
}

if (! function_exists('call_href')) {
    /** رابط زرار «اتصل الآن»؛ لو مفيش رقم بيرجع لرابط الحجز. */
    function call_href(string $fallback): string
    {
        return ($tel = call_tel()) ? 'tel:' . $tel : $fallback;
    }
}

if (! function_exists('call_label')) {
    function call_label(): string
    {
        return call_tel() ? 'اتصل الآن' : 'احجز فني دلوقتي';
    }
}
