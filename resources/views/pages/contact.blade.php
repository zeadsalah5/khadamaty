@extends('layouts.app')

{{--
    صفحة "اتصل بينا" — طرق التواصل + مناطق الخدمة + تحويل لطلب الخدمة.
    متغيرات: $breadcrumbs
--}}

@section('content')
    <x-page-hero
        title="اتصل بينا"
        subtitle="أي وقت تحتاجنا، إحنا قريّبين. اختار الطريقة اللي تريّحك — مكالمة، واتساب، أو تبعتلنا طلب خدمة وإحنا نرجعلك."
        :breadcrumbs="$breadcrumbs" />

    {{-- طرق التواصل --}}
    <section class="mx-auto max-w-6xl px-4 pb-4">
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            {{-- اتصال --}}
            <a href="tel:{{ config('site.phone_tel') }}"
               class="group rounded-2xl border border-line bg-white p-6 hover:border-brand transition-colors">
                <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand flex items-center justify-center mb-4 group-hover:bg-brand group-hover:text-white transition-colors">
                    <svg viewBox="0 0 24 24" class="w-6 h-6" fill="currentColor" aria-hidden="true"><path d="M6.6 10.8a15.5 15.5 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25c1.1.37 2.3.57 3.6.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.3.2 2.5.57 3.6a1 1 0 0 1-.25 1l-2.2 2.2Z"/></svg>
                </div>
                <h2 class="font-bold text-lg mb-1">اتصل بينا</h2>
                <p class="text-muted text-sm mb-2">كلّمنا دلوقتي ونظبّتلك المعاد.</p>
                <span class="font-extrabold text-brand" dir="ltr">{{ config('site.phone') }}</span>
            </a>

            {{-- واتساب --}}
            <a href="https://wa.me/{{ config('site.whatsapp') }}" target="_blank" rel="noopener"
               class="group rounded-2xl border border-line bg-white p-6 hover:border-accent transition-colors">
                <div class="w-12 h-12 rounded-xl bg-accent/10 text-accent flex items-center justify-center mb-4 group-hover:bg-accent group-hover:text-white transition-colors">
                    <svg viewBox="0 0 24 24" class="w-6 h-6" fill="currentColor" aria-hidden="true"><path d="M12 2a9.9 9.9 0 0 0-8.5 15l-1.3 4.8 4.9-1.3A10 10 0 1 0 12 2Zm5.8 14.2c-.2.7-1.4 1.3-2 1.4-.5.1-1.2.1-1.9-.1-.4-.1-1-.3-1.8-.6-3-1.3-5-4.4-5.1-4.6-.2-.2-1.3-1.7-1.3-3.2S6.5 7 6.8 6.7a1 1 0 0 1 .7-.3h.5c.2 0 .4 0 .6.5l.8 1.9c.1.2.1.4 0 .6l-.4.5c-.2.2-.3.4-.1.7.2.3.9 1.4 1.9 2.3 1.3 1.1 2.3 1.5 2.6 1.6.3.1.5.1.6-.1l.7-.8c.2-.3.4-.2.7-.1l1.9.9c.3.1.5.2.6.3.1.3.1.6-.1 1.3Z"/></svg>
                </div>
                <h2 class="font-bold text-lg mb-1">واتساب</h2>
                <p class="text-muted text-sm mb-2">ابعتلنا رسالة بصورة الجهاز أو العطل.</p>
                <span class="font-bold text-accent">افتح المحادثة</span>
            </a>

            {{-- بريد (يظهر بس لو متحدد في الإعدادات) --}}
            @if (config('site.email'))
                <a href="mailto:{{ config('site.email') }}"
                   class="group rounded-2xl border border-line bg-white p-6 hover:border-brand transition-colors">
                    <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand flex items-center justify-center mb-4 group-hover:bg-brand group-hover:text-white transition-colors">
                        <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                    </div>
                    <h2 class="font-bold text-lg mb-1">البريد الإلكتروني</h2>
                    <p class="text-muted text-sm mb-2">للاستفسارات والعروض للشركات.</p>
                    <span class="font-bold text-brand" dir="ltr">{{ config('site.email') }}</span>
                </a>
            @endif

            {{-- طلب خدمة --}}
            <a href="{{ route('request-service') }}"
               class="group rounded-2xl border border-line bg-white p-6 hover:border-brand transition-colors">
                <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand flex items-center justify-center mb-4 group-hover:bg-brand group-hover:text-white transition-colors">
                    <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18M9 15l2 2 4-4"/></svg>
                </div>
                <h2 class="font-bold text-lg mb-1">ابعت طلب خدمة</h2>
                <p class="text-muted text-sm mb-2">املأ البيانات وإحنا نرجعلك نأكد المعاد.</p>
                <span class="font-bold text-brand">افتح الفورم</span>
            </a>
        </div>
    </section>

    {{-- مناطق + ملاحظة الرد --}}
    <section class="mx-auto max-w-6xl px-4 py-12 md:py-16 grid gap-10 md:grid-cols-2">
        <div>
            <h2 class="text-2xl md:text-3xl font-extrabold mb-4">بنغطي فين</h2>
            <p class="text-muted mb-4 leading-relaxed">بنخدم {{ config('site.areas_label') }} والمناطق المحيطة. مش لاقي منطقتك؟ كلّمنا ونشوف نقدر نوصلك إزاي.</p>
            <ul class="flex flex-wrap gap-2.5">
                @foreach (config('site.areas') as $area)
                    <li class="bg-surface border border-line rounded-full px-4 py-2 text-sm font-medium">{{ $area }}</li>
                @endforeach
            </ul>
        </div>
        <div class="rounded-2xl bg-navy text-white p-7 md:p-8 flex flex-col justify-center">
            <h2 class="text-xl md:text-2xl font-extrabold mb-2">مواعيد الوصول</h2>
            <p class="text-white/75 leading-relaxed mb-5">
                بنحاول نوصلك في نفس اليوم حسب توفر الفنيين في منطقتك. احجز بدري عشان نضمنلك أقرب معاد.
            </p>
            <a href="{{ route('request-service') }}"
               class="inline-flex self-start items-center px-6 py-3 rounded-full bg-brand hover:bg-brand-600 font-bold transition-colors">
                احجز فني دلوقتي
            </a>
        </div>
    </section>
@endsection
