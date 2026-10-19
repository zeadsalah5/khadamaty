<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\ManagesSettings;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms;
use Filament\Pages\Page;

/**
 * محتوى الصفحة الرئيسية: شريط الإعلان، سلايدر الهيرو، ترتيب/إظهار الأقسام،
 * «إزاي بنشتغل»، «ليه تختارنا»، أقسام الخدمات، وقوايم الحجز/المناطق.
 * أدمن فقط. التعديل بيظهر على الموقع فوراً.
 */
class ManageHomePage extends Page implements HasForms
{
    use ManagesSettings;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationGroup = 'الإعدادات';

    protected static ?string $navigationLabel = 'الصفحة الرئيسية';

    protected static ?string $title = 'الصفحة الرئيسية';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.settings';

    /** مفاتيح أقسام الهوم المعروفة (للاختيار في ترتيب الأقسام). */
    protected const SECTION_OPTIONS = [
        'hero' => 'الهيرو (السلايدر)',
        'services_1' => 'قسم خدمات 1',
        'how' => 'إزاي بنشتغل',
        'services_2' => 'قسم خدمات 2',
        'services_3' => 'قسم خدمات 3',
        'services_link' => 'رابط «كل الخدمات»',
        'pricing' => 'الباقات والأسعار',
        'why' => 'ليه تختارنا',
        'reviews' => 'آراء العملاء',
        'areas' => 'مناطق الخدمة',
        'faq' => 'الأسئلة الشائعة',
        'booking' => 'فورم الحجز',
    ];

    protected function keyGroups(): array
    {
        return [
            'announcement_enabled' => 'home',
            'announcement_text' => 'home',
            'announcement_link' => 'home',
            'announcement_link_label' => 'home',
            'hero_slides' => 'home',
            'home_sections' => 'home',
            'how_steps' => 'home',
            'why_us' => 'home',
            'service_sections' => 'home',
            'booking_services' => 'booking',
            'booking_appliances' => 'booking',
            'areas' => 'booking',
        ];
    }

