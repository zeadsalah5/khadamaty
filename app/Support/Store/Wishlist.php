<?php

namespace App\Support\Store;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Wishlist as WishlistItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * المفضلة (wishlist) — المرحلة 6D.
 *
 * - الضيف: قايمة product_ids في الـ session.
 * - المسجّل: صفوف في جدول wishlists (تخزين دائم عبر الأجهزة).
 * - عند الدخول بيتدمج محتوى الـ session في حساب العميل مرة واحدة.
 * - العرض بيرجّع المنتجات المنشورة بس (المتشال/المحذوف بيتجاهل).
 */
class Wishlist
{
    public const SESSION_KEY = 'wishlist';

    /** العميل الحالي (لو مسجّل) أو null. */
    protected static function customer(): ?Customer
    {
        return Auth::guard('customer')->user();
    }

    /** كل الـ product_ids المفضّلة (للمسجّل من DB، للضيف من session). */
    public static function ids(): array
    {
        if ($customer = static::customer()) {
            return $customer->wishlistItems()->pluck('product_id')->map('intval')->all();
        }

        return array_values(array_unique(array_map('intval', (array) Session::get(self::SESSION_KEY, []))));
    }

    public static function has(int $productId): bool
    {
        return in_array($productId, static::ids(), true);
    }

    /** إضافة/شيل من المفضلة؛ بترجّع true لو بقى مضاف، false لو اتشال. */
    public static function toggle(int $productId): bool
    {
        if (static::has($productId)) {
            static::remove($productId);

            return false;
        }

        static::add($productId);

        return true;
    }

    public static function add(int $productId): void
    {
        // لازم يكون منتج موجود (مننضفش IDs وهمية).
        if (! Product::query()->whereKey($productId)->exists()) {
            return;
        }

        if ($customer = static::customer()) {
            WishlistItem::firstOrCreate(['customer_id' => $customer->id, 'product_id' => $productId]);

            return;
        }

        $ids = static::ids();
        $ids[] = $productId;
        Session::put(self::SESSION_KEY, array_values(array_unique($ids)));
    }

    public static function remove(int $productId): void
    {
        if ($customer = static::customer()) {
            $customer->wishlistItems()->where('product_id', $productId)->delete();

            return;
        }

        $ids = array_values(array_filter(static::ids(), fn ($id) => $id !== $productId));
        Session::put(self::SESSION_KEY, $ids);
    }

    public static function count(): int
    {
        return count(static::ids());
    }

    public static function isEmpty(): bool
    {
        return static::count() === 0;
    }

    /** المنتجات المفضّلة المنشورة بس (للعرض). */
    public static function products(): Collection
    {
        $ids = static::ids();

        if (empty($ids)) {
            return collect();
        }

        return Product::query()->published()
            ->whereIn('id', $ids)
            ->with(['primaryImage', 'category', 'brand'])
            ->get()
            ->sortBy(fn ($p) => array_search($p->id, $ids))
            ->values();
    }

    /** دمج مفضلة الـ session في حساب العميل عند الدخول، وتفريغ الـ session. */
    public static function mergeSessionIntoCustomer(Customer $customer): void
    {
        $sessionIds = array_map('intval', (array) Session::get(self::SESSION_KEY, []));

        foreach ($sessionIds as $productId) {
            if (Product::query()->whereKey($productId)->exists()) {
                WishlistItem::firstOrCreate(['customer_id' => $customer->id, 'product_id' => $productId]);
            }
        }

        Session::forget(self::SESSION_KEY);
    }
}
