<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\ArticleContent;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * C1 — تأمين متن الـ Markdown (المقالات/الصفحات) وتصليب الـ JSON-LD.
 *
 * - الـ HTML الخام (<script>/<img onerror>) وروابط javascript: بتتشال من المتن.
 * - "</script>" جوّه أي قيمة بتدخل schema مبتكسرش الصفحة (JSON_HEX_TAG).
 */
class MarkdownSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Settings::flush();
        $this->seed();
    }

    public function test_raw_script_is_stripped_from_markdown(): void
    {
        $content = ArticleContent::fromMarkdown("## عنوان\n\n<script>alert(1)</script>\n\nنص عادي.");

        $this->assertStringNotContainsString('<script', $content->html);
        $this->assertStringNotContainsString('alert(1)', $content->html);
    }

    public function test_img_onerror_is_stripped_from_markdown(): void
    {
        $content = ArticleContent::fromMarkdown('<img src=x onerror="alert(1)">');

        $this->assertStringNotContainsString('onerror', $content->html);
        $this->assertStringNotContainsString('<img', $content->html);
    }

    public function test_javascript_links_are_neutralized_in_markdown(): void
    {
        $content = ArticleContent::fromMarkdown('[اضغط](javascript:alert(1))');

        // allow_unsafe_links=false بيمنع إخراج href بروتوكول javascript:.
        $this->assertStringNotContainsString('href="javascript:', $content->html);
        $this->assertStringNotContainsString('javascript:alert', $content->html);
    }

    public function test_closing_script_tag_in_schema_value_does_not_break_page(): void
    {
        $category = ProductCategory::where('slug', 'air-conditioners')->firstOrFail();

        $product = Product::create([
            'slug'         => 'xss-schema-test',
            'name_ar'      => 'مكيّف </script><script>alert(1)</script>',
            'category_id'  => $category->id,
            'condition'    => 'new',
            'price'        => 5000,
            'stock_qty'    => 3,
            'status'       => 'published',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $html = $this->get($product->path())->assertOk()->getContent();

        // الـ JSON-LD بيهرّب < و > لـ </> فمفيش "</script>" خام جوّه ولا <script>alert.
        $this->assertStringContainsString('</script', $html);
        $this->assertStringNotContainsString('</script><script>alert(1)', $html);
    }
}
