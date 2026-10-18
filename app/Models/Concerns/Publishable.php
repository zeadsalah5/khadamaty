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
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)
            ->where('is_published', true)
            ->firstOrFail();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
