<?php

namespace App\Support;

/**
 * تصنيف زيارة: بوت ولا إنسان، والمتصفح/الجهاز، ومصدر الزائر
 * (محرك بحث / أداة AI / سوشيال / موقع آخر «منشن» / مباشر / تنقّل داخلي).
 * كله من الـ user-agent والـ referer — من غير أي خدمة خارجية.
 */
class VisitClassifier
{
    /**
     * زواحف الـ AI (مهمة): لو الزاحف ده دخل الموقع يبقى source=ai_bot.
     * المفتاح = نمط في الـ UA (lowercase)، القيمة = الاسم المعروض.
     */
    private const AI_BOTS = [
        'gptbot'              => 'GPTBot (OpenAI)',
        'oai-searchbot'       => 'OAI-SearchBot (OpenAI)',
        'chatgpt-user'        => 'ChatGPT-User',
        'ccbot'               => 'CCBot (Common Crawl)',
        'claudebot'           => 'ClaudeBot (Anthropic)',
        'claude-web'          => 'Claude-Web (Anthropic)',
        'claude-user'         => 'Claude-User (Anthropic)',
        'anthropic-ai'        => 'Anthropic-AI',
        'perplexitybot'       => 'PerplexityBot',
        'perplexity-user'     => 'Perplexity-User',
        'google-extended'     => 'Google-Extended',
        'googleother'         => 'GoogleOther',
        'bytespider'          => 'Bytespider (TikTok)',
        'amazonbot'           => 'Amazonbot',
        'meta-externalagent'  => 'Meta-ExternalAgent',
        'meta-externalfetcher' => 'Meta-ExternalFetcher',
        'facebookbot'         => 'FacebookBot',
        'applebot-extended'   => 'Applebot-Extended',
        'cohere-ai'           => 'Cohere-AI',
        'cohere-training-data-crawler' => 'Cohere Crawler',
        'diffbot'             => 'Diffbot',
        'imagesiftbot'        => 'ImageSiftBot',
        'youbot'              => 'YouBot (You.com)',
        'duckassistbot'       => 'DuckAssistBot',
        'timpibot'            => 'TimpiBot',
        'omgili'              => 'Omgili',
        'omgilibot'           => 'OmgiliBot',
        'webzio'              => 'Webz.io',
        'mistralai-user'      => 'MistralAI-User',
        'deepseekbot'         => 'DeepSeekBot',
        'petalbot'            => 'PetalBot (Huawei AI)',
    ];

    /** بوتات عادية (بحث/سوشيال/أدوات SEO). المفتاح = نمط UA، القيمة = الاسم. */
    private const OTHER_BOTS = [
        'googlebot'          => 'Googlebot',
        'adsbot-google'      => 'AdsBot-Google',
        'mediapartners-google' => 'Mediapartners-Google',
        'bingbot'            => 'Bingbot',
        'adidxbot'           => 'AdIdxBot (Bing)',
        'msnbot'             => 'MSNBot',
        'slurp'              => 'Yahoo! Slurp',
        'duckduckbot'        => 'DuckDuckBot',
        'duckduckgo'         => 'DuckDuckGo',
        'baiduspider'        => 'Baiduspider',
        'yandexbot'          => 'YandexBot',
        'yandex'             => 'Yandex',
        'sogou'              => 'Sogou',
        'exabot'             => 'Exabot',
        'seznambot'          => 'SeznamBot',
        'applebot'           => 'Applebot',
        'facebookexternalhit' => 'Facebook',
        'facebot'            => 'Facebook',
        'twitterbot'         => 'Twitterbot',
        'linkedinbot'        => 'LinkedInBot',
        'pinterest'          => 'Pinterest',
        'pinterestbot'       => 'Pinterestbot',
        'redditbot'          => 'RedditBot',
        'slackbot'           => 'Slackbot',
        'telegrambot'        => 'TelegramBot',
        'whatsapp'           => 'WhatsApp',
        'discordbot'         => 'Discordbot',
        'skypeuripreview'    => 'Skype',
        'embedly'            => 'Embedly',
        'flipboard'          => 'Flipboard',
        'tumblr'             => 'Tumblr',
        'w3c_validator'      => 'W3C Validator',
        'ahrefsbot'          => 'AhrefsBot',
        'semrushbot'         => 'SemrushBot',
        'mj12bot'            => 'MJ12bot',
        'dotbot'             => 'DotBot',
        'rogerbot'           => 'rogerbot',
        'screaming frog'     => 'Screaming Frog',
        'serpstatbot'        => 'serpstatbot',
        'dataforseobot'      => 'DataForSeoBot',
        'blexbot'            => 'BLEXBot',
        'uptimerobot'        => 'UptimeRobot',
        'pingdom'            => 'Pingdom',
        'gtmetrix'           => 'GTmetrix',
        'lighthouse'         => 'Lighthouse',
        'headlesschrome'     => 'HeadlessChrome',
        'python-requests'    => 'python-requests',
        'curl'               => 'curl',
        'wget'               => 'Wget',
        'go-http-client'     => 'Go-http-client',
        'axios'              => 'axios',
        'okhttp'             => 'OkHttp',
    ];

