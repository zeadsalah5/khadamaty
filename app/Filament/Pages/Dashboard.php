<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\PagesNeedingReview;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\AccountWidget;

/**
 * لوحة التحكم الرئيسية — خفيفة: ترحيب + «محتاجة مراجعة» بس.
 * إحصائيات الزوّار والاتصالات اتنقلت لصفحة «الزوّار» (التحليلات) عشان
 * الرئيسية تفضل سريعة ومتزحمش بالكويريز.
 */
class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            AccountWidget::class,
            PagesNeedingReview::class,
        ];
    }
}
