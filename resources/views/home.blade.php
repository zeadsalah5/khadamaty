@extends('layouts.app')

@php
    // الأسئلة الشائعة — من جدول faqs (fallback للإعدادات). على الرئيسية أول ٤ المفعّلة.
    $faqs = \App\Support\Catalog::homeFaqs(4);

    // slugs الخدمات المنشورة — عشان كروت القسم تربط بصفحات الخدمة الموجودة بس.
    $publishedServiceSlugs = \App\Models\Service::query()->published()->pluck('slug')->all();

    // محتوى الأقسام من الإعدادات/الجداول (كلها لها fallback).
    $serviceSections = (array) site('service_sections', []);
    $howSteps = (array) site('how_steps', []);
    $whyUs = (array) site('why_us', []);
    $packages = \App\Support\Catalog::packages();
    $areas = (array) site('areas', []);

    // ترتيب الأقسام وإظهارها (من الداشبورد) — الأقسام المفعّلة بالترتيب.
    $sections = collect(site('home_sections', []))
        ->filter(fn ($s) => ! empty($s['enabled']) && ! empty($s['key']))
        ->pluck('key')
        ->all();

    // روابط المناطق المنشورة (تتحوّل للينك في قسم المناطق).
    $areaLinks = \App\Models\Area::query()->published()->pluck('slug', 'name_ar');
@endphp

@push('schema')
    <x-schema.faq-page :items="$faqs" />
@endpush

@section('content')

