<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Support\Store\ProductsExporter;
use App\Support\Store\ProductsImporter;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            // استيراد Excel (أدمن فقط) — صفوف جديدة draft، تحديث بالـ sku، تقرير أخطاء لكل صف.
            Actions\Action::make('import')
                ->label('استيراد Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->visible(fn () => auth()->user()?->isAdmin() ?? false)
                ->form([
                    Forms\Components\FileUpload::make('file')->label('ملف Excel (.xlsx)')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'text/csv', 'application/csv',
                        ])
                        ->storeFiles(false)->required()
                        ->helperText('نزّل القالب الأول لو مش متأكد من الأعمدة.'),
                ])
                ->action(function (array $data): void {
                    $report = ProductsImporter::import($data['file']->getRealPath());
                    self::notifyImportReport($report);
                }),

            // تصدير كل المنتجات.
            Actions\Action::make('export')
                ->label('تصدير Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn () => response()->download(ProductsExporter::export(), 'products.xlsx')->deleteFileAfterSend()),

            // تحميل القالب النموذجي.
            Actions\Action::make('template')
                ->label('تحميل القالب')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(fn () => response()->download(ProductsExporter::template(), 'products-template.xlsx')->deleteFileAfterSend()),
        ];
    }

    /** Notification بملخّص الاستيراد + أخطاء كل صف. */
    protected static function notifyImportReport(array $report): void
    {
        $summary = "تم: {$report['created']} جديد، {$report['updated']} تحديث، {$report['skipped']} متخطّى.";

        $lines = [];
        foreach (array_slice($report['errors'], 0, 15) as $err) {
            $lines[] = "صف {$err['row']}: " . implode(' | ', $err['messages']);
        }
        if (count($report['errors']) > 15) {
            $lines[] = '… و' . (count($report['errors']) - 15) . ' خطأ إضافي.';
        }

        $hasErrors = ! empty($report['errors']);

        $body = $summary;
        if ($lines) {
            $body .= "\n\nالأخطاء:\n" . implode("\n", $lines);
        }
        if (! empty($report['warnings'])) {
            $body .= "\n\nتنبيهات: " . implode(' • ', array_slice($report['warnings'], 0, 5));
        }

        Notification::make()
            ->title($hasErrors ? 'الاستيراد خلص مع أخطاء' : 'الاستيراد تم بنجاح')
            ->body($body)
            ->status($hasErrors ? 'warning' : 'success')
            ->persistent()
            ->send();
    }
}
