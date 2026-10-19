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