    /** أنماط عامة للبوت لو مفيش اسم محدد فوق. */
    private const GENERIC_BOT_PATTERNS = ['bot', 'crawl', 'spider', 'scraper', 'fetch', 'monitor', 'preview'];

    /**
     * أدوات AL يدخل منها الإنسان (referer host). المفتاح = نمط في الـ host.
     * بتتفحص قبل محركات البحث (عشان gemini/bard أقسام من google).
     */
    private const AI_HOSTS = [
        'chat.openai.com'      => 'ChatGPT',
        'chatgpt.com'          => 'ChatGPT',
        'openai.com'           => 'OpenAI',
        'perplexity.ai'        => 'Perplexity',
        'gemini.google.com'    => 'Gemini',
        'bard.google.com'      => 'Gemini',
        'aistudio.google.com'  => 'Google AI Studio',
        'claude.ai'            => 'Claude',
        'copilot.microsoft.com' => 'Microsoft Copilot',
        'you.com'              => 'You.com',
        'poe.com'              => 'Poe',
        'phind.com'            => 'Phind',
        'deepseek.com'         => 'DeepSeek',
        'chat.deepseek.com'    => 'DeepSeek',
        'x.ai'                 => 'Grok (xAI)',
        'grok.com'             => 'Grok',
        'huggingface.co'       => 'Hugging Face',
        'mistral.ai'           => 'Mistral',
        'chat.mistral.ai'      => 'Le Chat (Mistral)',
        'character.ai'         => 'Character.AI',
    ];

    /** محركات البحث (referer host). */
    private const SEARCH_HOSTS = [
        'google.'      => 'Google',
        'bing.com'     => 'Bing',
        'yahoo.'       => 'Yahoo',
        'duckduckgo.com' => 'DuckDuckGo',
        'yandex.'      => 'Yandex',
        'baidu.com'    => 'Baidu',
        'ecosia.org'   => 'Ecosia',
        'search.brave.com' => 'Brave Search',
        'startpage.com' => 'Startpage',
        'qwant.com'    => 'Qwant',
        'ask.com'      => 'Ask',
        'aol.com'      => 'AOL',
        'naver.com'    => 'Naver',
        'seznam.cz'    => 'Seznam',
    ];

    /** سوشيال ميديا (referer host). */
    private const SOCIAL_HOSTS = [
        'facebook.com' => 'Facebook',
        'fb.com'       => 'Facebook',
        'fb.me'        => 'Facebook',
        'l.facebook.com' => 'Facebook',
        'm.facebook.com' => 'Facebook',
        'instagram.com' => 'Instagram',
        'l.instagram.com' => 'Instagram',
        't.co'         => 'Twitter / X',
        'twitter.com'  => 'Twitter / X',
        'x.com'        => 'Twitter / X',
        'linkedin.com' => 'LinkedIn',
        'lnkd.in'      => 'LinkedIn',
        'youtube.com'  => 'YouTube',
        'youtu.be'     => 'YouTube',
        'tiktok.com'   => 'TikTok',
        'reddit.com'   => 'Reddit',
        'pinterest.'   => 'Pinterest',
        't.me'         => 'Telegram',
        'telegram.'    => 'Telegram',
        'whatsapp.com' => 'WhatsApp',
        'wa.me'        => 'WhatsApp',
        'snapchat.com' => 'Snapchat',
        'tumblr.com'   => 'Tumblr',
        'quora.com'    => 'Quora',
        'medium.com'   => 'Medium',
    ];

