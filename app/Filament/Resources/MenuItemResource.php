<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\AdminOnly;
use App\Filament\Resources\MenuItemResource\Pages;
use App\Models\MenuItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MenuItemResource extends Resource
{
    use AdminOnly;

    protected static ?string $model = MenuItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3';

    protected static ?string $navigationGroup = 'الإعدادات';

    protected static ?string $navigationLabel = 'قوائم التنقّل';

    protected static ?string $modelLabel = 'عنصر قائمة';

    protected static ?string $pluralModelLabel = 'قوائم التنقّل';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('location')->label('الموقع')
                ->options(['header' => 'الهيدر', 'footer' => 'الفوتر'])->required()->native(false),
            Forms\Components\TextInput::make('label')->label('النص')->required()->maxLength(255),
            Forms\Components\TextInput::make('url')->label('الرابط')->required()
                ->helperText('مسار نسبي (/services) أو رابط كامل.')->columnSpanFull(),
            Forms\Components\TextInput::make('sort')->label('الترتيب')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('مفعّل')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('location')->label('الموقع')
                    ->formatStateUsing(fn ($state) => $state === 'header' ? 'الهيدر' : 'الفوتر')->badge(),
                Tables\Columns\TextColumn::make('sort')->label('ترتيب')->sortable(),
                Tables\Columns\TextColumn::make('label')->label('النص')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('url')->label('الرابط')->color('gray'),
                Tables\Columns\IconColumn::make('is_active')->label('مفعّل')->boolean(),
            ])
            ->defaultSort('sort')
            ->reorderable('sort')
            ->filters([
                Tables\Filters\SelectFilter::make('location')->label('الموقع')
                    ->options(['header' => 'الهيدر', 'footer' => 'الفوتر']),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageMenuItems::route('/')];
    }
}
