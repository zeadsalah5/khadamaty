<?php

namespace App\Payments;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * بوابة Paymob (دفع أونلاين) — المرحلة 6E.
 *
 * كل التفاصيل الخاصة بـ Paymob متمركزة هنا عشان لو اتغيّرت البوابة (Fawry/Kashier)
 * نكتب كلاس واحد بس من غير لمس الـ checkout ولا الـ webhook controller.
 *
 * مبادئ أمان ثابتة:
 * - مفيش تخزين لأي بيانات كروت إطلاقاً — التحويل لـ hosted checkout بتاع Paymob.
 * - المفاتيح من .env بإيد صاحب الموقع (config/services.php) — مش في الكود ولا الداشبورد.
 * - الدفع مابيتأكّدش إلا من webhook موقّع (HMAC-SHA512). صفحة الرجوع مش دليل.
 */
class PaymobGateway implements OnlineGateway
{
    public const KEY = 'paymob';

    public function key(): string
    {
        return self::KEY;
    }

    public function label(): string
    {
        return 'الدفع أونلاين (كارت / محفظة)';
    }

    public function description(): string
    {
        return 'بتدفع دلوقتي بأمان عبر بوابة Paymob (فيزا/ماستركارد/ميزة/محفظة). بتتحوّل لصفحة الدفع الآمنة.';
    }

    public function isOnline(): bool
    {
        return true;
    }

    public function prepare(Order $order): string
    {
        // لسه مستنيين تأكيد الدفع من الـ webhook الموقّع.
        return 'pending_payment';
    }

