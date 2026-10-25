<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * تصحيح slug ماركة ميديا: media => midea (الإنجليزي الصحيح Midea).
 * الـ 301 من /brands/media متظبّط في config/redirects.php.
 * صفحات (ماركة × خدمة) بتشير بـ brand_id فمش متأثرة.
 */
return new class extends Migration
{
    public function up(): void
    {
        // لو 'midea' موجود بالفعل (seed جديد) مانعملش تعارض — نمسح 'media' القديم.
        if (DB::table('brands')->where('slug', 'midea')->exists()) {
            DB::table('brands')->where('slug', 'media')->delete();

            return;
        }

        DB::table('brands')->where('slug', 'media')->update(['slug' => 'midea']);
    }

    public function down(): void
    {
        DB::table('brands')->where('slug', 'midea')->update(['slug' => 'media']);
    }
};
