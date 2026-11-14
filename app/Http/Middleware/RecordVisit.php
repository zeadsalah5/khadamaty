<?php

namespace App\Http\Middleware;

use App\Models\Visit;
use App\Support\VisitClassifier;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

/**
 * بيسجّل كل زيارة صفحة (GET) في جدول visits — بعد إرسال الرد (terminate)
 * عشان مايبطّأش الصفحة للزائر. بيتجاهل الأدمن والـ assets وطلبات الـ API،
 * وأي خطأ في التسجيل مابيكسرش الموقع (try/catch).
 */
class RecordVisit
{
    /** مسارات/بادئات بتتجاهل (مش صفحات محتوى). */
    private const SKIP_PREFIXES = [
        'admin', 'livewire', 'api', 'up', 'build', 'images', 'storage',
        'css', 'js', 'fonts', 'vendor', 'payments',
    ];

    /** امتدادات ملفات (assets) بتتجاهل. */
    private const SKIP_EXTENSIONS = [
        'css', 'js', 'map', 'ico', 'png', 'jpg', 'jpeg', 'webp', 'gif', 'svg',
        'woff', 'woff2', 'ttf', 'eot', 'xml', 'txt', 'json', 'webmanifest', 'pdf',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        try {
            if (! $this->shouldRecord($request, $response)) {
                return;
            }

            // أمان: لو الجدول لسه متعملّهوش migrate (نشر جزئي) مانكسرش الموقع.
            if (! Schema::hasTable('visits')) {
                return;
            }

            $data = VisitClassifier::classify(
                $request->userAgent(),
                $request->headers->get('referer'),
                $request->getHost(),
            );

            Visit::create(array_merge($data, [
                'ip'         => (string) $request->ip(),
                'path'       => mb_substr('/' . ltrim($request->path(), '/'), 0, 512),
                'method'     => $request->getMethod(),
                'referer'    => mb_substr((string) $request->headers->get('referer'), 0, 2000) ?: null,
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 2000) ?: null,
            ]));
        } catch (\Throwable $e) {
            // التحليلات مش حرجة — ما نكسرش الطلب لو فشل التسجيل.
            report($e);
        }
    }

    private function shouldRecord(Request $request, Response $response): bool
    {
        if ($request->getMethod() !== 'GET') {
            return false;
        }
        if ($request->expectsJson() || $request->ajax()) {
            return false;
        }

        // بنسجّل الصفحات الناجحة (2xx) والـ 404 (مفيد نعرف البوتات بتدوّر على إيه)،
        // لكن مش التحويلات (3xx) عشان مانعدّش الزيارة مرتين.
        $status = $response->getStatusCode();
        if ($status >= 300 && $status < 400) {
            return false;
        }

        $path = strtolower(trim($request->path(), '/'));
        if ($path === '') {
            return true; // الصفحة الرئيسية
        }

        $first = explode('/', $path)[0];
        if (in_array($first, self::SKIP_PREFIXES, true)) {
            return false;
        }

        $ext = pathinfo($path, PATHINFO_EXTENSION);
        if ($ext !== '' && in_array($ext, self::SKIP_EXTENSIONS, true)) {
            return false;
        }

        return true;
    }
}
