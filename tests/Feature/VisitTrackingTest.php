<?php

namespace Tests\Feature;

use App\Filament\Resources\VisitResource;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * تتبّع الزوّار: الـ middleware بيسجّل زيارات الصفحات (بعد الرد)
 * ويتجاهل الأدمن والـ assets، ويصنّف البوت/المصدر صح.
 */
class VisitTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_homepage_visit_is_recorded(): void
    {
        $this->get('/')->assertOk();

        $this->assertSame(1, Visit::count());
        $visit = Visit::first();
        $this->assertSame('/', $visit->path);
        $this->assertFalse($visit->is_bot);
    }

    public function test_googlebot_visit_is_flagged_as_bot(): void
    {
        $this->get('/', ['User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'])
            ->assertOk();

        $visit = Visit::firstOrFail();
        $this->assertTrue($visit->is_bot);
        $this->assertSame('Googlebot', $visit->bot_name);
    }

    public function test_vulnerability_probe_is_flagged_as_bot_even_with_browser_agent(): void
    {
        $chrome = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36';

        foreach (['/wp-admin/install.php', '/wp-login.php', '/.env', '/xmlrpc.php', '/phpmyadmin/'] as $path) {
            $this->get($path, ['User-Agent' => $chrome]);
        }

        $this->assertSame(5, Visit::count());
        $this->assertSame(0, Visit::where('is_bot', false)->count());
        $this->assertSame(5, Visit::where('bot_name', \App\Support\VisitClassifier::PROBE_BOT_NAME)->count());
    }

    public function test_real_pages_are_not_probes(): void
    {
        foreach (['', 'services/ac-installation', 'areas/new-capital/ac-installation', 'blog/how-to-clean-ac', 'prices'] as $path) {
            $this->assertFalse(\App\Support\VisitClassifier::isProbePath($path), $path);
        }
    }

    public function test_reclassify_command_fixes_old_probe_visits(): void
    {
        Visit::create(['ip' => '1.1.1.1', 'path' => '/wp-admin/install.php', 'method' => 'GET', 'is_bot' => false, 'device' => 'desktop', 'source_type' => 'direct']);
        Visit::create(['ip' => '2.2.2.2', 'path' => '/prices', 'method' => 'GET', 'is_bot' => false, 'device' => 'desktop', 'source_type' => 'direct']);

        $this->artisan('khadamaty:reclassify-probe-visits')->assertSuccessful();

        $this->assertTrue((bool) Visit::where('path', '/wp-admin/install.php')->value('is_bot'));
        $this->assertFalse((bool) Visit::where('path', '/prices')->value('is_bot'));
    }

    public function test_search_referer_is_classified(): void
    {
        $this->get('/', ['referer' => 'https://www.google.com/search?q=تكييف'])->assertOk();

        $this->assertSame('search', Visit::firstOrFail()->source_type);
    }

    public function test_ai_referer_is_classified(): void
    {
        $this->get('/', ['referer' => 'https://chat.openai.com/'])->assertOk();

        $visit = Visit::firstOrFail();
        $this->assertSame('ai', $visit->source_type);
        $this->assertSame('ChatGPT', $visit->source_name);
    }

    public function test_admin_paths_are_not_recorded(): void
    {
        $this->get('/admin');          // بيحوّل للّوجين — متسجّلش
        $this->get('/admin/login');

        $this->assertSame(0, Visit::count());
    }

    public function test_post_requests_are_not_recorded(): void
    {
        // أي POST (مهما كان رده) مايتسجّلش — بنسجّل GET بس.
        $this->post('/');

        $this->assertSame(0, Visit::count());
    }

    public function test_admin_can_view_analytics_page(): void
    {
        // زيارة عيّنة عشان الجدول والويدجت يترندروا ببيانات.
        $this->get('/', ['referer' => 'https://www.google.com/search?q=x'])->assertOk();

        $admin = User::where('role', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->get(VisitResource::getUrl('index'))
            ->assertOk()
            ->assertSee('الزوّار');
    }

    public function test_editor_cannot_view_analytics_page(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);

        $this->actingAs($editor)
            ->get(VisitResource::getUrl('index'))
            ->assertForbidden();
    }
}
