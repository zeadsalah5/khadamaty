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
        'visual_services' => 'خدماتنا بالصور',
        'about' => 'مين إحنا (بصورة)',
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
            'about_home' => 'home',
            'visual_services' => 'home',
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

                            // ===== التصميم الجديد (full-bleed) =====
                            Forms\Components\TextInput::make('eyebrow')
                                ->label('الشارة العلوية (eyebrow)')
                                ->helperText('نص صغير فوق العنوان. لو فاضي بيستخدم «الشارة العلوية» القديمة.'),
                            Forms\Components\Textarea::make('subtitle')
                                ->label('سطر الوصف تحت العنوان')->rows(2)->columnSpanFull()
                                ->helperText('لو فاضي بيستخدم «النص» فوق.'),
                            Forms\Components\Toggle::make('enabled')
                                ->label('الشريحة ظاهرة')->default(true),

                            Forms\Components\Select::make('gradient_preset')
                                ->label('تدرّج الخلفية (لو مفيش صورة)')
                                ->options([
                                    1 => 'كحلي ← أحمر',
                                    2 => 'كحلي ← أزرق',
                                    3 => 'أحمر ← كحلي',
                                ])
                                ->default(1),
                            Forms\Components\Select::make('overlay_strength')
                                ->label('قوة التعتيم فوق الصورة')
                                ->options([
                                    'light' => 'خفيف',
                                    'medium' => 'متوسط',
                                    'strong' => 'قوي',
                                ])
                                ->default('medium'),

                            Forms\Components\FileUpload::make('background_image')
                                ->label('صورة السلايد (خلفية بعرض الشاشة)')
                                ->image()->disk('hero')->visibility('public')->maxSize(8192)
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                ->saveUploadedFileUsing(fn ($file) => \App\Support\ImageUploads::storeHeroResponsive($file, 'hero'))
                                ->helperText('دي الصورة اللي بتظهر في السلايد. استخدم صورة عريضة (أفقية) عالية الجودة — عرض ١٦٠٠ بكسل على الأقل ونسبة قريبة من 16:9. الصور الطولية (من الموبايل) بتتقص. من غير صورة بيظهر تدرّج + رسمة تكييف.')
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('background_alt')
                                ->label('وصف الصورة (alt) — إجباري مع الصورة')
                                ->required(fn (Forms\Get $get) => filled($get('background_image')))
                                ->maxLength(160),
                            Forms\Components\Select::make('focal')
                                ->label('تركيز الصورة')
                                ->options(['right' => 'يمين', 'center' => 'الوسط', 'left' => 'شمال'])
                                ->default('center'),

                            Forms\Components\Repeater::make('features')
                                ->label('كروت المميزات (٣ كحد أقصى)')
                                ->schema([
                                    Forms\Components\Select::make('icon')
                                        ->label('الأيقونة')
                                        ->options([
                                            'booking' => 'حجز', 'coverage' => 'تغطية مناطق', 'shield' => 'ضمان',
                                            'maintenance' => 'صيانة', 'cleaning' => 'تنظيف', 'freon' => 'فريون',
                                            'install' => 'تركيب', 'move' => 'نقل', 'repair' => 'إصلاح',
                                            'contract' => 'عقد', 'pipes' => 'مواسير', 'diagnose' => 'تشخيص',
                                        ]),
                                    Forms\Components\TextInput::make('title')->label('العنوان')->required()
                                        ->helperText('تقدر تستخدم {areas_count} لعدد المناطق المنشورة.'),
                                    Forms\Components\Textarea::make('text')->label('النص')->rows(2)->columnSpanFull(),
                                ])
                                ->maxItems(3)
                                ->columns(2)
                                ->reorderable()
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                                ->addActionLabel('إضافة كارت')
                                ->columnSpanFull()
                                ->default([]),
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

                // مين إحنا (بصورة)
                Forms\Components\Tabs\Tab::make('مين إحنا')->schema([
                    Forms\Components\TextInput::make('about_home.eyebrow')->label('الشارة العلوية'),
                    Forms\Components\TextInput::make('about_home.cta_label')->label('نص الزرار'),
                    Forms\Components\TextInput::make('about_home.title')->label('العنوان')->columnSpanFull(),
                    Forms\Components\Textarea::make('about_home.body')->label('النص')->rows(3)->columnSpanFull(),
                    Forms\Components\FileUpload::make('about_home.image')
                        ->label('الصورة')->image()->disk('sections')->visibility('public')->maxSize(8192)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->saveUploadedFileUsing(fn ($file) => \App\Support\ImageUploads::storeWebp($file, 'sections'))
                        ->helperText('بتتحوّل WebP تلقائياً. من غير صورة بيظهر تدرّج بديل.')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('about_home.image_alt')
                        ->label('وصف الصورة (alt) — إجباري مع الصورة')
                        ->required(fn (Forms\Get $get) => filled($get('about_home.image')))->maxLength(160),
                    Forms\Components\TextInput::make('about_home.cta_href')->label('رابط الزرار')->default('/about'),
                    Forms\Components\TagsInput::make('about_home.points')
                        ->label('نقاط المميزات')->helperText('كل نقطة عنصر — Enter بعد كل واحدة.')->columnSpanFull(),
                ])->columns(2),

                // خدماتنا بالصور
                Forms\Components\Tabs\Tab::make('خدماتنا بالصور')->schema([
                    Forms\Components\TextInput::make('visual_services.title')->label('عنوان القسم'),
                    Forms\Components\TextInput::make('visual_services.subtitle')->label('وصف القسم'),
                    Forms\Components\Repeater::make('visual_services.items')
                        ->label('الخدمات (كل واحدة بصورة)')
                        ->schema([
                            Forms\Components\FileUpload::make('image')
                                ->label('الصورة')->image()->disk('sections')->visibility('public')->maxSize(8192)
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                ->saveUploadedFileUsing(fn ($file) => \App\Support\ImageUploads::storeWebp($file, 'sections'))
                                ->helperText('بتتحوّل WebP تلقائياً. من غير صورة بتظهر الأيقونة فوق تدرّج.')
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('image_alt')->label('وصف الصورة (alt)')
                                ->required(fn (Forms\Get $get) => filled($get('image')))->maxLength(160),
                            Forms\Components\Select::make('icon')->label('أيقونة احتياطية')
                                ->options([
                                    'maintenance' => 'صيانة', 'cleaning' => 'تنظيف', 'freon' => 'فريون',
                                    'install' => 'تركيب', 'move' => 'نقل', 'repair' => 'إصلاح',
                                    'contract' => 'عقد', 'pipes' => 'مواسير', 'diagnose' => 'تشخيص',
                                ])->default('maintenance'),
                            Forms\Components\TextInput::make('title')->label('العنوان')->required(),
                            Forms\Components\TextInput::make('slug')->label('Slug الخدمة (اختياري)')
                                ->helperText('يربط البطاقة بصفحة الخدمة لو منشورة.'),
                            Forms\Components\TextInput::make('booking')->label('خدمة الحجز')
                                ->helperText('تطابق اسم من قايمة الحجز.'),
                            Forms\Components\Textarea::make('desc')->label('الوصف')->rows(2)->columnSpanFull(),
                        ])
                        ->columns(2)->reorderable()->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->addActionLabel('إضافة خدمة')->default([])->columnSpanFull(),
                ])->columns(2),

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
