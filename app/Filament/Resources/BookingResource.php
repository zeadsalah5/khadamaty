<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationGroup = 'الطلبات';

    protected static ?string $navigationLabel = 'الحجوزات';

    protected static ?string $modelLabel = 'حجز';

    protected static ?string $pluralModelLabel = 'الحجوزات';

    protected static ?int $navigationSort = 1;

    /** حالات الحجز. */
    public const STATUSES = [
        'new' => 'جديد',
        'contacted' => 'تم التواصل',
        'done' => 'تمّ',
        'cancelled' => 'ملغي',
    ];

    public static function getNavigationBadge(): ?string
    {
        return (string) Booking::query()->where('status', 'new')->count() ?: null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('الاسم')->disabled(),
            Forms\Components\TextInput::make('phone')->label('التليفون')->disabled(),
            Forms\Components\TextInput::make('area')->label('المنطقة')->disabled(),
            Forms\Components\TextInput::make('service')->label('الخدمة')->disabled(),
            Forms\Components\TextInput::make('brand')->label('الماركة')->disabled(),
            Forms\Components\TextInput::make('appliance')->label('الجهاز')->disabled(),
            Forms\Components\TextInput::make('source_url')->label('صفحة المصدر')->disabled()->columnSpanFull(),
            Forms\Components\TextInput::make('devices_count')->label('عدد الأجهزة')->disabled(),
            Forms\Components\TextInput::make('preferred_time')->label('الوقت المفضّل')->disabled(),
            Forms\Components\Textarea::make('notes')->label('ملاحظات العميل')->disabled()->rows(2)->columnSpanFull(),

            Forms\Components\Select::make('status')->label('الحالة')
                ->options(self::STATUSES)->required()->native(false),
            Forms\Components\Textarea::make('admin_notes')->label('ملاحظات داخلية (مش بتظهر للعميل)')
                ->rows(3)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('التاريخ')->dateTime('Y-m-d H:i')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('الاسم')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('phone')->label('التليفون')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('service')->label('الخدمة')->searchable(),
                Tables\Columns\TextColumn::make('brand')->label('الماركة')->searchable()->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('area')->label('المنطقة')->searchable(),
                Tables\Columns\TextColumn::make('devices_count')->label('عدد')->alignCenter(),
                Tables\Columns\SelectColumn::make('status')->label('الحالة')
                    ->options(self::STATUSES)->selectablePlaceholder(false),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('الحالة')->options(self::STATUSES),
                Tables\Filters\SelectFilter::make('service')->label('الخدمة')
                    ->options(fn () => Booking::query()->distinct()->pluck('service', 'service')->filter()->all()),
                Tables\Filters\SelectFilter::make('brand')->label('الماركة')
                    ->options(fn () => Booking::query()->distinct()->pluck('brand', 'brand')->filter()->all()),
                Tables\Filters\SelectFilter::make('area')->label('المنطقة')
                    ->options(fn () => Booking::query()->distinct()->pluck('area', 'area')->filter()->all()),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('تصدير Excel (CSV)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn () => static::exportCsv()),
            ])
            ->actions([Tables\Actions\EditAction::make()->label('عرض/تعديل')])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    /** تصدير الحجوزات كـ CSV (بـ BOM عشان Excel يقرا العربي صح). */
    protected static function exportCsv(): StreamedResponse
    {
        $headers = ['التاريخ', 'الاسم', 'التليفون', 'المنطقة', 'الخدمة', 'الماركة', 'الجهاز', 'عدد الأجهزة', 'الوقت المفضّل', 'الحالة', 'صفحة المصدر', 'ملاحظات العميل', 'ملاحظات داخلية'];

        return response()->streamDownload(function () use ($headers) {
            $out = fopen('php://output', 'w');
            fprintf($out, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($out, $headers);

            Booking::query()->orderByDesc('created_at')->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $b) {
                    fputcsv($out, [
                        optional($b->created_at)->format('Y-m-d H:i'),
                        $b->name, $b->phone, $b->area, $b->service, $b->brand, $b->appliance,
                        $b->devices_count, $b->preferred_time,
                        self::STATUSES[$b->status] ?? $b->status,
                        $b->source_url, $b->notes, $b->admin_notes,
                    ]);
                }
            });

            fclose($out);
        }, 'bookings-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageBookings::route('/')];
    }
}
