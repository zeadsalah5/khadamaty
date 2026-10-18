<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('name');                           // اسم العميل (زي ما بيظهر)
            $table->string('area')->nullable();               // المنطقة (اختياري)
            $table->string('service')->nullable();            // الخدمة اللي اتقدّمت (اختياري)
            $table->unsignedTinyInteger('rating')->default(5); // من 1 لـ 5
            $table->text('body');                             // نص التقييم
            // pending = مستني مراجعة | approved = معتمد ويظهر على الموقع
            $table->string('status')->default('pending')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
