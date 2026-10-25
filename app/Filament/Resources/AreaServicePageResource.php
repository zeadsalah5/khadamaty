<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AreaServicePageResource\Pages;
use App\Models\Area;
use App\Models\AreaServicePage;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AreaServicePageResource extends Resource
{
    protected static ?string $model = AreaServicePage::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'المحتوى';

    protected static ?string $navigationLabel = 'صفحات (منطقة × خدمة)';

    protected static ?string $modelLabel = 'صفحة منطقة × خدمة';

    protected static ?string $pluralModelLabel = 'صفحات (منطقة × خدمة)';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('المنطقة والخدمة')
                ->description('المسار: /areas/{منطقة}/{خدمة}. الزوج (منطقة × خدمة) لازم يكون فريد.')
                ->schema([
                    Forms\Components\Select::make('area_id')->label('المنطقة')->required()
                        ->options(fn () => Area::query()->orderBy('name_ar')->pluck('name_ar', 'id'))
                        ->searchable()->preload(),
                    Forms\Components\Select::make('service_id')->label('الخدمة')->required()
                        ->options(fn () => Service::query()->orderBy('name_ar')->pluck('name_ar', 'id'))
                        ->searchable()->preload(),
                ])->columns(2),

            Forms\Components\Section::make('المحتوى الأساسي')
                ->description('الصفحة ماتتنشرش غير لما العنوان والمقدمة والأسئلة يكونوا متملّيين + مراجعة + صفر TODO.')
                ->schema([
                    Forms\Components\TextInput::make('h1')->label('العنوان (H1)')->required()->maxLength(255)->columnSpanFull(),
                    Forms\Components\MarkdownEditor::make('intro')->label('المقدمة (أول فقرة)')
                        ->columnSpanFull()->disableToolbarButtons(['attachFiles']),
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
                ->description('اختياريان ومبيمنعوش النشر.')
                ->schema([
                    Forms\Components\TextInput::make('price_note')->label('ملاحظة السعر')->maxLength(255),
                    Forms\Components\TextInput::make('duration_note')->label('ملاحظة المدة')->maxLength(255),
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
                Tables\Columns\TextColumn::make('area.name_ar')->label('المنطقة')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('service.name_ar')->label('الخدمة')->searchable(),
                Tables\Columns\TextColumn::make('content_status')->label('المراجعة')->badge()
                    ->formatStateUsing(fn (string $state) => AreaServicePage::reviewStatusLabels()[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        AreaServicePage::STATUS_READY => 'success',
                        AreaServicePage::STATUS_NEEDS_REVIEW => 'warning',
                        default => 'gray',
                    })->sortable(),
                Tables\Columns\IconColumn::make('is_published')->label('منشور')->boolean()->sortable(),
            ])
            ->defaultSort('area_id')
            ->filters([
                Tables\Filters\SelectFilter::make('content_status')->label('حالة المراجعة')
                    ->options(AreaServicePage::reviewStatusLabels()),
                Tables\Filters\TernaryFilter::make('is_published')->label('حالة النشر')
                    ->placeholder('الكل')->trueLabel('منشور')->falseLabel('مسوّدة'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAreaServicePages::route('/'),
            'create' => Pages\CreateAreaServicePage::route('/create'),
            'edit' => Pages\EditAreaServicePage::route('/{record}/edit'),
        ];
    }
}
