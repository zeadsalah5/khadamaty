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
