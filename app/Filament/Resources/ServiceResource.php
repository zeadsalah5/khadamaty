<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use App\Support\Settings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'المحتوى';

    protected static ?string $navigationLabel = 'الخدمات';

    protected static ?string $modelLabel = 'خدمة';

    protected static ?string $pluralModelLabel = 'الخدمات';

    protected static ?int $navigationSort = 1;

    /** أيقونات service-icon المتاحة. */
    public const ICONS = [
        'maintenance' => 'صيانة', 'cleaning' => 'تنظيف', 'contract' => 'عقود',
        'install' => 'تركيب', 'move' => 'نقل', 'pipes' => 'مواسير',
        'freon' => 'فريون', 'repair' => 'إصلاح', 'diagnose' => 'تشخيص',
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('المحتوى الأساسي')
                ->description('الخدمة مايتنشرش غير لما المقدمة والأعراض والخطوات و«بتشمل» والأسئلة يكونوا متملّيين.')
                ->schema([
                    Forms\Components\TextInput::make('name_ar')
                        ->label('اسم الخدمة')->required()->maxLength(255)->columnSpanFull(),

                    Forms\Components\TextInput::make('slug')
                        ->label('الـ Slug (بالإنجليزي)')
                        ->helperText('حروف صغيرة وأرقام وشرطات — بيظهر في /services/...')
                        ->required()->unique(ignoreRecord: true)
                        ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->maxLength(255),

                    Forms\Components\Select::make('icon')
                        ->label('الأيقونة')->options(self::ICONS)->native(false),

                    Forms\Components\Select::make('booking_service')
                        ->label('خدمة الحجز المرتبطة')
                        ->options(fn () => array_combine(
                            (array) Settings::get('booking_services'),
                            (array) Settings::get('booking_services'),
                        ))
                        ->helperText('بتتحدد تلقائياً في فورم الحجز من زرار «احجز».')
                        ->native(false),

                    Forms\Components\Textarea::make('excerpt')
                        ->label('الملخّص')->rows(2)->maxLength(300)->columnSpanFull()
                        ->helperText('بيظهر في الكارت ووصف محركات البحث.'),

                    Forms\Components\MarkdownEditor::make('body')
                        ->label('المقدمة (متن)')->columnSpanFull()
                        ->disableToolbarButtons(['attachFiles']),
                ])->columns(2),

            Forms\Components\Section::make('المحتوى المنظّم')
                ->schema([
                    Forms\Components\TagsInput::make('symptoms')
                        ->label('الأعراض (اللي بتقول إنك محتاج الخدمة)')
                        ->helperText('كل عرض عنصر — Enter بعد كل واحد.')->columnSpanFull(),

                    Forms\Components\TagsInput::make('includes')
                        ->label('الخدمة بتشمل')
                        ->helperText('كل بند عنصر.')->columnSpanFull(),

                    Forms\Components\Repeater::make('steps')
                        ->label('خطوات الشغل')
                        ->schema([
                            Forms\Components\TextInput::make('title')->label('العنوان')->required(),
                            Forms\Components\Textarea::make('desc')->label('الوصف')->rows(2)->required(),
                        ])
                        ->columns(2)->reorderable()->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->addActionLabel('إضافة خطوة')->columnSpanFull(),

                    Forms\Components\Repeater::make('faqs')
                        ->label('الأسئلة الشائعة')
                        ->schema([
                            Forms\Components\TextInput::make('q')->label('السؤال')->required(),
                            Forms\Components\Textarea::make('a')->label('الإجابة')->rows(2)->required(),
                        ])
                        ->reorderable()->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['q'] ?? null)
                        ->addActionLabel('إضافة سؤال')->columnSpanFull(),

                    Forms\Components\Select::make('related_services')
                        ->label('خدمات مرتبطة')->multiple()
                        ->options(fn (?Service $record) => Service::query()
                            ->when($record, fn ($q) => $q->whereKeyNot($record->getKey()))
                            ->orderBy('name_ar')->pluck('name_ar', 'slug'))
                        ->searchable()->columnSpanFull(),
                ]),

            Forms\Components\Section::make('ملاحظات وSEO')
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('price_note')->label('ملاحظة السعر'),
                    Forms\Components\TextInput::make('duration_note')->label('ملاحظة المدة'),
                    Forms\Components\TextInput::make('seo_title')->label('عنوان SEO')->maxLength(70),
                    Forms\Components\Textarea::make('seo_description')->label('وصف SEO')->rows(2)->maxLength(170),
                ])->columns(2),

            Forms\Components\Section::make('النشر')
                ->schema([
                    Forms\Components\Placeholder::make('missing')
                        ->label('المحتوى الناقص للنشر')
                        ->content(fn (?Service $record) => $record && ! empty($record->missingContent())
                            ? '⚠️ ناقص: ' . implode('، ', $record->missingContent())
                            : '✅ المحتوى كامل — جاهز للنشر.')
                        ->columnSpanFull(),

                    Forms\Components\DateTimePicker::make('published_at')->label('تاريخ النشر'),

                    Forms\Components\Toggle::make('is_published')
                        ->label('منشور')
                        ->helperText('لو المحتوى ناقص هيفضل مسوّدة (noindex + بره السايت ماب) حتى لو فعّلت ده.'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_ar')->label('الخدمة')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('icon')->label('الأيقونة')
                    ->formatStateUsing(fn (?string $state) => self::ICONS[$state] ?? $state)->badge(),
                Tables\Columns\IconColumn::make('is_published')->label('منشور')->boolean()->sortable(),
                Tables\Columns\TextColumn::make('missing')->label('الحالة')
                    ->state(fn (Service $record) => empty($record->missingContent())
                        ? 'كامل'
                        : 'ناقص: ' . implode('، ', $record->missingContent()))
                    ->badge()
                    ->color(fn (Service $record) => empty($record->missingContent()) ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->color('gray')->toggleable(),
            ])
            ->defaultSort('name_ar')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('حالة النشر')->placeholder('الكل')->trueLabel('منشور')->falseLabel('مسوّدة'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
