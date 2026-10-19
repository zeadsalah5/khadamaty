<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Payments\GatewayResult;
use App\Payments\OnlineGateway;
use App\Payments\PaymentManager;
use App\Support\Store\OrderNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * استقبال إشعارات الدفع من البوابة (webhook) — المرحلة 6E.
 *
 * قواعد الأمان:
 * - الدفع مابيتأكّدش إلا من هنا بعد التحقق من التوقيع (HMAC). صفحة الرجوع مش دليل.
 * - idempotent: نفس المعاملة (gateway_txn_id) مابتتطبّقش مرتين — تحت قفل الصف.
 * - الكول باكس بتوقيع غلط بتتسجّل للتدقيق من غير أي تغيير في حالة الطلب.
 * - الراوت مستثنى من CSRF (bootstrap/app.php) لأنه server-to-server.
 */
class PaymentWebhookController extends Controller
{
    /** استقبال الإشعار من البوابة (POST). */
    public function webhook(Request $request, string $gateway): Response
    {
        $method = app(PaymentManager::class)->get($gateway);

        if (! $method instanceof OnlineGateway) {
            abort(404);
        }

        $valid = $method->verifyWebhook($request);
        $result = $method->parseWebhook($request);

        if (! $valid) {
            $this->recordRejected($gateway, $result, $request);
            Log::warning('Payment webhook rejected: bad signature', ['gateway' => $gateway]);

            return response('invalid signature', 403);
        }

        if (! $result) {
            return response('ignored', 200); // نوع حدث مش بنعمله حاجة
        }

        $this->apply($gateway, $result, $request->all());

        return response('ok', 200);
    }

    /**
     * صفحة الرجوع من البوابة — مجرد تحويل لصفحة التأكيد اللي بتقرا الحالة من
     * الداتابيز. بنتجاهل أي باراميترات من الـ URL (مش دليل دفع).
     */
    public function return(Request $request): RedirectResponse
    {
        return $request->session()->has('last_order_id')
            ? redirect()->route('order.confirmation')
            : redirect()->route('order.track');
    }

    /** تطبيق نتيجة الدفع مرة واحدة بس (idempotent) تحت قفل الصف. */
    protected function apply(string $gateway, GatewayResult $result, array $payload): void
    {
        DB::transaction(function () use ($gateway, $result, $payload) {
            $order = Order::where('order_number', $result->orderNumber)->lockForUpdate()->first();

            if (! $order) {
                Log::warning('Payment webhook: unknown order', ['order' => $result->orderNumber]);

                return;
            }

            // منع التكرار: نفس المعاملة اتعالجت قبل كده؟ نسيبها.
            $seen = PaymentAttempt::where('gateway', $gateway)
                ->where('gateway_txn_id', $result->transactionId)
                ->lockForUpdate()
                ->exists();

            if ($seen) {
                return;
            }

            $order->paymentAttempts()->create([
                'gateway'        => $gateway,
                'gateway_txn_id' => $result->transactionId,
                'status'         => $result->status,
                'amount'         => $result->amount,
                'hmac_valid'     => true,
                'payload'        => $payload,
            ]);

            if ($result->isPaid() && ! $order->isPaid()) {
                $order->payment_status = 'paid';
                $order->payment_due_at = null;
                if ($order->status === 'new') {
                    $order->status = 'confirmed'; // دفع مؤكّد => الطلب مؤكّد
                }
                $order->save();

                OrderNotifier::notify($order); // إشعار صاحب الموقع بعد تأكيد الدفع
            } elseif ($result->isRefunded() && $order->isPaid()) {
                $order->payment_status = 'refunded';
                $order->save();
            } elseif ($result->status === 'failed' && $order->awaitingPayment()) {
                // بنعلّمه failed بس مابنرجّعش المخزون دلوقتي — العميل يقدر يعيد المحاولة
                // لحد المهلة، وبعدها الأمر المجدول بيلغي ويرجّع المخزون.
                $order->payment_status = 'failed';
                $order->save();
            }
        });
    }

    /** تسجيل كول باك مرفوض (توقيع غلط) للتدقيق — من غير أي تغيير في الطلب. */
    protected function recordRejected(string $gateway, ?GatewayResult $result, Request $request): void
    {
        if (! $result) {
            return;
        }

        $order = Order::where('order_number', $result->orderNumber)->first();

        $order?->paymentAttempts()->create([
            'gateway'        => $gateway,
            'gateway_txn_id' => null, // مانعتمدش معرّف من حمولة مش موثوقة
            'status'         => 'rejected',
            'amount'         => $result->amount,
            'hmac_valid'     => false,
            'payload'        => $request->all(),
        ]);
    }
}
