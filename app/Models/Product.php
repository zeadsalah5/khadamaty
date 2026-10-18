<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use Publishable;

    protected $fillable = [
        'slug', 'name_ar', 'excerpt', 'price', 'sku', 'is_published', 'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'price'        => 'decimal:2',
    ];

    /**
     * السعر يعتبر حقيقي بس لو موجود وأكبر من صفر — غير كده مبيطلعش offers في الـ schema.
     */
    public function hasRealPrice(): bool
    {
        return $this->price !== null && (float) $this->price > 0;
    }
}
