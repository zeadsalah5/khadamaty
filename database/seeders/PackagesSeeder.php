<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

/**
 * نقل الباقات من config/site.packages للجدول (المرحلة C3) — بنفس القيم.
 * idempotent حسب الاسم.
 */
class PackagesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ((array) config('site.packages', []) as $i => $pkg) {
            Package::updateOrCreate(
                ['name' => $pkg['name']],
                [
                    'price'     => $pkg['price'] ?? null,
                    'unit'      => $pkg['unit'] ?? null,
                    'featured'  => (bool) ($pkg['featured'] ?? false),
                    'features'  => $pkg['features'] ?? [],
                    'sort'      => $i,
                    'is_active' => true,
                ],
            );
        }
    }
}
