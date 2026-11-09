<?php

namespace App\Observers;

use App\Support\ContentCache;

/**
 * observer عام: أي حفظ/حذف/استرجاع لموديل محتوى أو إعداد
 * بيمسح كاش المحتوى والإعدادات علشان التعديل يظهر فوراً في الموقع.
 * بيتسجّل لكل موديلات المحتوى في AppServiceProvider.
 */
class ContentCacheObserver
{
    public function saved(mixed $model): void
    {
        ContentCache::flush();
    }

    public function deleted(mixed $model): void
    {
        ContentCache::flush();
    }

    public function restored(mixed $model): void
    {
        ContentCache::flush();
    }
}
