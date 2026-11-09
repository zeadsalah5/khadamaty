<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * هيرو الرئيسية (التصميم full-bleed + السلايدر):
 * H1 واحد، السلايدات غير النشطة inert (مش محذوفة)، وfallback تدرّج + رسمة بدون صورة،
 * والهيدر شفّاف على الرئيسية بس.
 */
class HomeHeroTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_homepage_has_exactly_one_h1(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<h1'), 'لازم يكون فيه H1 واحد بس في الصفحة.');
    }

    public function test_only_first_slide_is_active_the_rest_are_inert(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        // نحسب وسوم <article> بتاعة الشرايح، ونتأكد إن كل شريحة غير نشطة فيها inert.
        preg_match_all('/<article\b[^>]*\bdata-hero-slide\b[^>]*>/u', $html, $matches);
        $slideTags = $matches[0];

        $this->assertGreaterThanOrEqual(2, count($slideTags), 'المفروض أكتر من شريحة علشان نختبر الـ inert.');

        $inert = array_filter($slideTags, fn ($tag) => str_contains($tag, 'inert'));
        $active = array_filter($slideTags, fn ($tag) => str_contains($tag, 'is-active'));

        $this->assertCount(1, $active, 'شريحة واحدة بس تكون نشطة.');
        $this->assertCount(count($slideTags) - 1, $inert, 'كل شريحة غير نشطة لازم تبقى inert (مش محذوفة من DOM).');
    }

    public function test_slide_without_image_falls_back_to_gradient_and_illustration(): void
    {
        // الحالة المبدئية (seed) مفيهاش صور خلفية => كل الشرايح تدرّج + رسمة التكييف.
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('hero--art', $html, 'بدون صورة لازم يظهر وضع التدرّج.');
        $this->assertStringContainsString('hero-ac', $html, 'رسمة وحدة التكييف لازم تظهر في وضع التدرّج.');
        $this->assertStringNotContainsString('hero--image', $html, 'مفيش صور في الـ seed فمفيش وضع صورة.');
    }

    public function test_homepage_header_is_transparent_but_other_pages_are_not(): void
    {
        $this->get('/')->assertOk()->assertSee('data-transparent-header', false);
        $this->get('/services')->assertOk()->assertDontSee('data-transparent-header', false);
    }

    public function test_homepage_renders_about_and_visual_services_sections(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        // قسم «مين إحنا» (العنوان من config الافتراضي).
        $this->assertStringContainsString('فريق صيانة وتركيب تكييفات', $html);
        // قسم «خدماتنا بالصور» + بطاقاته.
        $this->assertStringContainsString('غسيل وتنظيف', $html);
        $this->assertStringContainsString('شحن فريون', $html);
    }
}
