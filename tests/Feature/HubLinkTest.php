<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Brand;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HubLinkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Settings::flush();
        $this->seed();
        Cache::flush();
    }

    public function test_home_area_and_brand_pages_link_to_maintenance_hub_with_keyword(): void
    {
        $link = 'href="/services/ac-maintenance" class="text-brand font-bold underline';
        $this->get('/')->assertOk()->assertSee($link, false)->assertSee('صيانة تكييفات ومكيفات لكل الماركات');

        $area = Area::withoutGlobalScopes()->firstOrFail();
        $area->forceFill(['is_published' => true, 'published_at' => now()])->saveQuietly();
        Cache::flush();
        $this->get('/areas/' . $area->slug)->assertOk()->assertSee($link, false);

        $brand = Brand::withoutGlobalScopes()->firstOrFail();
        $brand->forceFill(['is_published' => true, 'published_at' => now()])->saveQuietly();
        Cache::flush();
        $this->get('/brands/' . $brand->slug)->assertOk()->assertSee($link, false);
    }
}
