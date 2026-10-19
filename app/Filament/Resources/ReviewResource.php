<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'الطلبات';

    protected static ?string $navigationLabel = 'التقييمات';

    protected static ?string $modelLabel = 'تقييم';

    protected static ?string $pluralModelLabel = 'التقييمات';

    protected static ?int $navigationSort = 2;

    public const STATUSES = [
        'pending' => 'مستني مراجعة',
        'approved' => 'معتمد',
        'rejected' => 'مرفوض',
    ];

    public static function getNavigationBadge(): ?string
    {
        return (string) Review::query()->where('status', 'pending')->count() ?: null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('اسم العميل')->required(),
            Forms\Components\TextInput::make('area')->label('المنطقة'),
            Forms\Components\TextInput::make('service')->label('الخدمة'),
            Forms\Components\Select::make('rating')->label('التقييم')
                ->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'])->default(5)->required()->native(false),
            Forms\Components\Textarea::make('body')->label('نص التقييم')->required()->rows(3)->columnSpanFull(),
            Forms\Components\Select::make('status')->label('الحالة')
                ->options(self::STATUSES)->default('pending')->required()->native(false),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('التاريخ')->dateTime('Y-m-d')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('العميل')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('rating')->label('التقييم')
                    ->formatStateUsing(fn ($state) => str_repeat('★', (int) $state))->color('warning'),
                Tables\Columns\TextColumn::make('service')->label('الخدمة')->toggleable(),
                Tables\Columns\TextColumn::make('body')->label('النص')->limit(50),
                Tables\Columns\TextColumn::make('status')->label('الحالة')->badge()
                    ->formatStateUsing(fn ($state) => self::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'approved' => 'success', 'rejected' => 'danger', default => 'warning',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('الحالة')->options(self::STATUSES),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')->label('اعتماد')->icon('heroicon-o-check')
                    ->color('success')->visible(fn (Review $r) => $r->status !== 'approved')
                    ->action(fn (Review $r) => $r->update(['status' => 'approved'])),
                Tables\Actions\Action::make('reject')->label('رفض')->icon('heroicon-o-x-mark')
                    ->color('danger')->visible(fn (Review $r) => $r->status !== 'rejected')
                    ->requiresConfirmation()
                    ->action(fn (Review $r) => $r->update(['status' => 'rejected'])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageReviews::route('/')];
    }
}
