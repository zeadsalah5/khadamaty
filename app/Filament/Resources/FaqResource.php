<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaqResource\Pages;
use App\Models\Faq;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationGroup = 'المحتوى';

    protected static ?string $navigationLabel = 'الأسئلة الشائعة';

    protected static ?string $modelLabel = 'سؤال';

    protected static ?string $pluralModelLabel = 'الأسئلة الشائعة';

    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('question')->label('السؤال')->required()->columnSpanFull(),
            Forms\Components\Textarea::make('answer')->label('الإجابة')->required()->rows(3)->columnSpanFull(),
            Forms\Components\TextInput::make('sort')->label('الترتيب')->numeric()->default(0),
            Forms\Components\Toggle::make('show_on_home')->label('يظهر على الرئيسية')->default(true),
            Forms\Components\Toggle::make('is_active')->label('مفعّل')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort')->label('ترتيب')->sortable(),
                Tables\Columns\TextColumn::make('question')->label('السؤال')->searchable()->limit(60)->weight('bold'),
                Tables\Columns\IconColumn::make('show_on_home')->label('الرئيسية')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->label('مفعّل')->boolean(),
            ])
            ->defaultSort('sort')
            ->reorderable('sort')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageFaqs::route('/')];
    }
}
