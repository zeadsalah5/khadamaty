<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Payments\PaymentManager;
use App\Support\Settings;
use App\Support\Store\Cart;
use App\Support\Store\OrderCreator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * فجوة: session gate على إعادة محاولة الدفع (retry) — الطلب اللي في الجلسة بس.
 */
class CheckoutRetryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Settings::flush();
        $this->seed();

        config([
            'services.paymob.secret_key'     => 'sk_test',
            'services.paymob.public_key'     => 'pk_test',
            'services.paymob.hmac_secret'    => 'hmac_test',
            'services.paymob.integration_id' => 12345,
        ]);
        $this->app->forgetInstance(PaymentManager::class);
    }

    private function onlineOrder(): Order
    {
        $category = ProductCategory::where('slug', 'air-conditioners')->firstOrFail();
        $product = Product::create([
            'slug' => 'prod-' . uniqid(), 'name_ar' => 'منتج', 'category_id' => $category->id,
            'condition' => 'new', 'price' => 1000, 'stock_qty' => 10,
            'status' => 'published', 'is_published' => true, 'published_at' => now(),
        ]);
        $area = Area::create([
            'slug' => 'zone-' . uniqid(), 'name_ar' => 'منطقة', 'delivers' => true,
            'delivery_fee' => 50, 'is_published' => true, 'published_at' => now(),
        ]);

        session()->put(Cart::SESSION_KEY, [$product->id => 1]);

        return OrderCreator::create(
            ['name' => 'أحمد', 'phone' => '01012345678', 'address' => 'عنوان كافي', 'notes' => null],
            $area,
            app(PaymentManager::class)->get('paymob'),
        );
    }

    public function test_retry_without_session_is_blocked(): void
    {
        Http::fake(); // لو اتنادت البوابة الاختبار يفشل — المفروض الجيت يمنع قبلها.

        $order = $this->onlineOrder();

        // من غير last_order_id في الجلسة => تحويل للتتبّع، من غير أي اتصال بالبوابة.
        $this->get(route('payment.retry', $order))
            ->assertRedirect(route('order.track'));

        Http::assertNothingSent();
    }

    public function test_retry_for_other_order_in_session_is_blocked(): void
    {
        Http::fake();

        $mine = $this->onlineOrder();
        $other = $this->onlineOrder();

        // الجلسة بتخص طلب تاني => مينفعش يعيد محاولة طلب غيره.
        $this->withSession(['last_order_id' => $mine->id])
            ->get(route('payment.retry', $other))
            ->assertRedirect(route('order.track'));

        Http::assertNothingSent();
    }

    public function test_retry_for_own_order_starts_payment(): void
    {
        Http::fake([
            '*/v1/intention/' => Http::response(['client_secret' => 'cs_test'], 200),
        ]);

        $order = $this->onlineOrder();

        $response = $this->withSession(['last_order_id' => $order->id])
            ->get(route('payment.retry', $order));

        $response->assertStatus(302);
        $this->assertStringContainsString('unifiedcheckout', (string) $response->headers->get('Location'));
    }
}
