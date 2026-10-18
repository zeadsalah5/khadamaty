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
    ];

    /**
     * محتوى المنطقة كامل كفاية إنها تتنشر؟
     * لازم: مقدمة (intro) + مشاكل شائعة + أحياء + خدمات مطلوبة + أسئلة شائعة.
     * (زمن الوصول eta_note معلومة تشغيلية محلية = اختياري/TODO، مش شرط للنشر.)
     */
    public function hasCompleteContent(): bool
    {
        return filled($this->intro)
            && filled($this->common_problems)
            && filled($this->neighborhoods)
            && filled($this->top_services)
            && filled($this->faqs);
    }
}
