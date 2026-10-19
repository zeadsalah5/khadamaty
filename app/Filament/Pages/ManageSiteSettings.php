<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\ManagesSettings;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms;
use Filament\Pages\Page;

/**
 * إعدادات الموقع العامة: بيانات الشركة + SEO وأكواد التتبع.
 * أدمن فقط. القيم بتتخزّن في جدول settings وبتظهر على الموقع فوراً.
 */
class ManageSiteSettings extends Page implements HasForms
{
    use ManagesSettings;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'الإعدادات';

    protected static ?string $navigationLabel = 'إعدادات الموقع';

    protected static ?string $title = 'إعدادات الموقع';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.settings';

    protected function keyGroups(): array
    {
        return [
            'name' => 'company',
            'tagline' => 'company',
            'areas_label' => 'company',
            'phone' => 'company',
            'phone_tel' => 'company',
            'whatsapp' => 'company',
            'email' => 'company',
            'order_notify_email' => 'company',
            'order_notify_whatsapp' => 'company',
            'working_hours' => 'company',
            'social_links' => 'company',
            'default_og_image' => 'seo',
            'twitter_handle' => 'seo',
            'ga4_id' => 'seo',
            'gtm_id' => 'seo',
            'meta_pixel_id' => 'seo',
            'custom_head' => 'seo',
        ];
    }

    protected function schemaComponents(): array
    {
        return [
            Forms\Components\Tabs::make()->tabs([
                Forms\Components\Tabs\Tab::make('بيانات الشركة')->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('اسم الموقع')->required()->maxLength(255),
                    Forms\Components\TextInput::make('tagline')
                        ->label('الشعار النصي / وصف قصير')->maxLength(255),
                    Forms\Components\TextInput::make('areas_label')
                        ->label('نطاق الخدمة (نص عام)')->maxLength(255)
                        ->helperText('مثال: القاهرة الكبرى'),
                    Forms\Components\TextInput::make('phone')
                        ->label('رقم التليفون الظاهر')->maxLength(30),
                    Forms\Components\TextInput::make('phone_tel')
                        ->label('رقم الاتصال (صيغة دولية)')->maxLength(30)
                        ->helperText('مثال: +201000000000 — بيستخدم في زرار الاتصال.'),
                    Forms\Components\TextInput::make('whatsapp')
                        ->label('واتساب (دولي بدون +)')->maxLength(30)
                        ->helperText('مثال: 201000000000 — لرابط wa.me'),
                    Forms\Components\TextInput::make('email')
                        ->label('البريد الإلكتروني (اختياري)')->email()->maxLength(255),
                    Forms\Components\Textarea::make('working_hours')
                        ->label('ساعات العمل (اختياري)')->rows(2)
                        ->helperText('بيظهر في الفوتر لو متملّي.'),
                    Forms\Components\TextInput::make('order_notify_email')
                        ->label('إيميل إشعارات الطلبات (اختياري)')->email()->maxLength(255)
                        ->helperText('يتبعتله إيميل بكل طلب جديد من المتجر. سيبه فاضي لو مش عايز.'),
                    Forms\Components\TextInput::make('order_notify_whatsapp')
                        ->label('واتساب إشعارات الطلبات (اختياري)')->maxLength(30)
                        ->helperText('دولي بدون + — لرابط الواتساب الجاهز في لوحة الطلبات. فاضي = رقم الواتساب العام.'),
                    Forms\Components\Repeater::make('social_links')
                        ->label('روابط السوشيال ميديا (اختياري)')
                        ->schema([
                            Forms\Components\TextInput::make('label')->label('الاسم')->required()
                                ->placeholder('فيسبوك / إنستجرام ...'),
                            Forms\Components\TextInput::make('url')->label('الرابط')->url()->required(),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                        ->addActionLabel('إضافة رابط')
                        ->default([]),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make('SEO والتتبع')->schema([
                    Forms\Components\TextInput::make('default_og_image')
                        ->label('صورة المشاركة الافتراضية (OG)')->maxLength(255)
                        ->helperText('مسار داخل public، مثال: images/og-image.png'),
                    Forms\Components\TextInput::make('twitter_handle')
                        ->label('حساب تويتر/إكس (اختياري)')->maxLength(255)
                        ->helperText('بصيغة @handle'),
                    Forms\Components\TextInput::make('ga4_id')
                        ->label('Google Analytics 4 (اختياري)')->maxLength(50)
                        ->helperText('مثال: G-XXXXXXX'),
                    Forms\Components\TextInput::make('gtm_id')
                        ->label('Google Tag Manager (اختياري)')->maxLength(50)
                        ->helperText('مثال: GTM-XXXXXXX'),
                    Forms\Components\TextInput::make('meta_pixel_id')
                        ->label('Meta Pixel ID (اختياري)')->maxLength(50),
                    Forms\Components\Textarea::make('custom_head')
                        ->label('كود إضافي للـ <head> (اختياري)')->rows(4)
                        ->helperText('كود يتحقن داخل <head>. للأدمن الموثوق فقط.'),
                ])->columns(2),
            ])->columnSpanFull(),
        ];
    }
}
