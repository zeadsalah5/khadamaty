@php
    // قائمة الهيدر من الداشبورد (جدول menu_items)، وإلا القايمة الافتراضية.
    $nav = \App\Support\Catalog::menu('header');
    if (empty($nav)) {
        $nav = [
            ['label' => 'الخدمات', 'url' => route('services.index')],
            ['label' => 'المناطق', 'url' => route('areas.index')],
            ['label' => 'الماركات', 'url' => route('brands.index')],
            ['label' => 'المدونة', 'url' => route('blog.index')],
            ['label' => 'الأسعار', 'url' => route('prices')],
            ['label' => 'مين إحنا', 'url' => route('about')],
            ['label' => 'الأسئلة الشائعة', 'url' => route('faq')],
            ['label' => 'اتصل بينا', 'url' => route('contact')],
        ];
    }
    // رابط «المتجر» يظهر بس لو فيه منتج منشور؛ ومبيظهرش لو المتجر فاضي (حتى لو مضاف في القايمة).
    $hasShop = \App\Support\Store\Storefront::hasPublished();
    $isShopLink = fn ($url) => \Illuminate\Support\Str::contains($url, '/shop');
    $navHasShop = collect($nav)->contains(fn ($i) => $isShopLink($i['url'] ?? ''));

    if ($hasShop && ! $navHasShop) {
        // ندخّل «المتجر» بعد الماركات لو موجودة، وإلا في الآخر.
        $pos = collect($nav)->search(fn ($i) => \Illuminate\Support\Str::contains($i['url'] ?? '', '/brands'));
        $shopItem = ['label' => 'المتجر', 'url' => route('shop.index')];
        if ($pos !== false) {
            array_splice($nav, $pos + 1, 0, [$shopItem]);
        } else {
            $nav[] = $shopItem;
        }
    } elseif (! $hasShop && $navHasShop) {
        $nav = array_values(array_filter($nav, fn ($i) => ! $isShopLink($i['url'] ?? '')));
    }

    // الروابط النسبية (/services) تتحوّل لمطلقة؛ الروابط الكاملة والأنكور تفضل زيّها.
    $nav = array_map(function ($i) {
        $i['url'] = \Illuminate\Support\Str::startsWith($i['url'], ['http://', 'https://', '#', 'mailto:', 'tel:', '//'])
            ? $i['url'] : url($i['url']);
        return $i;
    }, $nav);
    $bookUrl = url('/') . '#booking';

    // عداد السلة (من الـ session) — يظهر بس لو المتجر فيه منتجات منشورة.
    $cartCount = $hasShop ? \App\Support\Store\Cart::count() : 0;
@endphp

<header class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-line">
    <nav class="mx-auto max-w-6xl px-4 h-16 flex items-center justify-between gap-4" aria-label="التنقل الرئيسي">
        <x-logo />

        {{-- روابط القايمة — ديسكتوب --}}
        <ul class="hidden lg:flex items-center gap-5 xl:gap-6 text-sm font-semibold text-ink">
            @foreach ($nav as $item)
                <li><a href="{{ $item['url'] }}" class="hover:text-brand transition-colors">{{ $item['label'] }}</a></li>
            @endforeach
        </ul>

        {{-- أزرار — ديسكتوب --}}
        <div class="hidden lg:flex items-center gap-3">
            @if ($hasShop)
                <a href="{{ route('cart.show') }}" aria-label="سلة المشتريات{{ $cartCount ? ' (' . $cartCount . ' منتج)' : '' }}"
                   class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-line text-ink transition-colors hover:border-brand hover:text-brand">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>
                    @if ($cartCount)
                        <span class="absolute -top-1.5 -end-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-brand px-1 text-xs font-bold text-white">{{ $cartCount }}</span>
                    @endif
                </a>
            @endif
            <a href="tel:{{ site('phone_tel') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-line text-ink font-semibold text-sm hover:border-brand hover:text-brand transition-colors">
                <svg viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor" aria-hidden="true"><path d="M6.6 10.8a15.5 15.5 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25c1.1.37 2.3.57 3.6.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.3.2 2.5.57 3.6a1 1 0 0 1-.25 1l-2.2 2.2Z"/></svg>
                اتصل بينا
            </a>
            <a href="{{ $bookUrl }}"
               class="inline-flex items-center px-5 py-2 rounded-full bg-brand hover:bg-brand-600 text-white font-bold text-sm transition-colors">
                احجز
            </a>
        </div>

        {{-- أيقونة السلة + زر القايمة — موبايل --}}
        <div class="flex items-center gap-2 lg:hidden">
            @if ($hasShop)
                <a href="{{ route('cart.show') }}" aria-label="سلة المشتريات{{ $cartCount ? ' (' . $cartCount . ' منتج)' : '' }}"
                   class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-line text-ink">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>
                    @if ($cartCount)
                        <span class="absolute -top-1.5 -end-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-brand px-1 text-xs font-bold text-white">{{ $cartCount }}</span>
                    @endif
                </a>
            @endif

            {{-- زر قايمة الموبايل --}}
            <button type="button" id="menu-toggle"
                    class="inline-flex items-center justify-center w-10 h-10 rounded-lg border border-line text-ink"
                    aria-label="فتح القائمة" aria-expanded="false" aria-controls="mobile-menu">
                <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
        </div>
    </nav>

    {{-- قايمة الموبايل --}}
    <div id="mobile-menu" class="lg:hidden hidden border-t border-line bg-white">
        <ul class="px-4 py-3 space-y-1 text-sm font-semibold">
            @foreach ($nav as $item)
                <li><a href="{{ $item['url'] }}" class="block py-2.5 text-ink hover:text-brand" data-close-menu>{{ $item['label'] }}</a></li>
            @endforeach
            <li class="pt-2">
                <a href="{{ $bookUrl }}" data-close-menu
                   class="block text-center py-2.5 rounded-full bg-brand text-white font-bold">احجز فني دلوقتي</a>
            </li>
        </ul>
    </div>
</header>
