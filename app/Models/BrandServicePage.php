<?php

namespace App\Models;

use App\Models\Concerns\HasReviewWorkflow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * صفحة (ماركة × خدمة) — المرحلة K1 (SEO عضوي).
 * محتوى فريد لكل تقاطع ماركة/خدمة, يتدار من داشبورد Filament.
 * - المسار: /brands/{brand-slug}/{service-slug} (بنعيد استخدام slugs الموجودة).
 * - النشر: is_published + اكتمال المحتوى + سير المراجعة (HasReviewWorkflow):
 *   جاهزة + متراجعة + صفر TODO. الناقصة/المسوّدة => 404 (الكونترولر بيفلتر
 *   المنشور، أو معاينة للأدمن) + noindex + برّه السايت ماب.
 */
class BrandServicePage extends Model
{
    use HasReviewWorkflow;

    protected $fillable = [
        'brand_id', 'service_id',
        'h1', 'intro', 'common_faults', 'symptoms', 'steps',
        'price_note', 'duration_note', 'faqs',
        'seo_title', 'seo_description',
        'is_published', 'published_at',
        'content_status', 'reviewed_by', 'reviewed_at',
        'similarity_ack_by', 'similarity_ack_at', 'similarity_pct',
    ];

    protected $casts = [
        'is_published'      => 'boolean',
        'published_at'      => 'datetime',
        'reviewed_at'       => 'datetime',
        'similarity_ack_at' => 'datetime',
        'common_faults'     => 'array',
        'symptoms'          => 'array',
        'steps'             => 'array',
        'faqs'              => 'array',
    ];

    /**
     * الحقول اللي بتتفحص للـ TODO وتدخل في حساب التشابه.
     * price_note/duration_note مستثنيين (اختياريين — يملأهم صاحب الموقع).
     */
    public function reviewableTextFields(): array
    {
        return ['h1', 'intro', 'common_faults', 'symptoms', 'steps', 'faqs',
            'seo_title', 'seo_description'];
    }

    public function reviewTypeLabel(): string
    {
        return 'صفحة (ماركة × خدمة)';
    }

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
