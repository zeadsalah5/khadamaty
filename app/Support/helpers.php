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
