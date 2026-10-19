<?php

namespace Database\Seeders;

use App\Models\CategoryAttribute;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUsedDetail;
use Illuminate\Database\Seeder;

/**
 * المرحلة 6A — بيانات أساس المتجر:
 *  - قسم «تكييفات» بحقول مواصفاته + قسم «قطع غيار».
 *  - منتجات تجريبية مخفية (is_published=false, status=draft) للتجربة.
 * كله placeholder معلّم TODO يعدّله صاحب الموقع من الداشبورد.
 */
class StoreSeeder extends Seeder
{
    public function run(): void
    {
        // ── قسم التكييفات + حقوله ──
        $ac = ProductCategory::updateOrCreate(['slug' => 'air-conditioners'], [
            'name_ar'      => 'تكييفات',
            'excerpt'      => 'تكييفات جديدة ومستعملة بمختلف القدرات.',
            'position'     => 1,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $acAttributes = [
            ['key' => 'horsepower', 'label_ar' => 'القدرة بالحصان', 'type' => 'number', 'unit' => 'حصان', 'position' => 1],
            ['key' => 'type', 'label_ar' => 'النوع', 'type' => 'select', 'position' => 2,
                'options' => ['سبليت', 'شباك', 'كاسيت', 'مخفي', 'دولابي']],
            ['key' => 'inverter', 'label_ar' => 'إنفرتر', 'type' => 'boolean', 'position' => 3],
            ['key' => 'mode', 'label_ar' => 'بارد / ساخن', 'type' => 'select', 'position' => 4,
                'options' => ['بارد', 'بارد وساخن']],
            ['key' => 'gas_type', 'label_ar' => 'نوع الغاز', 'type' => 'select', 'position' => 5,
                'options' => ['R22', 'R32', 'R410A']],
        ];

        foreach ($acAttributes as $attr) {
            CategoryAttribute::updateOrCreate(
                ['product_category_id' => $ac->id, 'key' => $attr['key']],
                $attr + ['product_category_id' => $ac->id]
            );
        }

        // ── قسم قطع الغيار ──
        ProductCategory::updateOrCreate(['slug' => 'spare-parts'], [
            'name_ar'      => 'قطع غيار',
            'excerpt'      => 'قطع غيار ومستلزمات التكييفات والأجهزة.',
            'position'     => 2,
            'is_published' => true,
            'published_at' => now(),
        ]);

        // ── منتجات تجريبية مخفية (draft) ──
        // جديد — بمواصفات (من غير صور => يفضل مسوّدة، وده مطلوب للتجربة).
        Product::updateOrCreate(['slug' => 'test-split-ac-15'], [
            'name_ar'      => 'سبليت 1.5 حصان بارد (تجريبي TODO)',
            'excerpt'      => 'منتج تجريبي للاختبار — عدّله أو احذفه.',
            'sku'          => 'TEST-AC-001',
            'category_id'  => $ac->id,
            'condition'    => 'new',
            'price'        => 12000,
            'sale_price'   => 10999,
            'stock_qty'    => 5,
            'specs'        => [
                'horsepower' => '1.5', 'type' => 'سبليت',
                'inverter' => 'نعم', 'mode' => 'بارد', 'gas_type' => 'R32',
            ],
            'status'       => 'draft',
            'is_published' => false,
        ]);

        // مستعمل — بتفاصيل فحص (من غير 3 صور => يفضل مسوّدة).
        $used = Product::updateOrCreate(['slug' => 'test-used-split-ac'], [
            'name_ar'      => 'سبليت مستعمل 2.25 حصان (تجريبي TODO)',
            'excerpt'      => 'جهاز مستعمل تجريبي للاختبار — عدّله أو احذفه.',
            'sku'          => 'TEST-AC-USED-001',
            'category_id'  => $ac->id,
            'condition'    => 'used',
            'price'        => 7500,
            'stock_qty'    => 1,
            'specs'        => ['horsepower' => '2.25', 'type' => 'سبليت', 'mode' => 'بارد'],
            'status'       => 'draft',
            'is_published' => false,
        ]);

        ProductUsedDetail::updateOrCreate(['product_id' => $used->id], [
            'condition_grade'  => 'جيد جداً',
            'manufacture_year' => 2021,
            'inspected_by'     => 'فني الصيانة (TODO)',
            'inspected_at'     => now()->subDays(3),
            'work_done'        => 'شحن غاز + تنظيف شامل + تغيير ريموت.',
            'inspection'       => [
                ['item' => 'الكمبروسر', 'ok' => true, 'note' => 'يعمل بكفاءة'],
                ['item' => 'التبريد', 'ok' => true, 'note' => ''],
                ['item' => 'جسم الوحدة', 'ok' => true, 'note' => 'خدوش بسيطة'],
            ],
        ]);
    }
}
