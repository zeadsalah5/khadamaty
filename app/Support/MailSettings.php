<?php

namespace App\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;

/**
 * يطبّق إعدادات البريد (SMTP) اللي بتتظبط من الداشبورد على إعدادات Laravel
 * وقت التشغيل — من غير أي سر في الكود أو في .env.
 * الباسورد بيتخزّن مشفّر (Crypt) في جدول settings وبيتفك هنا بس.
 */
class MailSettings
{
    public static function enabled(): bool
    {
        return (bool) Settings::get('mail_enabled', false);
    }

    /** الباسورد المفكوك (أو null). */
    public static function password(): ?string
    {
        $stored = Settings::get('mail_password');
        if (! is_string($stored) || $stored === '') {
            return null;
        }
        try {
            return Crypt::decryptString($stored);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** يطبّق إعدادات SMTP على config('mail') لو مفعّلة ومكتملة. */
    public static function apply(): void
    {
        if (! self::enabled()) {
            return;
        }

        $host = trim((string) Settings::get('mail_host', ''));
        if ($host === '') {
            return;
        }

        $encryption = (string) (Settings::get('mail_encryption', 'tls') ?: 'tls');
        $port = (int) (Settings::get('mail_port', 587) ?: 587);

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.host', $host);
        Config::set('mail.mailers.smtp.port', $port);
        Config::set('mail.mailers.smtp.username', Settings::get('mail_username') ?: null);
        Config::set('mail.mailers.smtp.password', self::password());
        // ssl = اتصال مشفّر مباشر (465) → scheme=smtps؛ tls/none → STARTTLS تلقائي.
        Config::set('mail.mailers.smtp.scheme', $encryption === 'ssl' ? 'smtps' : null);

        $fromAddress = trim((string) Settings::get('mail_from_address', ''));
        if ($fromAddress !== '') {
            Config::set('mail.from.address', $fromAddress);
        }
        $fromName = trim((string) Settings::get('mail_from_name', ''));
        Config::set('mail.from.name', $fromName !== '' ? $fromName : (string) site('name'));
    }
}
