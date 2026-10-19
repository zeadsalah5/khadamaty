<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document';

    protected static ?string $navigationGroup = 'المحتوى';

    protected static ?string $navigationLabel = 'الصفحات الثابتة';

    protected static ?string $modelLabel = 'صفحة';

    protected static ?string $pluralModelLabel = 'الصفحات الثابتة';

    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->label('العنوان')->required()->maxLength(255),
            Forms\Components\TextInput::make('slug')->label('الـ Slug (بالإنجليزي)')
                ->required()->unique(ignoreRecord: true)
                ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->maxLength(255)
                ->helperText('بيظهر في /pages/...'),
            Forms\Components\MarkdownEditor::make('body')->label('المحتوى (Markdown)')
                ->columnSpanFull()->disableToolbarButtons(['attachFiles']),
            Forms\Components\TextInput::make('seo_title')->label('عنوان SEO')->maxLength(70),
            Forms\Components\Textarea::make('seo_description')->label('وصف SEO')->rows(2)->maxLength(170),
            Forms\Components\Toggle::make('show_in_footer')->label('يظهر في الفوتر')->default(true),
            Forms\Components\Toggle::make('is_published')->label('منشور')
                ->helperText('غير المنشور بيرجّع 404 ومش بيظهر في الفوتر.'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('العنوان')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->color('gray'),
                Tables\Columns\IconColumn::make('is_published')->label('منشور')->boolean(),
                Tables\Columns\IconColumn::make('show_in_footer')->label('بالفوتر')->boolean(),
            ])
            ->defaultSort('title')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManagePages::route('/')];
    }
}
