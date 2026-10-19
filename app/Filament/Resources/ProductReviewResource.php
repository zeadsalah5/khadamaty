<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductReviewResource\Pages;
use App\Models\ProductReview;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * لوحة إدارة تقييمات المنتجات (المرحلة 6D).
 *
 * العميل بيقدر يقيّم بس منتج اشتراه واتسلّم؛ التقييم بيتراجع هنا قبل النشر.
 * المعتمد بس هو اللي بيظهر على صفحة المنتج وبيدخل schema.
 */
class ProductReviewResource extends Resource
{
    protected static ?string $model = ProductReview::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static ?string $navigationGroup = 'الطلبات';

    protected static ?string $navigationLabel = 'تقييمات المنتجات';

    protected static ?string $modelLabel = 'تقييم منتج';

    protected static ?string $pluralModelLabel = 'تقييمات المنتجات';

    protected static ?int $navigationSort = 3;

    public const STATUSES = [
        'pending'  => 'مستني مراجعة',
        'approved' => 'معتمد',
        'rejected' => 'مرفوض',
    ];

    public static function getNavigationBadge(): ?string
    {
        return (string) ProductReview::query()->where('status', 'pending')->count() ?: null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('product.name_ar')->label('المنتج')->disabled(),
            Forms\Components\TextInput::make('customer.name')->label('العميل')->disabled(),
            Forms\Components\Select::make('rating')->label('التقييم')
                ->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'])->required()->native(false),
            Forms\Components\Placeholder::make('verified')->label('شراء موثّق')
                ->content(fn (?ProductReview $record) => $record?->isVerified() ? 'نعم ✓' : 'لا'),
            Forms\Components\TextInput::make('title')->label('العنوان')->columnSpanFull(),
            Forms\Components\Textarea::make('body')->label('نص التقييم')->required()->rows(4)->columnSpanFull(),
            Forms\Components\Select::make('status')->label('الحالة')
                ->options(self::STATUSES)->default('pending')->required()->native(false),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('التاريخ')->dateTime('Y-m-d')->sortable(),
                Tables\Columns\TextColumn::make('product.name_ar')->label('المنتج')->searchable()->limit(30)->weight('bold'),
                Tables\Columns\TextColumn::make('customer.name')->label('العميل')->searchable(),
                Tables\Columns\TextColumn::make('rating')->label('التقييم')
                    ->formatStateUsing(fn ($state) => str_repeat('★', (int) $state))->color('warning'),
                Tables\Columns\IconColumn::make('order_item_id')->label('شراء موثّق')
                    ->boolean()->trueIcon('heroicon-o-check-badge')->falseIcon('heroicon-o-minus'),
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
                    ->color('success')->visible(fn (ProductReview $r) => $r->status !== 'approved')
                    ->action(fn (ProductReview $r) => $r->update(['status' => 'approved'])),
                Tables\Actions\Action::make('reject')->label('رفض')->icon('heroicon-o-x-mark')
                    ->color('danger')->visible(fn (ProductReview $r) => $r->status !== 'rejected')
                    ->requiresConfirmation()
                    ->action(fn (ProductReview $r) => $r->update(['status' => 'rejected'])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageProductReviews::route('/')];
    }
}
