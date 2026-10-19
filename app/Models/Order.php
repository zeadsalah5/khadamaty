<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * طلب شراء (الدفع عند الاستلام) — المرحلة 6C.
 *
 * المبالغ كلها snapshot بتتحسب server-side وقت الإنشاء.
 * إدارة أثر المخزون (رجوع عند الإلغاء/المرتجع، وتعليم المستعمل كـ«مباع» عند
 * التوصيل) كلها متمركزة في changeStatus() عشان متتكررش من أكتر من مكان.
 */
class Order extends Model
{
    protected $fillable = [
        'order_number', 'status', 'customer_id',
        'customer_name', 'customer_phone', 'address', 'area_id', 'area_name', 'notes',
        'subtotal', 'delivery_fee', 'total',
        'payment_method', 'payment_status', 'payment_due_at', 'admin_notes', 'ip',
    ];

    protected $casts = [
        'subtotal'       => 'decimal:2',
        'delivery_fee'   => 'decimal:2',
        'total'          => 'decimal:2',
        'payment_due_at' => 'datetime',
    ];

    /** حالات الطلب (labels عربية). */
    public const STATUSES = [
        'new'              => 'جديد',
        'confirmed'        => 'مؤكّد',
        'out_for_delivery' => 'خرج للتوصيل',
        'delivered'        => 'تم التوصيل',
        'cancelled'        => 'ملغي',
        'returned'         => 'مرتجع',
    ];

    /** الحالات اللي بترجّع المخزون (الطلب مابيحجزش مخزون فيها). */
    public const STOCK_RETURNING = ['cancelled', 'returned'];

    /** حالات الدفع (labels عربية) — المرحلة 6E. */
    public const PAYMENT_STATUSES = [
        'pending'         => 'عند الاستلام',   // COD — بيتحصّل كاش عند التسليم
        'pending_payment' => 'بانتظار الدفع',  // أونلاين — مستنيين تأكيد البوابة
        'paid'            => 'مدفوع',
        'failed'          => 'فشل الدفع',
        'refunded'        => 'مُسترجَع',
    ];

    // ───────────────────────── العلاقات ─────────────────────────

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentAttempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class)->latest();
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /** الحساب المربوط (لو العميل كان مسجّل أو اتربط لاحقاً بالموبايل). */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    // ───────────────────────── مساعدات ─────────────────────────

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function paymentStatusLabel(): string
    {
        return self::PAYMENT_STATUSES[$this->payment_status] ?? $this->payment_status;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /** الطلب لسه مستنّي دفع أونلاين. */
    public function awaitingPayment(): bool
    {
        return $this->payment_status === 'pending_payment';
    }

    public function itemsCount(): int
    {
        return (int) ($this->relationLoaded('items')
            ? $this->items->sum('qty')
            : $this->items()->sum('qty'));
    }

    /**
     * تغيير حالة الطلب مع إدارة أثر المخزون داخل transaction:
     * - إلغاء/مرتجع => رجوع المخزون والمستعمل يرجع available.
     * - تم التوصيل  => المستعمل يتعلّم «مباع» (sold + sold_at).
     * - الرجوع من ملغي/مرتجع لحالة فعّالة => إعادة حجز المخزون.
     */
    public function changeStatus(string $new): void
    {
        $old = $this->status;

        if ($old === $new || ! array_key_exists($new, self::STATUSES)) {
            return;
        }

        DB::transaction(function () use ($old, $new) {
            $this->loadMissing('items.product');

            $oldReturns = in_array($old, self::STOCK_RETURNING, true);
            $newReturns = in_array($new, self::STOCK_RETURNING, true);

            if (! $oldReturns && $newReturns) {
                // رجوع المخزون (والمستعمل يرجع available).
                foreach ($this->items as $item) {
                    $item->product?->release($item->qty);
                }
            } elseif ($oldReturns && ! $newReturns) {
                // إعادة الحجز لو رجّعنا الطلب من ملغي/مرتجع لحالة فعّالة.
                foreach ($this->items as $item) {
                    $item->product?->reserve($item->qty);
                }
            }

            if ($new === 'delivered') {
                // المستعمل اتسلّم فعلاً => مباع.
                foreach ($this->items as $item) {
                    if ($item->condition === 'used') {
                        $item->product?->markSold();
                    }
                }
            } elseif ($old === 'delivered' && ! $newReturns) {
                // رجوع من «تم التوصيل» لحالة فعّالة => شيل علامة البيع (المخزون لسه محجوز).
                foreach ($this->items as $item) {
                    if ($item->condition === 'used') {
                        $item->product?->unmarkSold();
                    }
                }
            }

            $this->status = $new;
            $this->save();
        });
    }
}
