<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /*
        | مستخدم الأدمن للدخول على لوحة الإدارة (/admin — Filament).
        | البيانات بتتقرا من env لو موجودة، وإلا بيانات افتراضية للتطوير المحلي.
        | TODO (مهم للإنتاج): غيّر الإيميل والباسورد — حطّ ADMIN_EMAIL و
        |       ADMIN_PASSWORD في ملف .env، أو عدّل اليوزر من tinker بعد النشر.
        */
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@khadamaty.test')],
            [
                'name' => 'مدير الموقع',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
            ]
        );

        $this->call(ContentSeeder::class);
    }
}
