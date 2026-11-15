<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Service;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * بوابة المرحلة الأولى من خطة الـ SEO: «صفر نص ناقص ظاهر للزائر».
 * الحقول الاختيارية (السعر/المدة/زمن الوصول) مسموح يفضل فيها TODO كتذكير في الداشبورد،
 * لكن ممنوع تظهر في الصفحة العامة — ومفيش أرقام/ملاحظات placeholder في الكود.
 */
class PublicTodoGateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Settings::flush();
        $this->seed();
        Cache::flush();
    }

    public function test_public_note_helper_filters_blank_and_todo(): void
    {
        $this->assertNull(public_note(null));
        $this->assertNull(public_note('  '));
        $this->assertNull(public_note('TODO: حدّد السعر'));
        $this->assertSame('يبدأ من كذا', public_note(' يبدأ من كذا '));
    }

    public function test_service_page_hides_todo_notes_and_shows_real_ones(): void
    {
        $service = Service::firstOrFail();
        DB::table('services')->where('id', $service->id)->update([
            'is_published'  => true,
            'price_note'    => 'TODO: حدّد السعر المبدئي',
            'duration_note' => 'ساعة تقريباً للجهاز الواحد',
        ]);
        Cache::flush();

        $this->get('/services/' . $service->slug)
            ->assertOk()
            ->assertDontSee('TODO')
            ->assertSee('ساعة تقريباً للجهاز الواحد');
    }

    public function test_area_page_hides_todo_eta_note(): void
    {
        $area = Area::firstOrFail();
        DB::table('areas')->where('id', $area->id)->update([
            'is_published' => true,
            'eta_note'     => 'TODO: حدّد زمن الوصول التقريبي',
        ]);
        Cache::flush();

        $this->get('/areas/' . $area->slug)
            ->assertOk()
            ->assertDontSee('TODO')
            ->assertDontSee('زمن الوصول التقريبي');
    }

    public function test_home_prices_and_used_have_no_placeholder_text(): void
    {
        foreach (['/', '/prices', '/used'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertDontSee('TODO')
                ->assertDontSee('(placeholder)');
        }
    }

    public function test_prices_table_lists_only_services_with_real_price(): void
    {
        DB::table('services')->update(['price_note' => 'TODO: حدّد السعر']);
        Cache::flush();
        $this->get('/prices')->assertOk()->assertDontSee('أسعار الخدمات (إرشادية)');

        $service = Service::firstOrFail();
        DB::table('services')->where('id', $service->id)->update([
            'is_published' => true,
            'price_note'   => 'سعر تجريبي للاختبار',
        ]);
        Cache::flush();
        $this->get('/prices')
            ->assertOk()
            ->assertSee('أسعار الخدمات (إرشادية)')
            ->assertSee('سعر تجريبي للاختبار');
    }

    public function test_price_from_note_reads_only_a_visible_egp_price(): void
    {
        $this->assertSame(300.0, price_from_note('300 ج.م'));
        $this->assertSame(800.0, price_from_note('بداية من 1.5 حصان 800 ج.م ويزيد 200 ج.م'));
        $this->assertSame(1300.0, price_from_note('الشحنة الكاملة ١٣٠٠ جنيه'));
        $this->assertNull(price_from_note('السعر بيتحدد وقت المعاينة'));
        $this->assertNull(price_from_note('TODO: 300 ج.م'));
        $this->assertNull(price_from_note(null));
    }

    public function test_service_schema_has_offer_only_with_real_price(): void
    {
        $service = Service::firstOrFail();
        DB::table('services')->where('id', $service->id)->update([
            'is_published' => true,
            'price_note'   => '300 ج.م',
        ]);
        Cache::flush();
        $this->get('/services/' . $service->slug)
            ->assertOk()
            ->assertSee('"priceCurrency":"EGP"', false)
            ->assertSee('"price":300', false);

        DB::table('services')->where('id', $service->id)->update(['price_note' => 'TODO: حدّد السعر']);
        Cache::flush();
        $this->get('/services/' . $service->slug)
            ->assertOk()
            ->assertDontSee('"priceCurrency"', false);
    }

    public function test_business_schema_lists_published_areas(): void
    {
        $area = Area::firstOrFail();
        DB::table('areas')->where('id', $area->id)->update(['is_published' => true]);
        Cache::flush();

        $this->get('/')
            ->assertOk()
            ->assertSee('"@type":"City"', false)
            ->assertSee($area->name_ar);
    }
}
