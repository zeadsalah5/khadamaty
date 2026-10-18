<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'المدونة';

    protected static ?string $navigationLabel = 'المقالات';

    protected static ?string $modelLabel = 'مقال';

    protected static ?string $pluralModelLabel = 'المقالات';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('المحتوى')
                ->description('المقال مايتنشرش غير لما العنوان والملخّص والمتن والتصنيف يكونوا متملّيين.')
                ->schema([
                    Forms\Components\TextInput::make('title_ar')
                        ->label('العنوان')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('slug')
                        ->label('الـ Slug (بالإنجليزي)')
                        ->helperText('حروف إنجليزي صغيرة وأرقام وشرطات بس — ده اللي بيظهر في الرابط /blog/...')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                        ->maxLength(255),

                    Forms\Components\Select::make('category_id')
                        ->label('التصنيف')
                        ->relationship('category', 'name_ar')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Textarea::make('excerpt')
                        ->label('الملخّص')
                        ->helperText('سطر أو اتنين بيظهروا في الكارت وفي وصف محركات البحث.')
                        ->rows(2)
                        ->maxLength(300)
                        ->columnSpanFull(),

                    Forms\Components\MarkdownEditor::make('body')
                        ->label('متن المقال')
                        ->helperText('اكتب بالـ Markdown. العناوين من نوع H2 (## عنوان) بتتحوّل تلقائياً لفهرس محتويات في المقال.')
                        ->columnSpanFull()
                        ->disableToolbarButtons(['attachFiles']),
                ])
                ->columns(2),

            Forms\Components\Section::make('الصورة والروابط')
                ->schema([
                    Forms\Components\FileUpload::make('cover')
                        ->label('صورة الغلاف')
                        ->image()
                        ->imageEditor()
                        ->disk('blog')
                        ->visibility('public')
                        ->maxSize(4096)
                        ->helperText('بتتحفظ في public/images/blog. المقاس المفضّل 1200×675.'),

                    Forms\Components\Select::make('suitable_services')
                        ->label('خدمات مناسبة (روابط داخلية في المقال)')
                        ->multiple()
                        ->options(fn () => Service::query()->orderBy('name_ar')->pluck('name_ar', 'slug'))
                        ->searchable()
                        ->helperText('اختار الخدمات اللي يناسب نلينكها جوّه المقال وفي آخره.'),
                ])
                ->columns(2),

            Forms\Components\Section::make('SEO (اختياري)')
                ->description('سيبهم فاضيين وهياخدوا العنوان والملخّص تلقائياً.')
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('seo_title')
                        ->label('عنوان SEO')
                        ->maxLength(70)
                        ->helperText('يُفضّل أقل من 60 حرف.'),

                    Forms\Components\Textarea::make('seo_description')
                        ->label('وصف SEO')
                        ->rows(2)
                        ->maxLength(170)
                        ->helperText('يُفضّل 150–160 حرف.'),
                ])
                ->columns(1),

            Forms\Components\Section::make('النشر')
                ->schema([
                    Forms\Components\DateTimePicker::make('published_at')
                        ->label('تاريخ النشر')
                        ->helperText('لو سِبته فاضي وفعّلت النشر هيتحط تلقائياً بتاريخ دلوقتي.'),

                    Forms\Components\Toggle::make('is_published')
                        ->label('منشور')
                        ->helperText('لو المحتوى ناقص، هيتسجّل كمسوّدة (noindex وبره السايت ماب) حتى لو فعّلت ده.'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover')
                    ->label('الغلاف')
                    ->disk('blog')
                    ->square(),

                Tables\Columns\TextColumn::make('title_ar')
                    ->label('العنوان')
                    ->searchable()
                    ->limit(50)
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('category.name_ar')
                    ->label('التصنيف')
                    ->badge()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('منشور')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('تاريخ النشر')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('التصنيف')
                    ->relationship('category', 'name_ar'),

                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('حالة النشر')
                    ->placeholder('الكل')
                    ->trueLabel('منشور')
                    ->falseLabel('مسوّدة'),
            ])
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
