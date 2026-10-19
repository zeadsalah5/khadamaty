<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductReview;
use App\Support\Settings;
use App\Support\Store\Compare;
use App\Support\Store\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * المرحلة 6D — حسابات العملاء، المفضلة، المقارنة، تقييمات المنتجات.
 */
class AccountTest extends TestCase
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

    protected function customer(array $attrs = []): Customer
    {
        return Customer::create(array_merge([
            'name'     => 'سيد صلاح',
            'phone'    => '01012345678',
            'password' => 'password123',
        ], $attrs));
    }

    /** طلب متسلّم لعميل على منتج (لإثبات الشراء للتقييم). */
    protected function deliveredOrder(Customer $customer, Product $product): Order
    {
        $order = Order::create([
            'order_number'  => 'KH-TEST-' . uniqid(),
            'status'        => 'delivered',
            'customer_id'   => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'address'       => 'عنوان',
            'area_name'     => 'منطقة',
            'subtotal'      => 1000,
            'delivery_fee'  => 0,
            'total'         => 1000,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'name'       => $product->name_ar,
            'condition'  => $product->condition,
            'unit_price' => 1000,
            'qty'        => 1,
            'line_total' => 1000,
        ]);

        return $order;
    }

    // ───────────────────────── التسجيل والدخول ─────────────────────────

    public function test_customer_can_register_and_is_logged_in(): void
    {
        $this->post(route('register.post'), [
            'name'                  => 'عميل جديد',
            'phone'                 => '01099998888',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('account.orders'));

        $this->assertAuthenticatedAs(Customer::first(), 'customer');
    }

    public function test_registration_requires_phone_or_email(): void
    {
        $this->from(route('register'))->post(route('register.post'), [
            'name'                  => 'بدون وسيلة تواصل',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('phone');
    }

    public function test_customer_can_login_with_phone_and_email(): void
    {
        $this->customer(['phone' => '01011112222', 'email' => 'a@test.com']);

        $this->post(route('login.post'), ['identifier' => '0101 111 2222', 'password' => 'password123'])
            ->assertRedirect(route('account.orders'));
        $this->assertAuthenticated('customer');

        auth('customer')->logout();

        $this->post(route('login.post'), ['identifier' => 'a@test.com', 'password' => 'password123'])
            ->assertRedirect(route('account.orders'));
        $this->assertAuthenticated('customer');
    }

    public function test_guest_orders_are_linked_on_login_by_phone(): void
    {
        // طلب كضيف بنفس الموبايل قبل التسجيل.
        $order = Order::create([
            'order_number'  => 'KH-GUEST-1',
            'status'        => 'new',
            'customer_name' => 'سيد صلاح',
            'customer_phone' => '01012345678',
            'address'       => 'عنوان',
            'area_name'     => 'منطقة',
            'subtotal'      => 500, 'delivery_fee' => 0, 'total' => 500,
        ]);

        $customer = $this->customer(['phone' => '01012345678']);
        $customer->linkGuestOrders();

        $this->assertEquals($customer->id, $order->fresh()->customer_id);
    }

    // ───────────────────────── صلاحيات صفحات الحساب ─────────────────────────

    public function test_account_pages_require_login_and_are_noindex(): void
    {
        $this->get(route('account.orders'))->assertRedirect(route('login'));

        $customer = $this->customer();
        $this->actingAs($customer, 'customer')
            ->get(route('account.orders'))
            ->assertStatus(200)
            ->assertSee('noindex', false);
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $owner = $this->customer(['phone' => '01000000001']);
        $other = $this->customer(['phone' => '01000000002', 'name' => 'آخر']);
        $order = $this->deliveredOrder($owner, $this->product());

        $this->actingAs($other, 'customer')
            ->get(route('account.orders.show', $order))
            ->assertNotFound();
    }

    // ───────────────────────── المفضلة ─────────────────────────

    public function test_guest_wishlist_uses_session_and_merges_on_login(): void
    {
        $product = $this->product();

        $this->post(route('wishlist.toggle'), ['product_id' => $product->id])->assertRedirect();
        $this->assertTrue(Wishlist::has($product->id));

        // بعد الدخول بتتدمج في حساب العميل.
        $customer = $this->customer();
        $this->post(route('login.post'), ['identifier' => $customer->phone, 'password' => 'password123']);

        $this->assertDatabaseHas('wishlists', [
            'customer_id' => $customer->id,
            'product_id'  => $product->id,
        ]);
    }

    // ───────────────────────── المقارنة ─────────────────────────

    public function test_compare_caps_at_three_and_same_category(): void
    {
        $cat2 = ProductCategory::create([
            'slug' => 'fridges', 'name_ar' => 'ثلاجات', 'is_published' => true, 'published_at' => now(),
        ]);

        $p1 = $this->product();
        $p2 = $this->product();
        $p3 = $this->product();
        $p4 = $this->product();
        $other = $this->product(['category_id' => $cat2->id]);

        $this->assertEquals('added', Compare::add($p1->id));
        // قسم مختلف يترفض.
        $this->assertEquals('category_mismatch', Compare::add($other->id));

        Compare::add($p2->id);
        Compare::add($p3->id);
        // الرابع يترفض (الحد 3).
        $this->assertEquals('full', Compare::add($p4->id));
        $this->assertCount(3, Compare::ids());
    }

    // ───────────────────────── تقييمات المنتجات ─────────────────────────

    public function test_only_delivered_purchaser_can_review(): void
    {
        $product = $this->product();
        $customer = $this->customer();

        // من غير طلب متسلّم => مرفوض.
        $this->actingAs($customer, 'customer')
            ->from($product->path())
            ->post(route('product.review.store', $product), ['rating' => 5, 'body' => 'منتج ممتاز جداً وبيبرّد كويس'])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('product_reviews', 0);

        // بعد طلب متسلّم => التقييم بيتسجّل pending.
        $this->deliveredOrder($customer, $product);

        $this->actingAs($customer, 'customer')
            ->post(route('product.review.store', $product), ['rating' => 5, 'body' => 'منتج ممتاز جداً وبيبرّد كويس'])
            ->assertRedirect($product->path());

        $this->assertDatabaseHas('product_reviews', [
            'product_id'  => $product->id,
            'customer_id' => $customer->id,
            'status'      => 'pending',
        ]);
    }

    public function test_product_rating_schema_only_after_approval(): void
    {
        $product = $this->product();
        $customer = $this->customer();
        $order = $this->deliveredOrder($customer, $product);

        $review = ProductReview::create([
            'product_id'    => $product->id,
            'customer_id'   => $customer->id,
            'order_item_id' => $order->items->first()->id,
            'rating'        => 5,
            'body'          => 'تجربة ممتازة مع المنتج ده',
            'status'        => 'pending',
        ]);

        // pending => مفيش AggregateRating في صفحة المنتج.
        $this->get($product->path())->assertDontSee('AggregateRating', false);

        // بعد الاعتماد => بيظهر.
        $review->update(['status' => 'approved']);
        $this->get($product->path())->assertSee('AggregateRating', false);
    }
}
