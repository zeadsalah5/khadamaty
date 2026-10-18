<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use Publishable;

    protected $fillable = [
        'slug', 'name_ar', 'excerpt', 'body',
        'symptoms', 'steps', 'includes', 'faqs', 'related_services',
        'price_note', 'duration_note', 'seo_title', 'seo_description',
        'is_published', 'published_at',
    ];

    protected $casts = [
        'is_published'     => 'boolean',
        'published_at'     => 'datetime',
        'symptoms'         => 'array',
        'steps'            => 'array',
        'includes'         => 'array',
        'faqs'             => 'array',
        'related_services' => 'array',
    ];

    /**
     * محتوى الخدمة كامل كفاية إنها تتنشر؟
     * لازم: مقدمة (body) + الخدمة بتشمل + الأعراض + الخطوات + أسئلة شائعة.
     */
    public function hasCompleteContent(): bool
    {
        return filled($this->body)
            && filled($this->includes)
            && filled($this->symptoms)
            && filled($this->steps)
            && filled($this->faqs);
    }
}
