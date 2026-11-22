<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BrandAdditionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Settings::flush();
        $this->seed();
        DB::table('brands')->whereIn('slug', ['carrier', 'sharp', 'midea', 'union-air', 'fresh'])
            ->update(['is_published' => true, 'warranty_agent_note' => null]);
        Cache::flush();
    }

    public function test_adds_after_warranty_note_and_faqs_once_without_touching_dashboard_text(): void
    {
        DB::table('brands')->where('slug', 'sharp')->update(['warranty_agent_note' => 'نص من الداشبورد']);
        $before = count(Brand::where('slug', 'carrier')->firstOrFail()->faqs);

        $this->artisan('khadamaty:brand-additions')->assertSuccessful();
        $this->artisan('khadamaty:brand-additions')->assertSuccessful();

        $carrier = Brand::where('slug', 'carrier')->firstOrFail();
        $this->assertStringContainsString('بعد ما ضمان الجهاز يخلص', $carrier->warranty_agent_note);
        $this->assertCount($before + 4, $carrier->faqs);
        $this->assertSame('نص من الداشبورد', Brand::where('slug', 'sharp')->value('warranty_agent_note'));
        $this->assertTrue((bool) $carrier->is_published);

        $this->get('/brands/carrier')->assertOk()
            ->assertSee('ملاحظة عن ضمان الوكيل')
            ->assertSee('تكييف كاريير الشباك القديم يستاهل صيانة؟')
            ->assertDontSee('TODO');
    }

    public function test_home_links_to_published_brand_pages(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('صيانة تكييفات الماركات')
            ->assertSee('href="/brands/carrier"', false);
    }

    public function test_unknown_slug_fails(): void
    {
        $this->artisan('khadamaty:brand-additions', ['slugs' => 'no-such'])->assertFailed();
    }
}
