<?php

namespace App\Support\Store;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Brand;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

/**
 * تصدير المنتجات لـ XLSX + توليد القالب النموذجي — بـ OpenSpout (متثبّت مع Filament).
 */
class ProductsExporter
{
    /** يصدّر كل المنتجات (أو استعلام) لملف XLSX مؤقت ويرجّع مساره. */
    public static function export(?iterable $products = null): string
    {
        $products ??= Product::query()->with(['brand', 'category'])->orderBy('name_ar')->get();

        $path = self::tempPath('products');
        $writer = new Writer();
        $writer->openToFile($path);
        $writer->getCurrentSheet()->setName('المنتجات');

        $writer->addRow(Row::fromValues(ProductColumns::KEYS));

        foreach ($products as $product) {
            $writer->addRow(Row::fromValues(self::rowValues($product)));
        }

        $writer->close();

        return $path;
    }

    /** يولّد قالب نموذجي: عناوين + صفوف مثال + شيت تعليمات بالأقسام/الماركات المتاحة. */
    public static function template(): string
    {
        $path = self::tempPath('products-template');
        $writer = new Writer();
        $writer->openToFile($path);

        // شيت المنتجات: العناوين + مثالين.
        $writer->getCurrentSheet()->setName('المنتجات');
        $writer->addRow(Row::fromValues(ProductColumns::KEYS));
        foreach (self::exampleRows() as $example) {
            $writer->addRow(Row::fromValues($example));
        }

        // شيت التعليمات.
        $writer->addNewSheetAndMakeItCurrent();
        $writer->getCurrentSheet()->setName('التعليمات');
        $writer->addRow(Row::fromValues(['العمود', 'الشرح']));
        foreach (ProductColumns::descriptions() as $key => $desc) {
            $writer->addRow(Row::fromValues([$key, $desc]));
        }

        $writer->addRow(Row::fromValues(['', '']));
        $writer->addRow(Row::fromValues(['الأقسام المتاحة (name — slug — مفاتيح المواصفات):', '']));
        foreach (ProductCategory::query()->with('attributes')->orderBy('name_ar')->get() as $cat) {
            $keys = $cat->attributes->pluck('key')->implode(', ');
            $writer->addRow(Row::fromValues([$cat->name_ar . ' — ' . $cat->slug, $keys]));
        }

        $writer->addRow(Row::fromValues(['', '']));
        $writer->addRow(Row::fromValues(['الماركات المتاحة:', Brand::query()->orderBy('name_ar')->pluck('name_ar')->implode('، ')]));

        $writer->close();

        return $path;
    }

    /** @return array<int, scalar|null> */
    protected static function rowValues(Product $p): array
    {
        return [
            $p->name_ar,
            $p->sku,
            $p->brand?->name_ar,
            $p->category?->name_ar,
            $p->condition,
            $p->price,
            $p->sale_price,
            $p->stock_qty,
            ProductColumns::encodeSpecs($p->specs),
            $p->slug,
            $p->description,
            $p->model_number,
            $p->warranty_note,
        ];
    }

    protected static function exampleRows(): array
    {
        return [
            ['سبليت 1.5 حصان بارد (مثال)', 'AC-EX-001', '', 'تكييفات', 'new', 12000, 10999, 5,
                'horsepower=1.5;type=سبليت;inverter=نعم;mode=بارد;gas_type=R32', '', 'وصف اختياري', 'XR-12', 'ضمان سنة'],
            ['قطعة غيار (مثال مستعمل)', 'SP-EX-002', '', 'قطع غيار', 'used', 350, '', 1,
                '', '', '', '', ''],
        ];
    }

    protected static function tempPath(string $prefix): string
    {
        $dir = storage_path('app/store-tmp');
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        return $dir . DIRECTORY_SEPARATOR . $prefix . '-' . Str::random(10) . '.xlsx';
    }
}
