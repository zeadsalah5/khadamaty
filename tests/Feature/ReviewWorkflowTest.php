<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Brand;
use App\Models\BrandServicePage;
use App\Models\Service;
use App\Models\User;
use App\Support\ContentSimilarity;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * سير المراجعة الموحّد (Section 2) + تعديلات مراجعة التركيب (Section 4):
 * - مفيش نشر من غير مراجعة أو مع TODO أو محتوى ناقص (canPublish).
 * - المسودة = 404 للزائر، 200 + شريط «مسودة» + X-Robots-Tag: noindex للأدمن.
 * - السايت ماب مفيهوش مسودات.
 * - الحجز بيخزّن الماركة ورابط المصدر.
 * - /brands/media => 301 /brands/midea.
 */
class ReviewWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Settings::flush();
        $this->seed();
        Cache::flush();
    }

    protected function admin(): User
    {
        return User::where('role', 'admin')->firstOrFail();
    }

    protected function service(string $slug = 'ac-maintenance'): Service
    {
        return Service::where('slug', $slug)->firstOrFail();
    }

    // ───────────────── canPublish (القاعدة الأساسية) ─────────────────

    public function test_ready_reviewed_complete_can_publish(): void
    {
        $s = $this->service();
        $s->content_status = Service::STATUS_READY;
        $s->reviewed_by = $this->admin()->id;

        $this->assertTrue($s->canPublish());
    }

    public function test_todo_in_content_blocks_publish(): void
    {
        $s = $this->service();
        $s->content_status = Service::STATUS_READY;
        $s->reviewed_by = $this->admin()->id;
        $s->body = $s->body . "\n\nTODO: راجع ده";

        $this->assertTrue($s->hasTodo());
        $this->assertFalse($s->canPublish(), 'صفحة فيها TODO مايصحّش تتنشر.');
    }

    public function test_unreviewed_blocks_publish(): void
    {
        $s = $this->service();
        $s->content_status = Service::STATUS_READY;
        $s->reviewed_by = null;

        $this->assertFalse($s->canPublish(), 'صفحة من غير مراجعة مايصحّش تتنشر.');
    }

    public function test_incomplete_content_blocks_publish(): void
    {
        $s = $this->service();
        $s->content_status = Service::STATUS_READY;
        $s->reviewed_by = $this->admin()->id;
        $s->body = null; // حقل مطلوب

        $this->assertFalse($s->canPublish());
    }

    public function test_optional_price_todo_does_not_block(): void
    {
        $s = $this->service();
        $s->content_status = Service::STATUS_READY;
        $s->reviewed_by = $this->admin()->id;
        $s->price_note = 'TODO: حدّد السعر'; // حقل اختياري => مش مانع

        $this->assertFalse($s->hasTodo(), 'TODO في السعر/المدة مش المفروض يتحسب.');
        $this->assertTrue($s->canPublish());
    }

    // ───────────────── معاينة الأدمن للمسوّدة ─────────────────

    public function test_draft_is_404_for_guest(): void
    {
        // صفحة (ماركة × خدمة) مزروعة كمسوّدة.
        $this->get('/brands/sharp/ac-maintenance')->assertNotFound();
    }

    public function test_draft_preview_for_admin_has_bar_and_noindex_header(): void
    {
        $res = $this->actingAs($this->admin())->get('/brands/sharp/ac-maintenance');

        $res->assertOk();
        $res->assertSee('مسودة', false);
        $res->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    // ───────────────── السايت ماب ─────────────────

    public function test_sitemap_has_no_draft_brand_services(): void
    {
        $this->get('/sitemap-brand-services.xml')->assertOk()
            ->assertDontSee('/brands/sharp/ac-maintenance', false);
    }

    // ───────────────── الحجز: الماركة + رابط المصدر ─────────────────

    public function test_booking_stores_brand_and_source_url(): void
    {
        $this->post('/booking', [
            'name'       => 'أحمد تجريبي',
            'phone'      => '01000000000',
            'area'       => 'التجمع الخامس',
            'service'    => 'صيانة دورية',
            'brand'      => 'كاريير',
            'source_url' => 'http://localhost/brands/carrier/ac-maintenance',
        ]);

        $booking = Booking::latest('id')->first();
        $this->assertNotNull($booking);
        $this->assertSame('كاريير', $booking->brand);
        $this->assertSame('http://localhost/brands/carrier/ac-maintenance', $booking->source_url);
    }

    // ───────────────── midea 301 ─────────────────

    public function test_media_brand_redirects_to_midea(): void
    {
        $this->assertNotNull(Brand::where('slug', 'midea')->first(), 'ماركة midea المفروض موجودة.');
        $this->get('/brands/media')->assertRedirect('/brands/midea');
    }

    // ───────────────── التشابه ─────────────────

    public function test_similarity_detects_identical_and_distinct(): void
    {
        $sim = app(ContentSimilarity::class);

        $text = 'صيانة تكييف شاملة كشف وتنظيف وضبط الأداء بأيدي فنيين متخصصين';
        $this->assertSame(100, $sim->percent($text, $text));
        $this->assertLessThan(30, $sim->percent($text, 'المتجر بيبيع قطع غيار ثلاجات وغسالات بأسعار مختلفة تماماً'));
    }
}
