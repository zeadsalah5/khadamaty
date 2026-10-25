<?php

namespace App\Models;

use App\Models\Concerns\HasReviewWorkflow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * صفحة (منطقة × خدمة) — K2 المناطق (SEO عضوي).
 * بتستهدف «صيانة تكييف {الخدمة} في {المنطقة}» و«فني {الخدمة} {المنطقة}».
 * - المسار: /areas/{area-slug}/{service-slug} (بنعيد استخدام slugs الموجودة).
 * - النشر: سير المراجعة الموحّد (HasReviewWorkflow) — جاهزة + متراجعة + صفر TODO + مكتملة.
 *   الناقصة/المسوّدة => 404 للزائر (معاينة للأدمن) + noindex + برّه السايت ماب.
 */
class AreaServicePage extends Model
{
    use HasReviewWorkflow;

    protected $fillable = [
        'area_id', 'service_id',
        'h1', 'intro', 'steps', 'faqs',
        'price_note', 'duration_note',
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
        'steps'             => 'array',
        'faqs'              => 'array',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /** المسار الكانوني (محتاج تحميل العلاقات area/service). */
    public function path(): string
    {
        return '/areas/' . $this->area->slug . '/' . $this->service->slug;
    }

    /** الحقول اللي بتتفحص للـ TODO وتدخل في حساب التشابه (price/duration اختياريين). */
    public function reviewableTextFields(): array
    {
        return ['h1', 'intro', 'steps', 'faqs', 'seo_title', 'seo_description'];
    }

    public function reviewTypeLabel(): string
    {
        return 'صفحة (منطقة×خدمة)';
    }

    /**
     * المحتوى كامل كفاية للنشر؟ لازم: H1 + مقدمة + أسئلة شائعة.
     * (السعر/المدة اختياريان TODO.)
     */
    public function hasCompleteContent(): bool
    {
        return empty($this->missingContent());
    }

    /** الحقول الناقصة اللازمة للنشر (labels عربية) — تظهر في الداشبورد. */
    public function missingContent(): array
    {
        $required = [
            'h1'    => 'العنوان (H1)',
            'intro' => 'المقدمة',
            'faqs'  => 'الأسئلة الشائعة',
        ];

        return array_values(array_filter(
            array_map(fn ($field, $label) => filled($this->{$field}) ? null : $label, array_keys($required), $required)
        ));
    }
}
