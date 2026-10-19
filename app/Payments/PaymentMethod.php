<?php

namespace App\Payments;

use App\Models\Order;

/**
 * واجهة طريقة الدفع (abstraction) — المرحلة 6C.
 *
 * الهدف: نضيف الدفع الأونلاين في 6E كـ implementation جديد من غير ما نعيد
 * كتابة الـ checkout. الـ CheckoutController بيتعامل مع الواجهة دي بس.
 */
interface PaymentMethod
{
    /** مفتاح ثابت للطريقة (بيتخزّن في orders.payment_method). */
    public function key(): string;

    /** اسم الطريقة للعرض للعميل. */
    public function label(): string;

    /** جملة توضيحية قصيرة تظهر في صفحة الدفع. */
    public function description(): string;

    /** الطريقة أونلاين (تحويل لبوابة دفع) ولا لأ؟ */
    public function isOnline(): bool;

    /**
     * تحضير الطلب للدفع بعد إنشائه، وإرجاع قيمة payment_status الابتدائية.
     * للدفع عند الاستلام: 'pending' (بيتحصّل كاش عند التسليم).
     */
    public function prepare(Order $order): string;
}
