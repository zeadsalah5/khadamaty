<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\BrandServicePage;
use App\Models\Service;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * المرحلة K1 — صفحات (ماركة × خدمة) للـ SEO العضوي.
 * بتأكّد على: النشر حسب الاكتمال، الـ 404 للمسوّدات، نص الاستقلال الإلزامي،
 * الـ breadcrumbs والـ schema، ودخول السايت ماب للمنشور المكتمل فقط.
 */
class BrandServicePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Settings::flush();
        $this->seed();
        Cache::flush();
    }

    protected function page(string $brandSlug = 'carrier', string $serviceSlug = 'ac-maintenance'): BrandServicePage
    {
        $brand = Brand::where('slug', $brandSlug)->firstOrFail();
        $service = Service::where('slug', $serviceSlug)->firstOrFail();

        return BrandServicePage::where('brand_id', $brand->id)
            ->where('service_id', $service->id)->firstOrFail();
    }

    protected function publish(BrandServicePage $page): void
    {
        $page->forceFill(['is_published' => true, 'published_at' => now()])->save();
        Cache::flush();
    }

    // ───────────────────────── النشر / المسوّدة ─────────────────────────

    public function test_seeded_pages_are_drafts_and_return_404(): void
    {
        $page = $this->page();
        $this->assertFalse($page->is_published, 'المفروض الصفحات تتزرع كمسوّدة.');
        $this->assertTrue($page->hasCompleteContent(), 'محتوى الصفحة المزروعة المفروض كامل.');

        $this->get('/brands/carrier/ac-maintenance')->assertNotFound();
    }

    public function test_published_page_renders_with_required_content(): void
    {
        $this->publish($this->page());

        $res = $this->get('/brands/carrier/ac-maintenance');
        $res->assertOk();
        $res->assertSee('صيانة تكييف كاريير', false);               // H1
        $res->assertSee('مركز صيانة مستقل، غير تابع لشركة كاريير', false); // توضيح الاستقلال الإلزامي
        $res->assertSee('index,follow', false);                      // robots = index لاكتمال المحتوى
        $res->assertDontSee('noindex', false);
        $res->assertSee('BreadcrumbList', false);                    // schema
        $res->assertSee('FAQPage', false);
    }

    public function test_unknown_service_or_brand_returns_404(): void
    {
        $this->publish($this->page());

        $this->get('/brands/carrier/does-not-exist')->assertNotFound(); // خدمة مش موجودة
        $this->get('/brands/does-not-exist/ac-maintenance')->assertNotFound(); // ماركة مش موجودة
        $this->get('/brands/sharp/ac-maintenance')->assertNotFound(); // صفحة لسه مسوّدة
    }

    // ───────────────────────── السايت ماب ─────────────────────────

    public function test_draft_pages_are_excluded_from_sitemap(): void
    {
        // كل الصفحات مسوّدة => مفيش sitemap-brand-services في الفهرس ولا روابط.
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('sitemap-brand-services.xml', false);
        $this->get('/sitemap-brand-services.xml')->assertOk()->assertDontSee('/brands/carrier/ac-maintenance', false);
    }

    public function test_published_complete_page_enters_sitemap(): void
    {
        $this->publish($this->page());

        $this->get('/sitemap.xml')->assertOk()->assertSee('sitemap-brand-services.xml', false);
        $this->get('/sitemap-brand-services.xml')->assertOk()
            ->assertSee('/brands/carrier/ac-maintenance', false);
    }

    public function test_page_excluded_from_sitemap_when_brand_unpublished(): void
    {
        $page = $this->page();
        $this->publish($page);
        // لو الماركة اتسحب نشرها، الصفحة تخرج من السايت ماب رغم إنها منشورة.
        $page->brand->forceFill(['is_published' => false])->save();
        Cache::flush();

        $this->get('/sitemap-brand-services.xml')->assertOk()
            ->assertDontSee('/brands/carrier/ac-maintenance', false);
    }

    // ───────────────────────── الروابط الداخلية ─────────────────────────

    public function test_service_page_links_to_published_brand_pages_only(): void
    {
        // قبل النشر: صفحة الخدمة مابتعرضش رابط الماركة.
        $this->get('/services/ac-maintenance')->assertOk()
            ->assertDontSee('/brands/carrier/ac-maintenance', false);

        $this->publish($this->page());

        $this->get('/services/ac-maintenance')->assertOk()
            ->assertSee('/brands/carrier/ac-maintenance', false);
    }

    public function test_brand_page_links_to_published_brand_service_page(): void
    {
        $this->publish($this->page());

        $this->get('/brands/carrier')->assertOk()
            ->assertSee('/brands/carrier/ac-maintenance', false);
    }
}
