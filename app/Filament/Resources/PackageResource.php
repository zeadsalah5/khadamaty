<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PackageResource\Pages;
use App\Models\Package;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'المحتوى';

    protected static ?string $navigationLabel = 'الباقات والأسعار';

    protected static ?string $modelLabel = 'باقة';

    protected static ?string $pluralModelLabel = 'الباقات';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('اسم الباقة')->required()->maxLength(255),
            Forms\Components\TextInput::make('price')->label('السعر')
                ->helperText('نص — بيقبل أرقام عربي (مثال: ٠٠٠).'),
            Forms\Components\TextInput::make('unit')->label('الوحدة')->placeholder('ج.م / للجهاز'),
            Forms\Components\TagsInput::make('features')->label('المميزات')->columnSpanFull()
                ->helperText('كل ميزة عنصر — Enter بعد كل واحدة.'),
            Forms\Components\TextInput::make('sort')->label('الترتيب')->numeric()->default(0),
            Forms\Components\Toggle::make('featured')->label('باقة مميزة (الأكثر طلباً)'),
            Forms\Components\Toggle::make('is_active')->label('مفعّلة')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort')->label('ترتيب')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('الباقة')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('price')->label('السعر'),
                Tables\Columns\TextColumn::make('unit')->label('الوحدة')->color('gray'),
                Tables\Columns\IconColumn::make('featured')->label('مميزة')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->label('مفعّلة')->boolean(),
            ])
            ->defaultSort('sort')
            ->reorderable('sort')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManagePackages::route('/')];
    }
}
