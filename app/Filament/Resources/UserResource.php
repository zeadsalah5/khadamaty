<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\AdminOnly;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    use AdminOnly;

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'الإعدادات';

    protected static ?string $navigationLabel = 'المستخدمون';

    protected static ?string $modelLabel = 'مستخدم';

    protected static ?string $pluralModelLabel = 'المستخدمون';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('الاسم')->required()->maxLength(255),
            Forms\Components\TextInput::make('email')->label('البريد الإلكتروني')->email()->required()
                ->unique(ignoreRecord: true)->maxLength(255),
            Forms\Components\Select::make('role')->label('الدور')
                ->options(['admin' => 'أدمن (كل الصلاحيات)', 'editor' => 'محرر (المحتوى فقط)'])
                ->default('editor')->required()->native(false),
            Forms\Components\TextInput::make('password')->label('كلمة السر')
                ->password()->revealable()
                ->helperText('سيبها فاضية وقت التعديل عشان ما تتغيرش.')
                ->required(fn (string $operation) => $operation === 'create')
                ->dehydrated(fn ($state) => filled($state))
                ->minLength(8),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('الاسم')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('email')->label('البريد')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('role')->label('الدور')->badge()
                    ->formatStateUsing(fn ($state) => $state === 'admin' ? 'أدمن' : 'محرر')
                    ->color(fn ($state) => $state === 'admin' ? 'danger' : 'gray'),
            ])
            ->defaultSort('name')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageUsers::route('/')];
    }
}
