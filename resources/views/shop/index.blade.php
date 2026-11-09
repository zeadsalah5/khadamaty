@extends('layouts.app')

{{--
    هب المتجر (/shop) — المرحلة 6B.
    $categories, $brands, $offers (Collection<Product>), $newest, $hasProducts
--}}

@section('content')
    @php use Illuminate\Support\Facades\Storage; $disk = Storage::disk('products'); @endphp

    <x-page-hero :title="$title" :subtitle="$subtitle" :breadcrumbs="$breadcrumbs" />

    @if (! $hasProducts)
        {{-- المتجر لسه فاضي (noindex) --}}
        <section class="mx-auto max-w-6xl px-4 pb-20">
            <div class="rounded-2xl border border-dashed border-line bg-surface px-4 py-16 text-center">
                <p class="text-lg text-muted">المنتجات قريباً — بنجهّز المتجر.</p>
                <a href="{{ url('/') }}#booking"
                   class="mt-5 inline-block rounded-full bg-brand px-6 py-3 font-bold text-white transition-colors hover:bg-brand-600">
                    احجز فني دلوقتي
                </a>
            </div>
        </section>
    @else

    {{-- ============ الأقسام ============ --}}
    @if ($categories->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4">
            <h2 class="mb-5 text-2xl font-extrabold">تسوّق حسب القسم</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($categories as $cat)
                    <a href="{{ url($cat->path()) }}"
                       class="group relative flex items-center gap-4 overflow-hidden rounded-2xl border border-line bg-white p-5 transition-all hover:border-brand hover:shadow-lg hover:shadow-brand/5">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-surface">
                            @if (filled($cat->image))
                                <img src="{{ $disk->url($cat->image) }}" alt="{{ $cat->name_ar }}"
                                     width="64" height="64" loading="lazy" decoding="async" class="h-full w-full object-contain p-1.5">
                            @else
                                <svg viewBox="0 0 24 24" class="h-8 w-8 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="12" rx="2"/><path d="M7 20h10M8 16v4M16 16v4M7 9h4"/></svg>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-lg font-bold transition-colors group-hover:text-brand">{{ $cat->name_ar }}</h3>
                            @if ($cat->excerpt)
                                <p class="mt-0.5 line-clamp-1 text-sm text-muted">{{ $cat->excerpt }}</p>
                            @endif
                        </div>
                        <svg viewBox="0 0 24 24" class="ms-auto h-5 w-5 shrink-0 text-muted transition-all group-hover:text-brand" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============ شريط «المستعمل» (كتلة ثقة) ============ --}}
    <section class="mx-auto mt-12 max-w-6xl px-4">
        <a href="{{ route('shop.used') }}"
           class="group flex flex-col items-start justify-between gap-4 overflow-hidden rounded-2xl bg-navy p-6 text-white md:flex-row md:items-center md:p-8">
            <div class="max-w-xl">
                <span class="rounded-full bg-amber-400/20 px-3 py-1 text-sm font-bold text-amber-300">تكييفات مستعملة</span>
                <h2 class="mt-3 text-2xl font-extrabold">أجهزة مستعملة مفحوصة بدرجات حالة واضحة</h2>
                <p class="mt-2 leading-relaxed text-white/75">كل جهاز بيتفحص فنياً قبل العرض، مع بيان الأعمال اللي اتعملت والضمان.</p>
            </div>
            <span class="inline-flex shrink-0 items-center gap-2 rounded-full bg-white px-6 py-3 font-bold text-navy transition-all group-hover:gap-3">
                اعرف أكتر
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            </span>
        </a>
    </section>

    {{-- ============ العروض ============ --}}
    @if ($offers->isNotEmpty())
        <section class="mx-auto mt-12 max-w-6xl px-4">
            <div class="mb-5 flex items-end justify-between gap-4">
                <h2 class="text-2xl font-extrabold">عروض وخصومات</h2>
            </div>
            <div class="grid grid-cols-2 gap-4 sm:gap-5 lg:grid-cols-4">
                @foreach ($offers as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============ أحدث المنتجات ============ --}}
    @if ($newest->isNotEmpty())
        <section class="mx-auto mt-12 max-w-6xl px-4">
            <h2 class="mb-5 text-2xl font-extrabold">أحدث المنتجات</h2>
            <div class="grid grid-cols-2 gap-4 sm:gap-5 lg:grid-cols-4">
                @foreach ($newest as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============ الماركات ============ --}}
    @if ($brands->isNotEmpty())
        <section class="mx-auto mt-12 max-w-6xl px-4">
            <h2 class="mb-5 text-2xl font-extrabold">تسوّق حسب الماركة</h2>
            <ul class="flex flex-wrap gap-2.5">
                @foreach ($brands as $brand)
                    <li>
                        <a href="{{ url('/brands/' . $brand->slug) }}"
                           class="inline-block rounded-full border border-line bg-white px-5 py-2.5 text-sm font-semibold transition-colors hover:border-brand hover:text-brand">
                            {{ $brand->name_ar }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- ============ شريط مساعدة ============ --}}
    <section class="mx-auto mt-14 max-w-6xl px-4 pb-16 md:pb-20">
        <div class="rounded-2xl border border-line bg-surface px-6 py-8 text-center">
            <h2 class="text-xl font-bold">محتار تختار إيه؟</h2>
            <p class="mx-auto mt-2 max-w-xl leading-relaxed text-muted">كلّمنا وهنرشّحلك الجهاز المناسب لمساحتك وميزانيتك، وبنركّب ونصلّح كل الماركات.</p>
            <div class="mt-5 flex flex-wrap justify-center gap-3">
                <a href="https://wa.me/{{ site('whatsapp') }}" target="_blank" rel="noopener"
                   class="rounded-full bg-accent px-6 py-3 font-bold text-white transition-colors hover:bg-accent-600">اسأل على واتساب</a>
                <a href="tel:{{ site('phone_tel') }}"
                   class="rounded-full border border-line px-6 py-3 font-bold transition-colors hover:border-brand hover:text-brand">اتصل: {{ site('phone') }}</a>
            </div>
        </div>
    </section>

    @endif
@endsection
