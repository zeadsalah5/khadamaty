<?php

namespace App\Support\Store;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Str;
use OpenSpout\Reader\XLSX\Reader;

/**
 * استيراد المنتجات من XLSX بـ OpenSpout — صف-بصف مع validation وتقرير أخطاء.
 * قواعد:
 *  - الصف الغلط يتعدّى والاستيراد مايفشلش كله (يتجمّع في تقرير برقم الصف والسبب).
 *  - الصفوف الجديدة draft دايماً (is_published=false) — مفيش نشر تلقائي.
 *  - match بالـ sku: موجود => تحديث، مش موجود => إنشاء.
 */
class ProductsImporter
{
    protected int $created = 0;
    protected int $updated = 0;
    protected int $skipped = 0;

    /** @var array<int, array{row:int, messages:array<int,string>}> */
    protected array $errors = [];

    /** @var array<int, string> */
    protected array $warnings = [];

    /** يستورد ملف ويرجّع تقرير. */
    public static function import(string $path): array
    {
        return (new self())->run($path);
    }

    protected function run(string $path): array
    {
        $reader = new Reader();
        $reader->open($path);

        $indexes = null;
        $rowNum = 0;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rowNum++;
                $cells = $row->toArray();

                if ($rowNum === 1) {
                    $indexes = ProductColumns::resolveIndexes($cells);

                    continue;
                }

                // صف فاضي تماماً => تجاهل صامت.
                if (collect($cells)->every(fn ($c) => trim((string) $c) === '')) {
                    continue;
                }

                $this->processRow($cells, $indexes, $rowNum);
            }

            break; // الشيت الأول بس.
        }

        $reader->close();

        return [
            'created'  => $this->created,
            'updated'  => $this->updated,
            'skipped'  => $this->skipped,
            'errors'   => $this->errors,
            'warnings' => $this->warnings,
        ];
    }

    protected function processRow(array $cells, array $indexes, int $rowNum): void
    {
        $get = fn (string $key) => trim((string) ($cells[$indexes[$key] ?? -1] ?? ''));

        $errors = [];

        // ── الاسم ──
        $name = $get('name');
        if ($name === '') {
            $errors[] = 'الاسم مطلوب';
        }

        // ── القسم ──
        $categoryRaw = $get('category');
        $category = null;
        if ($categoryRaw === '') {
            $errors[] = 'القسم مطلوب';
        } else {
            $category = ProductCategory::where('name_ar', $categoryRaw)
                ->orWhere('slug', $categoryRaw)->first();
            if (! $category) {
                $errors[] = "القسم «{$categoryRaw}» مش موجود";
            }
        }

        // ── الحالة (new/used) ──
        $conditionRaw = $get('condition');
        $condition = match (mb_strtolower($conditionRaw)) {
            'used', 'مستعمل' => 'used',
            'new', 'جديد', '' => 'new',
            default => null,
        };
        if ($condition === null) {
            $errors[] = "condition لازم new أو used (القيمة: «{$conditionRaw}»)";
        }

        // ── الماركة (اختيارية، لازم موجودة لو اتكتبت) ──
        $brandRaw = $get('brand');
        $brand = null;
        if ($brandRaw !== '') {
            $brand = Brand::where('name_ar', $brandRaw)->first();
            if (! $brand) {
                $errors[] = "الماركة «{$brandRaw}» مش موجودة";
            }
        }

        // ── الأرقام ──
        foreach (['price' => 'السعر', 'sale_price' => 'سعر العرض', 'stock' => 'المخزون'] as $f => $label) {
            $v = $get($f);
            if ($v !== '' && ! is_numeric($v)) {
                $errors[] = "{$label} لازم رقم (القيمة: «{$v}»)";
            }
        }

        if (! empty($errors)) {
            $this->skipped++;
            $this->errors[] = ['row' => $rowNum, 'messages' => $errors];

            return;
        }

        // ── المواصفات ──
        $specs = ProductColumns::parseSpecs($get('specs'));
        if ($category && $specs) {
            $known = $category->attributes()->pluck('key')->all();
            foreach (array_keys($specs) as $k) {
                if (! in_array($k, $known, true)) {
                    $this->warnings[] = "صف {$rowNum}: مفتاح مواصفة غير معروف للقسم «{$category->name_ar}»: {$k}";
                }
            }
        }

        $price = $get('price');
        $salePrice = $get('sale_price');
        $stock = $get('stock');

        $data = [
            'name_ar'       => $name,
            'category_id'   => $category->id,
            'brand_id'      => $brand?->id,
            'condition'     => $condition,
            'price'         => $price === '' ? null : (float) $price,
            'sale_price'    => $salePrice === '' ? null : (float) $salePrice,
            'stock_qty'     => $stock === '' ? 0 : (int) $stock,
            'specs'         => $specs ?: null,
            'description'   => $get('description') ?: null,
            'model_number'  => $get('model_number') ?: null,
            'warranty_note' => $get('warranty_note') ?: null,
        ];

        $sku = $get('sku');
        $existing = $sku !== '' ? Product::where('sku', $sku)->first() : null;

        if ($existing) {
            $existing->fill($data)->save();

            // أمان: لو التحديث خلّى منتج منشور ناقص => رجّعه مسوّدة.
            if ($existing->is_published && ! $existing->load(['images', 'usedDetails'])->hasCompleteContent()) {
                $existing->forceFill(['is_published' => false, 'status' => 'draft'])->save();
            }

            $this->updated++;
        } else {
            Product::create($data + [
                'sku'          => $sku ?: null,
                'slug'         => $this->uniqueSlug($get('slug'), $name, $sku),
                'status'       => 'draft',
                'is_published' => false,
            ]);

            $this->created++;
        }
    }

    /** slug فريد: من المُدخل، وإلا من الـ sku، وإلا عشوائي (الاسم العربي مش بيصلح slug). */
    protected function uniqueSlug(string $provided, string $name, string $sku): string
    {
        $base = Str::slug($provided) ?: (Str::slug($sku) ?: 'product-' . Str::lower(Str::random(6)));
        $slug = $base;
        $i = 2;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
