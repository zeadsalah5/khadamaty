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
