<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostCategoryResource\Pages;
use App\Models\PostCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PostCategoryResource extends Resource
{
    protected static ?string $model = PostCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'المدونة';

    protected static ?string $navigationLabel = 'التصنيفات';

    protected static ?string $modelLabel = 'تصنيف';

    protected static ?string $pluralModelLabel = 'التصنيفات';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name_ar')
                ->label('اسم التصنيف')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('slug')
                ->label('الـ Slug (بالإنجليزي)')
                ->helperText('حروف إنجليزي صغيرة وأرقام وشرطات بس — بيظهر في /blog/category/...')
                ->required()
                ->unique(ignoreRecord: true)
                ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                ->maxLength(255),

            Forms\Components\Textarea::make('excerpt')
                ->label('وصف قصير')
                ->rows(2)
                ->maxLength(300)
                ->columnSpanFull(),

            Forms\Components\TextInput::make('sort')
                ->label('ترتيب العرض')
                ->numeric()
                ->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort')->label('ترتيب')->sortable(),
                Tables\Columns\TextColumn::make('name_ar')->label('الاسم')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->color('gray'),
                Tables\Columns\TextColumn::make('posts_count')
                    ->label('عدد المقالات')
                    ->counts('posts'),
            ])
            ->defaultSort('sort')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPostCategories::route('/'),
            'create' => Pages\CreatePostCategory::route('/create'),
            'edit' => Pages\EditPostCategory::route('/{record}/edit'),
        ];
    }
}
