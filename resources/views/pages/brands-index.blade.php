@extends('layouts.app')

{{--
    هب الماركات (/brands) — المرحلة 3.
    متغيرات: $title, $subtitle, $brands, $items, $itemListName, $breadcrumbs
    ملاحظة: كروت نصّية — من غير لوجوهات رسمية. لو اتضاف logo بيظهر بدل الحرف.
--}}

@if (! empty($items))
    @push('schema')
        <x-schema.item-list :items="$items" :name="$itemListName ?? $title" />
    @endpush
@endif

@section('content')
    <x-page-hero :title="$title" :subtitle="$subtitle ?? null" :breadcrumbs="$breadcrumbs ?? []" />

    {{-- شريط توضيح: بنصلّح كل الماركات، مش توكيل --}}
    <section class="mx-auto max-w-6xl px-4 pb-8">
        <p class="inline-flex items-start gap-2 rounded-xl bg-surface border border-line px-5 py-4 text-sm text-muted leading-relaxed">
            <svg viewBox="0 0 24 24" class="w-5 h-5 shrink-0 text-brand mt-0.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
            <span>إحنا مش توكيل لأي ماركة — بنصلّح كل الماركات. فنيينا متدربين على أنواع وموديلات مختلفة.</span>
        </p>
    </section>

    <section class="mx-auto max-w-6xl px-4 pb-6">
        @if ($brands->isNotEmpty())
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($brands as $brand)
                    <a href="{{ '/brands/' . $brand->slug }}"
                       class="group flex flex-col rounded-2xl border border-line bg-white p-6 hover:border-brand hover:shadow-lg hover:shadow-brand/5 transition-all">
                        <div class="flex items-center gap-4 mb-3">
                            {{-- مكان اللوجو: صورة لو موجودة، وإلا أول حرف --}}
                            <div class="shrink-0 w-14 h-14 rounded-xl bg-surface border border-line flex items-center justify-center overflow-hidden">
                                @if (filled($brand->logo))
                                    <x-img :src="'images/brands/' . $brand->logo" :alt="$brand->name_ar" class="w-full h-full object-contain p-1.5" />
                                @else
                                    <span class="text-xl font-extrabold text-brand" aria-hidden="true">{{ mb_substr($brand->name_ar, 0, 1) }}</span>
                                @endif
                            </div>
                            <h2 class="text-lg font-bold group-hover:text-brand transition-colors">{{ $brand->name_ar }}</h2>
                        </div>
                        @if ($brand->excerpt)
                            <p class="text-muted text-sm leading-relaxed mb-5 flex-1">{{ $brand->excerpt }}</p>
                        @endif
                        <span class="inline-flex items-center gap-1.5 text-brand font-bold text-sm mt-auto group-hover:gap-2.5 transition-all">
                            التفاصيل والأعطال الشائعة
                            <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-line bg-surface text-center py-16 px-4">
                <p class="text-muted text-lg">صفحات الماركات قريباً — بنجهّز القسم ده.</p>
                <a href="{{ route('request-service') }}"
                   class="inline-block mt-5 px-6 py-3 rounded-full bg-brand hover:bg-brand-600 text-white font-bold transition-colors">
                    احجز فني دلوقتي
                </a>
            </div>
        @endif
    </section>

    {{-- شريط تطمين + CTA --}}
    @if ($brands->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 pb-16 md:pb-20">
            <div class="rounded-2xl bg-surface border border-line p-7 md:p-9 flex flex-col md:flex-row md:items-center justify-between gap-5">
                <div>
                    <h2 class="text-xl md:text-2xl font-extrabold mb-1">ماركتك مش في القايمة؟</h2>
                    <p class="text-muted leading-relaxed">مش مشكلة — بنصلّح كل الماركات. قولنا نوع جهازك وموديله وإحنا نظبّطك.</p>
                </div>
                <div class="flex flex-wrap gap-3 shrink-0">
                    <a href="{{ route('request-service') }}"
                       class="inline-flex items-center px-6 py-3 rounded-full bg-brand hover:bg-brand-600 text-white font-bold transition-colors">
                        اطلب خدمة
                    </a>
                    <a href="https://wa.me/{{ config('site.whatsapp') }}" target="_blank" rel="noopener"
                       class="inline-flex items-center px-6 py-3 rounded-full border border-line hover:border-brand hover:text-brand font-bold transition-colors">
                        اسأل على واتساب
                    </a>
                </div>
            </div>
        </section>
    @endif
@endsection
