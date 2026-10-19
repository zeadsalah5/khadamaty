@extends('layouts.app')

{{--
    فهرس المتجر (المرحلة 6A — عرض بسيط بكروت منتجات).
    متغيرات: $title, $subtitle, $breadcrumbs, $products (Collection<Product>), $items (للـ schema).
--}}

@if (! empty($items))
    @push('schema')
        <x-schema.item-list :items="$items" :name="$itemListName ?? $title" />
    @endpush
@endif

@section('content')
    @php use Illuminate\Support\Facades\Storage; $disk = Storage::disk('products'); @endphp

    <x-page-hero :title="$title" :subtitle="$subtitle ?? null" :breadcrumbs="$breadcrumbs ?? []" />

    <section class="mx-auto max-w-6xl px-4 pb-16 md:pb-20">
        @if ($products->isNotEmpty())
            <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                    @php $img = $product->primaryImage ?? $product->images->first(); @endphp
                    <li>
                        <a href="{{ $basePath ?? '/shop' }}/{{ $product->slug }}"
                           class="group flex h-full flex-col overflow-hidden rounded-2xl border border-line bg-white transition-colors hover:border-brand">
                            <div class="relative bg-surface">
                                @if ($img)
                                    <img src="{{ $disk->url($img->path) }}"
                                         alt="{{ $img->alt ?: $product->name_ar }}"
                                         class="aspect-square w-full object-contain">
                                @else
                                    <div class="flex aspect-square w-full items-center justify-center text-muted">لا توجد صورة</div>
                                @endif
                                <span class="absolute top-3 {{ 'end-3' }} rounded-full px-2.5 py-0.5 text-xs font-bold
                                    {{ $product->isUsed() ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800' }}">
                                    {{ $product->isUsed() ? 'مستعمل' : 'جديد' }}
                                </span>
                            </div>
                            <div class="flex flex-1 flex-col p-4">
                                @if ($product->category)
                                    <span class="mb-1 text-xs text-muted">{{ $product->category->name_ar }}</span>
                                @endif
                                <h2 class="font-bold leading-snug transition-colors group-hover:text-brand">{{ $product->name_ar }}</h2>
                                <div class="mt-auto pt-3">
                                    @if ($product->displayPrice())
                                        <span class="text-lg font-extrabold text-brand">{{ number_format((float) $product->displayPrice()) }} ج.م</span>
                                    @else
                                        <span class="text-sm text-muted">اتصل للاستفسار</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="rounded-2xl border border-dashed border-line bg-surface px-4 py-16 text-center">
                <p class="text-lg text-muted">المنتجات قريباً — بنجهّز المتجر.</p>
                <a href="{{ url('/') }}#booking"
                   class="mt-5 inline-block rounded-full bg-brand px-6 py-3 font-bold text-white transition-colors hover:bg-brand-600">
                    احجز فني دلوقتي
                </a>
            </div>
        @endif
    </section>
@endsection
