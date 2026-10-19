@extends('layouts.app')

{{-- مقارنة المنتجات (المرحلة 6D) — لحد 3 من نفس القسم. noindex. --}}
@php use Illuminate\Support\Facades\Storage; $disk = Storage::disk('products'); @endphp
@section('content')
    <section class="mx-auto max-w-6xl px-4 pt-8 pb-16 md:pb-20">
        <x-breadcrumbs :items="$breadcrumbs" />

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-extrabold md:text-3xl">مقارنة المنتجات</h1>
                <p class="mt-1 text-muted">قارن لحد 3 منتجات من نفس القسم جنب بعض.</p>
            </div>
            @if ($products->isNotEmpty())
                <form method="POST" action="{{ route('compare.clear') }}">
                    @csrf
                    <button type="submit" class="text-sm font-semibold text-muted hover:text-brand">تفريغ المقارنة</button>
                </form>
            @endif
        </div>

        @if (session('compare_success'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('compare_success') }}</div>
        @endif
        @if (session('compare_error'))
            <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">{{ session('compare_error') }}</div>
        @endif

        @if ($products->isEmpty())
            <div class="rounded-2xl border border-dashed border-line bg-surface p-10 text-center">
                <p class="text-muted">مفيش منتجات في المقارنة. دوس على «قارن» في أي منتج.</p>
                <a href="{{ route('shop.index') }}" class="mt-4 inline-flex rounded-full bg-brand px-6 py-2.5 font-bold text-white transition-colors hover:bg-brand-600">تصفّح المتجر</a>
            </div>
        @else
            <div class="overflow-x-auto rounded-2xl border border-line">
                <table class="w-full min-w-[640px] border-collapse bg-white text-sm">
                    <tbody>
                        {{-- الصور + الأسماء --}}
                        <tr class="border-b border-line">
                            <th class="w-32 bg-surface p-3 text-start text-muted">المنتج</th>
                            @foreach ($products as $product)
                                <td class="p-3 align-top">
                                    <a href="{{ url($product->path()) }}" class="block">
                                        @php $img = $product->primaryImage ?: $product->images->first(); @endphp
                                        @if ($img)
                                            <img src="{{ $disk->url($img->path) }}" alt="{{ $img->alt ?: $product->name_ar }}"
                                                 width="200" height="200" loading="lazy" class="mx-auto aspect-square w-full max-w-[160px] object-contain">
                                        @endif
                                        <span class="mt-2 block font-bold leading-snug hover:text-brand">{{ $product->name_ar }}</span>
                                    </a>
                                    <form method="POST" action="{{ route('compare.remove') }}" class="mt-2">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <button type="submit" class="text-xs font-semibold text-muted hover:text-red-600">شيل من المقارنة</button>
                                    </form>
                                </td>
                            @endforeach
                        </tr>

                        {{-- السعر --}}
                        <tr class="border-b border-line">
                            <th class="bg-surface p-3 text-start text-muted">السعر</th>
                            @foreach ($products as $product)
                                <td class="p-3">
                                    @if ($product->displayPrice())
                                        <span class="font-extrabold text-brand">{{ number_format((float) $product->displayPrice()) }} ج.م</span>
                                    @else
                                        <span class="text-muted">اتصل للاستفسار</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>

                        {{-- الحالة (جديد/مستعمل) --}}
                        <tr class="border-b border-line">
                            <th class="bg-surface p-3 text-start text-muted">الحالة</th>
                            @foreach ($products as $product)
                                <td class="p-3">{{ $product->isUsed() ? 'مستعمل' : 'جديد' }}</td>
                            @endforeach
                        </tr>

                        {{-- الماركة --}}
                        <tr class="border-b border-line">
                            <th class="bg-surface p-3 text-start text-muted">الماركة</th>
                            @foreach ($products as $product)
                                <td class="p-3">{{ $product->brand?->name_ar ?: '—' }}</td>
                            @endforeach
                        </tr>

                        {{-- التوفّر --}}
                        <tr class="border-b border-line">
                            <th class="bg-surface p-3 text-start text-muted">التوفّر</th>
                            @foreach ($products as $product)
                                <td class="p-3">{{ $product->isSold() ? 'تم البيع' : ($product->isInStock() ? 'متوفر' : 'غير متوفر') }}</td>
                            @endforeach
                        </tr>

                        {{-- مواصفات القسم --}}
                        @foreach ($attributes as $attr)
                            <tr class="border-b border-line last:border-0">
                                <th class="bg-surface p-3 text-start text-muted">{{ $attr->label_ar }}</th>
                                @foreach ($products as $product)
                                    @php
                                        $value = data_get($product->specs, $attr->key);
                                        $value = filled($value) ? ($attr->unit ? $value . ' ' . $attr->unit : $value) : '—';
                                    @endphp
                                    <td class="p-3">{{ $value }}</td>
                                @endforeach
                            </tr>
                        @endforeach

                        {{-- زيارة المنتج --}}
                        <tr>
                            <th class="bg-surface p-3 text-start text-muted"></th>
                            @foreach ($products as $product)
                                <td class="p-3">
                                    <a href="{{ url($product->path()) }}" class="inline-flex rounded-full bg-brand px-5 py-2 text-xs font-bold text-white transition-colors hover:bg-brand-600">شوف المنتج</a>
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
