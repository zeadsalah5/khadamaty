<?php

namespace App\Filament\Support;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Illuminate\Database\Eloquent\Model;

/**
 * قسم «المراجعة والنشر» المشترك لكل موديلات المحتوى (سير المراجعة الموحّد).
 * بيعرض: حالة المراجعة + مين راجع وإمتى + الناقص للنشر + تاريخ النشر + تبديل النشر.
 * القاعدة بتتفرض في GuardsReviewWorkflow — التبديل لوحده مش كفاية.
 */
class ReviewFields
{
    public static function section(): Section
    {
        return Section::make('المراجعة والنشر')
            ->description('مفيش صفحة تتنشر قبل مراجعة الفني وصفر TODO: لازم الحالة «جاهزة» + متراجعة + المحتوى مكتمل.')
            ->schema([
                Select::make('content_status')
                    ->label('حالة المراجعة')
                    // قيم HasReviewWorkflow::STATUS_* كـ literals (ثوابت الـ trait مينفعش
                    // توصلها بالاسم مباشرة في PHP 8.2 — لازم عبر كلاس بيستخدم الـ trait).
                    ->options([
                        'draft'             => 'مسودة',
                        'needs_tech_review' => 'محتاجة مراجعة الفني',
                        'ready'             => 'جاهزة للنشر',
                    ])
                    ->default('draft')
                    ->required()
                    ->native(false)
                    ->helperText('لما تختار «جاهزة» بيتسجّل إنك راجعتها (reviewed_by).'),

                Placeholder::make('review_info')
                    ->label('المراجعة')
                    ->content(fn (?Model $record) => $record && $record->reviewed_at
                        ? 'اتراجعت بواسطة ' . ($record->reviewer?->name ?? ('#' . $record->reviewed_by))
                          . ' في ' . $record->reviewed_at->format('Y-m-d H:i')
                        : 'لسه متراجعتش'),

                Placeholder::make('publish_blockers')
                    ->label('الناقص للنشر')
                    ->columnSpanFull()
                    ->content(fn (?Model $record) => $record
                        ? (empty($record->publishBlockers())
                            ? '✅ جاهزة للنشر'
                            : '⚠️ ' . implode('، ', $record->publishBlockers()))
                        : 'احفظ الأول عشان نتحقق.'),

                DateTimePicker::make('published_at')->label('تاريخ النشر'),

                Toggle::make('is_published')
                    ->label('منشور')
                    ->helperText('مايتفعّلش غير بالشروط. لو التشابه عالي استخدم زرّار «نشر مع تأكيد التشابه» فوق.'),
            ])
            ->columns(2);
    }
}