    /**
     * بيرجّع مصفوفة الحقول الجاهزة للتخزين في جدول visits.
     * $selfHost = نطاق الموقع نفسه (عشان نكتشف التنقّل الداخلي).
     */
    public static function classify(?string $userAgent, ?string $referer, ?string $selfHost = null): array
    {
        $ua = mb_strtolower(trim((string) $userAgent));

        [$isBot, $botName, $isAiBot] = self::detectBot($ua);

        $refHost = self::host($referer);
        [$sourceType, $sourceName] = self::detectSource($refHost, $selfHost, $isBot, $isAiBot, $botName);

        return [
            'referer_host' => $refHost,
            'browser'      => $isBot ? null : self::browser($ua),
            'device'       => $isBot ? 'bot' : self::device($ua),
            'is_bot'       => $isBot,
            'bot_name'     => $botName,
            'source_type'  => $sourceType,
            'source_name'  => $sourceName,
        ];
    }

    /** @return array{0:bool,1:?string,2:bool} [is_bot, bot_name, is_ai_bot] */
    public static function detectBot(string $ua): array
    {
        if ($ua === '') {
            return [true, 'غير معروف (UA فاضي)', false];
        }

        foreach (self::AI_BOTS as $needle => $name) {
            if (str_contains($ua, $needle)) {
                return [true, $name, true];
            }
        }
        foreach (self::OTHER_BOTS as $needle => $name) {
            if (str_contains($ua, $needle)) {
                return [true, $name, false];
            }
        }
        foreach (self::GENERIC_BOT_PATTERNS as $needle) {
            if (str_contains($ua, $needle)) {
                return [true, 'بوت غير معروف', false];
            }
        }

        return [false, null, false];
    }

    /** @return array{0:string,1:?string} [source_type, source_name] */
    private static function detectSource(?string $refHost, ?string $selfHost, bool $isBot, bool $isAiBot, ?string $botName): array
    {
        if ($isAiBot) {
            return ['ai_bot', $botName];
        }

        if ($refHost === null || $refHost === '') {
            // من غير referer: زائر مباشر (أو بوت بيزحف من غير مصدر).
            return ['direct', null];
        }

        // تنقّل داخل نفس الموقع.
        if ($selfHost && self::sameSite($refHost, $selfHost)) {
            return ['internal', null];
        }

        foreach (self::AI_HOSTS as $needle => $name) {
            if (str_contains($refHost, $needle)) {
                return ['ai', $name];
            }
        }
        foreach (self::SEARCH_HOSTS as $needle => $name) {
            if (str_contains($refHost, $needle)) {
                return ['search', $name];
            }
        }
        foreach (self::SOCIAL_HOSTS as $needle => $name) {
            if (str_contains($refHost, $needle)) {
                return ['social', $name];
            }
        }

        // موقع تاني عامل لينك/منشن.
        return ['referral', $refHost];
    }

    /** يطلّع الـ host من رابط الـ referer (lowercase، من غير www). */
    public static function host(?string $referer): ?string
    {
        $referer = trim((string) $referer);
        if ($referer === '') {
            return null;
        }
        $host = parse_url($referer, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return null;
        }
        $host = mb_strtolower($host);

        return str_starts_with($host, 'www.') ? substr($host, 4) : $host;
    }

    private static function sameSite(string $refHost, string $selfHost): bool
    {
        $selfHost = mb_strtolower($selfHost);
        $selfHost = str_starts_with($selfHost, 'www.') ? substr($selfHost, 4) : $selfHost;

        return $refHost === $selfHost;
    }

    private static function browser(string $ua): string
    {
        return match (true) {
            str_contains($ua, 'edg/') || str_contains($ua, 'edga/') || str_contains($ua, 'edgios/') => 'Edge',
            str_contains($ua, 'opr/') || str_contains($ua, 'opera')                                   => 'Opera',
            str_contains($ua, 'samsungbrowser')                                                        => 'Samsung Internet',
            str_contains($ua, 'ucbrowser')                                                             => 'UC Browser',
            str_contains($ua, 'firefox') || str_contains($ua, 'fxios')                                 => 'Firefox',
            str_contains($ua, 'chrome') || str_contains($ua, 'crios')                                  => 'Chrome',
            str_contains($ua, 'safari')                                                                => 'Safari',
            str_contains($ua, 'msie') || str_contains($ua, 'trident')                                  => 'Internet Explorer',
            default                                                                                    => 'أخرى',
        };
    }

    private static function device(string $ua): string
    {
        if (str_contains($ua, 'ipad') || (str_contains($ua, 'tablet') && ! str_contains($ua, 'mobile'))) {
            return 'tablet';
        }
        if (str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone') || str_contains($ua, 'ipod')) {
            return 'mobile';
        }

        return 'desktop';
    }
}
