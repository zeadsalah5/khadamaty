<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * صفحة (ماركة × خدمة) — المرحلة K1 (SEO عضوي).
 * محتوى فريد لكل تقاطع ماركة/خدمة، يتدار من داشبورد Filament.
 * - المسار: /brands/{brand-slug}/{service-slug} (بنعيد استخدام slugs الموجودة).
 * - النشر: is_published + اكتمال المحتوى (نفس منطق Service). الناقصة/المسوّدة =>
 *   404 (الكونترولر بيفلتر المنشور) + noindex + برّه السايت ماب.
 */
class BrandServicePage extends Model
{
    protected $fillable = [
        'brand_id', 'service_id',
        'h1', 'intro', 'common_faults', 'symptoms', 'steps',
        'price_note', 'duration_note', 'faqs',
        'seo_title', 'seo_description',
        'is_published', 'published_at',
    ];

    protected $casts = [
        'is_published'  => 'boolean',
        'published_at'  => 'datetime',
        'common_faults' => 'array',
        'symptoms'      => 'array',
        'steps'         => 'array',
        'faqs'          => 'array',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /** المسار الكانوني للصفحة (محتاج تحميل العلاقات brand/service). */
    public function path(): string
    {
        return '/brands/' . $this->brand->slug . '/' . $this->service->slug;
    }

    /**
     * المحتوى كامل كفاية للنشر؟ (نفس منطق الخدمات — السعر/المدة اختياريان TODO).
     * لازم: H1 + مقدمة + أعطال شائعة + أعراض + خطوات الشغل + أسئلة شائعة.
     */
    public function hasCompleteContent(): bool
    {
        return empty($this->missingContent());
    }

    /** الحقول الناقصة اللازمة للنشر (كـ labels عربية) — تظهر في الداشبورد. */
    public function missingContent(): array
    {
        $required = [
            'h1'            => 'العنوان (H1)',
            'intro'         => 'المقدمة',
            'common_faults' => 'الأعطال الشائعة',
            'symptoms'      => 'الأعراض',
            'steps'         => 'خطوات الشغل',
            'faqs'          => 'الأسئلة الشائعة',
        ];

        return array_values(array_filter(
            array_map(fn ($field, $label) => filled($this->{$field}) ? null : $label, array_keys($required), $required)
        ));
    }
}
