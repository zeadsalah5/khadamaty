<?php

namespace App\Payments;

use RuntimeException;

/**
 * خطأ أثناء بدء الدفع عند البوابة (تعذّر إنشاء جلسة الدفع) — المرحلة 6E.
 * بيتمسك في الـ checkout ويتحوّل لرسالة مفهومة للعميل مع إمكانية إعادة المحاولة.
 */
class PaymentException extends RuntimeException
{
}
