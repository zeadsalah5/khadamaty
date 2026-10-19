<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    use Publishable;

    protected $fillable = [
        'parent_id', 'name_ar', 'slug', 'image', 'excerpt',
        'seo_title', 'seo_description', 'position', 'is_published', 'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'position'     => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position');
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(CategoryAttribute::class)->orderBy('position');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    /** اسم بمسار الأب (للعرض في الـ selects): "تكييفات › سبليت". */
    public function labelPath(): string
    {
        return $this->parent
            ? $this->parent->name_ar . ' › ' . $this->name_ar
            : $this->name_ar;
    }
}
