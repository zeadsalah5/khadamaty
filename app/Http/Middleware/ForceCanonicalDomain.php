<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * يجبر النطاق الأساسي: non-www + HTTPS على config('site.canonical_host').
 *
 * بيتعطّل تماماً لو:
 *  - canonical_host مش متحدد (فاضي / لسه placeholder)، أو
 *  - البيئة local.
 * وبيطبّق redirect على GET/HEAD بس. بيعتمد على trusted proxies
 * (X-Forwarded-Proto/Host — متظبطة في bootstrap/app.php) علشان ميعملش loop ورا proxy.
 */
class ForceCanonicalDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = trim((string) config('site.canonical_host'));

        $disabled = $host === ''
            || app()->environment('local')
            || ! in_array($request->getMethod(), ['GET', 'HEAD'], true);

        if ($disabled) {
            return $next($request);
        }

        $needsRedirect = $request->getHost() !== $host || ! $request->isSecure();

        if ($needsRedirect) {
            $target = 'https://' . $host . $request->getRequestUri();

            return redirect()->away($target, 301);
        }

        return $next($request);
    }
}
