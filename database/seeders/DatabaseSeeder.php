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
        | مستخدم أدمن افتراضي للتطوير المحلي فقط.
        | في الإنتاج: متزرعش باسورد في الكود — استخدم الأمر التفاعلي:
        |       php artisan khadamaty:make-admin
        | (بيدخل الباسورد مخفي ومبيتطبعش). الـ seeder بيتجاهل الإنشاء في الإنتاج.
        */
        if (! app()->isProduction()) {
            User::updateOrCreate(
                ['email' => env('ADMIN_EMAIL', 'admin@khadamaty.test')],
                [
                    'name' => 'مدير الموقع',
                    'role' => 'admin',
                    'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
                ]
            );
        }

        $this->call(ContentSeeder::class);
        $this->call(SettingsSeeder::class);
        $this->call(PackagesSeeder::class);
        $this->call(FaqsSeeder::class);
        $this->call(MenuSeeder::class);
        $this->call(RedirectsSeeder::class);
        $this->call(PagesSeeder::class);
    }
}
