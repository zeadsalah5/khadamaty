<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'المحتوى';

    protected static ?string $navigationLabel = 'المتجر / قطع الغيار';

    protected static ?string $modelLabel = 'منتج';

    protected static ?string $pluralModelLabel = 'المنتجات';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name_ar')->label('اسم المنتج')->required()->maxLength(255),
            Forms\Components\TextInput::make('slug')->label('الـ Slug (بالإنجليزي)')
                ->required()->unique(ignoreRecord: true)
                ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->maxLength(255),
            Forms\Components\Textarea::make('excerpt')->label('الملخّص')->rows(2)->maxLength(300)->columnSpanFull(),
            Forms\Components\TextInput::make('price')->label('السعر (اختياري)')->numeric()->minValue(0)
                ->helperText('سيبه فاضي لو مش محدد — مش هيطلع offers في الـ schema.'),
            Forms\Components\TextInput::make('sku')->label('كود المنتج (SKU)')->maxLength(100),
            Forms\Components\DateTimePicker::make('published_at')->label('تاريخ النشر'),
            Forms\Components\Toggle::make('is_published')->label('منشور'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_ar')->label('المنتج')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('price')->label('السعر')->money('EGP')->placeholder('—')->sortable(),
                Tables\Columns\TextColumn::make('sku')->label('SKU')->color('gray')->toggleable(),
                Tables\Columns\IconColumn::make('is_published')->label('منشور')->boolean()->sortable(),
            ])
            ->defaultSort('name_ar')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')->label('حالة النشر')
                    ->placeholder('الكل')->trueLabel('منشور')->falseLabel('مسوّدة'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
