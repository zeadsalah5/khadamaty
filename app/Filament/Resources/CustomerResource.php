<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\AdminOnly;
use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * لوحة إدارة حسابات العملاء (المرحلة 6D) — أدمن فقط.
 *
 * عرض الحسابات وعدد طلباتها. الباسورد مبيتعرضش؛ تعديله اختياري (للدعم).
 */
class CustomerResource extends Resource
{
    use AdminOnly;

    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'الطلبات';

    protected static ?string $navigationLabel = 'حسابات العملاء';

    protected static ?string $modelLabel = 'عميل';

    protected static ?string $pluralModelLabel = 'حسابات العملاء';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('الاسم')->required()->maxLength(80),
            Forms\Components\TextInput::make('phone')->label('الموبايل')->tel()
                ->unique(ignoreRecord: true)->maxLength(20),
            Forms\Components\TextInput::make('email')->label('الإيميل')->email()
                ->unique(ignoreRecord: true)->maxLength(190),
            Forms\Components\TextInput::make('password')->label('كلمة السر')
                ->password()->revealable()
                ->helperText('سيبها فاضية عشان ما تتغيرش.')
                ->dehydrated(fn ($state) => filled($state))
                ->minLength(8),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('تاريخ التسجيل')->dateTime('Y-m-d')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('الاسم')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('phone')->label('الموبايل')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('email')->label('الإيميل')->searchable()->copyable()->toggleable(),
                Tables\Columns\TextColumn::make('orders_count')->label('الطلبات')->counts('orders')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageCustomers::route('/')];
    }
}
