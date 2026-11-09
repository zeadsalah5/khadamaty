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
 * المرحلة 6E — مهلة الدفع الأونلاين:
 * الطلب غير المدفوع بعد المهلة يتلغي ويرجع مخزونه؛ واللي لسه في المهلة يفضل.
 */
class ExpirePendingPaymentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Settings::flush();
        $this->seed();

        config([
            'services.paymob.secret_key'  => 'sk_test',
            'services.paymob.hmac_secret' => 'hmac_test',
        ]);
        $this->app->forgetInstance(PaymentManager::class);
    }

    private function onlineOrder(int $qty = 3): Order
    {
        $category = ProductCategory::where('slug', 'air-conditioners')->firstOrFail();

        $product = Product::create([
            'slug' => 'prod-' . uniqid(), 'name_ar' => 'منتج', 'category_id' => $category->id,
            'condition' => 'new', 'price' => 1000, 'stock_qty' => 10,
            'status' => 'published', 'is_published' => true, 'published_at' => now(),
        ]);

        $area = Area::create([
            'slug' => 'zone-' . uniqid(), 'name_ar' => 'منطقة', 'delivers' => true,
            'delivery_fee' => 0, 'is_published' => true, 'published_at' => now(),
        ]);

        session()->put(Cart::SESSION_KEY, [$product->id => $qty]);

        return OrderCreator::create(
            ['name' => 'سارة', 'phone' => '01112223334', 'address' => 'عنوان تفصيلي كافي', 'notes' => null],
            $area,
            app(PaymentManager::class)->get('paymob'),
        );
    }

    public function test_expired_unpaid_order_is_cancelled_and_stock_restored(): void
    {
        $order = $this->onlineOrder(qty: 3);
        $productId = $order->items->first()->product_id;

        $this->assertSame(7, (int) Product::find($productId)->stock_qty); // 10 - 3 محجوز

        // فات ميعاد الدفع.
        $order->update(['payment_due_at' => now()->subMinutes(5)]);

        $this->artisan('payments:expire-pending')->assertSuccessful();

        $order->refresh();
        $this->assertSame('cancelled', $order->status);
        $this->assertSame('failed', $order->payment_status);
        $this->assertSame(10, (int) Product::find($productId)->stock_qty); // رجع المخزون
    }

    public function test_order_within_timeout_is_not_cancelled(): void
    {
        $order = $this->onlineOrder(qty: 2);

        // لسه في المهلة (OrderCreator بيحط due_at = now + timeout).
        $this->artisan('payments:expire-pending')->assertSuccessful();

        $order->refresh();
        $this->assertSame('new', $order->status);
        $this->assertSame('pending_payment', $order->payment_status);
    }

    public function test_paid_order_is_never_expired(): void
    {
        $order = $this->onlineOrder(qty: 1);
        $order->update(['payment_status' => 'paid', 'payment_due_at' => now()->subHour()]);

        $this->artisan('payments:expire-pending')->assertSuccessful();

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertNotSame('cancelled', $order->status);
    }
}
