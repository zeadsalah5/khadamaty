@extends('layouts.app')

{{--
    فهرس قسم المتجر (/shop/{category}) — المرحلة 6B.
    $category, $products (Paginator), $brands, $filters, $hasProducts
--}}
@php
    $sortOptions = [
        'newest'     => 'الأحدث',
        'price_asc'  => 'السعر: من الأقل',
        'price_desc' => 'السعر: من الأعلى',
    ];
    $base = url($category->path());
    // فلاتر select/boolean من حقول القسم.
    $attrFilters = collect($category->attributes)->filter(fn ($a) => in_array($a->type, ['select', 'boolean'], true));
@endphp

@section('content')
    <x-page-hero :title="$title" :subtitle="$subtitle" :breadcrumbs="$breadcrumbs" />

    @if (! $hasProducts)
        <section class="mx-auto max-w-6xl px-4 pb-20">
            <div class="rounded-2xl border border-dashed border-line bg-surface px-4 py-16 text-center">
                <p class="text-lg text-muted">لسه مفيش منتجات في «{{ $category->name_ar }}» — هنضيف قريباً.</p>
                <a href="{{ route('shop.index') }}" class="mt-5 inline-block rounded-full border border-line px-6 py-3 font-bold transition-colors hover:border-brand hover:text-brand">رجوع للمتجر</a>
            </div>
        </section>
    @else

    <section class="mx-auto max-w-6xl px-4 pb-16 md:pb-20">
        <div class="grid gap-8 lg:grid-cols-[18rem_1fr]">

            {{-- ============ الفلاتر ============ --}}
            <aside class="lg:sticky lg:top-20 lg:self-start">
                <details class="group rounded-2xl border border-line bg-white" open>
                    <summary class="flex cursor-pointer items-center justify-between gap-2 p-4 font-bold lg:cursor-default">
                        <span class="flex items-center gap-2">
                            <svg viewBox="0 0 24 24" class="h-5 w-5 text-brand" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M7 12h10M10 18h4"/></svg>
                            فلترة وبحث
                        </span>
                        <svg class="h-5 w-5 text-muted transition-transform group-open:rotate-180 lg:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </summary>

                    <form method="GET" action="{{ $base }}" class="space-y-5 border-t border-line p-4">
                        {{-- بحث --}}
                        <div>
                            <label for="f-q" class="mb-1.5 block text-sm font-semibold">بحث</label>
                            <input type="search" id="f-q" name="q" value="{{ $filters['q'] ?? '' }}"
                                   autocomplete="off" spellcheck="false" placeholder="اسم المنتج أو الموديل…"
                                   class="w-full rounded-xl border border-line bg-surface px-3 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                        </div>

                        {{-- الحالة --}}
                        <fieldset>
                            <legend class="mb-1.5 text-sm font-semibold">الحالة</legend>
                            <div class="flex gap-2">
                                @foreach (['' => 'الكل', 'new' => 'جديد', 'used' => 'مستعمل'] as $val => $label)
                                    <label class="flex-1 cursor-pointer">
                                        <input type="radio" name="condition" value="{{ $val }}" class="peer sr-only" @checked(($filters['condition'] ?? '') === $val)>
                                        <span class="block rounded-xl border border-line px-2 py-2 text-center text-sm font-semibold peer-checked:border-brand peer-checked:bg-brand peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-brand/40">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        {{-- الماركة --}}
                        @if ($brands->isNotEmpty())
                            <div>
                                <label for="f-brand" class="mb-1.5 block text-sm font-semibold">الماركة</label>
                                <select id="f-brand" name="brand" class="w-full rounded-xl border border-line bg-surface px-3 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                                    <option value="">كل الماركات</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->slug }}" @selected(($filters['brand'] ?? '') === $brand->slug)>{{ $brand->name_ar }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- السعر --}}
                        <div>
                            <span class="mb-1.5 block text-sm font-semibold">السعر (ج.م)</span>
                            <div class="flex items-center gap-2">
                                <input type="number" inputmode="numeric" min="0" name="min" value="{{ $filters['min'] ?? '' }}" autocomplete="off" aria-label="السعر من" placeholder="من"
                                       class="w-full rounded-xl border border-line bg-surface px-3 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                                <span class="text-muted" aria-hidden="true">—</span>
                                <input type="number" inputmode="numeric" min="0" name="max" value="{{ $filters['max'] ?? '' }}" autocomplete="off" aria-label="السعر إلى" placeholder="إلى"
                                       class="w-full rounded-xl border border-line bg-surface px-3 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                            </div>
                        </div>

                        {{-- فلاتر المواصفات --}}
                        @foreach ($attrFilters as $attr)
                            @php
                                $key = 'attr_' . $attr->key;
                                $options = $attr->type === 'boolean' ? ['نعم', 'لا'] : ($attr->options ?? []);
                            @endphp
                            @if (! empty($options))
                                <div>
                                    <label for="f-{{ $attr->key }}" class="mb-1.5 block text-sm font-semibold">{{ $attr->label_ar }}</label>
                                    <select id="f-{{ $attr->key }}" name="{{ $key }}" class="w-full rounded-xl border border-line bg-surface px-3 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                                        <option value="">الكل</option>
                                        @foreach ($options as $opt)
                                            <option value="{{ $opt }}" @selected(($filters[$key] ?? '') === $opt)>{{ $opt }}{{ $attr->unit ? ' ' . $attr->unit : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        @endforeach

                        {{-- الترتيب (برضه متاح فوق للموبايل) --}}
                        <div>
                            <label for="f-sort" class="mb-1.5 block text-sm font-semibold">الترتيب</label>
                            <select id="f-sort" name="sort" class="w-full rounded-xl border border-line bg-surface px-3 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                                @foreach ($sortOptions as $val => $label)
                                    <option value="{{ $val }}" @selected(($filters['sort'] ?? 'newest') === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex gap-2 pt-1">
                            <button type="submit" class="flex-1 rounded-full bg-brand px-4 py-2.5 text-sm font-bold text-white transition-colors hover:bg-brand-600">طبّق</button>
                            <a href="{{ $base }}" class="rounded-full border border-line px-4 py-2.5 text-sm font-bold transition-colors hover:border-brand hover:text-brand">مسح</a>
                        </div>
                    </form>
                </details>
            </aside>

            {{-- ============ النتائج ============ --}}
            <div>
                <div class="mb-5 flex items-center justify-between gap-4">
                    <p class="text-sm text-muted">{{ number_format($products->total()) }} منتج</p>
                </div>

                @if ($products->isNotEmpty())
                    <div class="grid grid-cols-2 gap-4 sm:gap-5 xl:grid-cols-3">
                        @foreach ($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $products->links('pagination.blog') }}
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-line bg-surface px-4 py-16 text-center">
                        <p class="text-lg text-muted">مفيش منتجات مطابقة للفلاتر.</p>
                        <a href="{{ $base }}" class="mt-5 inline-block rounded-full border border-line px-6 py-3 font-bold transition-colors hover:border-brand hover:text-brand">امسح الفلاتر</a>
                    </div>
                @endif
            </div>
        </div>
    </section>

    @endif
@endsection
