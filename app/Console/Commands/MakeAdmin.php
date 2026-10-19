<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

/**
 * إنشاء/تحديث مستخدم لوحة الإدارة بشكل تفاعلي وآمن.
 * الباسورد بيتقرا مخفي (password prompt) ومبيتطبعش أبداً.
 *
 * التشغيل:  php artisan khadamaty:make-admin
 */
class MakeAdmin extends Command
{
    protected $signature = 'khadamaty:make-admin';

    protected $description = 'إنشاء أو تحديث مستخدم لوحة الإدارة (الباسورد بيتدخل مخفي ومبيتطبعش)';

    public function handle(): int
    {
        $name = text(
            label: 'الاسم',
            required: true,
            default: 'مدير الموقع',
        );

        $email = text(
            label: 'البريد الإلكتروني',
            required: true,
            validate: fn (string $value) => Validator::make(
                ['email' => $value],
                ['email' => 'required|email'],
            )->fails() ? 'البريد غير صالح.' : null,
        );

        $role = select(
            label: 'الدور',
            options: [
                'admin' => 'أدمن (كل الصلاحيات)',
                'editor' => 'محرر (المحتوى فقط)',
            ],
            default: 'admin',
        );

        $plain = password(
            label: 'الباسورد',
            required: true,
            validate: fn (string $value) => Validator::make(
                ['password' => $value],
                ['password' => ['required', PasswordRule::min(8)]],
            )->fails() ? 'الباسورد لازم 8 حروف على الأقل.' : null,
        );

        $confirm = password(label: 'أكّد الباسورد', required: true);

        if ($plain !== $confirm) {
            $this->error('الباسورد وتأكيده مش متطابقين. حاول تاني.');

            return self::FAILURE;
        }

        $existing = User::where('email', $email)->exists();

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'role' => $role,
                'password' => Hash::make($plain),
            ],
        );

        // ماننفعش نطبع الباسورد — بنطبع تأكيد بس.
        $this->info(($existing ? 'اتحدّث' : 'اتنشأ') . " المستخدم: {$user->name} <{$user->email}> — الدور: {$role}.");
        $this->line('تقدر تدخل لوحة الإدارة من: ' . url('/admin'));

        return self::SUCCESS;
    }
}
