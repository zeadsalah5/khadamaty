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

    public function test_adds_faqs_once_and_never_writes_a_warranty_note(): void
    {
        $before = count(Brand::where('slug', 'carrier')->firstOrFail()->faqs);

        $this->artisan('khadamaty:brand-additions')->assertSuccessful();
        $this->artisan('khadamaty:brand-additions')->assertSuccessful();

        $carrier = Brand::where('slug', 'carrier')->firstOrFail();
        $this->assertNull($carrier->warranty_agent_note);
        $this->assertCount($before + 4, $carrier->faqs);
        $this->get('/brands/carrier')->assertOk()
            ->assertDontSee('ملاحظة عن ضمان الوكيل')
            ->assertSee('تكييف كاريير الشباك القديم يستاهل صيانة؟');
    }

    public function test_clear_warranty_removes_only_our_text(): void
    {
        DB::table('brands')->where('slug', 'carrier')->update(['warranty_agent_note' => 'نص... بعد ما ضمان الجهاز يخلص، بتبقى حر.']);
        DB::table('brands')->where('slug', 'sharp')->update(['warranty_agent_note' => 'نص كتبه صاحب الموقع']);

        $this->artisan('khadamaty:brand-additions', ['--clear-warranty' => true])->assertSuccessful();

        $this->assertNull(Brand::where('slug', 'carrier')->value('warranty_agent_note'));
        $this->assertSame('نص كتبه صاحب الموقع', Brand::where('slug', 'sharp')->value('warranty_agent_note'));
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
