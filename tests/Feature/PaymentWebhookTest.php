<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Payments\PaymentManager;
use App\Payments\PaymobGateway;
use App\Support\Settings;
use App\Support\Store\Cart;
use App\Support\Store\OrderCreator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * المرحلة 6E — webhook الدفع الأونلاين (Paymob):
 * التأكيد بس من كول باك موقّع (HMAC)، idempotent، ورفض التوقيع الغلط.
 */
class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const HMAC = 'test_hmac_secret_123';

    protected function setUp(): void
    {
        parent::setUp();
        Settings::flush();
        $this->seed();

        // تفعيل Paymob في الاختبار بمفاتيح وهمية + إعادة بناء سجلّ الدفع.
        config([
            'services.paymob.secret_key'     => 'sk_test',
            'services.paymob.public_key'     => 'pk_test',
            'services.paymob.hmac_secret'    => self::HMAC,
            'services.paymob.integration_id' => 12345,
        ]);
        $this->app->forgetInstance(PaymentManager::class);
    }

    /** إنشاء طلب أونلاين (pending_payment) بمخزون محجوز — من غير أي اتصال بالبوابة. */
    private function onlineOrder(int $qty = 2, float $price = 1000, float $fee = 50): Order
    {
        $category = ProductCategory::where('slug', 'air-conditioners')->firstOrFail();

        $product = Product::create([
            'slug' => 'prod-' . uniqid(), 'name_ar' => 'منتج', 'category_id' => $category->id,
            'condition' => 'new', 'price' => $price, 'stock_qty' => 10,
            'status' => 'published', 'is_published' => true, 'published_at' => now(),
        ]);

        $area = Area::create([
            'slug' => 'zone-' . uniqid(), 'name_ar' => 'منطقة', 'delivers' => true,
            'delivery_fee' => $fee, 'is_published' => true, 'published_at' => now(),
        ]);

        session()->put(Cart::SESSION_KEY, [$product->id => $qty]);

        return OrderCreator::create(
            ['name' => 'أحمد محمد', 'phone' => '01012345678', 'address' => 'عنوان تفصيلي كافي', 'notes' => null],
            $area,
            app(PaymentManager::class)->get('paymob'),
        );
    }

    /** بناء كائن معاملة Paymob بالحقول اللي بيتحسب عليها الـ HMAC. */
    private function txnObj(Order $order, array $overrides = []): array
    {
        return array_merge([
            'id'                     => 900001,
            'amount_cents'           => (int) round((float) $order->total * 100),
            'created_at'             => '2026-10-19T10:00:00',
            'currency'               => 'EGP',
            'error_occured'          => false,
            'has_parent_transaction' => false,
            'integration_id'         => 12345,
            'is_3d_secure'           => true,
            'is_auth'                => false,
            'is_capture'             => false,
            'is_refunded'            => false,
            'is_standalone_payment'  => true,
            'is_voided'              => false,
            'owner'                  => 7,
            'pending'                => false,
            'success'                => true,
            'order'                  => ['id' => 555, 'merchant_order_id' => $order->order_number],
            'source_data'            => ['pan' => '2345', 'sub_type' => 'MasterCard', 'type' => 'card'],
        ], $overrides);
    }

    private function sendWebhook(array $obj, ?string $hmac = null)
    {
        $hmac ??= PaymobGateway::computeHmac($obj, self::HMAC);

        return $this->postJson(
            route('payment.webhook', ['gateway' => 'paymob']) . '?hmac=' . $hmac,
            ['type' => 'TRANSACTION', 'obj' => $obj],
        );
    }

    // ───────────────────────── صح ─────────────────────────

    public function test_valid_webhook_marks_order_paid(): void
    {
        $order = $this->onlineOrder();
        $this->assertSame('pending_payment', $order->payment_status);

        $this->sendWebhook($this->txnObj($order))->assertOk();

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('confirmed', $order->status);
        $this->assertNull($order->payment_due_at);

        $this->assertSame(1, $order->paymentAttempts()->count());
        $attempt = $order->paymentAttempts()->first();
        $this->assertSame('paid', $attempt->status);
        $this->assertTrue($attempt->hmac_valid);
        $this->assertSame('900001', $attempt->gateway_txn_id);
    }

    // ───────────────────────── توقيع غلط ─────────────────────────

    public function test_invalid_hmac_is_rejected_and_order_unchanged(): void
    {
        $order = $this->onlineOrder();

        $this->sendWebhook($this->txnObj($order), hmac: 'deadbeef_wrong_signature')
            ->assertStatus(403);

        $order->refresh();
        $this->assertSame('pending_payment', $order->payment_status); // ما اتغيّرش
        $this->assertSame('new', $order->status);

        // اتسجّل كمرفوض للتدقيق من غير تطبيق.
        $rejected = $order->paymentAttempts()->first();
        $this->assertNotNull($rejected);
        $this->assertSame('rejected', $rejected->status);
        $this->assertFalse($rejected->hmac_valid);
    }

    // ───────────────────────── مكرر (idempotent) ─────────────────────────

    public function test_duplicate_webhook_is_idempotent(): void
    {
        $order = $this->onlineOrder();
        $obj = $this->txnObj($order);

        $this->sendWebhook($obj)->assertOk();
        $this->sendWebhook($obj)->assertOk(); // نفس المعاملة تاني

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        // محاولة واحدة بس اتسجّلت ونفس المعاملة ما اتطبّقتش مرتين.
        $this->assertSame(1, $order->paymentAttempts()->where('status', 'paid')->count());
    }

    // ───────────────────────── فشل ─────────────────────────

    public function test_failed_webhook_marks_failed_without_releasing_stock(): void
    {
        $order = $this->onlineOrder(qty: 2);
        $productId = $order->items->first()->product_id;
        $stockAfterOrder = (int) Product::find($productId)->stock_qty; // 10 - 2 = 8

        $obj = $this->txnObj($order, ['success' => false, 'error_occured' => true, 'id' => 900002]);
        $this->sendWebhook($obj)->assertOk();

        $order->refresh();
        $this->assertSame('failed', $order->payment_status);
        // المخزون لسه محجوز (العميل ممكن يعيد المحاولة لحد المهلة).
        $this->assertSame($stockAfterOrder, (int) Product::find($productId)->stock_qty);
    }

    // ───────────────────────── نوع مش مهم ─────────────────────────

    public function test_non_transaction_type_is_ignored(): void
    {
        $order = $this->onlineOrder();
        $obj = $this->txnObj($order);

        // توقيع صالح بس النوع مش TRANSACTION => يتجاهل (200) من غير أي تغيير.
        $this->postJson(
            route('payment.webhook', ['gateway' => 'paymob']) . '?hmac=' . PaymobGateway::computeHmac($obj, self::HMAC),
            ['type' => 'TOKEN', 'obj' => $obj],
        )->assertOk();

        $order->refresh();
        $this->assertSame('pending_payment', $order->payment_status);
        $this->assertSame(0, $order->paymentAttempts()->count());
    }
}
