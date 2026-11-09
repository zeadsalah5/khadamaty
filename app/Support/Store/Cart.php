<?php

namespace App\Support\Store;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

/**
 * سلة الضيف (session) — المرحلة 6C.
 *
 * مبادئ ثابتة:
 * - التخزين خفيف: [product_id => qty] بس. مفيش أسعار متخزّنة في الـ session.
 * - كل الأسعار والتوفّر بيتقروا من الداتابيز وقت العرض (server-side) — العميل
 *   مايقدرش يعدّل السعر أبداً.
 * - الجهاز المستعمل كمية 1 بس (enforced)، وأي كمية بتتقصّ على المخزون المتاح.
 * - المنتج اللي اتباع/نفد/اتشال من النشر بيتشال من السلة تلقائياً وقت العرض.
 */
class Cart
{
    public const SESSION_KEY = 'cart';

    /** أقصى كمية للبند الواحد (سقف أمان فوق المخزون). */
    public const MAX_QTY = 20;

    /** المحتوى الخام: [product_id => qty]. */
    public static function raw(): array
    {
        return array_map('intval', (array) Session::get(self::SESSION_KEY, []));
    }

    protected static function put(array $items): void
    {
        $items = array_filter($items, fn ($qty) => (int) $qty > 0);

        if (empty($items)) {
            Session::forget(self::SESSION_KEY);
        } else {
            Session::put(self::SESSION_KEY, $items);
        }
    }

    /** إضافة كمية لمنتج (بيزوّد على الموجود). بيرجّع false لو المنتج مش قابل للبيع. */
    public static function add(int $productId, int $qty = 1): bool
    {
        $product = static::sellable($productId);

        if (! $product) {
            return false;
        }

        $items = static::raw();
        $current = $items[$productId] ?? 0;
        $items[$productId] = static::clampQty($product, $current + max(1, $qty));

        static::put($items);

        return true;
    }

    /** ضبط كمية منتج لقيمة محددة (0 = حذف). */
    public static function update(int $productId, int $qty): void
    {
        $items = static::raw();

        if ($qty <= 0) {
            unset($items[$productId]);
            static::put($items);

            return;
        }

        $product = static::sellable($productId);

        if (! $product) {
            unset($items[$productId]);
            static::put($items);

            return;
        }

        $items[$productId] = static::clampQty($product, $qty);
        static::put($items);
    }

    public static function remove(int $productId): void
    {
        $items = static::raw();
        unset($items[$productId]);
        static::put($items);
    }

    public static function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * بنود السلة الحيّة (المتاحة فقط) مع بيانات المنتج والسعر من الداتابيز.
     * بيرجّع Collection من كائنات فيها: product, qty, unit_price, line_total, adjusted.
     * ملاحظة: بيزامن الـ session (يشيل المنتجات اللي اتشالت، ويقصّ الكميات).
     */
    public static function lines(): Collection
    {
        $items = static::raw();

        if (empty($items)) {
            return collect();
        }

        $products = Product::query()->published()
            ->whereIn('id', array_keys($items))
            ->with(['primaryImage', 'category', 'brand'])
            ->get()
            ->keyBy('id');

        $lines = collect();
        $synced = [];

        foreach ($items as $id => $qty) {
            $product = $products->get($id);

            // اتشال من النشر/اتباع/نفد => يتشال من السلة.
            if (! $product || ! $product->isInStock() || ! $product->hasRealPrice()) {
                continue;
            }

            $effectiveQty = static::clampQty($product, (int) $qty);
            $unitPrice = (float) $product->displayPrice();

            $synced[$id] = $effectiveQty;

            $lines->push((object) [
                'product'    => $product,
                'qty'        => $effectiveQty,
                'unit_price' => $unitPrice,
                'line_total' => $unitPrice * $effectiveQty,
                'adjusted'   => $effectiveQty !== (int) $qty, // اتقصّت الكمية؟
            ]);
        }

        // زامن الـ session لو فيه فرق (منتجات اتشالت أو كميات اتقصّت).
        if ($synced !== $items) {
            static::put($synced);
        }

        return $lines;
    }

    /** إجمالي عدد القطع المتاحة في السلة (للعداد في الهيدر). */
    public static function count(): int
    {
        return (int) static::lines()->sum('qty');
    }

    /** إجمالي سعر البضاعة (من غير توصيل) — server-side. */
    public static function subtotal(): float
    {
        return (float) static::lines()->sum('line_total');
    }

    public static function isEmpty(): bool
    {
        return static::lines()->isEmpty();
    }

    // ───────────────────────── مساعدات داخلية ─────────────────────────

    /** المنتج لو قابل للبيع (منشور + متوفر + له سعر حقيقي)، وإلا null. */
    protected static function sellable(int $productId): ?Product
    {
        $product = Product::query()->published()->find($productId);

        if (! $product || ! $product->isInStock() || ! $product->hasRealPrice()) {
            return null;
        }

        return $product;
    }

    /** يقصّ الكمية على المتاح: المستعمل 1، والباقي على المخزون وسقف الأمان. */
    protected static function clampQty(Product $product, int $qty): int
    {
        $qty = max(1, $qty);

        if ($product->isUsed()) {
            return 1;
        }

        return min($qty, (int) $product->stock_qty, self::MAX_QTY);
    }
}
