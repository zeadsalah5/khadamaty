@extends('layouts.app')

{{--
    هب الخدمات — بيعرض الخدمات المنشورة من الداتابيز بأيقونات وروابط للتفاصيل.
    متغيرات: $title, $subtitle, $services, $items, $icons, $itemListName, $breadcrumbs
--}}

@if (! empty($items))
    @push('schema')
        <x-schema.item-list :items="$items" :name="$itemListName ?? $title" />
    @endpush
@endif

@section('content')
    <x-page-hero :title="$title" :subtitle="$subtitle ?? null" :breadcrumbs="$breadcrumbs ?? []" />

    <section class="mx-auto max-w-6xl px-4 pb-6">
        @if ($services->isNotEmpty())
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $service)
                    <article class="group flex flex-col rounded-2xl border border-line bg-white p-6 hover:border-brand hover:shadow-lg hover:shadow-brand/5 transition-all">
                        <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand flex items-center justify-center mb-4 group-hover:bg-brand group-hover:text-white transition-colors">
                            <x-service-icon :name="$icons[$service->slug] ?? 'maintenance'" />
                        </div>
                        <h2 class="text-lg font-bold mb-2">{{ $service->name_ar }}</h2>
                        @if ($service->excerpt)
                            <p class="text-muted text-sm leading-relaxed mb-5 flex-1">{{ $service->excerpt }}</p>
                        @endif
                        <div class="flex items-center justify-between gap-3 mt-auto pt-1">
                            <a href="{{ '/services/' . $service->slug }}"
                               class="inline-flex items-center gap-1.5 text-brand font-bold text-sm hover:gap-2.5 transition-all">
                                التفاصيل
                                <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                            </a>
                            <a href="{{ route('request-service', ['service' => $service->name_ar]) }}"
                               class="inline-flex items-center px-4 py-2 rounded-full bg-brand hover:bg-brand-600 text-white font-bold text-sm transition-colors">
                                احجز
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-line bg-surface text-center py-16 px-4">
                <p class="text-muted text-lg">المحتوى قريباً — بنجهّز القسم ده.</p>
                <a href="{{ route('request-service') }}"
                   class="inline-block mt-5 px-6 py-3 rounded-full bg-brand hover:bg-brand-600 text-white font-bold transition-colors">
                    احجز فني دلوقتي
                </a>
            </div>
        @endif
    </section>

    {{-- شريط تطمين + CTA --}}
    @if ($services->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 pb-16 md:pb-20">
            <div class="rounded-2xl bg-surface border border-line p-7 md:p-9 flex flex-col md:flex-row md:items-center justify-between gap-5">
                <div>
                    <h2 class="text-xl md:text-2xl font-extrabold mb-1">مش عارف تختار الخدمة المناسبة؟</h2>
                    <p class="text-muted leading-relaxed">قولنا الجهاز بيعمل إيه وإحنا نرشّحلك، أو احجز معاينة ونشوفها على الطبيعة.</p>
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
