<?php

namespace App\Support;

use App\Models\Faq;
use App\Models\MenuItem;
use App\Models\Package;
use Illuminate\Support\Facades\Cache;

/**
 * قارئ المحتوى الجدولي (باقات/أسئلة/قوائم) مع fallback لـ config/settings
 * وكاش بيتمسح تلقائياً عند أي تعديل (عبر الـ observers).
 * لو الجدول فاضي بيرجع للقيم الافتراضية — فالموقع مايتأثرش قبل الـ seeding.
 */
class Catalog
{
    /** الباقات المفعّلة بالترتيب (أو fallback من الإعدادات). */
    public static function packages(): array
    {
        return Cache::rememberForever('catalog.packages', function () {
            $rows = Package::query()->where('is_active', true)->orderBy('sort')->get();

            if ($rows->isEmpty()) {
                return (array) Settings::get('packages');
            }

            return $rows->map(fn (Package $p) => [
                'name'     => $p->name,
                'price'    => $p->price,
                'unit'     => $p->unit,
                'featured' => (bool) $p->featured,
                'features' => $p->features ?? [],
            ])->all();
        });
    }

    /** كل الأسئلة المفعّلة بالترتيب (أو fallback). */
    public static function faqs(): array
    {
        return Cache::rememberForever('catalog.faqs', function () {
            $rows = Faq::query()->where('is_active', true)->orderBy('sort')->get();

            if ($rows->isEmpty()) {
                return (array) Settings::get('faqs');
            }

            return $rows->map(fn (Faq $f) => ['q' => $f->question, 'a' => $f->answer])->all();
        });
    }

    /** أسئلة الرئيسية (المفعّلة + show_on_home) محدودة بعدد (أو fallback). */
    public static function homeFaqs(int $limit = 4): array
    {
        return Cache::rememberForever('catalog.faqs_home', function () use ($limit) {
            $rows = Faq::query()->where('is_active', true)->where('show_on_home', true)
                ->orderBy('sort')->limit($limit)->get();

            if ($rows->isEmpty()) {
                return array_slice((array) Settings::get('faqs'), 0, $limit);
            }

            return $rows->map(fn (Faq $f) => ['q' => $f->question, 'a' => $f->answer])->all();
        });
    }

    /**
     * عناصر قائمة بموقع معيّن (header|footer) مرتّبة.
     * بترجّع [] لو مفيش عناصر — والـ blade بيستخدم القايمة الافتراضية ساعتها.
     */
    public static function menu(string $location): array
    {
        return Cache::rememberForever("menu.$location", function () use ($location) {
            return MenuItem::query()->where('location', $location)->where('is_active', true)
                ->orderBy('sort')->get()
                ->map(fn (MenuItem $m) => ['label' => $m->label, 'url' => $m->url])
                ->all();
        });
    }
}
