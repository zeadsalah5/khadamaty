@props(['product'])
{{--
    كارت منتج موحّد (المرحلة 6B) — يُستخدم في الهب والقسم والماركة والمستعمل والمشابهة.
    زرار «أضف للسلة» يوجّه لواتساب دلوقتي (السلة الحقيقية في 6C).
--}}
@php
    use Illuminate\Support\Facades\Storage;

    $disk = Storage::disk('products');
    $img = $product->relationLoaded('primaryImage') ? $product->primaryImage : $product->primaryImage;
    $img = $img ?: ($product->relationLoaded('images') ? $product->images->first() : null);

    $isUsed   = $product->isUsed();
    $display  = $product->displayPrice();
    $hasSale  = $product->hasSale();
    $discount = $product->discountPercent();
    $sold     = $product->isSold();
    $inStock  = $product->isInStock();

    $waText = rawurlencode('عايز أستفسر عن: ' . $product->name_ar . ' — ' . url($product->path()));
    $waUrl  = 'https://wa.me/' . site('whatsapp') . '?text=' . $waText;
@endphp

<article class="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-line bg-white transition-all hover:border-brand hover:shadow-lg hover:shadow-brand/5">
    {{-- الصورة --}}
    <a href="{{ url($product->path()) }}" class="relative block bg-surface" aria-label="{{ $product->name_ar }}">
        @if ($img)
            <img src="{{ $disk->url($img->path) }}"
                 alt="{{ $img->alt ?: $product->name_ar }}"
                 width="400" height="400" loading="lazy" decoding="async"
                 class="aspect-square w-full object-contain transition-transform duration-300 group-hover:scale-[1.03]">
        @else
            <div class="flex aspect-square w-full items-center justify-center text-sm text-muted">لا توجد صورة</div>
        @endif

        {{-- بادجات: حالة الجهاز + نسبة الخصم --}}
        <div class="absolute top-3 start-3 flex flex-col items-start gap-1.5">
            <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $isUsed ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800' }}">
                {{ $isUsed ? 'مستعمل' : 'جديد' }}
            </span>
            @if ($discount)
                <span class="rounded-full bg-brand px-2.5 py-0.5 text-xs font-extrabold text-white">خصم {{ $discount }}%</span>
            @endif
        </div>

        @if ($sold)
            <span class="absolute bottom-3 end-3 rounded-full bg-navy px-2.5 py-0.5 text-xs font-bold text-white">تم البيع</span>
        @endif
    </a>

    {{-- أزرار المفضلة والمقارنة (خارج الـ anchor عشان تبقى تفاعلية) --}}
    <div class="absolute top-3 end-3 z-10 flex flex-col gap-2">
        <x-shop.wishlist-button :product="$product" />
        <x-shop.compare-button :product="$product" />
    </div>

    {{-- التفاصيل --}}
    <div class="flex flex-1 flex-col p-4">
        <div class="mb-1 flex items-center gap-2 text-xs text-muted">
            @if ($product->brand)
                <span class="font-semibold text-ink/70">{{ $product->brand->name_ar }}</span>
            @endif
            @if ($product->brand && $product->category)<span aria-hidden="true">·</span>@endif
            @if ($product->category)
                <span>{{ $product->category->name_ar }}</span>
            @endif
        </div>

        <h3 class="font-bold leading-snug">
            <a href="{{ url($product->path()) }}" class="transition-colors group-hover:text-brand">
                {{ $product->name_ar }}
            </a>
        </h3>

        {{-- السعر --}}
        <div class="mt-3">
            @if ($display)
                <div class="flex flex-wrap items-baseline gap-2">
                    <span class="text-lg font-extrabold text-brand">{{ number_format((float) $display) }} ج.م</span>
                    @if ($hasSale)
                        <span class="text-sm text-muted line-through">{{ number_format((float) $product->price) }} ج.م</span>
                    @endif
                </div>
            @else
                <span class="text-sm text-muted">اتصل للاستفسار</span>
            @endif
        </div>

        {{-- التوفر --}}
        <p class="mt-1 text-xs font-semibold {{ $sold ? 'text-navy' : ($inStock ? 'text-emerald-600' : 'text-muted') }}">
            {{ $sold ? 'تم البيع' : ($inStock ? 'متوفر' : 'غير متوفر حالياً') }}
        </p>

        {{-- أضف للسلة (يوجّه لواتساب في 6B) --}}
        <div class="relative z-10 mt-auto pt-3">
            @if ($sold)
                <a href="{{ url($product->path()) }}"
                   class="flex w-full items-center justify-center gap-2 rounded-full border border-line px-4 py-2.5 text-sm font-bold text-ink transition-colors hover:border-brand hover:text-brand">
                    شوف بدائل
                </a>
            @else
                <a href="{{ $waUrl }}" target="_blank" rel="noopener"
                   class="flex w-full items-center justify-center gap-2 rounded-full bg-brand px-4 py-2.5 text-sm font-bold text-white transition-colors hover:bg-brand-600">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>
                    أضف للسلة
                </a>
            @endif
        </div>
    </div>
</article>
