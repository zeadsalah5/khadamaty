<?php

namespace App\Support\Store;

use App\Models\Product;
use App\Support\ContentCache;

/**
 * مساعدات واجهة المتجر (المرحلة 6B).
 * - علم «فيه منتج منشور؟» (متكاش، بيتمسح مع أي حفظ منتج عبر الـ observer) —
 *   بيتحكم في ظهور رابط «المتجر» في الهيدر وفهرسة /shop.
 */
class Storefront
{
    /** فيه منتج واحد منشور على الأقل؟ (متكاش). */
    public static function hasPublished(): bool
    {
        return (bool) ContentCache::remember(
            'shop.has_published',
            fn () => Product::query()->published()->exists()
        );
    }
}
