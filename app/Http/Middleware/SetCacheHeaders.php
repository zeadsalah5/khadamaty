<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cache-Control خفيف للصفحات العامة اللي مفيهاش فورم.
 *
 * بيتطبّق بس على GET/HEAD اللي رجعت 200. مابيتحطّش على الرئيسية ولا الحجز
 * (علشان توكن الـ @csrf مايتخزّنش ويكسر الفورم — 419). ومابيلمسش أي رد فيه
 * Set-Cookie جديد (زي أول زيارة بتبدأ session) علشان مايتكاش في shared cache.
 */
class SetCacheHeaders
{
    public function __construct(protected int $maxAge = 300) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // معاينة مسوّدة (أدمن): noindex على مستوى الـ header + منع أي كاش عام.
        if ($request->attributes->get('draft_preview')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
            $response->headers->set('Cache-Control', 'private, no-store, max-age=0');

            return $response;
        }

        $isSafe = in_array($request->getMethod(), ['GET', 'HEAD'], true);
        $hasSetCookie = $response->headers->has('Set-Cookie');

        if ($isSafe && $response->getStatusCode() === 200 && ! $hasSetCookie) {
            $response->headers->set(
                'Cache-Control',
                "public, max-age={$this->maxAge}, s-maxage={$this->maxAge}"
            );
        }

        return $response;
    }
}
