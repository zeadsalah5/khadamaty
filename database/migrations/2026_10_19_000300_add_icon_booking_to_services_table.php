<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * نقل خريطة الأيقونة ونوع خدمة الحجز من ServiceController لأعمدة في الجدول
 * (المرحلة C2) — عشان يتعدّلوا من الداشبورد. backfill للصفوف الحالية بنفس القيم
 * فالناتج مايتغيرش.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('icon')->nullable()->after('name_ar');            // مفتاح أيقونة service-icon
            $table->string('booking_service')->nullable()->after('icon');    // نوع الخدمة في فورم الحجز
        });

        $icons = [
            'ac-maintenance' => 'maintenance', 'ac-repair' => 'repair', 'ac-cleaning' => 'cleaning',
            'ac-installation' => 'install', 'ac-gas-refill' => 'freon', 'ac-leak-repair' => 'diagnose',
            'split-ac-maintenance' => 'maintenance', 'central-ac-maintenance' => 'contract',
            'ac-piping-installation' => 'pipes',
        ];
        $booking = [
            'ac-maintenance' => 'صيانة دورية', 'ac-repair' => 'إصلاح عطل', 'ac-cleaning' => 'غسيل وتنظيف',
            'ac-installation' => 'تركيب تكييف', 'ac-gas-refill' => 'شحن فريون', 'ac-leak-repair' => 'إصلاح عطل',
            'split-ac-maintenance' => 'صيانة دورية', 'central-ac-maintenance' => 'صيانة دورية',
            'ac-piping-installation' => 'تركيب تكييف',
        ];

        foreach ($icons as $slug => $icon) {
            DB::table('services')->where('slug', $slug)->update([
                'icon' => $icon,
                'booking_service' => $booking[$slug] ?? null,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['icon', 'booking_service']);
        });
    }
};
