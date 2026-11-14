<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * تتبّع ضغطات أزرار التواصل (اتصال / واتساب) — عشان نعرف كام حد
 * اتصل أو راسل من الموقع. بيتسجّل عبر beacon من الجافاسكريبت.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_clicks', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->index();   // call | whatsapp
            $table->string('path', 512)->nullable(); // الصفحة اللي اتضغط منها
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_clicks');
    }
};
