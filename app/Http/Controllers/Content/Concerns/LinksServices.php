<?php

namespace App\Http\Controllers\Content\Concerns;

use App\Models\Service;
use Illuminate\Support\Collection;

/**
 * مشترك بين صفحات المناطق والماركات: تحويل slugs خدمات لموديلات منشورة
 * (مع الحفاظ على الترتيب) + خريطة أيقونة الخدمة للكروت.
 */
trait LinksServices
{
    /** يحوّل slugs خدمات لموديلات منشورة فقط، بنفس ترتيب ما اتكتبت. */
    protected function publishedServices(array $slugs): Collection
    {
        if (empty($slugs)) {
            return collect();
        }

        $found = Service::query()->published()->whereIn('slug', $slugs)->get()->keyBy('slug');

        return collect($slugs)->map(fn ($s) => $found->get($s))->filter()->values();
    }

    /** خريطة الأيقونات (slug خدمة => مفتاح أيقونة في service-icon). */
    protected function serviceIcons(): array
    {
        return [
            'ac-maintenance'         => 'maintenance',
            'ac-repair'              => 'repair',
            'ac-cleaning'            => 'cleaning',
            'ac-installation'        => 'install',
            'ac-gas-refill'          => 'freon',
            'ac-leak-repair'         => 'diagnose',
            'split-ac-maintenance'   => 'maintenance',
            'central-ac-maintenance' => 'contract',
            'ac-piping-installation' => 'pipes',
        ];
    }
}