    /**
     * إنشاء Payment Intention عند Paymob وإرجاع رابط الـ Unified Checkout.
     * @throws PaymentException
     */
    public function begin(Order $order): ?string
    {
        $order->loadMissing('items');

        $items = $order->items->map(fn ($item) => [
            'name'        => mb_substr($item->name, 0, 50),
            'amount'      => (int) round((float) $item->unit_price * 100),
            'description' => $item->condition === 'used' ? 'مستعمل' : 'جديد',
            'quantity'    => (int) $item->qty,
        ])->values()->all();

        // رسوم التوصيل كبند مستقل عشان مجموع البنود = الإجمالي.
        if ((float) $order->delivery_fee > 0) {
            $items[] = [
                'name'        => 'رسوم التوصيل',
                'amount'      => (int) round((float) $order->delivery_fee * 100),
                'description' => $order->area_name,
                'quantity'    => 1,
            ];
        }

        [$firstName, $lastName] = $this->splitName($order->customer_name);

        $payload = [
            'amount'          => (int) round((float) $order->total * 100),
            'currency'        => 'EGP',
            'payment_methods' => [$this->integrationId()],
            'items'           => $items,
            'billing_data'    => [
                'first_name'   => $firstName,
                'last_name'    => $lastName,
                'phone_number' => $order->customer_phone,
                'email'        => 'na@khadamaty.local', // Paymob بيطلب إيميل؛ مفيش بريد مخزّن للعميل.
                'street'       => mb_substr($order->address, 0, 100),
                'city'         => $order->area_name,
                'country'      => 'EG',
            ],
            'special_reference' => $order->order_number, // بيرجع في الـ webhook كـ merchant_order_id
            'notification_url'  => route('payment.webhook', ['gateway' => self::KEY]),
            'redirection_url'   => route('payment.return'),
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $this->secretKey(),
            ])->acceptJson()->timeout(20)->post($this->baseUrl() . '/v1/intention/', $payload);
        } catch (\Throwable $e) {
            Log::error('Paymob intention request failed', ['order' => $order->order_number, 'error' => $e->getMessage()]);
            throw new PaymentException('تعذّر الاتصال ببوابة الدفع. حاول تاني بعد شوية.');
        }

        if (! $response->successful() || ! ($secret = $response->json('client_secret'))) {
            Log::error('Paymob intention rejected', ['order' => $order->order_number, 'status' => $response->status(), 'body' => $response->body()]);
            throw new PaymentException('تعذّر بدء الدفع عند البوابة. حاول تاني أو اختار الدفع عند الاستلام.');
        }

        return $this->baseUrl() . '/unifiedcheckout/?publicKey=' . urlencode($this->publicKey()) . '&clientSecret=' . urlencode($secret);
    }

    public function verifyWebhook(Request $request): bool
    {
        $received = (string) $request->query('hmac', '');
        $obj = (array) $request->input('obj', []);

        if ($received === '' || $obj === []) {
            return false;
        }

        $expected = static::computeHmac($obj, $this->hmacSecret());

        return hash_equals($expected, strtolower($received));
    }

    public function parseWebhook(Request $request): ?GatewayResult
    {
        if (strtoupper((string) $request->input('type')) !== 'TRANSACTION') {
            return null; // أنواع تانية (توكن/أوردر) — مش بنعملها حاجة.
        }

        $obj = (array) $request->input('obj', []);

        $orderNumber = (string) data_get($obj, 'order.merchant_order_id', '');
        $transactionId = (string) data_get($obj, 'id', '');

        if ($orderNumber === '' || $transactionId === '') {
            return null;
        }

        $success = filter_var(data_get($obj, 'success'), FILTER_VALIDATE_BOOLEAN);
        $errored = filter_var(data_get($obj, 'error_occured'), FILTER_VALIDATE_BOOLEAN);
        $refunded = filter_var(data_get($obj, 'is_refunded'), FILTER_VALIDATE_BOOLEAN);

        $status = match (true) {
            $refunded             => 'refunded',
            $success && ! $errored => 'paid',
            default               => 'failed',
        };

        return new GatewayResult(
            orderNumber: $orderNumber,
            transactionId: $transactionId,
            status: $status,
            // amount_cents بالقروش (قرش = 1/100 جنيه) => نحوّلها لجنيه للمطابقة بإجمالي الطلب.
            amount: ((float) data_get($obj, 'amount_cents', 0)) / 100,
            currency: ($cur = data_get($obj, 'currency')) !== null ? strtoupper((string) $cur) : null,
        );
    }

    /**
     * حساب HMAC-SHA512 حسب ترتيب الحقول المعتمد من Paymob لمعاملة.
     * public/static عشان الاختبارات تقدر تبني حمولة موقّعة صح.
     *
     * @param  array<string, mixed>  $obj  كائن obj من حمولة الـ webhook.
     */
    public static function computeHmac(array $obj, string $secret): string
    {
        // الترتيب المعتمد من Paymob — متغيّرش الترتيب ده.
        $keys = [
            'amount_cents', 'created_at', 'currency', 'error_occured', 'has_parent_transaction',
            'id', 'integration_id', 'is_3d_secure', 'is_auth', 'is_capture', 'is_refunded',
            'is_standalone_payment', 'is_voided', 'order.id', 'owner',
            'source_data.pan', 'source_data.sub_type', 'source_data.type', 'success',
        ];

        $concatenated = '';
        foreach ($keys as $key) {
            $value = data_get($obj, $key);

            // البوليان بيتسلسل كـ "true"/"false" زي ما Paymob بتبعته في الـ JSON.
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }

            $concatenated .= $value ?? '';
        }

        return hash_hmac('sha512', $concatenated, $secret);
    }

    // ───────────────────────── إعدادات (lazy من config/services) ─────────────────────────

    protected function secretKey(): string
    {
        return (string) config('services.paymob.secret_key');
    }

    protected function publicKey(): string
    {
        return (string) config('services.paymob.public_key');
    }

    protected function hmacSecret(): string
    {
        return (string) config('services.paymob.hmac_secret');
    }

    protected function integrationId(): int
    {
        return (int) config('services.paymob.integration_id');
    }

    protected function baseUrl(): string
    {
        return rtrim((string) config('services.paymob.base_url', 'https://accept.paymob.com'), '/');
    }

    /** @return array{0:string,1:string} */
    protected function splitName(string $full): array
    {
        $parts = preg_split('/\s+/', trim($full), 2) ?: [];

        return [$parts[0] ?? 'عميل', $parts[1] ?? '-'];
    }
}
