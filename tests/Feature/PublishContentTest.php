<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AreaServicePage;
use App\Models\Brand;
use App\Models\BrandServicePage;
use App\Models\Service;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * khadamaty:publish-content — نشر صفحة محتوى واحدة من بيانات الـ seed
 * (ماركة / ماركة×خدمة / منطقة×خدمة) من غير لمس الصفحات المنشورة.
 */
class PublishContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Settings::flush();
        $this->seed();
        // المناطق/الخدمات المطلوبة منشورة زي الموقع الحي.
        DB::table('areas')->where('slug', '6th-october')->update(['is_published' => true]);
        DB::table('services')->where('slug', 'ac-installation')->update(['is_published' => true]);
        DB::table('brands')->where('slug', 'carrier')->update(['is_published' => true]);
        Cache::flush();
    }

    public function test_publishes_new_brand_page(): void
    {
        Brand::where('slug', 'haier')->delete();
        Cache::flush();
        $this->get('/brands/haier')->assertNotFound();

        $this->artisan('khadamaty:publish-content', ['type' => 'brand', 'key' => 'haier'])->assertSuccessful();

        $brand = Brand::where('slug', 'haier')->firstOrFail();
        $this->assertTrue((bool) $brand->is_published);
        $this->assertNotNull($brand->reviewed_by);
        $this->get('/brands/haier')->assertOk()->assertSee('صيانة تكييفات هاير')->assertDontSee('TODO');
    }

    public function test_publishes_brand_service_and_area_service_pages(): void
    {
        $this->artisan('khadamaty:publish-content', ['type' => 'brand-service', 'key' => 'carrier|ac-installation'])
            ->assertSuccessful();
        $this->artisan('khadamaty:publish-content', ['type' => 'area-service', 'key' => '6th-october|ac-installation'])
            ->assertSuccessful();

        $this->assertTrue((bool) BrandServicePage::whereHas('brand', fn ($q) => $q->where('slug', 'carrier'))
            ->whereHas('service', fn ($q) => $q->where('slug', 'ac-installation'))->firstOrFail()->is_published);
        $this->assertSame(1, AreaServicePage::where('area_id', Area::where('slug', '6th-october')->value('id'))
            ->where('service_id', Service::where('slug', 'ac-installation')->value('id'))->where('is_published', true)->count());

        $this->get('/brands/carrier/ac-installation')->assertOk()->assertSee('تركيب تكييف كاريير')->assertDontSee('TODO');
        $this->get('/areas/6th-october/ac-installation')->assertOk()->assertSee('حدائق أكتوبر')->assertDontSee('TODO');
    }

    public function test_does_not_touch_an_already_published_page_and_fails_on_unknown(): void
    {
        $this->artisan('khadamaty:publish-content', ['type' => 'brand', 'key' => 'haier'])->assertSuccessful();
        DB::table('brands')->where('slug', 'haier')->update(['excerpt' => 'تعديل من الداشبورد']);

        $this->artisan('khadamaty:publish-content', ['type' => 'brand', 'key' => 'haier'])->assertSuccessful();
        $this->assertSame('تعديل من الداشبورد', Brand::where('slug', 'haier')->value('excerpt'));

        $this->artisan('khadamaty:publish-content', ['type' => 'brand', 'key' => 'no-such'])->assertFailed();
        $this->artisan('khadamaty:publish-content', ['type' => 'area-service', 'key' => 'no-area|ac-installation'])->assertFailed();
    }
}
