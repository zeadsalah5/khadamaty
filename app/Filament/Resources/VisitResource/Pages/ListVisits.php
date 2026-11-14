<?php

namespace App\Filament\Resources\VisitResource\Pages;

use App\Filament\Resources\VisitResource;
use App\Filament\Widgets\ContactClicksOverview;
use App\Filament\Widgets\VisitorsOverview;
use Filament\Resources\Pages\ListRecords;

class ListVisits extends ListRecords
{
    protected static string $resource = VisitResource::class;

    /** إحصائيات الزوّار + عدّاد الاتصالات/الواتساب فوق جدول الزيارات. */
    protected function getHeaderWidgets(): array
    {
        return [
            VisitorsOverview::class,
            ContactClicksOverview::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 3;
    }
}
