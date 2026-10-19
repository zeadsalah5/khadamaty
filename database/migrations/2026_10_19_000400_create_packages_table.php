<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * الباقات والأسعار (المرحلة C3) — بتتدار من الداشبورد.
 * السعر نص (بيقبل أرقام عربي زي ٠٠٠). لو الجدول فاضي بيرجع لـ config/settings.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('price')->nullable();
            $table->string('unit')->nullable();
            $table->boolean('featured')->default(false);
            $table->json('features')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
