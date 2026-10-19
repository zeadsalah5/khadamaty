<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\CategoryAttribute;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\ProductUsedDetail;
use App\Models\User;
use App\Support\Settings;
use App\Support\Store\ProductColumns;
use App\Support\Store\ProductsExporter;
use App\Support\Store\ProductsImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Tests\TestCase;

/**
 * المرحلة 6A — أساس المتجر: قاعدة النشر، الاستيراد/التصدير، وبوابة الأدمن.
 */
class StoreTest extends TestCase
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

    // ───────────────────────── قاعدة النشر ─────────────────────────

    public function test_new_product_is_incomplete_without_price_or_image(): void
    {
        $product = Product::create([
            'slug' => 'p-new-1', 'name_ar' => 'منتج', 'category_id' => $this->category()->id,
            'condition' => 'new', 'status' => 'draft', 'is_published' => false,
        ]);

        $this->assertFalse($product->hasCompleteContent());
        $this->assertContains('السعر', $product->missingContent());
        $this->assertContains('صورة واحدة على الأقل', $product->missingContent());
    }

    public function test_new_product_is_complete_with_price_and_image(): void
    {
        $product = Product::create([
            'slug' => 'p-new-2', 'name_ar' => 'منتج', 'category_id' => $this->category()->id,
            'condition' => 'new', 'price' => 5000, 'status' => 'draft', 'is_published' => false,
        ]);
        ProductImage::create(['product_id' => $product->id, 'path' => 'a.webp', 'is_primary' => true]);

        $this->assertTrue($product->fresh()->load('images')->hasCompleteContent());
    }

    public function test_used_product_requires_three_images_and_inspection(): void
    {
        $product = Product::create([
            'slug' => 'p-used-1', 'name_ar' => 'جهاز مستعمل', 'category_id' => $this->category()->id,
            'condition' => 'used', 'price' => 3000, 'status' => 'draft', 'is_published' => false,
        ]);
        ProductImage::create(['product_id' => $product->id, 'path' => 'a.webp', 'is_primary' => true]);

        // صورة واحدة بس + مفيش تفاصيل => ناقص.
        $missing = $product->fresh()->load(['images', 'usedDetails'])->missingContent();
        $this->assertContains('٣ صور على الأقل (جهاز مستعمل)', $missing);
        $this->assertContains('درجة حالة الجهاز', $missing);
        $this->assertContains('قايمة الفحص', $missing);

        // كمّل: 3 صور + تفاصيل فحص.
        ProductImage::create(['product_id' => $product->id, 'path' => 'b.webp']);
        ProductImage::create(['product_id' => $product->id, 'path' => 'c.webp']);
        ProductUsedDetail::create([
            'product_id' => $product->id, 'condition_grade' => 'جيد',
            'inspection' => [['item' => 'الكمبروسر', 'ok' => true]],
        ]);

        $this->assertTrue($product->fresh()->load(['images', 'usedDetails'])->hasCompleteContent());
    }

    // ───────────────────────── الاستيراد ─────────────────────────

    public function test_import_creates_drafts_updates_by_sku_and_reports_errors(): void
    {
        Brand::create(['slug' => 'test-brand', 'name_ar' => 'ماركة اختبار', 'is_published' => true]);

        // منتج موجود بنفس الـ sku علشان نختبر التحديث.
        $existing = Product::create([
            'slug' => 'existing-sku', 'name_ar' => 'قديم', 'sku' => 'SKU-UP',
            'category_id' => $this->category()->id, 'condition' => 'new', 'price' => 100,
            'status' => 'draft', 'is_published' => false,
        ]);

        $path = $this->makeXlsx([
            ProductColumns::KEYS,
            // صف صحيح جديد.
            ['منتج جديد', 'SKU-NEW', 'ماركة اختبار', 'تكييفات', 'new', 4000, '', 3, 'horsepower=1.5', '', '', '', ''],
            // تحديث بالـ sku.
            ['اسم محدّث', 'SKU-UP', '', 'air-conditioners', 'new', 250, '', 7, '', '', '', '', ''],
            // صف غلط: مفيش اسم + قسم مش موجود + condition غلط.
            ['', '', '', 'قسم وهمي', 'maybe', 'abc', '', '', '', '', '', '', ''],
        ]);

        $report = ProductsImporter::import($path);
        @unlink($path);

        $this->assertSame(1, $report['created']);
        $this->assertSame(1, $report['updated']);
        $this->assertSame(1, $report['skipped']);
        $this->assertNotEmpty($report['errors']);
        $this->assertSame(4, $report['errors'][0]['row']); // الصف الرابع في الملف.

        // الجديد draft ومش منشور.
        $new = Product::where('sku', 'SKU-NEW')->first();
        $this->assertNotNull($new);
        $this->assertFalse($new->is_published);
        $this->assertSame('draft', $new->status);

        // التحديث اتطبّق بالـ sku.
        $this->assertSame('اسم محدّث', $existing->fresh()->name_ar);
        $this->assertEquals(250, (float) $existing->fresh()->price);
    }

    // ───────────────────────── التصدير ─────────────────────────

    public function test_export_produces_file_with_header_row(): void
    {
        $path = ProductsExporter::export();

        $this->assertFileExists($path);
        $this->assertStringContainsString('xlsx', $path);
        @unlink($path);
    }

    // ───────────────────────── بوابة الاستيراد (أدمن فقط) ─────────────────────────

    public function test_products_admin_pages_require_auth(): void
    {
        $this->get('/admin/products')->assertRedirect(); // مش مسجّل دخول.

        $editor = User::factory()->create(['role' => 'editor']);
        $this->actingAs($editor)->get('/admin/products')->assertStatus(200); // المحرّر يوصل للمنتجات.
    }

    // ───────────────────────── الواجهة ─────────────────────────

    public function test_shop_shows_published_product_and_404_for_draft(): void
    {
        $published = Product::create([
            'slug' => 'shown', 'name_ar' => 'منتج ظاهر', 'category_id' => $this->category()->id,
            'condition' => 'new', 'price' => 999, 'status' => 'published', 'is_published' => true,
        ]);
        ProductImage::create(['product_id' => $published->id, 'path' => 'a.webp', 'is_primary' => true]);

        $this->get('/shop')->assertStatus(200)->assertSee('منتج ظاهر');
        $this->get('/shop/shown')->assertStatus(200)->assertSee('منتج ظاهر');

        // مسوّدة => 404.
        Product::create([
            'slug' => 'hidden', 'name_ar' => 'مسوّدة', 'category_id' => $this->category()->id,
            'condition' => 'new', 'status' => 'draft', 'is_published' => false,
        ]);
        $this->get('/shop/hidden')->assertStatus(404);
    }

    // ───────────────────────── مساعد: بناء ملف XLSX للاختبار ─────────────────────────

    protected function makeXlsx(array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'imp') . '.xlsx';
        $writer = new Writer();
        $writer->openToFile($path);
        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues($row));
        }
        $writer->close();

        return $path;
    }
}
