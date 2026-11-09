<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدول الإعدادات المفردة (المرحلة C) — مصدر الحقيقة لكل إعداد يتعدّل من الداشبورد.
 * key => value (JSON) مع group للتجميع في الواجهة.
 * القيمة الناقصة بترجع تلقائياً لـ config/site.php (شوف App\Support\Settings).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->default('general')->index();
            $table->string('key')->unique();
            $table->longText('value')->nullable(); // JSON-encoded
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
