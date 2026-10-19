<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Payments\CashOnDelivery;
use App\Support\Settings;
use App\Support\Store\Cart;
use App\Support\Store\CartException;
use App\Support\Store\OrderCreator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * المرحلة 6C — دورة الشراء بالدفع عند الاستلام:
 * تسعير السلة، رسوم التوصيل، منع بيع آخر قطعة مرتين، وخصم/إرجاع المخزون.
 */
class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Settings::flush();
        $this->seed();
    }

    protected function category(): ProductCategory
    {
        return ProductCategory::where('slug', 'air-conditioners')->firstOrFail();
    }

    protected function area(array $attrs = []): Area
    {
        return Area::create(array_merge([
            'slug'         => 'test-zone',
            'name_ar'      => 'منطقة اختبار',
            'delivers'     => true,
            'delivery_fee' => 50,
            'is_published' => true,
            'published_at' => now(),
        ], $attrs));
    }

    protected function product(array $attrs = []): Product
    {
        return Product::create(array_merge([
            'slug'         => 'prod-' . uniqid(),
            'name_ar'      => 'منتج اختبار',
            'category_id'  => $this->category()->id,
            'condition'    => 'new',
            'price'        => 1000,
            'stock_qty'    => 10,
            'status'       => 'published',
            'is_published' => true,
            'published_at' => now(),
        ], $attrs));
    }

    protected function checkoutPayload(Area $area, array $overrides = []): array
    {
        return array_merge([
            'name'    => 'أحمد محمد',
            'phone'   => '01012345678',
            'address' => 'المعادي، شارع ٩، عمارة ٥، الدور ٣',
            'area'    => $area->id,
            'notes'   => '',
            'payment' => 'cod',
        ], $overrides);
    }

    // ───────────────────────── تسعير السلة (server-side) ─────────────────────────

    public function test_cart_subtotal_is_computed_from_db_price_times_qty(): void
    {
        $product = $this->product(['price' => 1500]);

        $this->post(route('cart.add'), ['product_id' => $product->id, 'qty' => 3])->assertRedirect();

        $this->get(route('checkout.show'))
            ->assertStatus(200)
            ->assertSee('4,500 ج.م', false); // 1500 × 3
    }

    public function test_cart_uses_sale_price_not_client_value(): void
    {
        $product = $this->product(['price' => 2000, 'sale_price' => 1200]);

        // حتى لو العميل بعت سعر/إجمالي، بيتجاهل — السعر من الداتابيز.
        $this->post(route('cart.add'), ['product_id' => $product->id, 'qty' => 2, 'price' => 1])->assertRedirect();

        $area = $this->area(['delivery_fee' => 0]);
        $this->post(route('checkout.store'), $this->checkoutPayload($area))->assertRedirect(route('order.confirmation'));

        $order = Order::latest('id')->first();
        $this->assertEquals(2400.0, (float) $order->subtotal); // 1200 × 2
    }

    public function test_used_product_quantity_is_capped_at_one(): void
    {
        $used = $this->product(['condition' => 'used', 'stock_qty' => 1, 'price' => 3000]);

        $this->post(route('cart.add'), ['product_id' => $used->id, 'qty' => 5])->assertRedirect();

        // الإجمالي = سعر قطعة واحدة بس.
        $this->get(route('checkout.show'))->assertStatus(200)->assertSee('3,000 ج.م', false);
    }

    // ───────────────────────── رسوم التوصيل ─────────────────────────

    public function test_delivery_fee_is_added_from_area(): void
    {
        $product = $this->product(['price' => 1000]);
        $area = $this->area(['delivery_fee' => 75]);

        $this->post(route('cart.add'), ['product_id' => $product->id, 'qty' => 1])->assertRedirect();
        $this->post(route('checkout.store'), $this->checkoutPayload($area))->assertRedirect(route('order.confirmation'));

        $order = Order::latest('id')->first();
        $this->assertEquals(1000.0, (float) $order->subtotal);
        $this->assertEquals(75.0, (float) $order->delivery_fee);
        $this->assertEquals(1075.0, (float) $order->total);
    }

    public function test_non_deliverable_area_is_rejected(): void
    {
        $product = $this->product();
        $outside = $this->area(['slug' => 'outside', 'name_ar' => 'منطقة بعيدة', 'delivers' => false]);

        $this->post(route('cart.add'), ['product_id' => $product->id, 'qty' => 1])->assertRedirect();

        $this->post(route('checkout.store'), $this->checkoutPayload($outside))
            ->assertSessionHasErrors('area');

        $this->assertSame(0, Order::count());
    }

    // ───────────────────────── منع بيع آخر قطعة مرتين ─────────────────────────

    public function test_last_piece_cannot_be_ordered_twice(): void
    {
        $product = $this->product(['stock_qty' => 1, 'condition' => 'used']);
        $area = $this->area();

        // الطلب الأول ينجح وينزّل المخزون لصفر.
        $this->post(route('cart.add'), ['product_id' => $product->id, 'qty' => 1])->assertRedirect();
        $this->post(route('checkout.store'), $this->checkoutPayload($area))->assertRedirect(route('order.confirmation'));

        $this->assertSame(1, Order::count());
        $this->assertSame(0, (int) $product->fresh()->stock_qty);

        // محاولة إضافته تاني (نفد) => مرفوض من السلة.
        $this->post(route('cart.add'), ['product_id' => $product->id, 'qty' => 1])
            ->assertSessionHas('cart_error');
    }

    public function test_order_creation_rechecks_stock_under_lock(): void
    {
        $product = $this->product(['stock_qty' => 1]);
        $area = $this->area();

        // السلة فيها قطعة، لكن المخزون نفد قبل التأكيد (محاكاة تزامن).
        session()->put(Cart::SESSION_KEY, [$product->id => 1]);
        $product->update(['stock_qty' => 0]);

        $this->expectException(CartException::class);

        OrderCreator::create([
            'name' => 'سارة', 'phone' => '01112223334', 'address' => 'عنوان تفصيلي', 'notes' => null,
        ], $area, new CashOnDelivery());
    }

    // ───────────────────────── خصم/إرجاع المخزون ─────────────────────────

    public function test_stock_is_decremented_on_order_and_restored_on_cancel(): void
    {
        $product = $this->product(['stock_qty' => 10, 'price' => 500]);
        $area = $this->area();

        $this->post(route('cart.add'), ['product_id' => $product->id, 'qty' => 3])->assertRedirect();
        $this->post(route('checkout.store'), $this->checkoutPayload($area))->assertRedirect(route('order.confirmation'));

        $this->assertSame(7, (int) $product->fresh()->stock_qty); // 10 - 3

        $order = Order::latest('id')->first();
        $order->changeStatus('cancelled');

        $this->assertSame(10, (int) $product->fresh()->stock_qty); // رجع
    }

    public function test_used_product_marked_sold_on_delivery_and_restored_on_return(): void
    {
        $used = $this->product(['condition' => 'used', 'stock_qty' => 1, 'price' => 4000]);
        $area = $this->area();

        $this->post(route('cart.add'), ['product_id' => $used->id, 'qty' => 1])->assertRedirect();
        $this->post(route('checkout.store'), $this->checkoutPayload($area))->assertRedirect(route('order.confirmation'));

        $order = Order::latest('id')->first();

        // تم التوصيل => المستعمل مباع.
        $order->changeStatus('delivered');
        $this->assertSame('sold', $used->fresh()->status);
        $this->assertNotNull($used->fresh()->sold_at);

        // مرتجع => يرجع available والمخزون يرجع.
        $order->changeStatus('returned');
        $fresh = $used->fresh();
        $this->assertSame('published', $fresh->status);
        $this->assertNull($fresh->sold_at);
        $this->assertSame(1, (int) $fresh->stock_qty);
    }

    // ───────────────────────── التتبّع ─────────────────────────

    public function test_tracking_requires_matching_number_and_phone(): void
    {
        $product = $this->product();
        $area = $this->area();

        $this->post(route('cart.add'), ['product_id' => $product->id, 'qty' => 1])->assertRedirect();
        $this->post(route('checkout.store'), $this->checkoutPayload($area))->assertRedirect(route('order.confirmation'));

        $order = Order::latest('id')->first();

        // رقم صح + موبايل غلط => مفيش كشف.
        $this->post(route('order.track.post'), ['order_number' => $order->order_number, 'phone' => '01099999999'])
            ->assertSessionHas('track_error');

        // رقم + موبايل صح => يظهر الطلب.
        $this->post(route('order.track.post'), ['order_number' => $order->order_number, 'phone' => '01012345678'])
            ->assertStatus(200)->assertSee($order->order_number);
    }

    public function test_cart_and_checkout_are_noindex(): void
    {
        $product = $this->product();
        $this->post(route('cart.add'), ['product_id' => $product->id, 'qty' => 1]);

        $this->get(route('cart.show'))->assertSee('name="robots" content="noindex,nofollow"', false);
        $this->get(route('checkout.show'))->assertSee('name="robots" content="noindex,nofollow"', false);
    }
}
