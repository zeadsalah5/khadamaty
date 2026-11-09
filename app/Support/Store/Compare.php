<?php

namespace App\Support\Store;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

/**
 * مقارنة المنتجات — المرحلة 6D.
 *
 * - session-based (مؤقتة، مش محتاجة حساب).
 * - لحد 3 منتجات، وكلهم لازم من نفس القسم (enforced server-side).
 * - أول منتج بيحدد القسم؛ أي منتج من قسم مختلف بيترفض برسالة واضحة.
 */
class Compare
{
    public const SESSION_KEY = 'compare';

    public const MAX = 3;

    /** الـ product_ids الحالية (بالترتيب). */
    public static function ids(): array
    {
        return array_values(array_unique(array_map('intval', (array) Session::get(self::SESSION_KEY, []))));
    }

    public static function has(int $productId): bool
    {
        return in_array($productId, static::ids(), true);
    }

    public static function count(): int
    {
        return count(static::ids());
    }

    public static function isEmpty(): bool
    {
        return static::count() === 0;
    }

    /** قسم المقارنة الحالي (قسم أول منتج) أو null. */
    public static function categoryId(): ?int
    {
        $first = static::ids()[0] ?? null;

        if (! $first) {
            return null;
        }

        return Product::query()->whereKey($first)->value('category_id');
    }

    /**
     * إضافة منتج للمقارنة. بترجّع حالة:
     * added | exists | full | category_mismatch | not_found | removed (لو كان موجود → toggle).
     */
    public static function toggle(int $productId): string
    {
        if (static::has($productId)) {
            static::remove($productId);

            return 'removed';
        }

        return static::add($productId);
    }

    public static function add(int $productId): string
    {
        $product = Product::query()->published()->find($productId);

        if (! $product) {
            return 'not_found';
        }

        $ids = static::ids();

        if (in_array($productId, $ids, true)) {
            return 'exists';
        }

        if (count($ids) >= self::MAX) {
            return 'full';
        }

        // لازم نفس قسم المنتجات الموجودة.
        $categoryId = static::categoryId();
        if ($categoryId !== null && (int) $product->category_id !== (int) $categoryId) {
            return 'category_mismatch';
        }

        $ids[] = $productId;
        Session::put(self::SESSION_KEY, $ids);

        return 'added';
    }

    public static function remove(int $productId): void
    {
        $ids = array_values(array_filter(static::ids(), fn ($id) => $id !== $productId));
        Session::put(self::SESSION_KEY, $ids);
    }

    public static function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /** منتجات المقارنة المنشورة بس (بالترتيب) مع العلاقات اللازمة. */
    public static function products(): Collection
    {
        $ids = static::ids();

        if (empty($ids)) {
            return collect();
        }

        $products = Product::query()->published()
            ->whereIn('id', $ids)
            ->with(['primaryImage', 'category', 'brand'])
            ->get()
            ->sortBy(fn ($p) => array_search($p->id, $ids))
            ->values();

        // زامن الـ session لو فيه منتجات اتشالت من النشر.
        $liveIds = $products->pluck('id')->map('intval')->all();
        if ($liveIds !== $ids) {
            Session::put(self::SESSION_KEY, $liveIds);
        }

        return $products;
    }
}
