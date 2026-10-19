<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use Publishable;

    protected $fillable = [
        'slug', 'name_ar', 'excerpt', 'description', 'specs',
        'brand_id', 'category_id', 'condition',
        'price', 'sale_price', 'stock_qty', 'status',
        'warranty_note', 'model_number', 'sku',
        'seo_title', 'seo_description',
        'is_published', 'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'price'        => 'decimal:2',
        'sale_price'   => 'decimal:2',
        'stock_qty'    => 'integer',
        'specs'        => 'array',
    ];

    // ───────────────────────── العلاقات ─────────────────────────

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function usedDetails(): HasOne
    {
        return $this->hasOne(ProductUsedDetail::class);
    }

    /** ماركات متوافقة (قطع الغيار). */
    public function compatibleBrands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'product_brand');
    }

    // ───────────────────────── مساعدات ─────────────────────────

    public function isUsed(): bool
    {
        return $this->condition === 'used';
    }

    /**
     * السعر يعتبر حقيقي بس لو موجود وأكبر من صفر — غير كده مبيطلعش offers في الـ schema.
     */
    public function hasRealPrice(): bool
    {
        return $this->price !== null && (float) $this->price > 0;
    }

    /** السعر المعروض: سعر العرض لو موجود، وإلا السعر الأساسي. */
    public function displayPrice(): ?string
    {
        if ($this->sale_price !== null && (float) $this->sale_price > 0) {
            return $this->sale_price;
        }

        return $this->hasRealPrice() ? $this->price : null;
    }

    public function imageUrl(): ?string
    {
        $image = $this->relationLoaded('primaryImage')
            ? $this->primaryImage
            : $this->primaryImage()->first() ?? $this->images()->first();

        return $image ? \Illuminate\Support\Facades\Storage::disk('products')->url($image->path) : null;
    }

    // ───────────────────────── قاعدة النشر ─────────────────────────

    /**
     * المنتج كامل كفاية إنه يتنشر؟
     * كل المنتجات: اسم + قسم + سعر > 0 + صورة واحدة على الأقل.
     * المستعمل زيادة: 3 صور على الأقل + تفاصيل مستعمل (درجة + قايمة فحص).
     */
    public function hasCompleteContent(): bool
    {
        return empty($this->missingContent());
    }

    /** الحقول الناقصة اللازمة للنشر (labels عربية) — تظهر في الداشبورد. */
    public function missingContent(): array
    {
        $missing = [];

        if (blank($this->name_ar)) {
            $missing[] = 'الاسم';
        }

        if (blank($this->category_id)) {
            $missing[] = 'القسم';
        }

        if (! $this->hasRealPrice()) {
            $missing[] = 'السعر';
        }

        $imagesCount = $this->relationLoaded('images')
            ? $this->images->count()
            : $this->images()->count();

        if ($imagesCount < 1) {
            $missing[] = 'صورة واحدة على الأقل';
        }

        if ($this->isUsed()) {
            if ($imagesCount < 3) {
                $missing[] = '٣ صور على الأقل (جهاز مستعمل)';
            }

            $used = $this->relationLoaded('usedDetails')
                ? $this->usedDetails
                : $this->usedDetails()->first();

            if (! $used || blank($used->condition_grade)) {
                $missing[] = 'درجة حالة الجهاز';
            }

            if (! $used || ! $used->hasCompleteInspection()) {
                $missing[] = 'قايمة الفحص';
            }
        }

        return $missing;
    }
}
