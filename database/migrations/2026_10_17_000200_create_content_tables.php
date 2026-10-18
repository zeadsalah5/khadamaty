<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // الحقول المشتركة لكل جداول المحتوى
        $common = function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->boolean('is_published')->default(false)->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        };

        Schema::create('services', function (Blueprint $table) use ($common) {
            $common($table);
            $table->string('name_ar');
            $table->string('excerpt')->nullable();
            $table->longText('body')->nullable();
        });

        Schema::create('appliances', function (Blueprint $table) use ($common) {
            $common($table);
            $table->string('name_ar');
            $table->string('excerpt')->nullable();
        });

        Schema::create('areas', function (Blueprint $table) use ($common) {
            $common($table);
            $table->string('name_ar');
        });

        Schema::create('brands', function (Blueprint $table) use ($common) {
            $common($table);
            $table->string('name_ar');
        });

        Schema::create('posts', function (Blueprint $table) use ($common) {
            $common($table);
            $table->string('title_ar');
            $table->string('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->string('cover')->nullable();
        });

        Schema::create('products', function (Blueprint $table) use ($common) {
            $common($table);
            $table->string('name_ar');
            $table->string('excerpt')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('sku')->nullable();
        });

        Schema::create('vendors', function (Blueprint $table) use ($common) {
            $common($table);
            $table->string('name_ar');
            $table->text('about')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('products');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('areas');
        Schema::dropIfExists('appliances');
        Schema::dropIfExists('services');
    }
};
