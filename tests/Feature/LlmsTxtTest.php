<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ملف /llms.txt الديناميكي: بيترندر، نصّي، فيه الخدمات المنشورة،
 * ومحايد تجاه التوكيل (مفيش كلمة «توكيل») — حسب قاعدة المشروع.
 */
class LlmsTxtTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_llms_txt_renders_as_plain_text(): void
    {
        $this->get('/llms.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=utf-8')
            ->assertSee('# ' . site('name'), false)
            ->assertSee('## الخدمات', false)
            ->assertSee('/services/', false);
    }

    public function test_llms_txt_is_neutral_on_agency(): void
    {
        $body = $this->get('/llms.txt')->getContent();

        $this->assertStringNotContainsString('توكيل', $body, 'llms.txt لازم يفضل محايد (مفيش توكيل/مش توكيل).');
    }
}
