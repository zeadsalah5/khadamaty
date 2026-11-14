<?php

namespace Tests\Unit;

use App\Support\VisitClassifier;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * تصنيف الزيارة: بوت/إنسان، المتصفح/الجهاز، ومصدر الزائر
 * (بحث/AI/سوشيال/referral/مباشر/داخلي).
 */
class VisitClassifierTest extends TestCase
{
    private const CHROME = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36';
    private const IPHONE = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

    public function test_normal_chrome_is_human_direct(): void
    {
        $r = VisitClassifier::classify(self::CHROME, null, 'example.com');

        $this->assertFalse($r['is_bot']);
        $this->assertSame('Chrome', $r['browser']);
        $this->assertSame('desktop', $r['device']);
        $this->assertSame('direct', $r['source_type']);
    }

    public function test_iphone_is_mobile(): void
    {
        $r = VisitClassifier::classify(self::IPHONE, null, 'example.com');

        $this->assertFalse($r['is_bot']);
        $this->assertSame('mobile', $r['device']);
    }

    public function test_googlebot_is_bot(): void
    {
        $r = VisitClassifier::classify('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', null, 'example.com');

        $this->assertTrue($r['is_bot']);
        $this->assertSame('Googlebot', $r['bot_name']);
        $this->assertSame('bot', $r['device']);
        // بوت بحث من غير referer = مصدر مباشر (مش زاحف AI).
        $this->assertSame('direct', $r['source_type']);
    }

    public function test_empty_user_agent_is_bot(): void
    {
        $r = VisitClassifier::classify('', null, 'example.com');
        $this->assertTrue($r['is_bot']);
    }

    #[DataProvider('aiBotProvider')]
    public function test_ai_crawlers_are_ai_bot(string $ua): void
    {
        $r = VisitClassifier::classify($ua, null, 'example.com');

        $this->assertTrue($r['is_bot'], "لازم يتعرف كبوت: {$ua}");
        $this->assertSame('ai_bot', $r['source_type'], "لازم يكون ai_bot: {$ua}");
        $this->assertNotNull($r['bot_name']);
    }

    public static function aiBotProvider(): array
    {
        return [
            'GPTBot'        => ['Mozilla/5.0 (compatible; GPTBot/1.1; +https://openai.com/gptbot)'],
            'ClaudeBot'     => ['Mozilla/5.0 (compatible; ClaudeBot/1.0; +claudebot@anthropic.com)'],
            'PerplexityBot' => ['Mozilla/5.0 (compatible; PerplexityBot/1.0; +https://perplexity.ai/bot)'],
            'CCBot'         => ['CCBot/2.0 (https://commoncrawl.org/faq/)'],
            'Google-Extended' => ['Mozilla/5.0 (compatible; Google-Extended/1.0)'],
            'Bytespider'    => ['Mozilla/5.0 (compatible; Bytespider; spider-feedback@bytedance.com)'],
        ];
    }

    public function test_human_from_google_search(): void
    {
        $r = VisitClassifier::classify(self::CHROME, 'https://www.google.com/search?q=تكييف', 'example.com');

        $this->assertFalse($r['is_bot']);
        $this->assertSame('search', $r['source_type']);
        $this->assertSame('Google', $r['source_name']);
    }

    public function test_human_from_chatgpt(): void
    {
        $r = VisitClassifier::classify(self::CHROME, 'https://chat.openai.com/', 'example.com');

        $this->assertSame('ai', $r['source_type']);
        $this->assertSame('ChatGPT', $r['source_name']);
    }

    public function test_gemini_is_ai_not_search(): void
    {
        // gemini.google.com لازم يتصنّف AI مش بحث (ترتيب الفحص).
        $r = VisitClassifier::classify(self::CHROME, 'https://gemini.google.com/app', 'example.com');

        $this->assertSame('ai', $r['source_type']);
        $this->assertSame('Gemini', $r['source_name']);
    }

    public function test_human_from_facebook_is_social(): void
    {
        $r = VisitClassifier::classify(self::CHROME, 'https://m.facebook.com/', 'example.com');

        $this->assertSame('social', $r['source_type']);
        $this->assertSame('Facebook', $r['source_name']);
    }

    public function test_other_site_is_referral(): void
    {
        $r = VisitClassifier::classify(self::CHROME, 'https://some-blog.net/post/123', 'example.com');

        $this->assertSame('referral', $r['source_type']);
        $this->assertSame('some-blog.net', $r['source_name']);
    }

    public function test_same_site_is_internal(): void
    {
        $r = VisitClassifier::classify(self::CHROME, 'https://example.com/services', 'example.com');

        $this->assertSame('internal', $r['source_type']);
    }
}
