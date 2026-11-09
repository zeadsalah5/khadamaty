<?php

namespace Database\Seeders;

use App\Models\Redirect;
use Illuminate\Database\Seeder;

/**
 * نقل إعادة التوجيه من config/redirects.php للجدول (المرحلة C3) — بنفس القيم.
 */
class RedirectsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ((array) config('redirects', []) as $from => $to) {
            Redirect::updateOrCreate(
                ['from' => ltrim($from, '/')],
                ['to' => $to, 'status' => 301, 'is_active' => true],
            );
        }
    }
}
