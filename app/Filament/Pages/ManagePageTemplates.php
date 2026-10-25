<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\ManagesSettings;
use Filament\Forms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;

/**
 * قوالب أنواع الصفحات — بيتعدّلوا من الداشبورد (Section 1).
 * دلوقتي: قالب صفحة (ماركة × خدمة) اللي بيستخدمه مولّد الصفحات.
 * المتغيّرات المتاحة: {{brand}} = اسم الماركة، {{service}} = اسم الخدمة.
 * أدمن فقط.
 */
class ManagePageTemplates extends Page implements HasForms
{
    use ManagesSettings;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationGroup = 'الإعدادات';

    protected static ?string $navigationLabel = 'قوالب الصفحات';

    protected static ?string $title = 'قوالب الصفحات';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.settings';

    protected function keyGroups(): array
    {
        return [
            'bsp_tpl_h1'    => 'templates',
            'bsp_tpl_intro' => 'templates',
            'asp_tpl_h1'    => 'templates',
            'asp_tpl_intro' => 'templates',
        ];
    }

    protected function schemaComponents(): array
    {
        return [
            Forms\Components\Section::make('قالب صفحة (ماركة × خدمة)')
                ->description('بيستخدمه زرّار «إنشاء صفحات». المتغيّرات: {{brand}} = الماركة، {{service}} = الخدمة. '
                    . 'الصفحات المولّدة بتبقى مسودة فيها TODO لحد ما الفني يملاها ويراجعها.')
                ->schema([
                    Forms\Components\TextInput::make('bsp_tpl_h1')
                        ->label('عنوان الصفحة (H1)')
                        ->required()
                        ->helperText('مثال: {{service}} {{brand}}'),

                    Forms\Components\Textarea::make('bsp_tpl_intro')
                        ->label('المقدمة (قالب)')
                        ->rows(6)
                        ->helperText('اكتب قالب المقدمة. سيب TODO للأجزاء اللي محتاجة محتوى فريد من الفني.'),
                ]),

            Forms\Components\Section::make('قالب صفحة (منطقة × خدمة)')
                ->description('بيستخدمه زرّار «إنشاء صفحات (منطقة × خدمة)». المتغيّرات: {{area}} = المنطقة، {{service}} = الخدمة.')
                ->schema([
                    Forms\Components\TextInput::make('asp_tpl_h1')
                        ->label('عنوان الصفحة (H1)')
                        ->required()
                        ->helperText('مثال: {{service}} تكييف في {{area}}'),

                    Forms\Components\Textarea::make('asp_tpl_intro')
                        ->label('المقدمة (قالب)')
                        ->rows(6)
                        ->helperText('سيب TODO للأجزاء اللي محتاجة محتوى محلي فريد من الفني.'),
                ]),
        ];
    }
}