    protected function schemaComponents(): array
    {
        return [
            Forms\Components\Tabs::make()->tabs([
                // شريط الإعلان
                Forms\Components\Tabs\Tab::make('شريط الإعلان')->schema([
                    Forms\Components\Toggle::make('announcement_enabled')
                        ->label('تفعيل شريط الإعلان'),
                    Forms\Components\TextInput::make('announcement_text')
                        ->label('نص الإعلان')->maxLength(255),
                    Forms\Components\TextInput::make('announcement_link')
                        ->label('رابط الإعلان (اختياري)')->maxLength(255),
                    Forms\Components\TextInput::make('announcement_link_label')
                        ->label('نص رابط الإعلان')->maxLength(100),
                ]),

                // سلايدر الهيرو
                Forms\Components\Tabs\Tab::make('سلايدر الهيرو')->schema([
                    Forms\Components\Repeater::make('hero_slides')
                        ->label('شرائح السلايدر')
                        ->schema([
                            Forms\Components\Select::make('type')
                                ->label('النوع')
                                ->options([
                                    'illustration' => 'رسم (التصميم الافتراضي)',
                                    'photo' => 'صورة فني',
                                ])
                                ->default('photo')->required()->live(),
                            Forms\Components\FileUpload::make('image')
                                ->label('صورة الفني')
                                ->image()->disk('technicians')->visibility('public')->maxSize(4096)
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                ->saveUploadedFileUsing(fn ($file) => \App\Support\ImageUploads::storeWebp($file, 'technicians'))
                                ->helperText('ارفع الصورة من هنا — بتتحوّل WebP تلقائياً. (لنوع «صورة فني» فقط.)')
                                ->visible(fn (Forms\Get $get) => $get('type') === 'photo'),
                            Forms\Components\TextInput::make('badge')->label('الشارة العلوية'),
                            Forms\Components\TagsInput::make('title_lines')
                                ->label('سطور العنوان')
                                ->helperText('كل سطر عنصر — Enter بعد كل سطر.')
                                ->required(),
                            Forms\Components\Textarea::make('text')->label('النص')->rows(2)->columnSpanFull(),
                            Forms\Components\TextInput::make('cta_label')->label('نص الزرار'),
                            Forms\Components\TextInput::make('cta_href')->label('رابط الزرار')->default('#booking'),
                            Forms\Components\TextInput::make('cta_service')
                                ->label('خدمة تتحدد تلقائياً (اختياري)')
                                ->helperText('لازم تطابق اسم خدمة من قايمة الحجز.'),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => is_array($state['title_lines'] ?? null)
                            ? implode(' ', $state['title_lines'])
                            : ($state['badge'] ?? 'شريحة'))
                        ->addActionLabel('إضافة شريحة')
                        ->default([]),
                ]),

                // ترتيب وإظهار الأقسام
                Forms\Components\Tabs\Tab::make('ترتيب الأقسام')->schema([
                    Forms\Components\Repeater::make('home_sections')
                        ->label('أقسام الصفحة (اسحب لإعادة الترتيب)')
                        ->schema([
                            Forms\Components\Select::make('key')
                                ->label('القسم')
                                ->options(self::SECTION_OPTIONS)
                                ->required()
                                ->distinct()
                                ->columnSpan(2),
                            Forms\Components\Toggle::make('enabled')
                                ->label('ظاهر')->default(true),
                        ])
                        ->columns(3)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => self::SECTION_OPTIONS[$state['key'] ?? ''] ?? null)
                        ->addActionLabel('إضافة قسم')
                        ->default([]),
                ]),

                // إزاي بنشتغل
                Forms\Components\Tabs\Tab::make('إزاي بنشتغل')->schema([
                    Forms\Components\Repeater::make('how_steps')
                        ->label('الخطوات')
                        ->schema([
                            Forms\Components\TextInput::make('title')->label('العنوان')->required(),
                            Forms\Components\Textarea::make('desc')->label('الوصف')->rows(2)->required(),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->addActionLabel('إضافة خطوة')
                        ->default([]),
                ]),

                // ليه تختارنا
                Forms\Components\Tabs\Tab::make('ليه تختارنا')->schema([
                    Forms\Components\Repeater::make('why_us')
                        ->label('البطاقات')
                        ->schema([
                            Forms\Components\TextInput::make('t')->label('العنوان')->required(),
                            Forms\Components\Textarea::make('d')->label('الوصف')->rows(2)->required(),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['t'] ?? null)
                        ->addActionLabel('إضافة بطاقة')
                        ->default([]),
                ]),

                // أقسام الخدمات
                Forms\Components\Tabs\Tab::make('أقسام الخدمات')->schema([
                    Forms\Components\Repeater::make('service_sections')
                        ->label('أقسام الخدمات على الهوم')
                        ->schema([
                            Forms\Components\TextInput::make('id')->label('مُعرّف القسم (anchor)')->required(),
                            Forms\Components\TextInput::make('title')->label('العنوان')->required(),
                            Forms\Components\Textarea::make('subtitle')->label('الوصف')->rows(2)->columnSpanFull(),
                            Forms\Components\Repeater::make('items')
                                ->label('الكروت')
                                ->schema([
                                    Forms\Components\TextInput::make('icon')->label('الأيقونة')
                                        ->helperText('مفتاح من service-icon (maintenance, cleaning, ...).'),
                                    Forms\Components\TextInput::make('title')->label('العنوان')->required(),
                                    Forms\Components\TextInput::make('booking')->label('خدمة الحجز')
                                        ->helperText('تطابق اسم من قايمة الحجز.'),
                                    Forms\Components\TextInput::make('slug')->label('Slug الخدمة (اختياري)')
                                        ->helperText('يربط الكارت بصفحة الخدمة لو منشورة.'),
                                    Forms\Components\Textarea::make('desc')->label('الوصف')->rows(2)->columnSpanFull(),
                                ])
                                ->columns(2)
                                ->reorderable()
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                                ->addActionLabel('إضافة كارت')
                                ->columnSpanFull(),
                        ])
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->addActionLabel('إضافة قسم خدمات')
                        ->default([]),
                ]),

                // قوايم الحجز والمناطق
                Forms\Components\Tabs\Tab::make('الحجز والمناطق')->schema([
                    Forms\Components\TagsInput::make('booking_services')
                        ->label('خدمات الحجز')
                        ->helperText('بتظهر في قايمة فورم الحجز.'),
                    Forms\Components\TagsInput::make('booking_appliances')
                        ->label('أنواع الأجهزة')
                        ->helperText('بتظهر في فورم «اطلب خدمة».'),
                    Forms\Components\TagsInput::make('areas')
                        ->label('المناطق (قايمة العرض)')
                        ->helperText('بتظهر في قسم «مناطق الخدمة» والفوتر. المنطقة اللي ليها صفحة منشورة بتتحوّل لرابط تلقائياً.'),
                ])->columns(1),
            ])->columnSpanFull(),
        ];
    }
}
