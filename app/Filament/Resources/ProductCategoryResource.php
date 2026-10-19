<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductCategoryResource\Pages;
use App\Filament\Resources\ProductCategoryResource\RelationManagers\AttributesRelationManager;
use App\Models\ProductCategory;
use App\Support\ImageUploads;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductCategoryResource extends Resource
{
    protected static ?string $model = ProductCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'المتجر';

    protected static ?string $navigationLabel = 'أقسام المتجر';

    protected static ?string $modelLabel = 'قسم';

    protected static ?string $pluralModelLabel = 'أقسام المتجر';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('بيانات القسم')
                ->schema([
                    Forms\Components\TextInput::make('name_ar')->label('اسم القسم')->required()->maxLength(255),
                    Forms\Components\TextInput::make('slug')->label('الـ Slug (بالإنجليزي)')
                        ->required()->unique(ignoreRecord: true)
                        ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->maxLength(255)
                        ->helperText('بيظهر في /shop (صفحات الأقسام في مرحلة لاحقة).'),
                    Forms\Components\Select::make('parent_id')->label('القسم الأب (اختياري)')
                        ->relationship('parent', 'name_ar', fn ($query, ?ProductCategory $record) =>
                            $record ? $query->whereKeyNot($record->getKey()) : $query)
                        ->searchable()->preload()->placeholder('قسم رئيسي'),
                    Forms\Components\TextInput::make('position')->label('ترتيب العرض')->numeric()->default(0),
                    Forms\Components\Textarea::make('excerpt')->label('ملخّص')->rows(2)->maxLength(300)->columnSpanFull(),
                    Forms\Components\FileUpload::make('image')->label('صورة القسم (اختياري)')
                        ->image()->disk('products')->visibility('public')->maxSize(4096)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->saveUploadedFileUsing(fn ($file) => ImageUploads::storeWebp($file, 'products'))
                        ->helperText('بتتحوّل تلقائياً لـ WebP.')->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('SEO (اختياري)')->collapsed()->schema([
                Forms\Components\TextInput::make('seo_title')->label('عنوان SEO')->maxLength(70),
                Forms\Components\Textarea::make('seo_description')->label('وصف SEO')->rows(2)->maxLength(170),
            ])->columns(1),

            Forms\Components\Section::make('النشر')->schema([
                Forms\Components\DateTimePicker::make('published_at')->label('تاريخ النشر'),
                Forms\Components\Toggle::make('is_published')->label('منشور'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->label('الصورة')->disk('products')->square(),
                Tables\Columns\TextColumn::make('name_ar')->label('القسم')->searchable()->weight('bold')
                    ->description(fn (ProductCategory $record) => $record->parent?->name_ar),
                Tables\Columns\TextColumn::make('attributes_count')->label('عدد الحقول')->counts('attributes')->badge(),
                Tables\Columns\TextColumn::make('products_count')->label('المنتجات')->counts('products')->badge()->color('gray'),
                Tables\Columns\IconColumn::make('is_published')->label('منشور')->boolean()->sortable(),
            ])
            ->defaultSort('position')
            ->reorderable('position')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')->label('حالة النشر')
                    ->placeholder('الكل')->trueLabel('منشور')->falseLabel('مسوّدة'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [AttributesRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductCategories::route('/'),
            'create' => Pages\CreateProductCategory::route('/create'),
            'edit' => Pages\EditProductCategory::route('/{record}/edit'),
        ];
    }
}
