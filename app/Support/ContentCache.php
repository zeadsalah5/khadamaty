<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * كاش المحتوى المشتق (استعلامات بتتكرر في كل صفحة) + مفتاح مركزي لمسحه.
 * بيتمسح تلقائياً عند أي تعديل من الداشبورد عبر الـ observers.
 */
class ContentCache
{
    /** مفاتيح الكاش اللي بتتمسح مع أي تغيير في المحتوى. */
    public const KEYS = [
        'home.published_service_slugs',
        'home.area_links',
        'footer.areas',
        'menu.header',
        'menu.footer',
        'catalog.packages',
        'catalog.faqs',
        'catalog.faqs_home',
        'footer.pages',
        'redirects.map',
        'shop.has_published',
    ];

    /** مسح كل كاش المحتوى + الإعدادات (التعديل يظهر فوراً). */
    public static function flush(): void
    {
        foreach (self::KEYS as $key) {
            Cache::forget($key);
        }

        Settings::flush();
    }

    /** اختصار remember forever بمفتاح معروف. */
    public static function remember(string $key, \Closure $callback): mixed
    {
        return Cache::rememberForever($key, $callback);
    }
}
