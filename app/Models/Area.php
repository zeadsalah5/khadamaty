<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use Publishable;

    protected $fillable = [
        'slug', 'name_ar', 'excerpt', 'intro',
        'common_problems', 'neighborhoods', 'top_services', 'eta_note', 'faqs',
        'delivers', 'delivery_fee', 'delivery_note',
        'seo_title', 'seo_description',
        'is_published', 'published_at',
    ];

    protected $casts = [
        'is_published'    => 'boolean',
        'published_at'    => 'datetime',
        'common_problems' => 'array',
        'neighborhoods'   => 'array',
        'top_services'    => 'array',
        'faqs'            => 'array',
        'delivers'        => 'boolean',
        'delivery_fee'    => 'decimal:2',
    ];

    /** المنطقة داخل نطاق التوصيل؟ (بيتحكم في إتاحتها في الـ checkout). */
    public function isDeliverable(): bool
    {
        return (bool) $this->delivers;
    }

    /**
     * محتوى المنطقة كامل كفاية إنها تتنشر؟
     * لازم: مقدمة (intro) + مشاكل شائعة + أحياء + خدمات مطلوبة + أسئلة شائعة.
     * (زمن الوصول eta_note معلومة تشغيلية محلية = اختياري/TODO، مش شرط للنشر.)
     */
    public function hasCompleteContent(): bool
    {
        return empty($this->missingContent());
    }

    /** الحقول الناقصة اللازمة للنشر (كـ labels عربية) — تظهر في الداشبورد. */
    public function missingContent(): array
    {
        $required = [
            'intro' => 'المقدمة',
            'common_problems' => 'المشاكل الشائعة',
            'neighborhoods' => 'الأحياء',
            'top_services' => 'الخدمات الأكثر طلباً',
            'faqs' => 'الأسئلة الشائعة',
        ];

        return array_values(array_filter(
            array_map(fn ($field, $label) => filled($this->{$field}) ? null : $label, array_keys($required), $required)
        ));
    }
}
