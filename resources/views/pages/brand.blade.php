@extends('layouts.app')

{{--
    صفحة ماركة مفردة (/brands/{slug}) — المرحلة 3.
    متغيرات: $brand, $title, $subtitle, $canonical, $services, $serviceIcons,
             $areas, $breadcrumbs
    ملاحظة: اسم نصّي فقط — من غير لوجو رسمي. لو اتضاف logo لاحقاً بيظهر بدل الحرف.
--}}

@push('schema')
    <x-schema.service
        :name="'صيانة وإصلاح تكييفات ' . $brand->name_ar"
        :description="$brand->seo_description ?: $brand->excerpt"
        :url="$canonical" />

    @if (! empty($brand->faqs))
        <x-schema.faq-page :items="$brand->faqs" />
    @endif
@endpush

@section('content')

    {{-- ============ الهيرو ============ --}}
    <x-breadcrumbs :items="$breadcrumbs" />

    <header class="mx-auto max-w-6xl px-4 pt-6 pb-10 md:pb-12">
        <div class="flex items-start gap-4 md:gap-5">
            {{-- مكان اللوجو: صورة لو موجودة، وإلا أول حرف من اسم الماركة --}}
            <div class="shrink-0 w-16 h-16 md:w-20 md:h-20 rounded-2xl bg-surface border border-line flex items-center justify-center overflow-hidden">
                @if (filled($brand->logo))
                    <x-img :src="'images/brands/' . $brand->logo" :alt="$brand->name_ar" class="w-full h-full object-contain p-2" />
                @else
                    <span class="text-2xl md:text-3xl font-extrabold text-brand" aria-hidden="true">{{ mb_substr($brand->name_ar, 0, 1) }}</span>
                @endif
            </div>
            <div class="min-w-0">
                <p class="text-muted font-semibold mb-1">صيانة وإصلاح تكييفات</p>
                <h1 class="text-3xl md:text-4xl font-extrabold mb-3">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="text-muted text-lg leading-relaxed max-w-2xl">{{ $subtitle }}</p>
                @endif
            </div>
        </div>

        {{-- شريط توضيح: مش توكيل --}}
        <p class="mt-6 inline-flex items-start gap-2 rounded-xl bg-surface border border-line px-4 py-3 text-sm text-muted leading-relaxed max-w-2xl">
            <svg viewBox="0 0 24 24" class="w-5 h-5 shrink-0 text-brand mt-0.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
            <span>إحنا مش توكيل {{ $title }} ولا أي ماركة — بنصلّح كل الماركات. لو جهازك لسه في ضمان الوكيل يُفضّل ترجعله عشان الضمان ما يسقطش.</span>
        </p>

        <div class="flex flex-wrap gap-3 mt-7">
            <a href="#booking"
               class="inline-flex items-center px-6 py-3 rounded-full bg-brand hover:bg-brand-600 text-white font-bold transition-colors">
                احجز فني
            </a>
            <a href="tel:{{ site('phone_tel') }}"
               class="inline-flex items-center gap-2 px-6 py-3 rounded-full border border-line hover:border-brand hover:text-brand font-bold transition-colors">
                <svg viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor" aria-hidden="true"><path d="M6.6 10.8a15.5 15.5 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25c1.1.37 2.3.57 3.6.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.3.2 2.5.57 3.6a1 1 0 0 1-.25 1l-2.2 2.2Z"/></svg>
                اتصل: {{ site('phone') }}
            </a>
            <a href="https://wa.me/{{ site('whatsapp') }}" target="_blank" rel="noopener"
               class="inline-flex items-center px-6 py-3 rounded-full border border-line hover:border-accent hover:text-accent font-bold transition-colors">
                اسأل على واتساب
            </a>
        </div>
    </header>

    {{-- ============ المقدمة ============ --}}
    @if (filled($brand->intro))
        <section class="mx-auto max-w-6xl px-4 pb-4">
            <div class="max-w-3xl space-y-4 text-lg leading-loose text-ink/90">
                @foreach (preg_split('/\n\s*\n/', trim($brand->intro)) as $para)
                    <p>{{ $para }}</p>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============ أنواع الأجهزة ============ --}}
    @if (! empty($brand->device_types))
        <section class="mx-auto max-w-6xl px-4 pt-8">
            <h2 class="text-xl md:text-2xl font-extrabold mb-4">أنواع أجهزة {{ $title }} اللي بنتعامل معاها</h2>
            <ul class="flex flex-wrap gap-2.5">
                @foreach ($brand->device_types as $type)
                    <li class="bg-surface border border-line rounded-full px-4 py-2 text-sm font-medium">{{ $type }}</li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- ============ الأعطال الشائعة ============ --}}
    @if (! empty($brand->common_faults))
        <section class="bg-surface scroll-mt-20 mt-10">
            <div class="mx-auto max-w-6xl px-4 py-12 md:py-16">
                <div class="max-w-2xl mb-6">
                    <h2 class="text-2xl md:text-3xl font-extrabold mb-2">الأعطال الشائعة في تكييفات {{ $title }}</h2>
                    <p class="text-muted leading-relaxed">أكتر مشاكل بنقابلها — بنكشف ونحدد السبب الحقيقي قبل أي إصلاح.</p>
                </div>
                <ul class="grid gap-3 sm:grid-cols-2">
                    @foreach ($brand->common_faults as $fault)
                        <li class="flex items-start gap-3 rounded-xl bg-white border border-line px-5 py-4">
                            <span class="shrink-0 w-6 h-6 rounded-full bg-brand-50 text-brand flex items-center justify-center mt-0.5" aria-hidden="true">
                                <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
                            </span>
                            <span class="leading-relaxed">{{ $fault }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- ============ نصايح صيانة ============ --}}
    @if (! empty($brand->maintenance_tips))
        <section class="mx-auto max-w-6xl px-4 py-12 md:py-16">
            <h2 class="text-2xl md:text-3xl font-extrabold mb-6">نصايح للحفاظ على تكييف {{ $title }}</h2>
            <ul class="grid gap-3 sm:grid-cols-2">
                @foreach ($brand->maintenance_tips as $tip)
                    <li class="flex items-start gap-3 rounded-xl border border-line bg-white px-5 py-4">
                        <svg viewBox="0 0 24 24" class="w-5 h-5 shrink-0 text-accent mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                        <span class="leading-relaxed">{{ $tip }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- ============ الخدمات المناسبة ============ --}}
    @if ($services->isNotEmpty())
        <section class="bg-surface scroll-mt-20">
            <div class="mx-auto max-w-6xl px-4 py-12 md:py-16">
                <h2 class="text-2xl md:text-3xl font-extrabold mb-6">خدمات بنقدّمها لتكييفات {{ $title }}</h2>
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($services as $svc)
                        @php
                            // لو فيه صفحة (ماركة × خدمة) منشورة، نوصّل ليها بدل صفحة الخدمة العامة.
                            $hasBrandPage = in_array($svc->slug, $brandServiceSlugs ?? [], true);
                            $svcUrl = $hasBrandPage
                                ? '/brands/' . $brand->slug . '/' . $svc->slug
                                : '/services/' . $svc->slug;
                        @endphp
                        <a href="{{ $svcUrl }}"
                           class="group flex flex-col rounded-2xl border border-line bg-white p-6 hover:border-brand hover:shadow-lg hover:shadow-brand/5 transition-all">
                            <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand flex items-center justify-center mb-4 group-hover:bg-brand group-hover:text-white transition-colors">
                                <x-service-icon :name="$serviceIcons[$svc->slug] ?? 'maintenance'" />
                            </div>
                            <h3 class="text-lg font-bold mb-2">{{ $hasBrandPage ? $svc->name_ar . ' ' . $brand->name_ar : $svc->name_ar }}</h3>
                            @if ($svc->excerpt)
                                <p class="text-muted text-sm leading-relaxed flex-1">{{ $svc->excerpt }}</p>
                            @endif
                            <span class="inline-flex items-center gap-1.5 text-brand font-bold text-sm mt-4 group-hover:gap-2.5 transition-all">
                                التفاصيل
                                <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============ منتجات الماركة في المتجر ============ --}}
    @if ($products->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 py-12 md:py-16">
            <div class="mb-6 flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-extrabold md:text-3xl">منتجات {{ $title }} في المتجر</h2>
                    <p class="mt-2 leading-relaxed text-muted">تكييفات وقطع غيار {{ $title }} المتاحة عندنا دلوقتي.</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 sm:gap-5 lg:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============ مناطق الخدمة ============ --}}
    @if ($areas->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 py-12 md:py-16">
            <div class="max-w-2xl mb-6">
                <h2 class="text-2xl md:text-3xl font-extrabold mb-2">بنصلّح {{ $title }} في منطقتك</h2>
                <p class="text-muted leading-relaxed">بنغطّي {{ site('areas_label') }} والمناطق المحيطة. اختر منطقتك واعرف التفاصيل.</p>
            </div>
            <ul class="flex flex-wrap gap-2.5 mb-5">
                @foreach ($areas as $area)
                    <li>
                        <a href="{{ '/areas/' . $area->slug }}"
                           class="inline-block bg-surface border border-line rounded-full px-4 py-2 text-sm font-medium hover:border-brand hover:text-brand transition-colors">
                            {{ $area->name_ar }}
                        </a>
                    </li>
                @endforeach
            </ul>
            <a href="{{ route('areas.index') }}"
               class="inline-flex items-center gap-1.5 text-brand font-bold text-sm hover:gap-2.5 transition-all">
                كل مناطق الخدمة
                <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            </a>
        </section>
    @endif

    {{-- ============ الأسئلة الشائعة ============ --}}
    @if (! empty($brand->faqs))
        <section class="bg-surface scroll-mt-20">
            <div class="mx-auto max-w-6xl px-4 py-12 md:py-16">
                <h2 class="text-2xl md:text-3xl font-extrabold mb-6">أسئلة شائعة عن تكييفات {{ $title }}</h2>
                <div class="max-w-3xl space-y-3">
                    @foreach ($brand->faqs as $faq)
                        <details class="group rounded-xl border border-line bg-white p-5">
                            <summary class="flex items-center justify-between cursor-pointer font-bold text-lg">
                                {{ $faq['q'] ?? '' }}
                                <svg class="faq-icon w-5 h-5 text-brand transition-transform shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                            </summary>
                            <p class="text-muted leading-relaxed mt-3">{{ $faq['a'] ?? '' }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============ فورم الحجز ============ --}}
    <section id="booking" class="bg-navy scroll-mt-20">
        <div class="mx-auto max-w-4xl px-4 py-16 md:py-20">
            <x-booking-form
                :heading="'احجز صيانة ' . $title"
                sub="قولنا بياناتك والمنطقة والوقت المناسب، وهنتواصل معاك نأكد المعاد والسعر التقريبي." />
        </div>
    </section>

@endsection
