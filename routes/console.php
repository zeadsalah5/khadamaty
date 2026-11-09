<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// المرحلة 6E — إلغاء الطلبات الأونلاين غير المدفوعة بعد المهلة وإرجاع المخزون.
Schedule::command('payments:expire-pending')->everyFiveMinutes()->withoutOverlapping();
