<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * خاصية مشتركة لكل موديلات المحتوى:
 * - الـ route binding بيكون بالـ slug.
 * - أي عنصر is_published=false بيرجّع 404 (مبيتربطش في الراوت).
 * - scope published() للاستعلامات (sitemap/الفهارس).
 */
trait Publishable
{
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * يربط الراوت بالمنشور فقط؛ غير المنشور => ModelNotFoundException => 404.
     * استثناء: الأدمن/المحرّر (can_preview_drafts) بيشوف المسوّدات كمعاينة
     * (الكونترولر بيضيف شريط «مسودة» + X-Robots-Tag: noindex).
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $query = $this->where($field ?? $this->getRouteKeyName(), $value);

        if (! can_preview_drafts()) {
            $query->where('is_published', true);
        }

        return $query->firstOrFail();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
