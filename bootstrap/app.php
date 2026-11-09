<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // الثقة في البروكسيهات (X-Forwarded-For/Host/Proto) علشان الـ IP/HTTPS/host يتقرّوا صح ورا proxy.
        // المصدر: متغير البيئة TRUSTED_PROXIES (قايمة IPs/CIDR مفصولة بفاصلة). الافتراضي: لا شيء.
        // الثقة في مصدر غير موثوق بتسمح بانتحال الـ IP (اللي عليه الـ throttle) والـ Host،
        // فممنوع '*' في production — لو اتحطّت بتتجاهل.
        $trustedProxies = trim((string) env('TRUSTED_PROXIES', ''));

        if ($trustedProxies !== '') {
            if ($trustedProxies === '*') {
                if (! app()->environment('production')) {
                    $middleware->trustProxies(at: '*'); // مسموح بس خارج الإنتاج (تطوير/دوكر)
                }
            } else {
                $proxies = array_values(array_filter(array_map('trim', explode(',', $trustedProxies))));

                if ($proxies !== []) {
                    $middleware->trustProxies(at: $proxies);
                }
            }
        }

        // إجبار النطاق الأساسي (non-www + HTTPS) — متحكوم بالشروط جوّه الكلاس نفسه.
        $middleware->web(append: [
            \App\Http\Middleware\ForceCanonicalDomain::class,
        ]);

        // webhook الدفع server-to-server — مستثنى من CSRF (محمي بالتحقق من HMAC بدلاً منه).
        $middleware->validateCsrfTokens(except: [
            'payments/*/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
