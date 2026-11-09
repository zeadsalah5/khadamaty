<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Models\Media;
use App\Support\ImageUploads;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'المحتوى';

    protected static ?string $navigationLabel = 'مكتبة الصور';

    protected static ?string $modelLabel = 'صورة';

    protected static ?string $pluralModelLabel = 'مكتبة الصور';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\FileUpload::make('path')->label('الصورة')
                ->image()->disk('media')->visibility('public')->required()
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(5120)
                ->saveUploadedFileUsing(fn ($file) => ImageUploads::storeWebp($file, 'media'))
                ->helperText('بتتحوّل تلقائياً لـ WebP وتتصغّر.')->columnSpanFull(),
            Forms\Components\TextInput::make('alt')->label('النص البديل (alt)')->required()
                ->helperText('وصف مختصر للصورة — إجباري للـ SEO وإتاحة الوصول.')->columnSpanFull(),
            Forms\Components\TextInput::make('title')->label('عنوان (اختياري)'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('path')->label('الصورة')->disk('media')->square(),
                Tables\Columns\TextColumn::make('alt')->label('النص البديل')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('url')->label('الرابط')
                    ->state(fn (Media $r) => $r->url())->copyable()->copyMessage('اتنسخ')->limit(40)->color('gray'),
                Tables\Columns\TextColumn::make('created_at')->label('التاريخ')->dateTime('Y-m-d')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageMedia::route('/')];
    }
}
