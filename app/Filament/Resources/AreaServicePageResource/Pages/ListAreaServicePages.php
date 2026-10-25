<?php

namespace App\Filament\Resources\AreaServicePageResource\Pages;

use App\Filament\Resources\AreaServicePageResource;
use App\Models\Area;
use App\Models\Service;
use App\Support\AreaServicePageGenerator;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListAreaServicePages extends ListRecords
{
    protected static string $resource = AreaServicePageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // توليد صفحات (منطقة × خدمة) من قالب — حد أقصى 5 تركيبات، مسودات للناقص فقط.
            Actions\Action::make('generate')
                ->label('إنشاء صفحات (منطقة × خدمة)')
                ->icon('heroicon-o-squares-plus')
                ->color('gray')
                ->modalHeading('توليد صفحات من قالب')
                ->modalDescription('بحد أقصى ' . AreaServicePageGenerator::MAX_COMBINATIONS
                    . ' تركيبات في المرة (مفيش توليد بالجملة). بيتعمل مسودة لكل تركيبة غير موجودة، '
                    . 'المحتوى مؤقت فيه TODO لحد ما الفني يراجعها وينشرها.')
                ->form([
                    Forms\Components\Select::make('areas')
                        ->label('المناطق')
                        ->multiple()->required()->searchable()->preload()
                        ->options(fn () => Area::query()->orderBy('name_ar')->pluck('name_ar', 'id')),
                    Forms\Components\Select::make('services')
                        ->label('الخدمات')
                        ->multiple()->required()->searchable()->preload()
                        ->options(fn () => Service::query()->orderBy('name_ar')->pluck('name_ar', 'id')),
                ])
                ->action(function (array $data) {
                    try {
                        $result = app(AreaServicePageGenerator::class)
                            ->generate($data['areas'], $data['services']);
                    } catch (\InvalidArgumentException $e) {
                        Notification::make()->danger()->title('تعدّى الحد الأقصى')->body($e->getMessage())->send();

                        return;
                    }

                    Notification::make()
                        ->success()
                        ->title('تم توليد الصفحات')
                        ->body("اتعمل {$result['created']} مسودة جديدة، واتخطّى {$result['skipped']} (موجودة قبل كده).")
                        ->send();
                }),

            Actions\CreateAction::make(),
        ];
    }
}
