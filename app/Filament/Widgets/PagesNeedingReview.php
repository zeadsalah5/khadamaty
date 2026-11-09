<?php

namespace App\Filament\Widgets;

use App\Models\Area;
use App\Models\AreaServicePage;
use App\Models\Brand;
use App\Models\BrandServicePage;
use App\Models\Post;
use App\Models\Service;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * widget: عدد الصفحات المحتاجة مراجعة لكل نوع (مسودة أو محتاجة مراجعة الفني —
 * أي اللي لسه مش «جاهزة للنشر»). بيظهر فوق لوحة الداشبورد.
 */
class PagesNeedingReview extends BaseWidget
{
    protected static ?int $sort = -3;

    protected function getStats(): array
    {
        $types = [
            'خدمات'      => Service::class,
            'ماركات'     => Brand::class,
            'مناطق'      => Area::class,
            'مقالات'     => Post::class,
            'ماركة×خدمة' => BrandServicePage::class,
            'منطقة×خدمة' => AreaServicePage::class,
        ];

        $stats = [];

        foreach ($types as $label => $class) {
            // 'ready' = HasReviewWorkflow::STATUS_READY (literal — ثابت trait مينفعش بالاسم).
            $pending = $class::query()
                ->where('content_status', '!=', 'ready')
                ->count();

            $stats[] = Stat::make('محتاجة مراجعة — ' . $label, $pending)
                ->description($pending > 0 ? 'مسودات / محتاجة مراجعة الفني' : 'كله جاهز للنشر')
                ->descriptionIcon($pending > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($pending > 0 ? 'warning' : 'success');
        }

        return $stats;
    }
}
