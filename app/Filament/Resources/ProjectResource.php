<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use App\Support\ImageUploads;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * معرض الأعمال (Portfolio): إدارة أعمال الموقع الحقيقية بصور ووصف.
 * بتظهر في /portfolio. الصور بتتحوّل WebP تلقائياً.
 */
class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'المحتوى';

    protected static ?string $navigationLabel = 'معرض الأعمال';

    protected static ?string $modelLabel = 'عمل';

    protected static ?string $pluralModelLabel = 'معرض الأعمال';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('بيانات العمل')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('عنوان العمل')->required()->maxLength(255)->columnSpanFull()
                        ->placeholder('مثال: تركيب تكييف مركزي — فيلا بالتجمع الخامس'),

                    Forms\Components\TextInput::make('slug')
                        ->label('الـ Slug (بالإنجليزي)')
                        ->helperText('حروف صغيرة وأرقام وشرطات — بيظهر في /portfolio/...')
                        ->required()->unique(ignoreRecord: true)
                        ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->maxLength(255)
                        ->placeholder('central-ac-tagamoa'),

                    Forms\Components\TextInput::make('service_type')
                        ->label('نوع الخدمة')->maxLength(100)->placeholder('تركيب / صيانة / إصلاح…'),

                    Forms\Components\TextInput::make('area_name')
                        ->label('المنطقة')->maxLength(100)->placeholder('التجمع الخامس'),

                    Forms\Components\Textarea::make('summary')
                        ->label('وصف مختصر (للكارت)')->rows(2)->maxLength(300)->columnSpanFull()
                        ->helperText('سطر أو اتنين عن العمل — بيظهر تحت العنوان في المعرض.'),

                    Forms\Components\MarkdownEditor::make('body')
                        ->label('تفاصيل العمل (اختياري)')->columnSpanFull()
                        ->disableToolbarButtons(['attachFiles'])
                        ->helperText('المشكلة اللي كانت موجودة، اللي عملناه، والنتيجة.'),

                    Forms\Components\DatePicker::make('completed_at')
                        ->label('تاريخ تنفيذ العمل (اختياري)')->native(false),
                ])->columns(2),

            Forms\Components\Section::make('الصور')
                ->description('صورة الغلاف بتظهر في المعرض، وصور المعرض بتظهر في صفحة العمل. بتتحوّل WebP تلقائياً.')
                ->schema([
                    Forms\Components\FileUpload::make('cover')
                        ->label('صورة الغلاف')
                        ->image()->disk('portfolio')->visibility('public')->maxSize(8192)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->saveUploadedFileUsing(fn ($file) => ImageUploads::storeWebp($file, 'portfolio'))
                        ->imagePreviewHeight('150')->columnSpanFull(),

                    Forms\Components\FileUpload::make('images')
                        ->label('صور إضافية (معرض العمل)')
                        ->image()->multiple()->reorderable()->disk('portfolio')->visibility('public')->maxSize(8192)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->saveUploadedFileUsing(fn ($file) => ImageUploads::storeWebp($file, 'portfolio'))
                        ->helperText('اسحب لإعادة الترتيب. ممكن قبل/بعد أو لقطات من الشغل.')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('العرض والنشر')
                ->schema([
                    Forms\Components\Toggle::make('is_featured')->label('مميّز (يظهر الأول)')->inline(false),
                    Forms\Components\TextInput::make('position')->label('الترتيب')->numeric()->default(0)
                        ->helperText('الأصغر بيظهر الأول.'),
                    Forms\Components\Toggle::make('is_published')->label('منشور')->inline(false)
                        ->helperText('من غير نشر مايظهرش للزوّار.'),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->reorderable('position')
            ->columns([
                Tables\Columns\ImageColumn::make('cover')
                    ->label('الغلاف')->disk('portfolio')->height(44)->width(66),
                Tables\Columns\TextColumn::make('title')->label('العنوان')->searchable()->weight('bold')->limit(40),
                Tables\Columns\TextColumn::make('service_type')->label('الخدمة')->badge()->toggleable(),
                Tables\Columns\TextColumn::make('area_name')->label('المنطقة')->toggleable(),
                Tables\Columns\IconColumn::make('is_featured')->label('مميّز')->boolean()->toggleable(),
                Tables\Columns\IconColumn::make('is_published')->label('منشور')->boolean()->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('النشر')->placeholder('الكل')->trueLabel('منشور')->falseLabel('مسوّدة'),
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
            'index'  => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit'   => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
