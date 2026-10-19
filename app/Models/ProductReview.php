<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * تقييم منتج (المرحلة 6D).
 *
 * - العميل يقدر يقيّم بس منتج اشتراه واتسلّم (طلب delivered) — order_item_id إثبات الشراء.
 * - pending لحد المراجعة؛ approved بس هو اللي بيظهر وبيدخل schema.
 * - مفيش تقييمات مفبركة في الـ seeder — الجدول بيبدأ فاضي.
 */
class ProductReview extends Model
{
    protected $fillable = [
        'product_id', 'customer_id', 'order_item_id',
        'rating', 'title', 'body', 'status',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    /** التقييمات المعتمدة بس (اللي تظهر للعامة وتدخل الـ schema). */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /** اسم صاحب التقييم زي ما بيظهر (بدون كشف بيانات زيادة). */
    public function authorName(): string
    {
        return $this->customer?->name ?: 'عميل';
    }

    /** شراء موثّق؟ (مربوط ببند طلب فعلي). */
    public function isVerified(): bool
    {
        return $this->order_item_id !== null;
    }
}
