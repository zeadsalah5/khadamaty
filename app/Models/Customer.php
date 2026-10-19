<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * حساب عميل (المرحلة 6D) — منفصل تماماً عن User (الأدمن).
 *
 * الدخول/التسجيل اختياري بالموبايل أو الإيميل. الموبايل بيتخزّن بعد تطبيع
 * (شيل المسافات) عشان يطابق customer_phone في الطلبات القديمة.
 */
class Customer extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'phone', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // ───────────────────────── العلاقات ─────────────────────────

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function productReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    // ───────────────────────── مساعدات ─────────────────────────

    /** تطبيع رقم موبايل — مصدر موحّد (M6): App\Support\PhoneNumber::normalize. */
    public static function normalizePhone(?string $phone): ?string
    {
        return \App\Support\PhoneNumber::normalize($phone);
    }

    /**
     * بند طلب «متسلّم» يخص العميل للمنتج ده — إثبات الشراء للتقييم.
     * بيرجّع أحدث order_item في طلب حالته delivered، أو null.
     */
    public function deliveredOrderItemFor(int $productId): ?OrderItem
    {
        return OrderItem::query()
            ->where('product_id', $productId)
            ->whereHas('order', fn ($q) => $q->where('customer_id', $this->id)->where('status', 'delivered'))
            ->latest('id')
            ->first();
    }

    /** قيّم المنتج ده قبل كده؟ */
    public function hasReviewed(int $productId): bool
    {
        return $this->productReviews()->where('product_id', $productId)->exists();
    }
}
