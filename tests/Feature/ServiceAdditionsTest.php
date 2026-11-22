<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Service;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ServiceAdditionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Settings::flush();
        $this->seed();
        Cache::flush();
    }

    public function test_every_service_in_additions_publishes_with_cost_factors_and_no_todo(): void
    {
        $slugs = array_keys(require database_path('seeders/data/service_additions.php'));
        foreach ($slugs as $slug) {
            DB::table('services')->where('slug', $slug)->update(['cost_factors' => null]);
            $this->artisan('khadamaty:publish-service', ['slug' => $slug])->assertSuccessful();
            $s = Service::where('slug', $slug)->firstOrFail();
            $this->assertTrue((bool) $s->is_published, $slug);
            $this->assertNotEmpty($s->cost_factors, $slug);
            $this->get('/services/' . $slug)->assertOk()->assertSee('إيه اللي بيأثر على التكلفة')->assertDontSee('TODO');
        }
    }

    public function test_cost_factors_from_dashboard_are_kept(): void
    {
        DB::table('services')->where('slug', 'ac-cleaning')->update(['cost_factors' => json_encode(['عامل من الداشبورد'])]);
        $this->artisan('khadamaty:publish-service', ['slug' => 'ac-cleaning'])->assertSuccessful();
        $this->assertSame(['عامل من الداشبورد'], Service::where('slug', 'ac-cleaning')->firstOrFail()->cost_factors);
    }

    public function test_service_page_lists_related_published_posts_only(): void
    {
        DB::table('services')->where('slug', 'ac-repair')->update(['is_published' => true]);
        $this->artisan('khadamaty:publish-content', ['type' => 'post', 'key' => 'sharp-ac-error-9-4'])->assertSuccessful();
        $this->artisan('khadamaty:publish-content', ['type' => 'post', 'key' => 'midea-ac-error-e1', '--draft' => true])->assertSuccessful();
        Cache::flush();

        $html = $this->get('/services/ac-repair')->assertOk()->assertSee('اقرا كمان عن')->getContent();
        preg_match_all('#href="/blog/([a-z0-9-]+)"#', $html, $m);
        $shown = array_unique($m[1]);

        $this->assertNotEmpty($shown);
        $this->assertLessThanOrEqual(4, count($shown));
        $this->assertNotContains('midea-ac-error-e1', $shown, 'المسودة ماتظهرش');
        foreach ($shown as $slug) {
            $post = Post::where('slug', $slug)->firstOrFail();
            $this->assertTrue((bool) $post->is_published, $slug);
            $this->assertContains('ac-repair', (array) $post->suitable_services, $slug);
        }
    }

    public function test_ac_maintenance_targets_plural_keyword_in_title_intro_and_subtitle(): void
    {
        $this->artisan('khadamaty:publish-service', ['slug' => 'ac-maintenance'])->assertSuccessful();
        $this->artisan('khadamaty:publish-service', ['slug' => 'ac-maintenance'])->assertSuccessful();

        $service = \App\Models\Service::where('slug', 'ac-maintenance')->firstOrFail();
        $this->assertStringStartsWith('خدماتي بتقدّم صيانة تكييفات ومكيفات', $service->body);
        $this->assertSame(1, substr_count($service->body, 'خدماتي بتقدّم صيانة تكييفات'));

        $this->get('/services/ac-maintenance')->assertOk()
            ->assertSee('<title>صيانة تكييفات ومكيفات', false)
            ->assertSee('صيانة تكييفات ومكيفات في البيت لكل الماركات');
    }
}
