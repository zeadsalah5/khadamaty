<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * مكتبة الميديا (المرحلة C3) — رفع صور مع alt إجباري وتحويل WebP.
 * بتتخزّن في قرص media (public/images/media).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('disk')->default('media');
            $table->string('path');               // اسم الملف على القرص
            $table->string('alt');                // نص بديل إجباري (SEO + إتاحة)
            $table->string('title')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('mime')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
