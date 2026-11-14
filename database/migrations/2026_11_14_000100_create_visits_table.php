<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * سجل زيارات الموقع (تحليلات) — صف لكل زيارة صفحة.
 * بيتسجّل عبر middleware بعد إرسال الرد (مايبطّأش الصفحة).
 * بيخزّن: الـ IP، الصفحة، المصدر (بحث/AI/سوشيال/referral/مباشر)،
 * وهل بوت ولا إنسان + اسم البوت، والمتصفح/الجهاز.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 45)->index();            // IPv4/IPv6
            $table->string('path', 512);                   // الصفحة اللي اتزارت
            $table->string('method', 8)->default('GET');
            $table->text('referer')->nullable();           // الرابط اللي جه منه
            $table->string('referer_host', 255)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('browser', 40)->nullable();     // Chrome / Safari / ...
            $table->string('device', 20)->nullable();      // desktop / mobile / tablet / bot
            $table->boolean('is_bot')->default(false)->index();
            $table->string('bot_name', 60)->nullable();    // Googlebot / GPTBot / ...
            // search | ai | ai_bot | social | referral | direct | internal
            $table->string('source_type', 20)->default('direct')->index();
            $table->string('source_name', 80)->nullable(); // Google / ChatGPT / Facebook / example.com
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
