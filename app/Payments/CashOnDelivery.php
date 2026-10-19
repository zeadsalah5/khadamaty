<?php

namespace App\Payments;

use App\Models\Order;

/**
 * الدفع عند الاستلام (Cash on Delivery) — الطريقة الوحيدة في 6C.
 */
class CashOnDelivery implements PaymentMethod
{
    public const KEY = 'cod';

    public function key(): string
    {
        return self::KEY;
    }

    public function label(): string
    {
        return 'الدفع عند الاستلام';
    }

    public function description(): string
    {
        return 'بتدفع كاش للمندوب وقت استلام الطلب — من غير أي مقدم.';
    }

    public function isOnline(): bool
    {
        return false;
    }

    public function prepare(Order $order): string
    {
        // مفيش تحصيل مسبق — الفلوس بتتحصّل عند التسليم.
        return 'pending';
    }

    public function begin(Order $order): ?string
    {
        // طريقة أوفلاين — مفيش تحويل لبوابة.
        return null;
    }
}
