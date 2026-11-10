<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * صورة الخدمة (اختيارية) — بترفع من الداشبورد وتتحوّل WebP (قرص services).
 * بتظهر في صفحة الخدمة وكروت الرئيسية وصفحة الخدمات؛ لو فاضية بيظهر
 * الأيقونة فوق تدرّج بارد (fallback). مش بتدخل في شروط النشر (اختيارية).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('image')->nullable()->after('icon');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
