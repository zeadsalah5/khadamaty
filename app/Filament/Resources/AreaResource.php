<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AreaResource\Pages;
use App\Models\Area;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AreaResource extends Resource
{
    protected static ?string $model = Area::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'المحتوى';

    protected static ?string $navigationLabel = 'المناطق';

    protected static ?string $modelLabel = 'منطقة';

    protected static ?string $pluralModelLabel = 'المناطق';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('المحتوى الأساسي')
                ->description('المنطقة ماتتنشرش غير لما المقدمة والمشاكل والأحياء والخدمات والأسئلة يكونوا متملّيين.')
                ->schema([
                    Forms\Components\TextInput::make('name_ar')->label('اسم المنطقة')->required()->maxLength(255),
                    Forms\Components\TextInput::make('slug')->label('الـ Slug (بالإنجليزي)')
                        ->required()->unique(ignoreRecord: true)
                        ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->maxLength(255)
                        ->helperText('بيظهر في /areas/...'),
                    Forms\Components\Textarea::make('excerpt')->label('الملخّص')->rows(2)->maxLength(300)->columnSpanFull(),
                    Forms\Components\MarkdownEditor::make('intro')->label('المقدمة (طبيعة المنطقة)')
                        ->columnSpanFull()->disableToolbarButtons(['attachFiles']),
                    Forms\Components\TextInput::make('eta_note')->label('زمن الوصول التقريبي (اختياري)')->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('المحتوى المنظّم')
                ->schema([
                    Forms\Components\TagsInput::make('common_problems')->label('المشاكل الشائعة في المنطقة')->columnSpanFull(),
                    Forms\Components\TagsInput::make('neighborhoods')->label('أشهر الأحياء')->columnSpanFull(),
                    Forms\Components\Select::make('top_services')->label('الخدمات الأكثر طلباً')->multiple()
                        ->options(fn () => Service::query()->orderBy('name_ar')->pluck('name_ar', 'slug'))
                        ->searchable()->columnSpanFull()
                        ->helperText('بالترتيب ده هتظهر في صفحة المنطقة.'),
                    Forms\Components\Repeater::make('faqs')->label('الأسئلة الشائعة')
                        ->schema([
                            Forms\Components\TextInput::make('q')->label('السؤال')->required(),
                            Forms\Components\Textarea::make('a')->label('الإجابة')->rows(2)->required(),
                        ])
                        ->reorderable()->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['q'] ?? null)
                        ->addActionLabel('إضافة سؤال')->columnSpanFull(),
                ]),

            Forms\Components\Section::make('التوصيل (المتجر)')
                ->description('يتحكم في إتاحة المنطقة في صفحة الدفع ورسوم التوصيل عليها.')
                ->schema([
                    Forms\Components\Toggle::make('delivers')->label('التوصيل متاح لهذه المنطقة')
                        ->helperText('لو مقفول، المنطقة متظهرش في الـ checkout.'),
                    Forms\Components\TextInput::make('delivery_fee')->label('رسوم التوصيل (ج.م)')
                        ->numeric()->minValue(0)->default(0)
                        ->helperText('0 = توصيل مجاني.'),
                    Forms\Components\TextInput::make('delivery_note')->label('ملاحظة التوصيل (اختياري)')
                        ->maxLength(255)->columnSpanFull()
                        ->helperText('مثال: التوصيل خلال ٢٤ ساعة.'),
                ])->columns(2),

            Forms\Components\Section::make('SEO (اختياري)')->collapsed()->schema([
                Forms\Components\TextInput::make('seo_title')->label('عنوان SEO')->maxLength(70),
                Forms\Components\Textarea::make('seo_description')->label('وصف SEO')->rows(2)->maxLength(170),
            ])->columns(1),

            \App\Filament\Support\ReviewFields::section(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_ar')->label('المنطقة')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('content_status')->label('المراجعة')->badge()
                    ->formatStateUsing(fn (string $state) => Area::reviewStatusLabels()[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        Area::STATUS_READY => 'success',
                        Area::STATUS_NEEDS_REVIEW => 'warning',
                        default => 'gray',
                    })->sortable(),
                Tables\Columns\IconColumn::make('is_published')->label('منشور')->boolean()->sortable(),
                Tables\Columns\IconColumn::make('delivers')->label('توصيل')->boolean()->toggleable(),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->color('gray')->toggleable(),
            ])
            ->defaultSort('name_ar')
            ->filters([
                Tables\Filters\SelectFilter::make('content_status')->label('حالة المراجعة')
                    ->options(Area::reviewStatusLabels()),
                Tables\Filters\TernaryFilter::make('is_published')->label('حالة النشر')
                    ->placeholder('الكل')->trueLabel('منشور')->falseLabel('مسوّدة'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAreas::route('/'),
            'create' => Pages\CreateArea::route('/create'),
            'edit' => Pages\EditArea::route('/{record}/edit'),
        ];
    }
}
