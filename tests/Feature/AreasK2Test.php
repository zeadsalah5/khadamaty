<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AreaServicePage;
use App\Models\Service;
use App\Models\User;
use App\Support\AreaServicePageGenerator;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * K2 المناطق: صفحات (منطقة×خدمة) + فيصل مسودة + رقم فاضي يخفي الأزرار + preflight.
 */
class AreasK2Test extends TestCase
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

    protected function makePage(bool $published): AreaServicePage
    {
        $area = Area::where('slug', 'nasr-city')->firstOrFail();
        $service = Service::where('slug', 'ac-maintenance')->firstOrFail();

        return AreaServicePage::create([
            'area_id'        => $area->id,
            'service_id'     => $service->id,
            'h1'             => 'صيانة تكييف مدينة نصر',
            'intro'          => 'فني صيانة تكييف في مدينة نصر بيوصلك في نفس اليوم.',
            'faqs'           => [['q' => 'بتوصلوا مدينة نصر؟', 'a' => 'آه بنغطّي كل أحياء مدينة نصر.']],
            'is_published'   => $published,
            'published_at'   => $published ? now() : null,
            'content_status' => $published ? 'ready' : 'draft',
            'reviewed_by'    => $published ? $this->admin()->id : null,
        ]);
    }

    // ───────── صفحة (منطقة × خدمة) ─────────

    public function test_published_area_service_page_renders(): void
    {
        $this->makePage(true);
        Cache::flush();

        $res = $this->get('/areas/nasr-city/ac-maintenance');
        $res->assertOk();
        $res->assertSee('صيانة تكييف مدينة نصر', false);
        $res->assertSee('index,follow', false);
    }

    public function test_draft_area_service_is_404_for_guest_and_preview_for_admin(): void
    {
        $this->makePage(false);
        Cache::flush();

        $this->get('/areas/nasr-city/ac-maintenance')->assertNotFound();

        $res = $this->actingAs($this->admin())->get('/areas/nasr-city/ac-maintenance');
        $res->assertOk();
        $res->assertSee('مسودة', false);
        $res->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_sitemap_includes_published_excludes_draft_area_service(): void
    {
        $this->makePage(false);
        Cache::flush();
        $this->get('/sitemap-area-services.xml')->assertOk()
            ->assertDontSee('/areas/nasr-city/ac-maintenance', false);

        AreaServicePage::query()->update(['is_published' => true, 'content_status' => 'ready', 'reviewed_by' => $this->admin()->id, 'published_at' => now()]);
        Cache::flush();
        $this->get('/sitemap-area-services.xml')->assertOk()
            ->assertSee('/areas/nasr-city/ac-maintenance', false);
    }

    // ───────── المولّد (حد أقصى 5) ─────────

    public function test_generator_rejects_more_than_five_combinations(): void
    {
        $areas = Area::query()->where('is_published', true)->limit(2)->pluck('id')->all();
        $services = Service::query()->where('is_published', true)->limit(3)->pluck('id')->all();

        $this->expectException(\InvalidArgumentException::class);
        app(AreaServicePageGenerator::class)->generate($areas, $services); // 2×3 = 6 > 5
    }

    public function test_generator_creates_drafts_and_skips_existing(): void
    {
        $this->makePage(true); // nasr-city × ac-maintenance موجودة
        $area = Area::where('slug', 'nasr-city')->firstOrFail();
        $services = Service::query()->where('is_published', true)->limit(2)->pluck('id')->all();

        $result = app(AreaServicePageGenerator::class)->generate([$area->id], $services);
        $this->assertSame(1, $result['skipped']); // الموجودة اتخطّت
        $this->assertGreaterThanOrEqual(1, $result['created']);

        // المولّدة مسودة فيها TODO.
        $generated = AreaServicePage::where('area_id', $area->id)->where('is_published', false)->first();
        $this->assertNotNull($generated);
        $this->assertTrue($generated->hasTodo());
        $this->assertFalse($generated->canPublish());
    }

    // ───────── فيصل مسودة ─────────

    public function test_faisal_area_is_seeded_as_draft(): void
    {
        $faisal = Area::where('slug', 'faisal')->first();
        $this->assertNotNull($faisal, 'منطقة فيصل المفروض متزرعة.');
        $this->assertFalse($faisal->is_published, 'فيصل المفروض مسودة محتاجة تأكيد.');

        $this->get('/areas/faisal')->assertNotFound(); // مسودة => 404 للزائر
    }

    // ───────── رقم فاضي يخفي الأزرار ─────────

    public function test_empty_phone_hides_call_and_whatsapp_buttons(): void
    {
        // الإعدادات المزروعة بترجع لـ config (phone='' / whatsapp='').
        $this->get('/services/ac-maintenance')->assertOk()
            ->assertDontSee('href="tel:', false)
            ->assertDontSee('wa.me/', false);
    }

    // ───────── preflight ─────────

    public function test_preflight_fails_in_production_with_issues(): void
    {
        $this->app['env'] = 'production';

        $this->artisan('site:preflight')->assertExitCode(1);
    }
}
