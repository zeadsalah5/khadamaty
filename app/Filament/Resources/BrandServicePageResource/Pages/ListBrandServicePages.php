<?php

namespace App\Filament\Resources\BrandServicePageResource\Pages;

use App\Filament\Resources\BrandServicePageResource;
use App\Models\Brand;
use App\Models\Service;
use App\Support\BrandServicePageGenerator;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListBrandServicePages extends ListRecords
{
    protected static string $resource = BrandServicePageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // إنشاء صفحات (ماركة × خدمة) بالجملة من قالب — مسودات بس للتركيبات الناقصة.
            Actions\Action::make('generate')
                ->label('إنشاء صفحات (ماركة × خدمة)')
                ->icon('heroicon-o-squares-plus')
                ->color('gray')
                ->modalHeading('توليد صفحات من قالب')
                ->modalDescription('بيتعمل مسودة لكل تركيبة غير موجودة (من غير تكرار). المحتوى مؤقت فيه TODO '
                    . 'والصفحة بتفضل مسودة لحد ما الفني يراجعها وينشرها.')
                ->form([
                    Forms\Components\Select::make('brands')
                        ->label('الماركات')
                        ->multiple()->required()->searchable()->preload()
                        ->options(fn () => Brand::query()->orderBy('name_ar')->pluck('name_ar', 'id')),
                    Forms\Components\Select::make('services')
                        ->label('الخدمات')
                        ->multiple()->required()->searchable()->preload()
                        ->options(fn () => Service::query()->orderBy('name_ar')->pluck('name_ar', 'id')),
                ])
                ->action(function (array $data) {
                    $result = app(BrandServicePageGenerator::class)
                        ->generate($data['brands'], $data['services']);

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
