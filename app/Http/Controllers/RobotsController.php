<?php

namespace App\Http\Controllers;

use App\Support\Seo;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $base = rtrim(app(Seo::class)->baseUrl(), '/');
        $lines = ['User-agent: *'];

        if (app()->environment('production')) {
            // إنتاج: اسمح بكل حاجة ما عدا لوحات التحكم وصفحات المعاملات.
            $lines[] = 'Disallow: /admin';
            $lines[] = 'Disallow: /vendor-dashboard';
            $lines[] = 'Disallow: /cart';
            $lines[] = 'Disallow: /checkout';
            $lines[] = 'Disallow: /order/';
            $lines[] = 'Disallow: /track';
            // حسابات العملاء والمفضلة والمقارنة والدخول (المرحلة 6D) — كلها noindex.
            $lines[] = 'Disallow: /account';
            $lines[] = 'Disallow: /wishlist';
            $lines[] = 'Disallow: /compare';
            $lines[] = 'Disallow: /login';
            $lines[] = 'Disallow: /register';
            $lines[] = '';
            // زواحف الـ AI (GPTBot, OAI-SearchBot, ChatGPT-User, ClaudeBot, PerplexityBot,
            // Google-Extended, Applebot-Extended, Amazonbot, FacebookBot…) مرحّب بيها —
            // بتورث قاعدة «*» فوق (بتزحف كل المحتوى وتتمنع من صفحات الإدارة بس).
            // إعلان صريح بالموافقة على استخدام الـ AI (معيار Content-Signal — IETF draft).
            $lines[] = 'Content-Signal: ai-train=yes, search=yes, ai-retrieval=yes';
            $lines[] = '';
            $lines[] = 'Sitemap: ' . $base . '/sitemap.xml';
        } else {
            // غير الإنتاج: احجب الموقع كله من الأرشفة.
            $lines[] = 'Disallow: /';
        }

        return response(implode("\n", $lines) . "\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