@foreach ($sections as $key)
    @switch($key)

        @case('hero')
            <x-hero-slider />
            @break

        @case('services_1')
            @if (isset($serviceSections[0]))
                <x-services-section :section="$serviceSections[0]" :published-slugs="$publishedServiceSlugs" />
            @endif
            @break

        @case('how')
            <section id="how" class="bg-surface scroll-mt-20">
                <div class="mx-auto max-w-6xl px-4 py-16 md:py-20">
                    <div class="max-w-2xl mb-10">
                        <h2 class="text-3xl md:text-4xl font-extrabold mb-3">إزاي بنشتغل</h2>
                        <p class="text-muted text-lg">ثلاث خطوات بسيطة، من غير تعقيد.</p>
                    </div>
                    <ol class="grid gap-6 md:grid-cols-3">
                        @foreach ($howSteps as $i => $step)
                            <li class="relative rounded-2xl bg-white border border-line p-7">
                                <span class="absolute -top-4 start-7 w-9 h-9 rounded-full bg-brand text-white font-bold flex items-center justify-center text-lg">
                                    {{ $i + 1 }}
                                </span>
                                <h3 class="text-xl font-bold mt-3 mb-2">{{ $step['title'] }}</h3>
                                <p class="text-muted leading-relaxed">{{ $step['desc'] }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>
            @break

        @case('services_2')
            @if (isset($serviceSections[1]))
                <x-services-section :section="$serviceSections[1]" :published-slugs="$publishedServiceSlugs" />
            @endif
            @break

        @case('services_3')
            @if (isset($serviceSections[2]))
                <x-services-section :section="$serviceSections[2]" :alt="true" :published-slugs="$publishedServiceSlugs" />
            @endif
            @break

        @case('services_link')
            @if (! empty($publishedServiceSlugs))
                <div class="mx-auto max-w-6xl px-4 -mt-6 pb-4">
                    <a href="{{ route('services.index') }}"
                       class="inline-flex items-center gap-1.5 text-brand font-bold hover:gap-2.5 transition-all">
                        شوف كل الخدمات بالتفصيل
                        <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                    </a>
                </div>
            @endif
            @break

        @case('pricing')
            <section id="pricing" class="mx-auto max-w-6xl px-4 py-16 md:py-20 scroll-mt-20">
                <div class="max-w-2xl mb-10">
                    <h2 class="text-3xl md:text-4xl font-extrabold mb-3">الباقات والأسعار</h2>
                    <p class="text-muted text-lg">أسعار واضحة من غير مفاجآت. الأرقام تقديرية وبتتأكد وقت المعاينة.</p>
                </div>
                <div class="grid gap-6 md:grid-cols-3 items-stretch">
                    @foreach ($packages as $pkg)
                        <div class="relative rounded-2xl border p-7 flex flex-col
                                    {{ ! empty($pkg['featured']) ? 'border-brand shadow-xl shadow-brand/10' : 'border-line' }}">
                            @if (! empty($pkg['featured']))
                                <span class="absolute -top-3 end-7 bg-accent text-white text-xs font-bold px-3 py-1 rounded-full">الأكثر طلباً</span>
                            @endif
                            <h3 class="text-xl font-bold mb-1">{{ $pkg['name'] }}</h3>
                            <div class="flex items-end gap-1.5 my-4">
                                <span class="text-4xl font-extrabold text-brand">{{ $pkg['price'] }}</span>
                                <span class="text-muted text-sm mb-1">{{ $pkg['unit'] }}</span>
                            </div>
                            <ul class="space-y-3 text-sm mb-7 flex-1">
                                @foreach ($pkg['features'] as $feature)
                                    <li class="flex items-start gap-2.5">
                                        <svg viewBox="0 0 24 24" class="w-5 h-5 shrink-0 text-accent mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            <a href="#booking"
                               class="block text-center py-3 rounded-full font-bold transition-colors
                                      {{ ! empty($pkg['featured']) ? 'bg-brand hover:bg-brand-600 text-white' : 'border border-line hover:border-brand hover:text-brand' }}">
                                احجز الباقة
                            </a>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-muted mt-4">* الأسعار المعروضة مبدئية (placeholder) — عدّلها من ملف الإعدادات.</p>
            </section>
            @break

        @case('why')
            <section class="bg-navy text-white scroll-mt-20">
                <div class="mx-auto max-w-6xl px-4 py-16 md:py-20">
                    <div class="max-w-2xl mb-10">
                        <h2 class="text-3xl md:text-4xl font-extrabold mb-3">ليه تختارنا؟</h2>
                        <p class="text-white/70 text-lg">حاجات بسيطة بتفرق في تجربتك معانا.</p>
                    </div>
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($whyUs as $item)
                            <div class="rounded-2xl bg-navy-700/60 border border-white/10 p-6">
                                <div class="w-11 h-11 rounded-xl bg-accent/20 text-accent flex items-center justify-center mb-4">
                                    <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                </div>
                                <h3 class="font-bold text-lg mb-1.5">{{ $item['t'] }}</h3>
                                <p class="text-white/70 text-sm leading-relaxed">{{ $item['d'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
            @break

        @case('reviews')
            {{-- التقييمات الحقيقية المعتمدة بتظهر في صفحة /reviews — متحطّش تقييمات وهمية --}}
            <section class="mx-auto max-w-6xl px-4 py-16 md:py-20">
                <div class="rounded-2xl border border-dashed border-line bg-surface text-center py-14 px-4">
                    <h2 class="text-2xl md:text-3xl font-extrabold mb-2">آراء العملاء</h2>
                    <p class="text-muted mb-5">بنعرض آراء عملائنا الحقيقية بعد مراجعتها. شوف صفحة الآراء أو كن أول من يقيّمنا.</p>
                    <a href="{{ route('reviews') }}"
                       class="inline-flex items-center gap-1.5 text-brand font-bold hover:gap-2.5 transition-all">
                        صفحة آراء العملاء
                        <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                    </a>
                </div>
            </section>
            @break

        @case('areas')
            <section id="areas" class="bg-surface scroll-mt-20">
                <div class="mx-auto max-w-6xl px-4 py-16 md:py-20">
                    <div class="max-w-2xl mb-8">
                        <h2 class="text-3xl md:text-4xl font-extrabold mb-3">مناطق بنغطيها</h2>
                        <p class="text-muted text-lg">بنخدم {{ site('areas_label') }} والمناطق المحيطة. مش لاقي منطقتك؟ كلّمنا.</p>
                    </div>

                    {{-- المناطق اللي ليها صفحة منشورة بتتحوّل لرابط؛ الباقي شارة اسم. --}}
                    <ul class="flex flex-wrap gap-2.5">
                        @foreach ($areas as $area)
                            <li>
                                @if ($areaLinks->has($area))
                                    <a href="{{ '/areas/' . $areaLinks->get($area) }}"
                                       class="inline-block bg-white border border-line rounded-full px-4 py-2 text-sm font-medium hover:border-brand hover:text-brand transition-colors">{{ $area }}</a>
                                @else
                                    <span class="inline-block bg-white border border-line rounded-full px-4 py-2 text-sm font-medium">{{ $area }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    <div class="flex flex-wrap gap-5 mt-7">
                        <a href="{{ route('areas.index') }}"
                           class="inline-flex items-center gap-1.5 text-brand font-bold hover:gap-2.5 transition-all">
                            كل مناطق الخدمة
                            <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                        </a>
                        <a href="{{ route('brands.index') }}"
                           class="inline-flex items-center gap-1.5 text-brand font-bold hover:gap-2.5 transition-all">
                            بنصلّح كل الماركات
                            <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                        </a>
                    </div>
                </div>
            </section>
            @break

        @case('faq')
            <section id="faq" class="mx-auto max-w-6xl px-4 py-16 md:py-20 scroll-mt-20">
                <div class="max-w-2xl mb-10">
                    <h2 class="text-3xl md:text-4xl font-extrabold mb-3">الأسئلة الشائعة</h2>
                    <p class="text-muted text-lg">أكتر أسئلة بتتسأل. مش لاقي إجابتك؟ ابعتلنا واتساب.</p>
                </div>
                <div class="max-w-3xl space-y-3">
                    @foreach ($faqs as $faq)
                        <details class="group rounded-xl border border-line bg-white p-5">
                            <summary class="flex items-center justify-between cursor-pointer font-bold text-lg">
                                {{ $faq['q'] }}
                                <svg class="faq-icon w-5 h-5 text-brand transition-transform shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                            </summary>
                            <p class="text-muted leading-relaxed mt-3">{{ $faq['a'] }}</p>
                        </details>
                    @endforeach
                </div>
                <a href="{{ route('faq') }}"
                   class="inline-flex items-center gap-1.5 mt-6 text-brand font-bold hover:gap-2.5 transition-all">
                    شوف كل الأسئلة الشائعة
                    <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                </a>
            </section>
            @break

        @case('booking')
            <section id="booking" class="bg-navy scroll-mt-20">
                <div class="mx-auto max-w-4xl px-4 py-16 md:py-20">
                    <x-booking-form :show-appliance="false" />
                </div>
            </section>
            @break

    @endswitch
@endforeach

@endsection
