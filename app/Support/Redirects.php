<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * خريطة إعادة التوجيه (301/302) — دمج config/redirects.php مع جدول redirects
 * (الداتابيز بتاخد الأولوية). بتتكاش وتتمسح تلقائياً عند أي تعديل.
 * try/catch عشان ما تكسرش تسجيل الراوتس لو الجدول لسه مش موجود.
 */
class Redirects
{
    /** @return array<string, array{to:string, status:int}> مفتاحه from بدون / */
    public static function all(): array
    {
        return Cache::rememberForever('redirects.map', function () {
            $map = [];

            foreach ((array) config('redirects', []) as $from => $to) {
                $map[ltrim($from, '/')] = ['to' => $to, 'status' => 301];
            }

            try {
                foreach (\App\Models\Redirect::query()->where('is_active', true)->get() as $r) {
                    $map[ltrim($r->from, '/')] = ['to' => $r->to, 'status' => (int) $r->status];
                }
            } catch (\Throwable $e) {
                // الجدول لسه مش موجود => config بس.
            }

            return $map;
        });
    }
}
