<?php

namespace App\Payments;

use Illuminate\Http\Request;

/**
 * بوابة دفع أونلاين — المرحلة 6E.
 *
 * بتوسّع PaymentMethod بالحاجات الخاصة بالـ webhook: التحقق من التوقيع (HMAC)
 * وترجمة الحمولة لنتيجة موحّدة. كل التفاصيل الخاصة بالبوابة متمركزة في الـ
 * implementation (مثلاً PaymobGateway)، والـ webhook controller بيتعامل مع
 * الواجهة دي بس — فتغيير البوابة = كلاس واحد من غير لمس باقي الدورة.
 */
interface OnlineGateway extends PaymentMethod
{
    /**
     * التحقق من توقيع الـ webhook (HMAC) — صفحة الرجوع مش دليل دفع،
     * الدفع مابيتأكّدش إلا من هنا.
     */
    public function verifyWebhook(Request $request): bool;

    /**
     * ترجمة حمولة الـ webhook لنتيجة موحّدة، أو null لو مش معاملة نهتم بيها.
     * ملاحظة: بتتنادى بعد التحقق من التوقيع فقط.
     */
    public function parseWebhook(Request $request): ?GatewayResult;
}
