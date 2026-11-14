<?php

namespace App\Filament\Pages;

use App\Support\MailSettings;
use App\Support\Settings;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

/**
 * الإشعارات والبريد: الإيميل اللي بتوصله إشعارات الطلبات/الحجوزات،
 * وإعدادات السيرفر (SMTP) عشان الإيميلات تتبعت فعلاً — كله من الداشبورد.
 * الباسورد بيتخزّن مشفّر. أدمن فقط.
 */
class ManageNotifications extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'الإعدادات';

    protected static ?string $navigationLabel = 'الإشعارات والبريد';

    protected static ?string $title = 'الإشعارات والبريد';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'order_notify_email'    => Settings::get('order_notify_email'),
            'order_notify_whatsapp' => Settings::get('order_notify_whatsapp'),
            'mail_enabled'          => (bool) Settings::get('mail_enabled', false),
            'mail_host'             => Settings::get('mail_host'),
            'mail_port'             => Settings::get('mail_port', 587),
            'mail_username'         => Settings::get('mail_username'),
            'mail_password'         => '', // مبنعرضش الباسورد المخزّن
            'mail_encryption'       => Settings::get('mail_encryption', 'tls'),
            'mail_from_address'     => Settings::get('mail_from_address'),
            'mail_from_name'        => Settings::get('mail_from_name'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('إشعارات الطلبات والحجوزات')
                ->description('لما يجيلك طلب من المتجر أو حجز خدمة، بنبعتلك إيميل على العنوان ده.')
                ->schema([
                    Forms\Components\TextInput::make('order_notify_email')
                        ->label('إيميل الإشعارات (الجيميل بتاعك)')
                        ->email()->maxLength(255)
                        ->helperText('اكتب جيميلك هنا — كل طلب/حجز جديد هيوصلك عليه.'),
                    Forms\Components\TextInput::make('order_notify_whatsapp')
                        ->label('واتساب الإشعارات (اختياري)')->maxLength(30)
                        ->helperText('دولي بدون + — لرابط واتساب جاهز في لوحة الطلبات. فاضي = رقم الواتساب العام.'),
                ])->columns(2),

            Forms\Components\Section::make('إعدادات السيرفر للبريد (SMTP)')
                ->description('عشان الإيميلات تتبعت فعلاً لازم تظبط السيرفر. الأسهل تعمل إيميل على استضافتك (مثلاً no-reply@نطاقك) وتحط بياناته هنا. الباسورد بيتخزّن مشفّر.')
                ->schema([
                    Forms\Components\Toggle::make('mail_enabled')
                        ->label('فعّل إرسال الإيميل عبر SMTP')
                        ->helperText('من غير تفعيل، الإيميلات بتتسجّل في اللوج بس ومبتتبعتش.')
                        ->live()->columnSpanFull(),

                    Forms\Components\TextInput::make('mail_host')
                        ->label('SMTP Host')->placeholder('smtp.hostinger.com')
                        ->visible(fn (Forms\Get $get) => $get('mail_enabled')),
                    Forms\Components\TextInput::make('mail_port')
                        ->label('المنفذ (Port)')->numeric()->placeholder('465 أو 587')
                        ->visible(fn (Forms\Get $get) => $get('mail_enabled')),
                    Forms\Components\TextInput::make('mail_username')
                        ->label('اسم المستخدم (الإيميل)')->placeholder('no-reply@نطاقك')
                        ->visible(fn (Forms\Get $get) => $get('mail_enabled')),
                    Forms\Components\TextInput::make('mail_password')
                        ->label('كلمة سر الإيميل')->password()->revealable()
                        ->placeholder('سيبها فاضية لو مش عايز تغيّرها')
                        ->helperText('بتتخزّن مشفّرة. سيبها فاضية عشان تحافظ على القديمة.')
                        ->visible(fn (Forms\Get $get) => $get('mail_enabled')),
                    Forms\Components\Select::make('mail_encryption')
                        ->label('نوع التشفير')
                        ->options(['ssl' => 'SSL (منفذ 465)', 'tls' => 'TLS (منفذ 587)', 'none' => 'بدون'])
                        ->default('tls')
                        ->visible(fn (Forms\Get $get) => $get('mail_enabled')),
                    Forms\Components\TextInput::make('mail_from_address')
                        ->label('يتبعت من (From)')->email()->placeholder('no-reply@نطاقك')
                        ->helperText('غالباً نفس اسم المستخدم فوق.')
                        ->visible(fn (Forms\Get $get) => $get('mail_enabled')),
                    Forms\Components\TextInput::make('mail_from_name')
                        ->label('اسم المرسِل (From Name)')->placeholder(site('name'))
                        ->visible(fn (Forms\Get $get) => $get('mail_enabled')),
                ])->columns(2),
        ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Settings::setMany([
            'order_notify_email'    => $data['order_notify_email'] ?? null,
            'order_notify_whatsapp' => $data['order_notify_whatsapp'] ?? null,
            'mail_enabled'          => (bool) ($data['mail_enabled'] ?? false),
            'mail_host'             => $data['mail_host'] ?? null,
            'mail_port'             => $data['mail_port'] ?? null,
            'mail_username'         => $data['mail_username'] ?? null,
            'mail_encryption'       => $data['mail_encryption'] ?? 'tls',
            'mail_from_address'     => $data['mail_from_address'] ?? null,
            'mail_from_name'        => $data['mail_from_name'] ?? null,
        ], 'mail');

        // الباسورد: نحدّثه بس لو اتكتب (مشفّر). فاضي = نسيب القديم زي ما هو.
        if (filled($data['mail_password'] ?? null)) {
            Settings::set('mail_password', Crypt::encryptString($data['mail_password']), 'mail');
        }

        Notification::make()->title('اتحفظت إعدادات الإشعارات والبريد.')->success()->send();
    }

    public function sendTest(): void
    {
        $this->save();

        $email = trim((string) Settings::get('order_notify_email', ''));
        if ($email === '') {
            Notification::make()->title('اكتب إيميل الإشعارات الأول.')->warning()->send();

            return;
        }

        try {
            MailSettings::apply();
            Mail::raw(
                'ده إيميل تجريبي من لوحة ' . site('name') . '. لو وصلك يبقى إعدادات البريد شغّالة تمام ✅',
                fn ($m) => $m->to($email)->subject('إيميل تجريبي — ' . site('name')),
            );

            Notification::make()
                ->title('اتبعت إيميل تجريبي لـ ' . $email)
                ->body('لو مجاش خلال دقيقة، راجع إعدادات SMTP أو مجلد الـ Spam.')
                ->success()->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('فشل إرسال الإيميل التجريبي')
                ->body($e->getMessage())
                ->danger()->persistent()->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')->label('حفظ التغييرات')->submit('save'),
            Action::make('sendTest')->label('إرسال إيميل تجريبي')->color('gray')
                ->icon('heroicon-o-paper-airplane')->action('sendTest'),
        ];
    }
}
