<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\AdminOnly;
use App\Filament\Resources\VisitResource\Pages;
use App\Models\Visit;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * تحليلات الزوّار (عرض فقط، أدمن فقط): سجل الزيارات بتفاصيل المصدر
 * (بحث/AI/سوشيال/referral/مباشر) وهل بوت ولا إنسان + المتصفح والجهاز.
 */
class VisitResource extends Resource
{
    use AdminOnly;

    protected static ?string $model = Visit::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'التحليلات';

    protected static ?string $navigationLabel = 'الزوّار';

    protected static ?string $modelLabel = 'زيارة';

    protected static ?string $pluralModelLabel = 'الزيارات';

    protected static ?int $navigationSort = 1;

    /** عرض فقط — مفيش إضافة/تعديل. */
    public static function canCreate(): bool
    {
        return false;
    }

    /** لون شارة نوع المصدر. */
    public static function sourceColor(string $type): string
    {
        return match ($type) {
            'search'   => 'info',
            'ai'       => 'success',
            'ai_bot'   => 'warning',
            'social'   => 'primary',
            'referral' => 'gray',
            'internal' => 'gray',
            default    => 'gray',
        };
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('الوقت')->dateTime('Y-m-d H:i')->sortable(),

                Tables\Columns\TextColumn::make('ip')
                    ->label('IP')->searchable()->copyable()->weight('bold'),

                Tables\Columns\TextColumn::make('source_type')
                    ->label('المصدر')->badge()
                    ->formatStateUsing(fn (string $state) => Visit::SOURCE_LABELS[$state] ?? $state)
                    ->color(fn (string $state) => self::sourceColor($state))
                    ->description(fn (Visit $r) => $r->source_name),

                Tables\Columns\TextColumn::make('is_bot')
                    ->label('النوع')->badge()
                    ->formatStateUsing(fn (bool $state) => $state ? 'بوت' : 'إنسان')
                    ->color(fn (bool $state) => $state ? 'warning' : 'success')
                    ->description(fn (Visit $r) => $r->is_bot ? $r->bot_name : ($r->browser . ' · ' . $r->device)),

                Tables\Columns\TextColumn::make('path')
                    ->label('الصفحة')->limit(36)->tooltip(fn (Visit $r) => $r->path)->searchable(),

                Tables\Columns\TextColumn::make('referer_host')
                    ->label('جه من')->placeholder('—')->toggleable(),

                Tables\Columns\TextColumn::make('device')
                    ->label('الجهاز')->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('user_agent')
                    ->label('User-Agent')->limit(50)->tooltip(fn (Visit $r) => $r->user_agent)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('source_type')
                    ->label('المصدر')->options(Visit::SOURCE_LABELS),

                Tables\Filters\TernaryFilter::make('is_bot')
                    ->label('النوع')->placeholder('الكل')
                    ->trueLabel('بوتات فقط')->falseLabel('بشر فقط'),

                Tables\Filters\SelectFilter::make('period')
                    ->label('الفترة')
                    ->options([
                        'today' => 'النهارده',
                        '7d'    => 'آخر ٧ أيام',
                        '30d'   => 'آخر ٣٠ يوم',
                    ])
                    ->default('30d')
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'today' => $query->whereDate('created_at', today()),
                            '7d'    => $query->where('created_at', '>=', now()->subDays(7)),
                            '30d'   => $query->where('created_at', '>=', now()->subDays(30)),
                            default => $query,
                        };
                    }),
            ])
            ->groups([
                Tables\Grouping\Group::make('ip')->label('حسب الـ IP'),
                Tables\Grouping\Group::make('source_type')
                    ->label('حسب المصدر')
                    ->getTitleFromRecordUsing(fn (Visit $r) => Visit::SOURCE_LABELS[$r->source_type] ?? $r->source_type),
            ])
            ->actions([])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('لسه مفيش زيارات')
            ->emptyStateDescription('الزيارات بتتسجّل تلقائياً أول ما حد يفتح الموقع.')
            ->poll('60s');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVisits::route('/'),
        ];
    }
}
