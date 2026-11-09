<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\AdminOnly;
use App\Filament\Resources\RedirectResource\Pages;
use App\Models\Redirect;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RedirectResource extends Resource
{
    use AdminOnly;

    protected static ?string $model = Redirect::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?string $navigationGroup = 'الإعدادات';

    protected static ?string $navigationLabel = 'إعادة التوجيه (301)';

    protected static ?string $modelLabel = 'تحويل';

    protected static ?string $pluralModelLabel = 'إعادة التوجيه';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('from')->label('من (المسار القديم)')->required()
                ->unique(ignoreRecord: true)
                ->helperText('بدون / في الأول — مثال: services/old-slug')->columnSpanFull(),
            Forms\Components\TextInput::make('to')->label('إلى (المسار الجديد)')->required()
                ->helperText('مثال: /services/ac-maintenance')->columnSpanFull(),
            Forms\Components\Select::make('status')->label('النوع')
                ->options([301 => '301 (دائم)', 302 => '302 (مؤقت)'])->default(301)->native(false),
            Forms\Components\Toggle::make('is_active')->label('مفعّل')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('from')->label('من')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('to')->label('إلى')->searchable()->color('gray'),
                Tables\Columns\TextColumn::make('status')->label('النوع')->badge(),
                Tables\Columns\IconColumn::make('is_active')->label('مفعّل')->boolean(),
            ])
            ->defaultSort('from')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageRedirects::route('/')];
    }
}
