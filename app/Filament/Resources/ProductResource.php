<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\CategoryAttribute;
use App\Models\Product;
use App\Support\ImageUploads;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'المتجر';

    protected static ?string $navigationLabel = 'المنتجات';

    protected static ?string $modelLabel = 'منتج';

    protected static ?string $pluralModelLabel = 'المنتجات';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('البيانات الأساسية')
                ->schema([
                    Forms\Components\TextInput::make('name_ar')->label('اسم المنتج')->required()->maxLength(255),
                    Forms\Components\TextInput::make('slug')->label('الـ Slug (بالإنجليزي)')
                        ->required()->unique(ignoreRecord: true)
                        ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->maxLength(255)
                        ->helperText('بيظهر في /shop/...'),
                    Forms\Components\Select::make('category_id')->label('القسم')
                        ->relationship('category', 'name_ar')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->labelPath())
                        ->searchable()->preload()->live()
                        ->helperText('لازم للنشر — وبيحدّد حقول المواصفات.'),
                    Forms\Components\Select::make('brand_id')->label('الماركة الأساسية (اختياري)')
                        ->relationship('brand', 'name_ar')->searchable()->preload(),
                    Forms\Components\Radio::make('condition')->label('حالة المنتج')
                        ->options(['new' => 'جديد', 'used' => 'مستعمل'])
                        ->default('new')->inline()->live()->required(),
                    Forms\Components\TextInput::make('sku')->label('كود المنتج (SKU)')->maxLength(100)
                        ->helperText('مفيد للاستيراد/التحديث من Excel.'),
                    Forms\Components\TextInput::make('model_number')->label('رقم الموديل')->maxLength(100),
                    Forms\Components\Textarea::make('excerpt')->label('الملخّص')->rows(2)->maxLength(300)->columnSpanFull(),
                    Forms\Components\MarkdownEditor::make('description')->label('الوصف التفصيلي')
                        ->disableToolbarButtons(['attachFiles'])->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('التسعير والمخزون')
                ->schema([
                    Forms\Components\TextInput::make('price')->label('السعر (جنيه)')->numeric()->minValue(0)
                        ->helperText('لازم للنشر. من غيره مفيش offers في الـ schema.'),
                    Forms\Components\TextInput::make('sale_price')->label('سعر العرض (اختياري)')->numeric()->minValue(0),
                    Forms\Components\TextInput::make('stock_qty')->label('الكمية بالمخزون')->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('warranty_note')->label('ملاحظة الضمان')->maxLength(255),
                ])->columns(2),

            Forms\Components\Section::make('المواصفات')
                ->description('الحقول بتتغيّر حسب القسم المختار (بتتدار من صفحة القسم).')
                ->schema(fn (Get $get): array => self::specFields($get('category_id')))
                ->columns(2),

            Forms\Components\Section::make('الصور')
                ->description('الصورة الرئيسية واحدة. الترتيب بالسحب. المستعمل لازم ٣ صور على الأقل.')
                ->schema([
                    Forms\Components\Repeater::make('images')->label('')
                        ->relationship()->orderColumn('position')
                        ->schema([
                            Forms\Components\FileUpload::make('path')->label('الصورة')
                                ->image()->disk('products')->visibility('public')->maxSize(4096)
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                ->saveUploadedFileUsing(fn ($file) => ImageUploads::storeWebp($file, 'products'))
                                ->required()->columnSpan(2),
                            Forms\Components\TextInput::make('alt')->label('نص بديل (alt)')->maxLength(255),
                            Forms\Components\Toggle::make('is_primary')->label('رئيسية')->inline(false),
                        ])
                        ->columns(2)
                        ->reorderable()->collapsible()->cloneable(false)
                        ->defaultItems(0)->addActionLabel('إضافة صورة')
                        ->itemLabel(fn (array $state): ?string => ($state['is_primary'] ?? false) ? '★ رئيسية' : ($state['alt'] ?? null)),
                ]),

            Forms\Components\Section::make('تفاصيل الجهاز المستعمل')
                ->visible(fn (Get $get) => $get('condition') === 'used')
                ->relationship('usedDetails')
                ->schema([
                    Forms\Components\Select::make('condition_grade')->label('درجة الحالة')
                        ->options([
                            'ممتاز' => 'ممتاز', 'جيد جداً' => 'جيد جداً',
                            'جيد' => 'جيد', 'مقبول' => 'مقبول',
                        ]),
                    Forms\Components\TextInput::make('manufacture_year')->label('سنة الصنع')->numeric()
                        ->minValue(1980)->maxValue((int) date('Y')),
                    Forms\Components\TextInput::make('inspected_by')->label('الفحص بواسطة'),
                    Forms\Components\DatePicker::make('inspected_at')->label('تاريخ الفحص'),
                    Forms\Components\Textarea::make('work_done')->label('الأعمال اللي اتعملت')->rows(2)->columnSpanFull(),
                    Forms\Components\Repeater::make('inspection')->label('قايمة الفحص')
                        ->schema([
                            Forms\Components\TextInput::make('item')->label('البند')->required()->columnSpan(2),
                            Forms\Components\Toggle::make('ok')->label('سليم')->inline(false),
                            Forms\Components\TextInput::make('note')->label('ملاحظة')->columnSpanFull(),
                        ])
                        ->columns(3)->reorderable()->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['item'] ?? null)
                        ->addActionLabel('إضافة بند فحص')->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('SEO (اختياري)')->collapsed()->schema([
                Forms\Components\TextInput::make('seo_title')->label('عنوان SEO')->maxLength(70),
                Forms\Components\Textarea::make('seo_description')->label('وصف SEO')->rows(2)->maxLength(170),
            ])->columns(1),

            Forms\Components\Section::make('النشر')->schema([
                Forms\Components\Placeholder::make('missing')->label('المحتوى الناقص للنشر')
                    ->content(fn (?Product $record) => $record && ! empty($record->missingContent())
                        ? '⚠️ ناقص: ' . implode('، ', $record->missingContent())
                        : '✅ المحتوى كامل — جاهز للنشر.')->columnSpanFull(),
                Forms\Components\Select::make('status')->label('الحالة')
                    ->options(['draft' => 'مسوّدة', 'published' => 'منشور', 'sold' => 'مُباع'])
                    ->default('draft')->required(),
                Forms\Components\DateTimePicker::make('published_at')->label('تاريخ النشر'),
                Forms\Components\Toggle::make('is_published')->label('منشور')
                    ->helperText('المحتوى الناقص أو المُباع بيفضل مخفي حتى لو فعّلت ده.'),
            ])->columns(2),
        ]);
    }

    /** حقول المواصفات الديناميكية حسب القسم المختار (statePath: specs.<key>). */
    protected static function specFields(?int $categoryId): array
    {
        if (! $categoryId) {
            return [Forms\Components\Placeholder::make('specs_hint')->label('')
                ->content('اختر القسم الأول علشان تظهر حقول المواصفات.')->columnSpanFull()];
        }

        $attrs = CategoryAttribute::where('product_category_id', $categoryId)
            ->orderBy('position')->get();

        if ($attrs->isEmpty()) {
            return [Forms\Components\Placeholder::make('specs_empty')->label('')
                ->content('القسم ده مفيهوش حقول مواصفات. ضيفها من صفحة «أقسام المتجر».')->columnSpanFull()];
        }

        return $attrs->map(function (CategoryAttribute $a) {
            $path = 'specs.' . $a->key;
            $label = $a->label_ar . ($a->unit ? " ({$a->unit})" : '');

            $field = match ($a->type) {
                'number' => Forms\Components\TextInput::make($path)->numeric(),
                'boolean' => Forms\Components\Select::make($path)->options(['نعم' => 'نعم', 'لا' => 'لا']),
                'select' => Forms\Components\Select::make($path)
                    ->options(collect($a->options ?? [])->mapWithKeys(fn ($o) => [$o => $o])->all()),
                default => Forms\Components\TextInput::make($path),
            };

            return $field->label($label);
        })->all();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('primaryImage.path')->label('الصورة')
                    ->disk('products')->square()->defaultImageUrl(null),
                Tables\Columns\TextColumn::make('name_ar')->label('المنتج')->searchable()->weight('bold')
                    ->description(fn (Product $record) => $record->sku ? 'SKU: ' . $record->sku : null),
                Tables\Columns\TextColumn::make('category.name_ar')->label('القسم')->badge()->color('gray')->toggleable(),
                Tables\Columns\TextColumn::make('brand.name_ar')->label('الماركة')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('condition')->label('الحالة')->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'used' ? 'مستعمل' : 'جديد')
                    ->color(fn (string $state) => $state === 'used' ? 'warning' : 'info'),
                Tables\Columns\TextColumn::make('price')->label('السعر')->money('EGP')->placeholder('—')->sortable(),
                Tables\Columns\TextColumn::make('stock_qty')->label('المخزون')->badge()
                    ->color(fn (int $state) => $state <= 0 ? 'danger' : ($state < 3 ? 'warning' : 'success'))->sortable(),
                Tables\Columns\TextColumn::make('status')->label('الحالة')->badge()
                    ->formatStateUsing(fn (string $state) => ['draft' => 'مسوّدة', 'published' => 'منشور', 'sold' => 'مُباع'][$state] ?? $state)
                    ->color(fn (string $state) => ['draft' => 'gray', 'published' => 'success', 'sold' => 'danger'][$state] ?? 'gray'),
                Tables\Columns\IconColumn::make('is_published')->label('ظاهر')->boolean()->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')->label('القسم')
                    ->relationship('category', 'name_ar')->searchable()->preload(),
                Tables\Filters\SelectFilter::make('brand_id')->label('الماركة')
                    ->relationship('brand', 'name_ar')->searchable()->preload(),
                Tables\Filters\SelectFilter::make('condition')->label('حالة المنتج')
                    ->options(['new' => 'جديد', 'used' => 'مستعمل']),
                Tables\Filters\SelectFilter::make('status')->label('حالة النشر')
                    ->options(['draft' => 'مسوّدة', 'published' => 'منشور', 'sold' => 'مُباع']),
                Tables\Filters\Filter::make('low_stock')->label('مخزون منخفض (< 3)')
                    ->query(fn ($query) => $query->where('stock_qty', '<', 3)),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
