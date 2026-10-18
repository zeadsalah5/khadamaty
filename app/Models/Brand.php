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
        return filled($this->intro)
            && filled($this->common_faults)
            && filled($this->maintenance_tips)
            && filled($this->device_types)
            && filled($this->suitable_services)
            && filled($this->faqs);
    }
}
