<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

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

    /** تطبيع رقم موبايل: شيل أي مسافات/رموز غير أرقام (بيحافظ على + في الأول). */
    public static function normalizePhone(?string $phone): ?string
    {
        if (blank($phone)) {
            return null;
        }

        $phone = trim($phone);
        $plus = str_starts_with($phone, '+') ? '+' : '';
        $digits = preg_replace('/\D+/', '', $phone);

        return $digits === '' ? null : $plus . $digits;
    }

    /**
     * ربط الطلبات القديمة (كضيف) اللي موبايلها يطابق موبايل الحساب — مرة واحدة.
     * بتتنادى عند التسجيل والدخول. بترجّع عدد الطلبات اللي اتربطت.
     */
    public function linkGuestOrders(): int
    {
        if (blank($this->phone)) {
            return 0;
        }

        return DB::table('orders')
            ->whereNull('customer_id')
            ->where('customer_phone', $this->phone)
            ->update(['customer_id' => $this->id, 'updated_at' => now()]);
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
