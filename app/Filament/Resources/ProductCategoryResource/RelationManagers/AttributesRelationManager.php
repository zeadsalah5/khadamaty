<?php

namespace App\Filament\Resources\ProductCategoryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * حقول المواصفات لكل قسم — يتدار من صفحة القسم.
 * قيم المنتج بتتخزّن في products.specs مفهرسة بالـ key.
 */
class AttributesRelationManager extends RelationManager
{
    protected static string $relationship = 'attributes';

    protected static ?string $title = 'حقول المواصفات';

    protected static ?string $modelLabel = 'حقل';

    protected static ?string $pluralModelLabel = 'الحقول';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('label_ar')->label('اسم الحقل (عربي)')
                ->required()->maxLength(255)
                ->helperText('زي: القدرة بالحصان'),
            Forms\Components\TextInput::make('key')->label('المفتاح (إنجليزي)')
                ->required()->maxLength(100)
                ->regex('/^[a-z0-9]+(?:_[a-z0-9]+)*$/')
                ->helperText('حروف صغيرة و_ فقط. زي: horsepower')
                ->rule(function (Forms\Get $get) {
                    // مفتاح فريد داخل القسم الواحد.
                    $categoryId = $this->getOwnerRecord()->getKey();

                    return \Illuminate\Validation\Rule::unique('category_attributes', 'key')
                        ->where('product_category_id', $categoryId)
                        ->ignore($get('id'));
                }),
            Forms\Components\Select::make('type')->label('نوع الحقل')
                ->options([
                    'text'    => 'نص',
                    'number'  => 'رقم',
                    'select'  => 'اختيار من قائمة',
                    'boolean' => 'نعم / لا',
                ])->default('text')->required()->live(),
            Forms\Components\TextInput::make('unit')->label('الوحدة (اختياري)')
                ->maxLength(50)->helperText('زي: حصان، سنة'),
            Forms\Components\TagsInput::make('options')->label('قيم القائمة')
                ->visible(fn (Forms\Get $get) => $get('type') === 'select')
                ->helperText('اكتب كل قيمة واضغط Enter.')->columnSpanFull(),
            Forms\Components\Toggle::make('is_required')->label('إجباري'),
            Forms\Components\TextInput::make('position')->label('الترتيب')->numeric()->default(0),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label_ar')->label('الحقل')->weight('bold'),
                Tables\Columns\TextColumn::make('key')->label('المفتاح')->color('gray'),
                Tables\Columns\TextColumn::make('type')->label('النوع')->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'number' => 'رقم', 'select' => 'قائمة', 'boolean' => 'نعم/لا', default => 'نص',
                    }),
                Tables\Columns\TextColumn::make('unit')->label('الوحدة')->placeholder('—'),
                Tables\Columns\IconColumn::make('is_required')->label('إجباري')->boolean(),
            ])
            ->defaultSort('position')
            ->reorderable('position')
            ->headerActions([Tables\Actions\CreateAction::make()->label('إضافة حقل')])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }
}
