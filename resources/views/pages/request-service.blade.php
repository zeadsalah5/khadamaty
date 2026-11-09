@extends('layouts.app')

{{--
    صفحة "اطلب خدمة" — نفس فورم الحجز مع اختيار الخدمة والجهاز والمنطقة.
    القسم ليه id="booking" عشان الأزرار العائمة و data-service يوصّلوا هنا.
    متغيرات: $preselect (خدمة مبدئية), $breadcrumbs
--}}

@section('content')
    <x-page-hero
        title="اطلب خدمة"
        subtitle="قولنا محتاج إيه وفين وإمتى، وإحنا نرجعلك نأكد المعاد والسعر التقريبي. الدفع بعد ما الشغل يخلص."
        :breadcrumbs="$breadcrumbs" />

    {{-- مميزات سريعة --}}
    <section class="mx-auto max-w-4xl px-4 pb-8">
        <ul class="grid gap-3 sm:grid-cols-3 text-sm">
            @foreach ([
                'فني متخصص يوصلك في المعاد',
                'سعر واضح قبل ما نبدأ',
                'الدفع بعد ما تتأكد',
            ] as $perk)
                <li class="flex items-center gap-2.5 rounded-xl border border-line bg-white px-4 py-3">
                    <svg viewBox="0 0 24 24" class="w-5 h-5 shrink-0 text-accent" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                    <span class="font-medium">{{ $perk }}</span>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- الفورم --}}
    <section id="booking" class="scroll-mt-20 pb-16 md:pb-20">
        <div class="mx-auto max-w-4xl px-4">
            <x-booking-form
                heading="املأ طلب الخدمة"
                sub="كل الخانات اللي عليها علامة (*) مطلوبة، والباقي بيساعدنا نجهّز أكتر."
                :service="$preselect" />
        </div>
    </section>

    {{-- طرق تواصل بديلة --}}
    <section class="mx-auto max-w-4xl px-4 pb-16 md:pb-20">
        <div class="rounded-2xl border border-line bg-surface p-6 text-center">
            <p class="text-muted mb-4">تفضّل تكلّمنا على طول؟</p>
            <div class="flex flex-wrap justify-center gap-3">
                <a href="tel:{{ site('phone_tel') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-full border border-line hover:border-brand hover:text-brand font-bold transition-colors">
                    اتصل: {{ site('phone') }}
                </a>
                <a href="https://wa.me/{{ site('whatsapp') }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-accent hover:bg-accent-600 text-white font-bold transition-colors">
                    واتساب
                </a>
            </div>
        </div>
    </section>
@endsection
