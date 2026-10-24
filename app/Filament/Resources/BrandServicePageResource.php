<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandServicePageResource\Pages;
use App\Models\Brand;
use App\Models\BrandServicePage;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BrandServicePageResource extends Resource
{
    protected static ?string $model = BrandServicePage::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationGroup = 'المحتوى';

    protected static ?string $navigationLabel = 'صفحات (ماركة × خدمة)';

    protected static ?string $modelLabel = 'صفحة ماركة × خدمة';

    protected static ?string $pluralModelLabel = 'صفحات (ماركة × خدمة)';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('الماركة والخدمة')
                ->description('المسار: /brands/{ماركة}/{خدمة}. الزوج (ماركة × خدمة) لازم يكون فريد.')
                ->schema([
                    Forms\Components\Select::make('brand_id')->label('الماركة')->required()
                        ->options(fn () => Brand::query()->orderBy('name_ar')->pluck('name_ar', 'id'))
                        ->searchable()->preload(),
                    Forms\Components\Select::make('service_id')->label('الخدمة')->required()
                        ->options(fn () => Service::query()->orderBy('name_ar')->pluck('name_ar', 'id'))
                        ->searchable()->preload(),
                ])->columns(2),

            Forms\Components\Section::make('المحتوى الأساسي')
                ->description('الصفحة ماتتنشرش غير لما العنوان والمقدمة والأعطال والأعراض والخطوات والأسئلة يكونوا متملّيين.')
                ->schema([
                    Forms\Components\TextInput::make('h1')->label('العنوان (H1)')->required()->maxLength(255)->columnSpanFull(),
                    Forms\Components\MarkdownEditor::make('intro')->label('المقدمة (أول فقرة)')
                        ->columnSpanFull()->disableToolbarButtons(['attachFiles']),
                ]),

            Forms\Components\Section::make('المحتوى المنظّم')
                ->schema([
                    Forms\Components\TagsInput::make('common_faults')->label('الأعطال الشائعة')->columnSpanFull(),
                    Forms\Components\TagsInput::make('symptoms')->label('الأعراض')->columnSpanFull(),
                    Forms\Components\Repeater::make('steps')->label('إزاي بنشتغل (الخطوات)')
                        ->schema([
                            Forms\Components\TextInput::make('title')->label('عنوان الخطوة')->required(),
                            Forms\Components\Textarea::make('desc')->label('الوصف')->rows(2)->required(),
                        ])
                        ->reorderable()->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->addActionLabel('إضافة خطوة')->columnSpanFull(),
                    Forms\Components\Repeater::make('faqs')->label('الأسئلة الشائعة')
                        ->schema([
                            Forms\Components\TextInput::make('q')->label('السؤال')->required(),
                            Forms\Components\Textarea::make('a')->label('الإجابة')->rows(2)->required(),
                        ])
                        ->reorderable()->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['q'] ?? null)
                        ->addActionLabel('إضافة سؤال')->columnSpanFull(),
                ]),

            Forms\Components\Section::make('السعر والمدة (يملأها صاحب الموقع)')
                ->description('اختياريان ومبيمنعوش النشر. سيبهم فاضيين لحد ما تحدّد تقدير مناسب.')
                ->schema([
                    Forms\Components\TextInput::make('price_note')->label('ملاحظة السعر')->maxLength(255),
                    Forms\Components\TextInput::make('duration_note')->label('ملاحظة المدة')->maxLength(255),
                ])->columns(2),

            Forms\Components\Section::make('SEO (اختياري)')->collapsed()->schema([
                Forms\Components\TextInput::make('seo_title')->label('عنوان SEO')->maxLength(70),
                Forms\Components\Textarea::make('seo_description')->label('وصف SEO')->rows(2)->maxLength(170),
            ])->columns(1),

            Forms\Components\Section::make('النشر')->schema([
                Forms\Components\Placeholder::make('missing')->label('المحتوى الناقص للنشر')
                    ->content(fn (?BrandServicePage $record) => $record && ! empty($record->missingContent())
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
                Tables\Columns\TextColumn::make('brand.name_ar')->label('الماركة')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('service.name_ar')->label('الخدمة')->searchable(),
                Tables\Columns\IconColumn::make('is_published')->label('منشور')->boolean()->sortable(),
                Tables\Columns\TextColumn::make('missing')->label('الحالة')
                    ->state(fn (BrandServicePage $record) => empty($record->missingContent())
                        ? 'كامل' : 'ناقص: ' . implode('، ', $record->missingContent()))
                    ->badge()->color(fn (BrandServicePage $record) => empty($record->missingContent()) ? 'success' : 'warning'),
            ])
            ->defaultSort('brand_id')
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
            'index' => Pages\ListBrandServicePages::route('/'),
            'create' => Pages\CreateBrandServicePage::route('/create'),
            'edit' => Pages\EditBrandServicePage::route('/{record}/edit'),
        ];
    }
}
