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
        'price', 'sale_price', 'stock_qty', 'status', 'sold_at',
        'warranty_note', 'model_number', 'sku',
        'seo_title', 'seo_description',
        'is_published', 'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'sold_at'      => 'datetime',
        'price'        => 'decimal:2',
        'sale_price'   => 'decimal:2',
        'stock_qty'    => 'integer',
        'specs'        => 'array',
    ];

    /** عدد الأيام اللي بعدها المنتج المباع يبقى noindex ويخرج من السايت ماب. */
    public const SOLD_NOINDEX_DAYS = 60;

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

    /** كل روابط الصور المطلقة (للـ schema والمعرض). */
    public function imageUrls(): array
    {
        $disk = \Illuminate\Support\Facades\Storage::disk('products');
        $images = $this->relationLoaded('images') ? $this->images : $this->images()->get();

        return $images->map(fn ($img) => $disk->url($img->path))->values()->all();
    }

    /** في عرض؟ سعر عرض موجود وأقل من السعر الأساسي. */
    public function hasSale(): bool
    {
        return $this->sale_price !== null && (float) $this->sale_price > 0
            && $this->hasRealPrice() && (float) $this->sale_price < (float) $this->price;
    }

    /** نسبة الخصم (عدد صحيح مقرّب) أو null لو مفيش عرض. */
    public function discountPercent(): ?int
    {
        if (! $this->hasSale()) {
            return null;
        }

        $percent = (1 - ((float) $this->sale_price / (float) $this->price)) * 100;

        return (int) round($percent);
    }

    public function isSold(): bool
    {
        return $this->status === 'sold';
    }

    /** متوفر للبيع؟ مش مباع وفيه مخزون (أو جهاز مستعمل قطعة واحدة لسه مش مباعة). */
    public function isInStock(): bool
    {
        return ! $this->isSold() && $this->stock_qty > 0;
    }

    // ───────────────────────── المخزون (الطلبات — المرحلة 6C) ─────────────────────────

    /** حجز كمية من المخزون (خصم). بيتنادى جوّه transaction مع lock عند إنشاء الطلب. */
    public function reserve(int $qty): void
    {
        $this->decrement('stock_qty', min($qty, (int) $this->stock_qty));
    }

    /** إرجاع كمية للمخزون (إلغاء/مرتجع)؛ والمستعمل المباع يرجع available. */
    public function release(int $qty): void
    {
        $this->increment('stock_qty', $qty);
        $this->unmarkSold();
    }

    /** تعليم المنتج كـ«مباع» (المستعمل عند التوصيل). */
    public function markSold(): void
    {
        $this->forceFill(['status' => 'sold', 'sold_at' => now()])->save();
    }

    /** شيل علامة «مباع» ورجّع المنتج لحالته حسب النشر. */
    public function unmarkSold(): void
    {
        if ($this->status === 'sold') {
            $this->forceFill([
                'status'  => $this->is_published ? 'published' : 'draft',
                'sold_at' => null,
            ])->save();
        }
    }

    /** قيمة schema.org/itemCondition حسب حالة الجهاز. */
    public function schemaCondition(): string
    {
        return $this->isUsed()
            ? 'https://schema.org/UsedCondition'
            : 'https://schema.org/NewCondition';
    }

    /** قيمة schema.org availability حسب التوفر/البيع. */
    public function schemaAvailability(): string
    {
        if ($this->isSold()) {
            return 'https://schema.org/SoldOut';
        }

        return $this->isInStock()
            ? 'https://schema.org/InStock'
            : 'https://schema.org/OutOfStock';
    }

    /** المنتج المباع بقاله أكتر من ٦٠ يوم؟ (يبقى noindex ويخرج من السايت ماب). */
    public function isStaleSold(): bool
    {
        return $this->isSold()
            && $this->sold_at !== null
            && $this->sold_at->lt(now()->subDays(self::SOLD_NOINDEX_DAYS));
    }

    /** يتفهرس؟ المنشور بس، والمباع القديم (٦٠ يوم+) لأ. */
    public function isIndexable(): bool
    {
        return $this->is_published && ! $this->isStaleSold();
    }

    /** مسار الصفحة الكانوني: /shop/{category}/{product}. */
    public function path(): string
    {
        $category = $this->relationLoaded('category') ? $this->category : $this->category()->first();
        $categorySlug = $category?->slug ?? 'shop';

        return '/shop/' . $categorySlug . '/' . $this->slug;
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
