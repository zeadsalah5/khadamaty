<?php

namespace Tests\Feature;

use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * اختبارات الصفحة الرئيسية ومنظومة الإعدادات (المرحلة C).
 * بتشتغل على داتابيز مهاجَرة + seeded (الموقع ديناميكي بالكامل دلوقتي).
 */
class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // كاش الإعدادات static — بنمسحه بين الاختبارات عشان مايتسربش.
        Settings::flush();

        $this->seed();
    }

    public function test_the_application_returns_a_successful_response(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee(config('site.name'));
    }

    public function test_setting_change_reflects_on_home_immediately(): void
    {
        $unique = 'عرض-اختبار-' . uniqid();

        Settings::set('announcement_text', $unique, 'home');

        $this->get('/')
            ->assertStatus(200)
            ->assertSee($unique);
    }

    public function test_missing_setting_falls_back_to_config(): void
    {
        // مفيش سجل phone في settings => بترجع قيمة config.
        \App\Models\Setting::query()->where('key', 'phone')->delete();
        Settings::flush();

        $this->assertSame(config('site.phone'), Settings::get('phone'));
    }
}
