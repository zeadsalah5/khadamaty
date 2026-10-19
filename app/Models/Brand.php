<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use Publishable;

    protected $fillable = [
        'slug', 'name_ar', 'excerpt', 'intro',
        'common_faults', 'maintenance_tips', 'device_types', 'suitable_services', 'faqs',
        'logo', 'seo_title', 'seo_description',
        'is_published', 'published_at',
    ];

    protected $casts = [
        'is_published'      => 'boolean',
        'published_at'      => 'datetime',
        'common_faults'     => 'array',
        'maintenance_tips'  => 'array',
        'device_types'      => 'array',
        'suitable_services' => 'array',
        'faqs'              => 'array',
    ];

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
