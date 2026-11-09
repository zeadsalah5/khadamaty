<?php

namespace App\Models;

use App\Models\Concerns\HasReviewWorkflow;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    use HasReviewWorkflow, Publishable;

    /** منتجات المتجر التابعة للماركة (brand_id). */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    protected $fillable = [
        'slug', 'name_ar', 'excerpt', 'intro',
        'common_faults', 'maintenance_tips', 'device_types', 'suitable_services', 'faqs',
        'warranty_agent_note', 'error_codes',
        'logo', 'seo_title', 'seo_description',
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
        'maintenance_tips'  => 'array',
        'device_types'      => 'array',
        'suitable_services' => 'array',
        'faqs'              => 'array',
        'error_codes'       => 'array',
    ];

    /** الحقول اللي بتتفحص للـ TODO وتدخل في حساب التشابه. */
    public function reviewableTextFields(): array
    {
        return ['name_ar', 'excerpt', 'intro', 'common_faults', 'maintenance_tips',
            'device_types', 'faqs', 'warranty_agent_note', 'error_codes',
            'seo_title', 'seo_description'];
    }

    public function reviewTypeLabel(): string
    {
        return 'ماركة';
    }

    /**
     * محتوى الماركة كامل كفاية إنها تتنشر؟
     * لازم: نبذة (intro) + أعطال شائعة + نصايح صيانة + أنواع أجهزة + خدمات مناسبة + أسئلة شائعة.
     */
    public function hasCompleteContent(): bool
    {
        return empty($this->missingContent());
    }

    /** الحقول الناقصة اللازمة للنشر (كـ labels عربية) — تظهر في الداشبورد. */
    public function missingContent(): array
    {
        $required = [
            'intro' => 'النبذة',
            'common_faults' => 'الأعطال الشائعة',
            'maintenance_tips' => 'نصايح الصيانة',
            'device_types' => 'أنواع الأجهزة',
            'suitable_services' => 'الخدمات المناسبة',
            'faqs' => 'الأسئلة الشائعة',
        ];

        return array_values(array_filter(
            array_map(fn ($field, $label) => filled($this->{$field}) ? null : $label, array_keys($required), $required)
        ));
    }
}
