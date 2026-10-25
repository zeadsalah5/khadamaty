<?php

namespace App\Models;

use App\Models\Concerns\HasReviewWorkflow;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasReviewWorkflow, Publishable;

    protected $fillable = [
        'slug', 'name_ar', 'icon', 'booking_service', 'excerpt', 'body',
        'symptoms', 'steps', 'includes', 'excludes', 'cost_factors', 'faqs', 'related_services',
        'price_note', 'duration_note', 'seo_title', 'seo_description',
        'is_published', 'published_at',
        'content_status', 'reviewed_by', 'reviewed_at',
        'similarity_ack_by', 'similarity_ack_at', 'similarity_pct',
    ];

    protected $casts = [
        'is_published'      => 'boolean',
        'published_at'      => 'datetime',
        'reviewed_at'       => 'datetime',
        'similarity_ack_at' => 'datetime',
        'symptoms'          => 'array',
        'steps'             => 'array',
        'includes'          => 'array',
        'excludes'          => 'array',
        'cost_factors'      => 'array',
        'faqs'              => 'array',
        'related_services'  => 'array',
    ];

    /**
     * الحقول اللي بتتفحص للـ TODO وتدخل في حساب التشابه.
     * ملاحظة: price_note/duration_note مستثنيين — حقول اختيارية «يملأها صاحب الموقع»
     * والـ TODO فيها تذكير مش مانع للنشر (زيّهم زي استثنائهم من hasCompleteContent).
     */
    public function reviewableTextFields(): array
    {
        return ['name_ar', 'excerpt', 'body', 'symptoms', 'steps', 'includes',
            'excludes', 'cost_factors', 'faqs', 'seo_title', 'seo_description'];
    }

    public function reviewTypeLabel(): string
    {
        return 'خدمة';
    }

    /**
     * محتوى الخدمة كامل كفاية إنها تتنشر؟
     * لازم: مقدمة (body) + الخدمة بتشمل + الأعراض + الخطوات + أسئلة شائعة.
     */
    public function hasCompleteContent(): bool
    {
        return empty($this->missingContent());
    }

    /** الحقول الناقصة اللازمة للنشر (كـ labels عربية) — تظهر في الداشبورد. */
    public function missingContent(): array
    {
        $required = [
            'body' => 'المقدمة',
            'includes' => 'الخدمة بتشمل',
            'symptoms' => 'الأعراض',
            'steps' => 'خطوات الشغل',
            'faqs' => 'الأسئلة الشائعة',
        ];

        return array_values(array_filter(
            array_map(fn ($field, $label) => filled($this->{$field}) ? null : $label, array_keys($required), $required)
        ));
    }
}
