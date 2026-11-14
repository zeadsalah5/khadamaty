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
