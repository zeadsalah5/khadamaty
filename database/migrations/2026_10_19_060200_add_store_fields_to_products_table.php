<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * توسيع جدول products الموجود لأساس المتجر (المرحلة 6A).
 * الأعمدة الموجودة: slug, name_ar, excerpt, price, sku, is_published, published_at.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('id')
                ->constrained('brands')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->after('brand_id')
                ->constrained('product_categories')->nullOnDelete();
            $table->string('condition')->default('new')->after('sku')->index(); // new | used
            $table->longText('description')->nullable()->after('excerpt');
            $table->json('specs')->nullable()->after('description');
            $table->decimal('sale_price', 10, 2)->nullable()->after('price');
            $table->unsignedInteger('stock_qty')->default(0)->after('sale_price');
            $table->string('status')->default('draft')->after('stock_qty')->index(); // draft | published | sold
            $table->string('warranty_note')->nullable()->after('status');
            $table->string('model_number')->nullable()->after('warranty_note');
            $table->string('seo_title')->nullable()->after('model_number');
            $table->string('seo_description')->nullable()->after('seo_title');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['category_id']);
            $table->dropColumn([
                'brand_id', 'category_id', 'condition', 'description', 'specs',
                'sale_price', 'stock_qty', 'status', 'warranty_note', 'model_number',
                'seo_title', 'seo_description',
            ]);
        });
    }
};
