<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * قارئ/كاتب الإعدادات المفردة (المرحلة C).
 *
 * المبدأ: مصدر الحقيقة هو جدول settings، والقيمة الناقصة بترجع تلقائياً
 * لـ config/site.php (الموقع ميقعش أبداً حتى لو الجدول فاضي أو قبل الـ seeding).
 *
 * الاستخدام:
 *   Settings::get('phone')                 // قيمة الداتابيز أو config('site.phone')
 *   Settings::get('announcement.enabled', false)
 *   Settings::set('phone', '0100...', 'company')
 *
 * كل القيم متخزّنة JSON. بتتحمّل مرة واحدة لكل ريكوست وتتكاش forever،
 * والكاش بيتمسح تلقائياً عند أي حفظ (Settings::set أو SettingObserver).
 */
class Settings
{
    public const CACHE_KEY = 'app.settings';

    /** كاش داخل نفس الريكوست (عشان مانكلّمش الكاش ستور كل مرة). */
    protected static ?array $memo = null;

    /** كل الإعدادات كـ [key => value] (مفكوكة من JSON). */
    public static function all(): array
    {
        if (static::$memo !== null) {
            return static::$memo;
        }

        if (Cache::has(self::CACHE_KEY)) {
            return static::$memo = (array) Cache::get(self::CACHE_KEY, []);
        }

        // لو الجدول لسه مش موجود (قبل الـ migration أو وقت الاختبارات)،
        // بنرجّع [] من غير ما نكاشها، عشان القيم تيجي من config (fallback)
        // والصفحة ما تقعش — ولما الجدول يتعمل تتكاش عادي.
        try {
            $data = Setting::query()
                ->get(['key', 'value'])
                ->mapWithKeys(fn (Setting $s) => [$s->key => static::decode($s->value)])
                ->all();
        } catch (\Throwable $e) {
            return [];
        }

        Cache::forever(self::CACHE_KEY, $data);

        return static::$memo = $data;
    }

    /**
     * قيمة إعداد: من الداتابيز لو موجودة ومش null، وإلا $default،
     * وإلا القيمة الافتراضية من config('site.{key}').
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::all();

        if (array_key_exists($key, $all) && $all[$key] !== null) {
            return $all[$key];
        }

        if ($default !== null) {
            return $default;
        }

        return config('site.' . $key);
    }

    /** حفظ إعداد (JSON) ومسح الكاش. */
    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        Setting::query()->updateOrCreate(
            ['key' => $key],
            ['group' => $group, 'value' => static::encode($value)],
        );

        static::flush();
    }

    /** حفظ مجموعة مفاتيح دفعة واحدة (كلها في نفس الـ group) ومسح الكاش مرة واحدة. */
    public static function setMany(array $pairs, string $group = 'general'): void
    {
        foreach ($pairs as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['group' => $group, 'value' => static::encode($value)],
            );
        }

        static::flush();
    }

    /** مسح كاش الإعدادات (داخل الريكوست + الكاش ستور). */
    public static function flush(): void
    {
        static::$memo = null;
        Cache::forget(self::CACHE_KEY);
    }

    protected static function encode(mixed $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    protected static function decode(?string $value): mixed
    {
        if ($value === null) {
            return null;
        }

        return json_decode($value, true);
    }
}
