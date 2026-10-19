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

    /** مسار صفحة القسم: /shop/{slug}. */
    public function path(): string
    {
        return '/shop/' . $this->slug;
    }

    /** معرّفات القسم نفسه + أقسامه الفرعية (عشان نجمع منتجات الشجرة كلها). */
    public function descendantIds(): array
    {
        $ids = [$this->id];

        $children = $this->relationLoaded('children') ? $this->children : $this->children()->get();
        foreach ($children as $child) {
            $ids = array_merge($ids, $child->descendantIds());
        }

        return array_values(array_unique($ids));
    }

    /** استعلام منتجات القسم (وأقسامه الفرعية) المنشورة. */
    public function publishedProductsQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Product::query()->published()->whereIn('category_id', $this->descendantIds());
    }
}
