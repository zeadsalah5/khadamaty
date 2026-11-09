<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * M2 — السعر المعروض (displayPrice) يستخدم سعر العرض بس لو أقل من الأساسي.
 */
class ProductPricingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Settings::flush();
        $this->seed();
    }

    private function product(array $attrs): Product
    {
        $category = ProductCategory::where('slug', 'air-conditioners')->firstOrFail();

        return Product::create(array_merge([
            'slug'         => 'prc-' . uniqid(),
            'name_ar'      => 'منتج',
            'category_id'  => $category->id,
            'condition'    => 'new',
            'stock_qty'    => 5,
            'status'       => 'published',
            'is_published' => true,
            'published_at' => now(),
        ], $attrs));
    }

    public function test_valid_sale_price_is_used(): void
    {
        $p = $this->product(['price' => 1000, 'sale_price' => 800]);

        $this->assertSame(800.0, (float) $p->displayPrice());
    }

    public function test_sale_price_higher_than_price_is_ignored(): void
    {
        // غلط إدخال: sale_price > price => نرجّع السعر الأساسي.
        $p = $this->product(['price' => 1000, 'sale_price' => 1500]);

        $this->assertSame(1000.0, (float) $p->displayPrice());
    }

    public function test_no_price_returns_null(): void
    {
        $p = $this->product(['price' => null, 'sale_price' => null]);

        $this->assertNull($p->displayPrice());
    }
}
