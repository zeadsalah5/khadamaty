@extends('layouts.app')

{{--
    صفحة الأسئلة الشائعة.
    متغيرات: $faqs (من config/site.faqs), $breadcrumbs
    بتطبع FAQPage schema من نفس مصدر العرض.
--}}

@push('schema')
    <x-schema.faq-page :items="$faqs" />
@endpush

@section('content')
    <x-page-hero
        title="الأسئلة الشائعة"
        subtitle="جمعنا لك أكتر الأسئلة اللي بتتكرر قبل الحجز. لو لقيت سؤالك هنا، تمام — ولو لأ، إحنا على بُعد رسالة."
        :breadcrumbs="$breadcrumbs" />

    <section class="mx-auto max-w-3xl px-4 pb-4">
        <div class="space-y-3">
            @foreach ($faqs as $faq)
                <details class="group rounded-xl border border-line bg-white p-5 open:border-brand/40">
                    <summary class="flex items-center justify-between cursor-pointer gap-4 font-bold text-lg">
                        {{ $faq['q'] }}
                        <svg class="faq-icon w-5 h-5 text-brand transition-transform shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </summary>
                    <p class="text-muted leading-relaxed mt-3">{{ $faq['a'] }}</p>
                </details>
            @endforeach
        </div>
    </section>

    {{-- مازال عندك سؤال؟ --}}
    <section class="mx-auto max-w-3xl px-4 pb-16 md:pb-20">
        <div class="rounded-2xl bg-navy text-white p-7 md:p-9 text-center">
            <h2 class="text-2xl md:text-3xl font-extrabold mb-2">لسه عندك سؤال؟</h2>
            <p class="text-white/70 mb-6 max-w-xl mx-auto leading-relaxed">
                كلّمنا على واتساب أو اتصل بينا، أو ابعتلنا طلب خدمة وهنرجعلك نأكد كل التفاصيل.
            </p>
            <div class="flex flex-wrap justify-center gap-3">
                <a href="https://wa.me/{{ config('site.whatsapp') }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-accent hover:bg-accent-600 font-bold transition-colors">
                    اسأل على واتساب
                </a>
                <a href="{{ url('/') }}#booking"
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-brand hover:bg-brand-600 font-bold transition-colors">
                    احجز فني دلوقتي
                </a>
            </div>
        </div>
    </section>
@endsection
