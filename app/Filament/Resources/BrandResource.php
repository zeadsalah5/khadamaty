<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Models\Brand;
use App\Models\Service;
use App\Support\ImageUploads;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'المحتوى';

    protected static ?string $navigationLabel = 'الماركات';

    protected static ?string $modelLabel = 'ماركة';

    protected static ?string $pluralModelLabel = 'الماركات';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('المحتوى الأساسي')
                ->description('الماركة ماتتنشرش غير لما النبذة والأعطال والنصايح والأنواع والخدمات والأسئلة يكونوا متملّيين.')
                ->schema([
                    Forms\Components\TextInput::make('name_ar')->label('اسم الماركة')->required()->maxLength(255),
                    Forms\Components\TextInput::make('slug')->label('الـ Slug (بالإنجليزي)')
                        ->required()->unique(ignoreRecord: true)
                        ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->maxLength(255)
                        ->helperText('بيظهر في /brands/...'),
                    Forms\Components\Textarea::make('excerpt')->label('الملخّص')->rows(2)->maxLength(300)->columnSpanFull(),
                    Forms\Components\MarkdownEditor::make('intro')->label('النبذة (مقدمة عن الماركة)')
                        ->columnSpanFull()->disableToolbarButtons(['attachFiles']),
                    Forms\Components\FileUpload::make('logo')->label('لوجو الماركة (اختياري)')
                        ->image()->disk('brands')->visibility('public')->maxSize(4096)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->saveUploadedFileUsing(fn ($file) => ImageUploads::storeWebp($file, 'brands'))
                        ->helperText('بيتحوّل تلقائياً لـ WebP. سيبه فاضي لكارت نصّي.')
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('المحتوى المنظّم')
                ->schema([
                    Forms\Components\TagsInput::make('common_faults')->label('الأعطال الشائعة')->columnSpanFull(),
                    Forms\Components\TagsInput::make('maintenance_tips')->label('نصايح الصيانة')->columnSpanFull(),
                    Forms\Components\TagsInput::make('device_types')->label('أنواع الأجهزة')->columnSpanFull(),
                    Forms\Components\Select::make('suitable_services')->label('الخدمات المناسبة')->multiple()
                        ->options(fn () => Service::query()->orderBy('name_ar')->pluck('name_ar', 'slug'))
                        ->searchable()->columnSpanFull(),
                    Forms\Components\Repeater::make('faqs')->label('الأسئلة الشائعة')
                        ->schema([
                            Forms\Components\TextInput::make('q')->label('السؤال')->required(),
                            Forms\Components\Textarea::make('a')->label('الإجابة')->rows(2)->required(),
                        ])
                        ->reorderable()->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['q'] ?? null)
                        ->addActionLabel('إضافة سؤال')->columnSpanFull(),
                ]),

            Forms\Components\Section::make('SEO (اختياري)')->collapsed()->schema([
                Forms\Components\TextInput::make('seo_title')->label('عنوان SEO')->maxLength(70),
                Forms\Components\Textarea::make('seo_description')->label('وصف SEO')->rows(2)->maxLength(170),
            ])->columns(1),

            Forms\Components\Section::make('النشر')->schema([
                Forms\Components\Placeholder::make('missing')->label('المحتوى الناقص للنشر')
                    ->content(fn (?Brand $record) => $record && ! empty($record->missingContent())
                        ? '⚠️ ناقص: ' . implode('، ', $record->missingContent())
                        : '✅ المحتوى كامل — جاهز للنشر.')->columnSpanFull(),
                Forms\Components\DateTimePicker::make('published_at')->label('تاريخ النشر'),
                Forms\Components\Toggle::make('is_published')->label('منشور')
                    ->helperText('المحتوى الناقص بيفضل مسوّدة حتى لو فعّلت ده.'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')->label('اللوجو')->disk('brands')->square(),
                Tables\Columns\TextColumn::make('name_ar')->label('الماركة')->searchable()->weight('bold'),
                Tables\Columns\IconColumn::make('is_published')->label('منشور')->boolean()->sortable(),
                Tables\Columns\TextColumn::make('missing')->label('الحالة')
                    ->state(fn (Brand $record) => empty($record->missingContent())
                        ? 'كامل' : 'ناقص: ' . implode('، ', $record->missingContent()))
                    ->badge()->color(fn (Brand $record) => empty($record->missingContent()) ? 'success' : 'warning'),
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
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
