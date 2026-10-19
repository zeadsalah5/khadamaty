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
        // الثقة في البروكسيهات (X-Forwarded-Proto/Host) علشان الـ HTTPS/host يتقرّوا صح ورا proxy.
        $middleware->trustProxies(at: '*');

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
