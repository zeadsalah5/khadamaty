@extends('layouts.app')

{{--
    هب المناطق (/areas) — المرحلة 3.
    متغيرات: $title, $subtitle, $areas, $items, $itemListName, $breadcrumbs
--}}

@if (! empty($items))
    @push('schema')
        <x-schema.item-list :items="$items" :name="$itemListName ?? $title" />
    @endpush
@endif

@section('content')
    <x-page-hero :title="$title" :subtitle="$subtitle ?? null" :breadcrumbs="$breadcrumbs ?? []" />

    <section class="mx-auto max-w-6xl px-4 pb-6">
        @if ($areas->isNotEmpty())
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($areas as $area)
                    <article class="group flex flex-col rounded-2xl border border-line bg-white p-6 hover:border-brand hover:shadow-lg hover:shadow-brand/5 transition-all">
                        <div class="flex items-center gap-2 text-brand mb-3">
                            <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-6-5.3-6-10a6 6 0 0 1 12 0c0 4.7-6 10-6 10Z"/><circle cx="12" cy="11" r="2.2"/></svg>
                            <h2 class="text-lg font-bold">{{ $area->name_ar }}</h2>
                        </div>
                        @if ($area->excerpt)
                            <p class="text-muted text-sm leading-relaxed mb-5 flex-1">{{ $area->excerpt }}</p>
                        @endif
                        <div class="flex items-center justify-between gap-3 mt-auto pt-1">
                            <a href="{{ '/areas/' . $area->slug }}"
                               class="inline-flex items-center gap-1.5 text-brand font-bold text-sm hover:gap-2.5 transition-all">
                                خدماتنا في {{ $area->name_ar }}
                                <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-line bg-surface text-center py-16 px-4">
                <p class="text-muted text-lg">صفحات المناطق قريباً — بنجهّز القسم ده.</p>
                <a href="{{ route('request-service') }}"
                   class="inline-block mt-5 px-6 py-3 rounded-full bg-brand hover:bg-brand-600 text-white font-bold transition-colors">
                    احجز فني دلوقتي
                </a>
            </div>
        @endif
    </section>

    {{-- شريط تطمين + CTA --}}
    @if ($areas->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 pb-16 md:pb-20">
            <div class="rounded-2xl bg-surface border border-line p-7 md:p-9 flex flex-col md:flex-row md:items-center justify-between gap-5">
                <div>
                    <h2 class="text-xl md:text-2xl font-extrabold mb-1">مش لاقي منطقتك؟</h2>
                    <p class="text-muted leading-relaxed">بنغطّي {{ site('areas_label') }} والمناطق المحيطة. كلّمنا بعنوانك ونشوف أقرب معاد نوصلك فيه.</p>
                </div>
                <div class="flex flex-wrap gap-3 shrink-0">
                    <a href="{{ route('request-service') }}"
                       class="inline-flex items-center px-6 py-3 rounded-full bg-brand hover:bg-brand-600 text-white font-bold transition-colors">
                        اطلب خدمة
                    </a>
                    <a href="https://wa.me/{{ site('whatsapp') }}" target="_blank" rel="noopener"
                       class="inline-flex items-center px-6 py-3 rounded-full border border-line hover:border-brand hover:text-brand font-bold transition-colors">
                        اسأل على واتساب
                    </a>
                </div>
            </div>
        </section>
    @endif
@endsection
