<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendorResource\Pages;
use App\Models\Vendor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'المحتوى';

    protected static ?string $navigationLabel = 'التجار';

    protected static ?string $modelLabel = 'تاجر';

    protected static ?string $pluralModelLabel = 'التجار';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name_ar')->label('اسم التاجر')->required()->maxLength(255),
            Forms\Components\TextInput::make('slug')->label('الـ Slug (بالإنجليزي)')
                ->required()->unique(ignoreRecord: true)
                ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->maxLength(255),
            Forms\Components\Textarea::make('about')->label('نبذة')->rows(4)->columnSpanFull(),
            Forms\Components\DateTimePicker::make('published_at')->label('تاريخ النشر'),
            Forms\Components\Toggle::make('is_published')->label('منشور'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_ar')->label('التاجر')->searchable()->weight('bold'),
                Tables\Columns\IconColumn::make('is_published')->label('منشور')->boolean()->sortable(),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->color('gray')->toggleable(),
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
            'index' => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'edit' => Pages\EditVendor::route('/{record}/edit'),
        ];
    }
}
