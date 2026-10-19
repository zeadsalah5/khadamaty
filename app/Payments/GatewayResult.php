<?php

namespace App\Payments;

/**
 * نتيجة موحّدة بيطلّعها الـ parser بتاع أي بوابة من حمولة الـ webhook — المرحلة 6E.
 *
 * الـ webhook controller بيشتغل على القيمة دي بس (gateway-agnostic):
 * لا يعرف تفاصيل Paymob/Fawry — كل بوابة بتترجم حمولتها لـ GatewayResult.
 */
class GatewayResult
{
    public function __construct(
        /** رقم الطلب عندنا (orders.order_number) زي ما رجع من البوابة. */
        public readonly string $orderNumber,
        /** معرّف المعاملة عند البوابة — مفتاح منع التكرار (idempotency). */
        public readonly string $transactionId,
        /** حالة الدفع الموحّدة: paid | failed | refunded. */
        public readonly string $status,
        /** المبلغ اللي اتعامل عليه (بالجنيه) — للتأكيد والمطابقة. */
        public readonly ?float $amount = null,
        /** عملة المعاملة زي ما رجعت من البوابة (مثلاً EGP) — للمطابقة. */
        public readonly ?string $currency = null,
    ) {
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }
}
