<?php

namespace App\Support\Store;

/**
 * تعريف أعمدة Excel للمنتجات — مصدر واحد للاستيراد والتصدير والقالب.
 * العناوين (الصف الأول) بتكون بالمفاتيح الإنجليزية علشان الـ mapping يبقى ثابت.
 */
class ProductColumns
{
    /** ترتيب الأعمدة الرسمي. */
    public const KEYS = [
        'name', 'sku', 'brand', 'category', 'condition',
        'price', 'sale_price', 'stock', 'specs',
        'slug', 'description', 'model_number', 'warranty_note',
    ];

    /** الأعمدة الإلزامية في كل صف. */
    public const REQUIRED = ['name', 'category'];

    /** شرح عربي لكل عمود (شيت التعليمات في القالب). */
    public static function descriptions(): array
    {
        return [
            'name'          => 'اسم المنتج (إلزامي).',
            'sku'           => 'كود المنتج — لو اتكرر بيحدّث المنتج الموجود بدل ما يعمل جديد.',
            'brand'         => 'اسم الماركة (لازم تكون موجودة مسبقاً).',
            'category'      => 'اسم القسم أو الـ slug بتاعه (إلزامي، لازم يكون موجود).',
            'condition'     => 'new أو used (أو: جديد / مستعمل).',
            'price'         => 'السعر بالجنيه — رقم.',
            'sale_price'    => 'سعر العرض (اختياري) — رقم.',
            'stock'         => 'الكمية بالمخزون — رقم صحيح.',
            'specs'         => 'المواصفات: key=value;key2=value2 — المفاتيح حسب حقول القسم.',
            'slug'          => 'رابط إنجليزي (اختياري) — يتولّد تلقائياً لو فاضي.',
            'description'   => 'وصف تفصيلي (اختياري).',
            'model_number'  => 'رقم الموديل (اختياري).',
            'warranty_note' => 'ملاحظة الضمان (اختياري).',
        ];
    }

    /**
     * يبني map [key => index] من صف العناوين.
     * لو العناوين مش متعرّفة، بيرجّع الترتيب الافتراضي بالموضع.
     */
    public static function resolveIndexes(array $headerRow): array
    {
        $normalized = array_map(
            fn ($cell) => strtolower(trim((string) $cell)),
            $headerRow
        );

        $map = [];
        foreach (self::KEYS as $key) {
            $pos = array_search($key, $normalized, true);
            if ($pos !== false) {
                $map[$key] = $pos;
            }
        }

        // لو مفيش أي عنوان متعرّف => ترتيب افتراضي بالموضع.
        if (empty($map)) {
            foreach (self::KEYS as $i => $key) {
                $map[$key] = $i;
            }
        }

        return $map;
    }

    /** يفكّك خلية specs: "a=1;b=2" => ['a'=>'1','b'=>'2']؛ ويقبل JSON كمان. */
    public static function parseSpecs(?string $raw): array
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return [];
        }

        // JSON؟
        if (str_starts_with($raw, '{')) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        $specs = [];
        foreach (preg_split('/[;\n]+/', $raw) as $pair) {
            if (! str_contains($pair, '=')) {
                continue;
            }
            [$k, $v] = array_map('trim', explode('=', $pair, 2));
            if ($k !== '') {
                $specs[$k] = $v;
            }
        }

        return $specs;
    }

    /** يركّب خلية specs من array للتصدير. */
    public static function encodeSpecs(?array $specs): string
    {
        if (empty($specs)) {
            return '';
        }

        return collect($specs)
            ->map(fn ($v, $k) => $k . '=' . $v)
            ->implode(';');
    }
}
