<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * محاولة/حدث دفع لطلب — المرحلة 6E.
 *
 * بتتسجّل من الـ webhook (مدفوع/فشل/مرتجع) ومن الاسترجاع اليدوي في الداشبورد،
 * وكمان الكول باكس المرفوضة بتوقيع غلط (hmac_valid=false) للتدقيق.
 */
class PaymentAttempt extends Model
{
    protected $fillable = [
        'order_id', 'gateway', 'gateway_txn_id', 'status', 'amount', 'hmac_valid', 'payload',
    ];

    protected $casts = [
        'amount'     => 'decimal:2',
        'hmac_valid' => 'boolean',
        'payload'    => 'array',
    ];

    public const STATUSES = [
        'paid'     => 'مدفوع',
        'failed'   => 'فشل',
        'refunded' => 'مرتجع',
        'rejected' => 'مرفوض (توقيع غلط)',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
